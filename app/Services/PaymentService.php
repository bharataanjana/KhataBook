<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Party;
use App\Models\Payment;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function generateNumber(string $type = 'payment_in'): string
    {
        $prefix = $type === 'payment_in' ? 'PAYIN-' : 'PAYOUT-';
        $lastPayment = Payment::where('payment_type', $type)
            ->where('payment_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        $nextNum = 1;
        if ($lastPayment) {
            $currentStr = str_replace($prefix, '', $lastPayment->payment_number);
            if (is_numeric($currentStr)) {
                $nextNum = (int) $currentStr + 1;
            }
        }

        return $prefix.str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Record a payment and synchronize balances.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data): Payment {
            $payment = Payment::create([
                'payment_type' => $data['payment_type'],
                'payment_number' => $data['payment_number'] ?? $this->generateNumber($data['payment_type']),
                'party_id' => $data['party_id'],
                'invoice_id' => $data['invoice_id'] ?? null,
                'purchase_id' => $data['purchase_id'] ?? null,
                'date' => $data['date'] ?? now()->toDateString(),
                'amount' => (float) $data['amount'],
                'payment_mode' => $data['payment_mode'] ?? 'cash',
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Sync invoice if linked
            if ($payment->invoice_id) {
                $invoice = Invoice::find($payment->invoice_id);
                $invoice?->syncPaymentStatus();
            }

            // Sync purchase if linked
            if ($payment->purchase_id) {
                $purchase = Purchase::find($payment->purchase_id);
                $purchase?->syncPaymentStatus();
            }

            // Sync party balance
            $party = Party::findOrFail($payment->party_id);
            $party->syncBalance();

            return $payment;
        });
    }

    public function deletePayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $party = $payment->party;
            $invoice = $payment->invoice;
            $purchase = $payment->purchase;

            $payment->delete();

            $invoice?->syncPaymentStatus();
            $purchase?->syncPaymentStatus();
            $party?->syncBalance();
        });
    }
}
