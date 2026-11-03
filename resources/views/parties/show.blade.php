<x-layouts.app>
    <x-slot:title>{{ $party->name }} - Party Ledger</x-slot:title>

    <div class="space-y-6">
        <!-- Top Nav & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('parties.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                    ← Back to All Parties
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $party->name }}</h1>
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wider {{ $party->type === 'customer' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ ucfirst($party->type) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('payments.create', ['type' => $party->current_balance >= 0 ? 'payment_in' : 'payment_out', 'party_id' => $party->id]) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs">
                    + Record Payment
                </a>
                <a href="{{ route('sales.create', ['party_id' => $party->id]) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-xs">
                    + New Sale
                </a>
                <a href="{{ route('parties.edit', $party) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700">
                    Edit Details
                </a>
                <button onclick="window.print()" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700">
                    🖨️ Print Ledger
                </button>
            </div>
        </div>

        <!-- Party Info & Balance Banner -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Contact Card -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Contact Details</h3>
                <div class="text-xs space-y-1.5 text-slate-600">
                    <p><span class="font-semibold text-slate-800">Phone:</span> {{ $party->phone ?: 'Not provided' }}</p>
                    <p><span class="font-semibold text-slate-800">Email:</span> {{ $party->email ?: 'Not provided' }}</p>
                    <p><span class="font-semibold text-slate-800">GSTIN:</span> {{ $party->gstin ?: 'Unregistered' }}</p>
                </div>
            </div>

            <!-- Address Card -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Billing Address</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    {{ $party->billing_address ?: 'No address specified' }}
                    @if($party->state)
                        <br><span class="text-slate-800 font-medium">State:</span> {{ $party->state }} ({{ $party->state_code }})
                    @endif
                </p>
            </div>

            <!-- Current Balance Card -->
            <div class="rounded-xl border p-4 shadow-xs flex flex-col justify-between {{ $party->current_balance > 0 ? 'bg-emerald-50/50 border-emerald-200' : ($party->current_balance < 0 ? 'bg-rose-50/50 border-rose-200' : 'bg-slate-50 border-slate-200') }}">
                <span class="text-xs font-bold uppercase tracking-wider {{ $party->current_balance > 0 ? 'text-emerald-700' : ($party->current_balance < 0 ? 'text-rose-700' : 'text-slate-600') }}">
                    Current Net Balance
                </span>
                <div class="mt-2">
                    <div class="text-2xl font-bold {{ $party->current_balance > 0 ? 'text-emerald-600' : ($party->current_balance < 0 ? 'text-rose-600' : 'text-slate-700') }}">
                        ₹{{ number_format(abs($party->current_balance), 2) }}
                    </div>
                    <div class="text-xs font-semibold mt-0.5 {{ $party->current_balance > 0 ? 'text-emerald-700' : ($party->current_balance < 0 ? 'text-rose-700' : 'text-slate-500') }}">
                        {{ $party->current_balance > 0 ? 'You will receive (Dr)' : ($party->current_balance < 0 ? 'You have to pay (Cr)' : 'Fully Settled') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Ledger Statement Filter -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Statement / Ledger Book</h2>
                <p class="text-xs text-slate-500">Chronological history of invoices, payments, and returns</p>
            </div>

            <form method="GET" action="{{ route('parties.show', $party) }}" class="flex items-center gap-2">
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg">
                <span class="text-xs text-slate-400">to</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg">
                <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-slate-800 text-white rounded-lg hover:bg-slate-700">Filter</button>
                @if($startDate || $endDate)
                    <a href="{{ route('parties.show', $party) }}" class="text-xs text-slate-500 hover:text-slate-800">Clear</a>
                @endif
            </form>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px] tracking-wider">
                            <th class="p-3.5">Date</th>
                            <th class="p-3.5">Type</th>
                            <th class="p-3.5">Reference #</th>
                            <th class="p-3.5">Notes</th>
                            <th class="p-3.5 text-right">Debit (Dr)</th>
                            <th class="p-3.5 text-right">Credit (Cr)</th>
                            <th class="p-3.5 text-right">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($statement['ledger'] as $entry)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5 text-slate-600 whitespace-nowrap">{{ $entry['date'] }}</td>
                                <td class="p-3.5 font-medium text-slate-900">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold
                                        @if(str_contains($entry['type'], 'Sale')) bg-emerald-50 text-emerald-800
                                        @elseif(str_contains($entry['type'], 'Purchase')) bg-blue-50 text-blue-800
                                        @elseif(str_contains($entry['type'], 'Payment In')) bg-teal-50 text-teal-800
                                        @elseif(str_contains($entry['type'], 'Payment Out')) bg-indigo-50 text-indigo-800
                                        @else bg-slate-100 text-slate-700 @endif">
                                        {{ $entry['type'] }}
                                    </span>
                                </td>
                                <td class="p-3.5 font-medium text-slate-700">{{ $entry['ref_no'] }}</td>
                                <td class="p-3.5 text-slate-500 text-[11px] max-w-xs truncate">{{ $entry['notes'] ?: '-' }}</td>
                                <td class="p-3.5 text-right font-medium text-slate-900">
                                    {{ $entry['debit'] > 0 ? '₹' . number_format($entry['debit'], 2) : '-' }}
                                </td>
                                <td class="p-3.5 text-right font-medium text-slate-900">
                                    {{ $entry['credit'] > 0 ? '₹' . number_format($entry['credit'], 2) : '-' }}
                                </td>
                                <td class="p-3.5 text-right font-bold whitespace-nowrap {{ $entry['running_balance'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    ₹{{ number_format(abs($entry['running_balance']), 2) }} {{ $entry['running_balance'] >= 0 ? 'Dr' : 'Cr' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    No transactions recorded for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100/80 font-bold border-t border-slate-200 text-slate-900">
                            <td colspan="6" class="p-3.5 text-right uppercase text-[11px] tracking-wider">Closing Balance:</td>
                            <td class="p-3.5 text-right text-sm {{ $statement['closing_balance'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                ₹{{ number_format(abs($statement['closing_balance']), 2) }} {{ $statement['closing_balance'] >= 0 ? 'Dr' : 'Cr' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
