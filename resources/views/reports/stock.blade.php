<x-layouts.app>
    <x-slot:title>Stock Valuation - KhataApp</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('reports.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                    ← Back to Reports
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Stock Summary & Inventory Valuation</h1>
                <p class="text-xs sm:text-sm text-slate-500">Asset valuation report of all physical stock in warehouse</p>
            </div>
            <button onclick="window.print()" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-xs">
                🖨️ Print Report
            </button>
        </div>

        <!-- Valuation KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Valuation (Cost Price)</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">₹{{ number_format($totalStockValuation, 2) }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Asset capital tied in inventory</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Valuation (Retail Sale)</span>
                <div class="text-2xl font-bold text-blue-600 mt-1">₹{{ number_format($totalRetailValuation, 2) }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Expected realization on full sale</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-emerald-200 bg-emerald-50/20 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Gross Margin Potential</span>
                <div class="text-2xl font-bold text-emerald-600 mt-1">
                    ₹{{ number_format(max(0, $totalRetailValuation - $totalStockValuation), 2) }}
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">Potential profit locked in stock</p>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px] tracking-wider">
                            <th class="p-3.5">Item Name</th>
                            <th class="p-3.5">Category</th>
                            <th class="p-3.5 text-center">Stock Quantity</th>
                            <th class="p-3.5 text-right">Cost Rate</th>
                            <th class="p-3.5 text-right">Cost Value (₹)</th>
                            <th class="p-3.5 text-right">Sale Rate</th>
                            <th class="p-3.5 text-right">Retail Value (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $itm)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5">
                                    <a href="{{ route('items.show', $itm) }}" class="font-bold text-slate-900 hover:text-blue-600 text-sm">
                                        {{ $itm->name }}
                                    </a>
                                    <span class="text-[11px] text-slate-400 block font-mono">HSN: {{ $itm->hsn_code ?: '-' }}</span>
                                </td>
                                <td class="p-3.5 text-slate-600">{{ $itm->category?->name ?: 'General' }}</td>
                                <td class="p-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold {{ $itm->current_stock <= 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-800' }}">
                                        {{ $itm->current_stock }} {{ $itm->unit }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-right text-slate-600">₹{{ number_format($itm->purchase_price, 2) }}</td>
                                <td class="p-3.5 text-right font-bold text-slate-900">₹{{ number_format($itm->stock_value, 2) }}</td>
                                <td class="p-3.5 text-right text-slate-600">₹{{ number_format($itm->sale_price, 2) }}</td>
                                <td class="p-3.5 text-right font-bold text-blue-600">₹{{ number_format($itm->retail_value, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-8 text-center text-slate-400">No items in catalog.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100 font-bold border-t border-slate-200 text-slate-900">
                            <td colspan="4" class="p-3.5 text-right uppercase text-[11px]">Total Inventory Valuation:</td>
                            <td class="p-3.5 text-right text-sm">₹{{ number_format($totalStockValuation, 2) }}</td>
                            <td></td>
                            <td class="p-3.5 text-right text-sm text-blue-600">₹{{ number_format($totalRetailValuation, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
