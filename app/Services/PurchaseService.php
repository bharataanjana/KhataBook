<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Item;
use App\Models\Party;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Generate the next sequential purchase bill number.
     */
    public function generateNumber(string $type = 'purchase'): string
    {
        $business = Business::current();
        $prefix = $type === 'purchase_return' ? 'PRT-' : ($business->purchase_prefix ?: 'PUR-');

        $lastPurchase = Purchase::where('purchase_type', $type)
            ->where('bill_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        $nextNum = 1;
        if ($lastPurchase) {
            $currentStr = str_replace($prefix, '', $lastPurchase->bill_number);
            if (is_numeric($currentStr)) {
                $nextNum = (int) $currentStr + 1;
            }
        }

        return $prefix.str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a purchase bill with items, increase stock, and sync supplier balance.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data): Purchase {
            $business = Business::current();
            $party = Party::findOrFail($data['party_id']);

            $isInterState = ! empty($party->state_code)
                && ! empty($business->state_code)
                && $party->state_code !== $business->state_code;

            $taxType = $isInterState ? 'inter_state' : 'intra_state';

            $purchase = Purchase::create([
                'purchase_type' => $data['purchase_type'] ?? 'purchase',
                'bill_number' => $data['bill_number'] ?? $this->generateNumber($data['purchase_type'] ?? 'purchase'),
                'party_id' => $party->id,
                'date' => $data['date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? null,
                'tax_type' => $taxType,
                'discount_amount' => (float) ($data['discount_amount'] ?? 0),
                'subtotal' => 0,
                'cgst_amount' => 0,
                'sgst_amount' => 0,
                'igst_amount' => 0,
                'tax_amount' => 0,
                'round_off' => (float) ($data['round_off'] ?? 0),
                'total_amount' => 0,
                'paid_amount' => (float) ($data['paid_amount'] ?? 0),
                'balance_amount' => 0,
                'payment_status' => 'unpaid',
                'payment_mode' => $data['payment_mode'] ?? 'cash',
                'notes' => $data['notes'] ?? null,
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
                $taxPercent = (float) ($row['tax_percent'] ?? 0);

                $taxableLine = $qty * $unitPrice;
                $taxAmount = ($taxableLine * $taxPercent) / 100;
                $lineTotal = $taxableLine + $taxAmount;

                $subtotal += $taxableLine;
                $totalTax += $taxAmount;

                $itemModel = ! empty($row['item_id']) ? Item::find($row['item_id']) : null;
                $itemName = $itemModel ? $itemModel->name : ($row['item_name'] ?? 'Item');
                $hsn = $itemModel ? $itemModel->hsn_code : ($row['hsn_code'] ?? null);
                $unit = $itemModel ? $itemModel->unit : ($row['unit'] ?? 'PCS');

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $itemModel?->id,
                    'item_name' => $itemName,
                    'hsn_code' => $hsn,
                    'unit' => $unit,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'total' => $lineTotal,
                ]);

                if ($itemModel) {
                    $itemModel->syncStock();
                }
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

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $totalAmount = max(0, $subtotal - $discountAmount + $totalTax + (float) ($data['round_off'] ?? 0));
            $paidAmount = min($totalAmount, (float) ($data['paid_amount'] ?? 0));
            $balanceAmount = max(0, $totalAmount - $paidAmount);

            $paymentStatus = 'unpaid';
            if ($balanceAmount <= 0 && $totalAmount > 0) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }

            $purchase->update([
                'subtotal' => $subtotal,
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'tax_amount' => $totalTax,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $balanceAmount,
                'payment_status' => $paymentStatus,
            ]);

            // If immediate payment made on purchase, record in payments
            if ($paidAmount > 0 && $purchase->purchase_type === 'purchase') {
                Payment::create([
                    'payment_type' => 'payment_out',
                    'payment_number' => 'VCHR-'.$purchase->bill_number,
                    'party_id' => $party->id,
                    'purchase_id' => $purchase->id,
                    'date' => $purchase->date,
                    'amount' => $paidAmount,
                    'payment_mode' => $data['payment_mode'] ?? 'cash',
                    'notes' => 'Paid against bill '.$purchase->bill_number,
                ]);
            }

            $party->syncBalance();

            return $purchase;
        });
    }

    /**
     * Delete a purchase bill, update stock and supplier balance.
     */
    public function deletePurchase(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase): void {
            $party = $purchase->party;
            $items = $purchase->items()->get();

            $purchase->payments()->delete();
            $purchase->items()->delete();
            $purchase->delete();

            foreach ($items as $purItem) {
                if ($purItem->item) {
                    $purItem->item->syncStock();
                }
            }

            if ($party) {
                $party->syncBalance();
            }
        });
    }
}
