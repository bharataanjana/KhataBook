<x-layouts.app>
    <x-slot:title>Parties - KhataApp</x-slot:title>

    <div class="space-y-6">
        <!-- Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Parties</h1>
                <p class="text-xs sm:text-sm text-slate-500">Manage your customers, suppliers, and outstanding balances</p>
            </div>
            <a href="{{ route('parties.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Add Party
            </a>
        </div>

        <!-- Receivables & Payables Header Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl p-4 border border-emerald-200 bg-linear-to-r from-white to-emerald-50/30 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Total You'll Receive</span>
                    <div class="text-2xl font-bold text-emerald-600 mt-1">₹{{ number_format($totalToReceive, 2) }}</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-rose-200 bg-linear-to-r from-white to-rose-50/30 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-rose-800 uppercase tracking-wider">Total You'll Pay</span>
                    <div class="text-2xl font-bold text-rose-600 mt-1">₹{{ number_format($totalToPay, 2) }}</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col md:flex-row gap-4 justify-between items-center shadow-xs">
            <!-- Tabs -->
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-lg w-full md:w-auto">
                <a href="{{ route('parties.index', ['type' => 'all', 'search' => $search]) }}" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors {{ $type === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    All Parties
                </a>
                <a href="{{ route('parties.index', ['type' => 'customer', 'search' => $search]) }}" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors {{ $type === 'customer' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Customers
                </a>
                <a href="{{ route('parties.index', ['type' => 'supplier', 'search' => $search]) }}" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors {{ $type === 'supplier' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Suppliers
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('parties.index') }}" class="w-full md:w-80 flex gap-2">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="relative w-full">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search party by name, phone, GSTIN..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-slate-50 focus:bg-white transition-colors">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3 py-2 text-xs font-semibold bg-slate-800 text-white rounded-lg hover:bg-slate-700">Search</button>
                @if($search)
                    <a href="{{ route('parties.index', ['type' => $type]) }}" class="px-2 py-2 text-xs text-slate-500 hover:text-slate-700 flex items-center">Reset</a>
                @endif
            </form>
        </div>

        <!-- Parties Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px] tracking-wider">
                            <th class="p-4">Party Name</th>
                            <th class="p-4">Contact</th>
                            <th class="p-4">GSTIN / State</th>
                            <th class="p-4 text-right">Balance</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($parties as $party)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-4">
                                    <a href="{{ route('parties.show', $party) }}" class="font-bold text-slate-900 hover:text-blue-600 text-sm block">
                                        {{ $party->name }}
                                    </a>
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] uppercase font-semibold tracking-wider mt-0.5 {{ $party->type === 'customer' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($party->type === 'supplier' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200') }}">
                                        {{ ucfirst($party->type) }}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-600">
                                    <div>{{ $party->phone ?: '-' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $party->email ?: '' }}</div>
                                </td>
                                <td class="p-4 text-slate-600">
                                    <div>{{ $party->gstin ?: 'Unregistered' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $party->state ?: '-' }}</div>
                                </td>
                                <td class="p-4 text-right">
                                    @if($party->current_balance > 0)
                                        <div class="font-bold text-emerald-600 text-sm">₹{{ number_format($party->current_balance, 2) }}</div>
                                        <div class="text-[10px] text-emerald-700 font-medium uppercase">To Receive</div>
                                    @elseif($party->current_balance < 0)
                                        <div class="font-bold text-rose-600 text-sm">₹{{ number_format(abs($party->current_balance), 2) }}</div>
                                        <div class="text-[10px] text-rose-700 font-medium uppercase">To Pay</div>
                                    @else
                                        <div class="font-bold text-slate-500 text-sm">₹0.00</div>
                                        <div class="text-[10px] text-slate-400 font-medium uppercase">Settled</div>
                                    @endif
                                </td>
                                <td class="p-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('parties.show', $party) }}" class="px-2.5 py-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 font-medium text-xs">
                                            Ledger
                                        </a>
                                        <a href="{{ route('parties.edit', $party) }}" class="p-1 rounded text-slate-500 hover:text-slate-800 hover:bg-slate-100" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form method="POST" action="{{ route('parties.destroy', $party) }}" onsubmit="return confirm('Delete this party?');" class="inline">
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
                                <td colspan="5" class="p-8 text-center text-slate-400">
                                    No parties found matching criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($parties->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $parties->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
