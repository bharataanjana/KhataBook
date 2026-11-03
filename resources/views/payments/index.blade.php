<x-layouts.app>
    <x-slot:title>Payments - KhataApp</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $type === 'payment_in' ? 'Payment In (Collections)' : 'Payment Out (Disbursements)' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500">Record payments received from customers or paid out to suppliers</p>
            </div>
            <div class="flex items-center gap-2">
                @if($type === 'payment_in')
                    <a href="{{ route('payments.create', ['type' => 'payment_in']) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm">
                        + Record Payment In
                    </a>
                @else
                    <a href="{{ route('payments.create', ['type' => 'payment_out']) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-sm">
                        + Record Payment Out
                    </a>
                @endif
            </div>
        </div>

        <!-- Metric Card -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs max-w-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                {{ $type === 'payment_in' ? 'Total Collected (Money In)' : 'Total Paid Out (Money Out)' }}
            </span>
            <div class="text-2xl font-bold {{ $type === 'payment_in' ? 'text-emerald-600' : 'text-blue-600' }} mt-1">
                ₹{{ number_format($totalAmount, 2) }}
            </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col md:flex-row gap-4 justify-between items-center shadow-xs">
            <!-- Tabs -->
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-lg w-full md:w-auto">
                <a href="{{ route('payments.index', ['type' => 'payment_in']) }}" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors {{ $type === 'payment_in' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Payment In (From Customers)
                </a>
                <a href="{{ route('payments.index', ['type' => 'payment_out']) }}" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors {{ $type === 'payment_out' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Payment Out (To Suppliers)
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('payments.index') }}" class="w-full md:w-80 flex gap-2">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="relative w-full">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search receipt #, ref, party..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-slate-50 focus:bg-white">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3 py-2 text-xs font-semibold bg-slate-800 text-white rounded-lg hover:bg-slate-700">Search</button>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px] tracking-wider">
                            <th class="p-3.5">Receipt / Voucher #</th>
                            <th class="p-3.5">Date</th>
                            <th class="p-3.5">Party Name</th>
                            <th class="p-3.5">Linked Bill</th>
                            <th class="p-3.5">Payment Mode</th>
                            <th class="p-3.5">Reference #</th>
                            <th class="p-3.5 text-right">Amount</th>
                            <th class="p-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5 font-bold font-mono text-slate-900">
                                    {{ $payment->payment_number }}
                                </td>
                                <td class="p-3.5 text-slate-600 whitespace-nowrap">
                                    {{ $payment->date->format('d M Y') }}
                                </td>
                                <td class="p-3.5">
                                    <a href="{{ route('parties.show', $payment->party) }}" class="font-semibold text-slate-900 hover:text-blue-600">
                                        {{ $payment->party->name }}
                                    </a>
                                </td>
                                <td class="p-3.5 text-slate-600">
                                    @if($payment->invoice)
                                        <a href="{{ route('sales.show', $payment->invoice) }}" class="text-blue-600 hover:underline">{{ $payment->invoice->invoice_number }}</a>
                                    @elseif($payment->purchase)
                                        <a href="{{ route('purchases.show', $payment->purchase) }}" class="text-blue-600 hover:underline">{{ $payment->purchase->bill_number }}</a>
                                    @else
                                        <span class="text-slate-400">On Account</span>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider
                                        @if($payment->payment_mode === 'cash') bg-emerald-50 text-emerald-700
                                        @elseif($payment->payment_mode === 'upi') bg-purple-50 text-purple-700
                                        @elseif($payment->payment_mode === 'bank') bg-blue-50 text-blue-700
                                        @else bg-slate-100 text-slate-700 @endif">
                                        {{ $payment->payment_mode }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-500 font-mono">
                                    {{ $payment->reference_number ?: '-' }}
                                </td>
                                <td class="p-3.5 text-right font-bold text-sm {{ $payment->payment_type === 'payment_in' ? 'text-emerald-600' : 'text-blue-600' }}">
                                    ₹{{ number_format($payment->amount, 2) }}
                                </td>
                                <td class="p-3.5 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('payments.destroy', $payment) }}" onsubmit="return confirm('Delete this payment record? Balances will be recalculated.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded text-rose-400 hover:text-rose-600 hover:bg-rose-50" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400">
                                    No payments recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
