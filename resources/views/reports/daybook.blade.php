<x-layouts.app>
    <x-slot:title>Day Book - KhataApp</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('reports.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                    ← Back to Reports
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Day Book</h1>
                <p class="text-xs sm:text-sm text-slate-500">Daily business log for {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('reports.daybook') }}" class="flex items-center gap-2">
                    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-white">
                </form>
                <button onclick="window.print()" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-xs">
                    🖨️ Print
                </button>
            </div>
        </div>

        <!-- Daily Financial Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Sales</span>
                <div class="text-xl font-bold text-slate-900 mt-1">₹{{ number_format($daybook['total_sales'], 2) }}</div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Purchases</span>
                <div class="text-xl font-bold text-slate-900 mt-1">₹{{ number_format($daybook['total_purchases'], 2) }}</div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-emerald-200 bg-linear-to-r from-white to-emerald-50/20 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Money In</span>
                <div class="text-xl font-bold text-emerald-600 mt-1">₹{{ number_format($daybook['money_in'], 2) }}</div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-rose-200 bg-linear-to-r from-white to-rose-50/20 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-700">Money Out</span>
                <div class="text-xl font-bold text-rose-600 mt-1">₹{{ number_format($daybook['money_out'], 2) }}</div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-indigo-200 bg-linear-to-r from-white to-indigo-50/20 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-700">Net Cashflow</span>
                <div class="text-xl font-bold {{ $daybook['net_cashflow'] >= 0 ? 'text-indigo-600' : 'text-rose-600' }} mt-1">
                    ₹{{ number_format($daybook['net_cashflow'], 2) }}
                </div>
            </div>
        </div>

        <!-- Section 1: Sales Invoices of the Day -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Sales Invoices ({{ count($daybook['sales']) }})</h3>
                <span class="text-xs font-bold text-slate-900">₹{{ number_format($daybook['total_sales'], 2) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500">
                            <th class="p-3">Invoice #</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3 text-right">Taxable</th>
                            <th class="p-3 text-right">Tax</th>
                            <th class="p-3 text-right">Total</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($daybook['sales'] as $inv)
                            <tr>
                                <td class="p-3 font-semibold text-blue-600"><a href="{{ route('sales.show', $inv) }}">{{ $inv->invoice_number }}</a></td>
                                <td class="p-3 text-slate-800">{{ $inv->party->name }}</td>
                                <td class="p-3 text-right text-slate-600">₹{{ number_format($inv->subtotal, 2) }}</td>
                                <td class="p-3 text-right text-slate-600">₹{{ number_format($inv->tax_amount, 2) }}</td>
                                <td class="p-3 text-right font-bold text-slate-900">₹{{ number_format($inv->total_amount, 2) }}</td>
                                <td class="p-3 text-center uppercase text-[10px] font-bold">{{ $inv->payment_status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-4 text-center text-slate-400">No sales invoices recorded on this date.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Payments In (Collections) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-800">Payment In Received ({{ count($daybook['payments_in']) }})</h3>
                <span class="text-xs font-bold text-emerald-600">₹{{ number_format($daybook['money_in'], 2) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500">
                            <th class="p-3">Receipt #</th>
                            <th class="p-3">Party</th>
                            <th class="p-3">Mode</th>
                            <th class="p-3">Notes</th>
                            <th class="p-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($daybook['payments_in'] as $pi)
                            <tr>
                                <td class="p-3 font-mono font-bold text-slate-800">{{ $pi->payment_number }}</td>
                                <td class="p-3 text-slate-800">{{ $pi->party->name }}</td>
                                <td class="p-3 uppercase text-slate-600">{{ $pi->payment_mode }}</td>
                                <td class="p-3 text-slate-500">{{ $pi->notes ?: '-' }}</td>
                                <td class="p-3 text-right font-bold text-emerald-600">₹{{ number_format($pi->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-center text-slate-400">No customer payments received on this date.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 3: Payments Out & Expenses -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Payment Out -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-blue-800">Payments to Suppliers</h3>
                    <span class="text-xs font-bold text-blue-600">₹{{ number_format($daybook['payments_out']->sum('amount'), 2) }}</span>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($daybook['payments_out'] as $po)
                        <div class="p-3 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-800">{{ $po->payment_number }}</span> &bull; {{ $po->party->name }}
                                <p class="text-[11px] text-slate-500 uppercase">{{ $po->payment_mode }} &bull; {{ $po->notes ?: '-' }}</p>
                            </div>
                            <span class="font-bold text-blue-600">₹{{ number_format($po->amount, 2) }}</span>
                        </div>
                    @empty
                        <div class="p-4 text-center text-slate-400 text-xs">No payments out on this date.</div>
                    @endforelse
                </div>
            </div>

            <!-- Expenses -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-rose-800">Expenses Recorded</h3>
                    <span class="text-xs font-bold text-rose-600">₹{{ number_format($daybook['expenses']->sum('amount'), 2) }}</span>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($daybook['expenses'] as $exp)
                        <div class="p-3 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-800">{{ $exp->category?->name ?: 'General' }}</span> - {{ $exp->paid_to ?: $exp->expense_number }}
                                <p class="text-[11px] text-slate-500 uppercase">{{ $exp->payment_mode }} &bull; {{ $exp->notes ?: '-' }}</p>
                            </div>
                            <span class="font-bold text-rose-600">₹{{ number_format($exp->amount, 2) }}</span>
                        </div>
                    @empty
                        <div class="p-4 text-center text-slate-400 text-xs">No expenses recorded on this date.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
