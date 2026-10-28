<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Party;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Generate the next sequential invoice or quotation number.
     */
    public function generateNumber(string $type = 'sale'): string
    {
        $business = Business::current();
        $prefix = match ($type) {
            'quotation' => $business->quotation_prefix ?: 'EST-',
            'sale_return' => 'RET-',
            default => $business->invoice_prefix ?: 'INV-',
        };

        $lastInvoice = Invoice::where('invoice_type', $type)
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        $nextNum = 1;
        if ($lastInvoice) {
            $currentStr = str_replace($prefix, '', $lastInvoice->invoice_number);
            if (is_numeric($currentStr)) {
                $nextNum = (int) $currentStr + 1;
            }
        }

        return $prefix.str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create an invoice with items, update inventory, and sync party balance.
     *
     * @param  array<string, mixed>  $data
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data): Invoice {
            $business = Business::current();
            $party = Party::findOrFail($data['party_id']);

            $isInterState = ! empty($party->state_code)
                && ! empty($business->state_code)
                && $party->state_code !== $business->state_code;

            $taxType = $isInterState ? 'inter_state' : 'intra_state';

            $invoice = Invoice::create([
                'invoice_type' => $data['invoice_type'] ?? 'sale',
                'invoice_number' => $data['invoice_number'] ?? $this->generateNumber($data['invoice_type'] ?? 'sale'),
                'party_id' => $party->id,
                'date' => $data['date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'tax_type' => $taxType,
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'discount_value' => $data['discount_value'] ?? 0,
                'discount_amount' => 0,
                'subtotal' => 0,
                'cgst_amount' => 0,
                'sgst_amount' => 0,
                'igst_amount' => 0,
                'tax_amount' => 0,
                'round_off' => $data['round_off'] ?? 0,
                'total_amount' => 0,
                'paid_amount' => $data['paid_amount'] ?? 0,
                'balance_amount' => 0,
                'payment_status' => 'unpaid',
                'payment_mode' => $data['payment_mode'] ?? 'cash',
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? $business->terms_and_conditions,
            ]);

            $subtotal = 0.00;
            $totalTax = 0.00;
            $items = $data['items'] ?? [];

            foreach ($items as $row) {
                if (empty($row['item_name']) && empty($row['item_id'])) {
                    continue;
                }

                $qty = (float) ($row['quantity'] ?? 1);
                $unitPrice = (float) ($row['unit_price'] ?? 0);
                $discPercent = (float) ($row['discount_percent'] ?? 0);
                $taxPercent = (float) ($row['tax_percent'] ?? 0);

                $grossLine = $qty * $unitPrice;
                $discAmount = ($grossLine * $discPercent) / 100;
                $taxableLine = $grossLine - $discAmount;
                $taxAmount = ($taxableLine * $taxPercent) / 100;
                $lineTotal = $taxableLine + $taxAmount;

                $subtotal += $taxableLine;
                $totalTax += $taxAmount;

                $itemModel = ! empty($row['item_id']) ? Item::find($row['item_id']) : null;
                $itemName = $itemModel ? $itemModel->name : ($row['item_name'] ?? 'Item');
                $hsn = $itemModel ? $itemModel->hsn_code : ($row['hsn_code'] ?? null);
                $unit = $itemModel ? $itemModel->unit : ($row['unit'] ?? 'PCS');

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $itemModel?->id,
                    'item_name' => $itemName,
                    'hsn_code' => $hsn,
                    'unit' => $unit,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discPercent,
                    'discount_amount' => $discAmount,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'total' => $lineTotal,
                ]);

                if ($itemModel && $invoice->invoice_type !== 'quotation') {
                    $itemModel->syncStock();
                }
            }

            // Overall discount calculation
            $overallDiscount = 0.00;
            if (($data['discount_type'] ?? 'fixed') === 'percentage') {
                $overallDiscount = ($subtotal * (float) ($data['discount_value'] ?? 0)) / 100;
            } else {
                $overallDiscount = (float) ($data['discount_value'] ?? 0);
            }

            $cgst = 0.00;
            $sgst = 0.00;
            $igst = 0.00;

            if ($taxType === 'inter_state') {
                $igst = $totalTax;
            } else {
                $cgst = $totalTax / 2;
                $sgst = $totalTax / 2;
            }

            $totalAmount = max(0, $subtotal - $overallDiscount + $totalTax + (float) ($data['round_off'] ?? 0));
            $paidAmount = min($totalAmount, (float) ($data['paid_amount'] ?? 0));
            $balanceAmount = max(0, $totalAmount - $paidAmount);

            $paymentStatus = 'unpaid';
            if ($balanceAmount <= 0 && $totalAmount > 0) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'discount_amount' => $overallDiscount,
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'tax_amount' => $totalTax,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $balanceAmount,
                'payment_status' => $paymentStatus,
            ]);

            // If immediate payment received on sale, record in payments
            if ($paidAmount > 0 && $invoice->invoice_type === 'sale') {
                Payment::create([
                    'payment_type' => 'payment_in',
                    'payment_number' => 'RCPT-'.$invoice->invoice_number,
                    'party_id' => $party->id,
                    'invoice_id' => $invoice->id,
                    'date' => $invoice->date,
                    'amount' => $paidAmount,
                    'payment_mode' => $data['payment_mode'] ?? 'cash',
                    'notes' => 'Received against '.$invoice->invoice_number,
                ]);
            }

            if ($invoice->invoice_type !== 'quotation') {
                $party->syncBalance();
            }

            return $invoice;
        });
    }

    /**
     * Delete an invoice, restore inventory and party balance.
     */
    public function deleteInvoice(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice): void {
            $party = $invoice->party;
            $items = $invoice->items()->get();

            // Delete associated payments
            $invoice->payments()->delete();
            $invoice->items()->delete();
            $invoice->delete();

            foreach ($items as $invItem) {
                if ($invItem->item) {
                    $invItem->item->syncStock();
                }
            }

            if ($party) {
                $party->syncBalance();
            }
        });
    }
}
