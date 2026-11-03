<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Party;
use App\Services\InvoiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function index(Request $request): View
    {
        $type = $request->query('type', 'sale');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Invoice::with('party')
            ->where('invoice_type', $type);

        if ($status) {
            $query->where('payment_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('party', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();

        $totalSales = (float) Invoice::where('invoice_type', $type)->sum('total_amount');
        $totalPaid = (float) Invoice::where('invoice_type', $type)->sum('paid_amount');
        $totalUnpaid = (float) Invoice::where('invoice_type', $type)->sum('balance_amount');

        return view('sales.index', compact('invoices', 'type', 'status', 'search', 'totalSales', 'totalPaid', 'totalUnpaid'));
    }

    public function create(Request $request): View
    {
        $type = $request->query('type', 'sale');
        $nextInvoiceNumber = $this->invoiceService->generateNumber($type);
        $parties = Party::whereIn('type', ['customer', 'both'])->orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $business = Business::current();

        return view('sales.create', compact('type', 'nextInvoiceNumber', 'parties', 'items', 'business'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_type' => 'required|in:sale,quotation,sale_return',
            'invoice_number' => 'required|string|max:100',
            'party_id' => 'required|exists:parties,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date',
            'reference_no' => 'nullable|string|max:100',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'round_off' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.item_name' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $invoice = $this->invoiceService->createInvoice($validated);

        return redirect()->route('sales.show', $invoice)->with('success', 'Invoice generated successfully!');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['party', 'items.item', 'payments']);
        $business = Business::current();

        return view('sales.show', compact('invoice', 'business'));
    }

    public function print(Invoice $invoice): View
    {
        $invoice->load(['party', 'items.item']);
        $business = Business::current();

        return view('sales.print', compact('invoice', 'business'));
    }

    public function convertToSale(Invoice $invoice): RedirectResponse
    {
        if ($invoice->invoice_type !== 'quotation') {
            return back()->with('error', 'Only quotations can be converted to sale invoices.');
        }

        $newNumber = $this->invoiceService->generateNumber('sale');
        $invoice->update([
            'invoice_type' => 'sale',
            'invoice_number' => $newNumber,
            'date' => now()->toDateString(),
        ]);

        // Decrement stock for items
        foreach ($invoice->items as $invItem) {
            if ($invItem->item) {
                $invItem->item->syncStock();
            }
        }

        $invoice->party?->syncBalance();

        return redirect()->route('sales.show', $invoice)->with('success', 'Quotation successfully converted to Sale Invoice '.$newNumber);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $type = $invoice->invoice_type;
        $this->invoiceService->deleteInvoice($invoice);

        return redirect()->route('sales.index', ['type' => $type])->with('success', 'Invoice deleted successfully.');
    }
}
