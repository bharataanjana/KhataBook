<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'item_code',
        'barcode',
        'hsn_code',
        'category_id',
        'unit',
        'sale_price',
        'purchase_price',
        'tax_rate',
        'is_tax_included',
        'opening_stock',
        'current_stock',
        'low_stock_threshold',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'is_tax_included' => 'boolean',
            'opening_stock' => 'decimal:2',
            'current_stock' => 'decimal:2',
            'low_stock_threshold' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->low_stock_threshold;
    }

    /**
     * Recomputes and updates the current stock of this item.
     */
    public function syncStock(): void
    {
        $stock = (float) $this->opening_stock;

        // Sales (decreases stock) & Sale Returns (increases stock)
        $soldQty = (float) $this->invoiceItems()
            ->whereHas('invoice', fn ($q) => $q->where('invoice_type', 'sale'))
            ->sum('quantity');

        $returnedSaleQty = (float) $this->invoiceItems()
            ->whereHas('invoice', fn ($q) => $q->where('invoice_type', 'sale_return'))
            ->sum('quantity');

        $stock -= ($soldQty - $returnedSaleQty);

        // Purchases (increases stock) & Purchase Returns (decreases stock)
        $purchasedQty = (float) $this->purchaseItems()
            ->whereHas('purchase', fn ($q) => $q->where('purchase_type', 'purchase'))
            ->sum('quantity');

        $returnedPurchaseQty = (float) $this->purchaseItems()
            ->whereHas('purchase', fn ($q) => $q->where('purchase_type', 'purchase_return'))
            ->sum('quantity');

        $stock += ($purchasedQty - $returnedPurchaseQty);

        // Manual adjustments
        $addedAdj = (float) $this->adjustments()->where('adjustment_type', 'add')->sum('quantity');
        $reducedAdj = (float) $this->adjustments()->where('adjustment_type', 'reduce')->sum('quantity');
        $stock += ($addedAdj - $reducedAdj);

        $this->update(['current_stock' => $stock]);
    }
}
