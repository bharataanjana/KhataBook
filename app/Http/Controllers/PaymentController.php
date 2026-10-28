<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Party;
use App\Models\Payment;
use App\Models\Purchase;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request): View
    {
        $type = $request->query('type', 'payment_in');
        $search = $request->query('search');

        $query = Payment::with(['party', 'invoice', 'purchase'])
            ->where('payment_type', $type);

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('party', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();
        $totalAmount = (float) Payment::where('payment_type', $type)->sum('amount');

        return view('payments.index', compact('payments', 'type', 'search', 'totalAmount'));
    }

    public function create(Request $request): View
    {
        $type = $request->query('type', 'payment_in');
        $partyId = $request->query('party_id');
        $invoiceId = $request->query('invoice_id');
        $purchaseId = $request->query('purchase_id');

        $parties = $type === 'payment_in'
            ? Party::whereIn('type', ['customer', 'both'])->orderBy('name')->get()
            : Party::whereIn('type', ['supplier', 'both'])->orderBy('name')->get();

        $selectedParty = $partyId ? Party::find($partyId) : null;
        $selectedInvoice = $invoiceId ? Invoice::find($invoiceId) : null;
        $selectedPurchase = $purchaseId ? Purchase::find($purchaseId) : null;

        $unpaidInvoices = [];
        $unpaidPurchases = [];

        if ($selectedParty) {
            if ($type === 'payment_in') {
                $unpaidInvoices = $selectedParty->invoices()
                    ->where('invoice_type', 'sale')
                    ->where('balance_amount', '>', 0)
                    ->get();
            } else {
                $unpaidPurchases = $selectedParty->purchases()
                    ->where('purchase_type', 'purchase')
                    ->where('balance_amount', '>', 0)
                    ->get();
            }
        }

        $paymentNumber = $this->paymentService->generateNumber($type);

        return view('payments.create', compact(
            'type',
            'parties',
            'selectedParty',
            'selectedInvoice',
            'selectedPurchase',
            'unpaidInvoices',
            'unpaidPurchases',
            'paymentNumber'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_type' => 'required|in:payment_in,payment_out',
            'payment_number' => 'required|string|max:100',
            'party_id' => 'required|exists:parties,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:cash,bank,upi,cheque',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $this->paymentService->recordPayment($validated);

        return redirect()->route('payments.index', ['type' => $validated['payment_type']])
            ->with('success', 'Payment recorded successfully!');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $type = $payment->payment_type;
        $this->paymentService->deletePayment($payment);

        return redirect()->route('payments.index', ['type' => $type])->with('success', 'Payment deleted successfully.');
    }
}
