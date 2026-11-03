<x-layouts.app>
    <x-slot:title>{{ $item->name }} - Item Details</x-slot:title>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('items.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                    ← Back to Items
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $item->name }}</h1>
                <p class="text-xs text-slate-500">Category: {{ $item->category?->name ?: 'General' }} &bull; SKU: {{ $item->item_code ?: 'N/A' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('items.edit', $item) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-xs">
                    Edit Item
                </a>
            </div>
        </div>

        <!-- Metric Highlights -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Current Stock</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $item->current_stock }} {{ $item->unit }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Threshold: {{ $item->low_stock_threshold }} {{ $item->unit }}</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sale Price</span>
                <div class="text-2xl font-bold text-emerald-600 mt-1">₹{{ number_format($item->sale_price, 2) }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Per {{ $item->unit }}</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Purchase Price</span>
                <div class="text-2xl font-bold text-blue-600 mt-1">₹{{ number_format($item->purchase_price, 2) }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Cost per {{ $item->unit }}</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">GST Tax Rate</span>
                <div class="text-2xl font-bold text-indigo-600 mt-1">{{ $item->tax_rate }}%</div>
                <div class="text-[11px] text-slate-500 mt-0.5">HSN: {{ $item->hsn_code ?: '-' }}</div>
            </div>
        </div>

        <!-- Transaction Histories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Sales of Item -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h2 class="text-sm font-bold text-slate-800">Recent Customer Sales</h2>
                    <span class="text-xs text-slate-500">Last 10 invoices</span>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($salesHistory as $sh)
                        <div class="p-3 flex items-center justify-between hover:bg-slate-50/80">
                            <div>
                                <a href="{{ route('sales.show', $sh->invoice) }}" class="font-bold text-blue-600 hover:text-blue-800">{{ $sh->invoice->invoice_number }}</a>
                                <p class="text-[11px] text-slate-500">{{ $sh->invoice->party->name }} &bull; {{ $sh->invoice->date->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-slate-900">{{ $sh->quantity }} {{ $sh->unit }} @ ₹{{ number_format($sh->unit_price, 2) }}</div>
                                <div class="text-[11px] text-slate-500">₹{{ number_format($sh->total, 2) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">No sales recorded yet for this item.</div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Purchases of Item -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h2 class="text-sm font-bold text-slate-800">Recent Vendor Purchases</h2>
                    <span class="text-xs text-slate-500">Last 10 purchase bills</span>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($purchaseHistory as $ph)
                        <div class="p-3 flex items-center justify-between hover:bg-slate-50/80">
                            <div>
                                <a href="{{ route('purchases.show', $ph->purchase) }}" class="font-bold text-blue-600 hover:text-blue-800">{{ $ph->purchase->bill_number }}</a>
                                <p class="text-[11px] text-slate-500">{{ $ph->purchase->party->name }} &bull; {{ $ph->purchase->date->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-slate-900">{{ $ph->quantity }} {{ $ph->unit }} @ ₹{{ number_format($ph->unit_price, 2) }}</div>
                                <div class="text-[11px] text-slate-500">₹{{ number_format($ph->total, 2) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">No purchase bills recorded yet for this item.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
