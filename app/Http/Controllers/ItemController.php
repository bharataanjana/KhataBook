<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\StockAdjustment;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->query('category_id');
        $lowStockOnly = $request->boolean('low_stock');
        $search = $request->query('search');

        $query = Item::with('category');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($lowStockOnly) {
            $query->whereColumn('current_stock', '<=', 'low_stock_threshold');
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('hsn_code', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::all();

        $allItems = Item::all();
        $totalStockValue = (float) $allItems->sum(fn ($i) => (float) $i->current_stock * (float) $i->purchase_price);
        $totalItemsCount = $allItems->count();
        $lowStockCount = $allItems->filter(fn ($i) => $i->isLowStock())->count();

        return view('items.index', compact(
            'items',
            'categories',
            'categoryId',
            'lowStockOnly',
            'search',
            'totalStockValue',
            'totalItemsCount',
            'lowStockCount'
        ));
    }

    public function create(): View
    {
        $categories = Category::all();
        $units = Unit::all();

        return view('items.create', compact('categories', 'units'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:100|unique:items,item_code',
            'barcode' => 'nullable|string|max:100',
            'hsn_code' => 'nullable|string|max:20',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string|max:20',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'is_tax_included' => 'boolean',
            'opening_stock' => 'nullable|numeric',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['is_tax_included'] = $request->boolean('is_tax_included');
        $validated['opening_stock'] = (float) ($validated['opening_stock'] ?? 0);
        $validated['current_stock'] = $validated['opening_stock'];
        $validated['low_stock_threshold'] = (float) ($validated['low_stock_threshold'] ?? 5);

        $item = Item::create($validated);
        $item->syncStock();

        return redirect()->route('items.index')->with('success', 'Item created successfully!');
    }

    public function show(Item $item): View
    {
        $item->load(['category', 'adjustments']);

        $salesHistory = $item->invoiceItems()->with('invoice.party')->latest()->take(10)->get();
        $purchaseHistory = $item->purchaseItems()->with('purchase.party')->latest()->take(10)->get();

        return view('items.show', compact('item', 'salesHistory', 'purchaseHistory'));
    }

    public function edit(Item $item): View
    {
        $categories = Category::all();
        $units = Unit::all();

        return view('items.edit', compact('item', 'categories', 'units'));
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:100|unique:items,item_code,'.$item->id,
            'barcode' => 'nullable|string|max:100',
            'hsn_code' => 'nullable|string|max:20',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string|max:20',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['is_tax_included'] = $request->boolean('is_tax_included');
        $validated['low_stock_threshold'] = (float) ($validated['low_stock_threshold'] ?? 5);

        $item->update($validated);
        $item->syncStock();

        return redirect()->route('items.index')->with('success', 'Item updated successfully!');
    }

    public function adjustStock(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'adjustment_type' => 'required|in:add,reduce',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
        ]);

        StockAdjustment::create([
            'item_id' => $item->id,
            'adjustment_type' => $validated['adjustment_type'],
            'quantity' => $validated['quantity'],
            'date' => now()->toDateString(),
            'reason' => $validated['reason'],
        ]);

        $item->syncStock();

        return back()->with('success', 'Stock adjusted successfully!');
    }

    public function destroy(Item $item): RedirectResponse
    {
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item deleted successfully!');
    }
}
