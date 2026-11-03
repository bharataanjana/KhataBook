<x-layouts.app>
    <x-slot:title>Purchases - KhataApp</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $type === 'purchase_return' ? 'Purchase Returns (Debit Notes)' : 'Purchase Bills' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500">Track inward supplier invoices, stock inventory updates, and payables</p>
            </div>
            <div class="flex items-center gap-2">
                @if($type === 'purchase_return')
                    <a href="{{ route('purchases.create', ['type' => 'purchase_return']) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-sm">
                        + Record Purchase Return
                    </a>
                @else
                    <a href="{{ route('purchases.create', ['type' => 'purchase']) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-sm">
                        + Record Purchase Bill
                    </a>
                @endif
            </div>
        </div>

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Purchases</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">₹{{ number_format($totalPurchases, 2) }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ ucfirst($type) }} gross total</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-blue-200 bg-linear-to-r from-white to-blue-50/20 shadow-xs">
                <span class="text-xs font-semibold text-blue-700 uppercase tracking-wider">Amount Paid</span>
                <div class="text-2xl font-bold text-blue-600 mt-1">₹{{ number_format($totalPaid, 2) }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Disbursed to suppliers</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-rose-200 bg-linear-to-r from-white to-rose-50/20 shadow-xs">
                <span class="text-xs font-semibold text-rose-700 uppercase tracking-wider">Balance You Owe</span>
                <div class="text-2xl font-bold text-rose-600 mt-1">₹{{ number_format($totalUnpaid, 2) }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Pending payment</p>
            </div>
        </div>

        <!-- Toolbar & Filter -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col md:flex-row gap-4 justify-between items-center shadow-xs">
            <!-- Tabs -->
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-lg w-full md:w-auto">
                <a href="{{ route('purchases.index', ['type' => 'purchase']) }}" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors {{ $type === 'purchase' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Purchase Bills
                </a>
                <a href="{{ route('purchases.index', ['type' => 'purchase_return']) }}" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors {{ $type === 'purchase_return' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Purchase Returns
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('purchases.index') }}" class="w-full md:w-80 flex gap-2">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="relative w-full">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search bill # or supplier..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-slate-50 focus:bg-white">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3 py-2 text-xs font-semibold bg-slate-800 text-white rounded-lg hover:bg-slate-700">Search</button>
            </form>
        </div>

        <!-- Purchases Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px] tracking-wider">
                            <th class="p-3.5">Bill #</th>
                            <th class="p-3.5">Date</th>
                            <th class="p-3.5">Supplier Name</th>
                            <th class="p-3.5 text-right">Total Amount</th>
                            <th class="p-3.5 text-right">Paid</th>
                            <th class="p-3.5 text-right">Balance Due</th>
                            <th class="p-3.5 text-center">Status</th>
                            <th class="p-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($purchases as $purchase)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="font-bold text-blue-600 hover:text-blue-800 text-sm">
                                        {{ $purchase->bill_number }}
                                    </a>
                                </td>
                                <td class="p-3.5 text-slate-600 whitespace-nowrap">
                                    {{ $purchase->date->format('d M Y') }}
                                </td>
                                <td class="p-3.5">
                                    <a href="{{ route('parties.show', $purchase->party) }}" class="font-semibold text-slate-900 hover:text-blue-600">
                                        {{ $purchase->party->name }}
                                    </a>
                                </td>
                                <td class="p-3.5 text-right font-bold text-slate-900 text-sm">
                                    ₹{{ number_format($purchase->total_amount, 2) }}
                                </td>
                                <td class="p-3.5 text-right text-blue-600 font-semibold">
                                    ₹{{ number_format($purchase->paid_amount, 2) }}
                                </td>
                                <td class="p-3.5 text-right font-semibold {{ $purchase->balance_amount > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    ₹{{ number_format($purchase->balance_amount, 2) }}
                                </td>
                                <td class="p-3.5 text-center">
                                    @if($purchase->payment_status === 'paid')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">PAID</span>
                                    @elseif($purchase->payment_status === 'partial')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">PARTIAL</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">UNPAID</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <a href="{{ route('purchases.print', $purchase) }}" target="_blank" class="p-1 rounded text-slate-500 hover:text-slate-800 hover:bg-slate-100" title="Print Bill">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </a>
                                        <a href="{{ route('purchases.show', $purchase) }}" class="p-1 rounded text-slate-500 hover:text-slate-800 hover:bg-slate-100" title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" onsubmit="return confirm('Delete this purchase bill? Stock and supplier balance will be adjusted.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 rounded text-rose-400 hover:text-rose-600 hover:bg-rose-50" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400">
                                    No purchase records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchases->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
