<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Party extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'phone',
        'email',
        'gstin',
        'pan',
        'billing_address',
        'shipping_address',
        'state',
        'state_code',
        'opening_balance',
        'opening_balance_type',
        'current_balance',
        'credit_limit',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Recalculates and updates the current balance of the party.
     * Positive = You'll Receive (Receivable)
     * Negative = You'll Pay (Payable)
     */
    public function syncBalance(): void
    {
        $balance = 0.00;

        if ($this->opening_balance_type === 'to_receive') {
            $balance += (float) $this->opening_balance;
        } else {
            $balance -= (float) $this->opening_balance;
        }

        // Sales: Sale invoices increase receivables, returns decrease
        $salesTotal = (float) $this->invoices()->where('invoice_type', 'sale')->sum('total_amount');
        $saleReturnsTotal = (float) $this->invoices()->where('invoice_type', 'sale_return')->sum('total_amount');
        $balance += ($salesTotal - $saleReturnsTotal);

        // Purchases: Purchase bills increase payables (decrease net balance), returns decrease payables
        $purchasesTotal = (float) $this->purchases()->where('purchase_type', 'purchase')->sum('total_amount');
        $purchaseReturnsTotal = (float) $this->purchases()->where('purchase_type', 'purchase_return')->sum('total_amount');
        $balance -= ($purchasesTotal - $purchaseReturnsTotal);

        // Payments: Payment in increases our cash, decreases party's receivable balance
        $paymentsIn = (float) $this->payments()->where('payment_type', 'payment_in')->sum('amount');
        $balance -= $paymentsIn;

        // Payment out decreases our payable to them (increases net balance towards zero)
        $paymentsOut = (float) $this->payments()->where('payment_type', 'payment_out')->sum('amount');
        $balance += $paymentsOut;

        $this->update(['current_balance' => $balance]);
    }
}
