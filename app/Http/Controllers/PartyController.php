<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PartyController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $type = $request->query('type', 'all');
        $search = $request->query('search');

        $query = Party::query();

        if ($type === 'customer') {
            $query->whereIn('type', ['customer', 'both']);
        } elseif ($type === 'supplier') {
            $query->whereIn('type', ['supplier', 'both']);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('gstin', 'like', "%{$search}%");
            });
        }

        $parties = $query->orderBy('name')->paginate(15)->withQueryString();

        $allParties = Party::all();
        $totalToReceive = (float) $allParties->where('current_balance', '>', 0)->sum('current_balance');
        $totalToPay = abs((float) $allParties->where('current_balance', '<', 0)->sum('current_balance'));

        return view('parties.index', compact('parties', 'type', 'search', 'totalToReceive', 'totalToPay'));
    }

    public function create(): View
    {
        return view('parties.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:customer,supplier,both',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gstin' => 'nullable|string|max:20',
            'pan' => 'nullable|string|max:20',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'state_code' => 'nullable|string|max:10',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_balance_type' => 'required|in:to_receive,to_pay',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $party = Party::create($validated);
        $party->syncBalance();

        return redirect()->route('parties.show', $party)->with('success', 'Party created successfully!');
    }

    public function show(Party $party, Request $request): View
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $statement = $this->reportService->getPartyStatement($party, $startDate, $endDate);

        return view('parties.show', compact('party', 'statement', 'startDate', 'endDate'));
    }

    public function edit(Party $party): View
    {
        return view('parties.edit', compact('party'));
    }

    public function update(Request $request, Party $party): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:customer,supplier,both',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gstin' => 'nullable|string|max:20',
            'pan' => 'nullable|string|max:20',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'state_code' => 'nullable|string|max:10',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_balance_type' => 'required|in:to_receive,to_pay',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $party->update($validated);
        $party->syncBalance();

        return redirect()->route('parties.show', $party)->with('success', 'Party updated successfully!');
    }

    public function destroy(Party $party): RedirectResponse
    {
        $party->delete();

        return redirect()->route('parties.index')->with('success', 'Party deleted successfully!');
    }
}
