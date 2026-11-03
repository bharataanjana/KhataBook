<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Item;
use App\Models\Party;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    public function index(Request $request): View
    {
        $type = $request->query('type', 'purchase');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Purchase::with('party')
            ->where('purchase_type', $type);

        if ($status) {
            $query->where('payment_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('bill_number', 'like', "%{$search}%")
                    ->orWhereHas('party', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $purchases = $query->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();

        $totalPurchases = (float) Purchase::where('purchase_type', $type)->sum('total_amount');
        $totalPaid = (float) Purchase::where('purchase_type', $type)->sum('paid_amount');
        $totalUnpaid = (float) Purchase::where('purchase_type', $type)->sum('balance_amount');

        return view('purchases.index', compact('purchases', 'type', 'status', 'search', 'totalPurchases', 'totalPaid', 'totalUnpaid'));
    }

    public function create(Request $request): View
    {
        $type = $request->query('type', 'purchase');
        $nextBillNumber = $this->purchaseService->generateNumber($type);
        $parties = Party::whereIn('type', ['supplier', 'both'])->orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $business = Business::current();

        return view('purchases.create', compact('type', 'nextBillNumber', 'parties', 'items', 'business'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_type' => 'required|in:purchase,purchase_return',
            'bill_number' => 'required|string|max:100',
            'party_id' => 'required|exists:parties,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date',
            'discount_amount' => 'nullable|numeric|min:0',
            'round_off' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.item_name' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $purchase = $this->purchaseService->createPurchase($validated);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase bill recorded successfully!');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['party', 'items.item', 'payments']);
        $business = Business::current();

        return view('purchases.show', compact('purchase', 'business'));
    }

    public function print(Purchase $purchase): View
    {
        $purchase->load(['party', 'items.item']);
        $business = Business::current();

        return view('purchases.print', compact('purchase', 'business'));
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        $type = $purchase->purchase_type;
        $this->purchaseService->deletePurchase($purchase);

        return redirect()->route('purchases.index', ['type' => $type])->with('success', 'Purchase bill deleted successfully.');
    }
}
