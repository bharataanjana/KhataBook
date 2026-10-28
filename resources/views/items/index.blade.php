<x-layouts.app>
    <x-slot:title>Items & Inventory - KhataApp</x-slot:title>

    <div class="space-y-6" x-data="{ adjustModal: false, activeItem: null }">
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Items & Inventory</h1>
                <p class="text-xs sm:text-sm text-slate-500">Manage catalog products, stock levels, GST tax rates, and adjustments</p>
            </div>
            <a href="{{ route('items.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Add Item
            </a>
        </div>

        <!-- Inventory KPI Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Stock Value (Cost)</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">₹{{ number_format($totalStockValue, 2) }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Valued at purchase rates</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Catalog Items</span>
                <div class="text-2xl font-bold text-blue-600 mt-1">{{ $totalItemsCount }} Products</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Across all categories</p>
            </div>

            <div class="bg-white rounded-xl p-4 border {{ $lowStockCount > 0 ? 'border-amber-200 bg-amber-50/20' : 'border-slate-200' }} shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider {{ $lowStockCount > 0 ? 'text-amber-800' : 'text-slate-500' }}">Low Stock Alerts</span>
                <div class="text-2xl font-bold {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-emerald-600' }} mt-1">
                    {{ $lowStockCount }} Items
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">At or below reorder threshold</p>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col md:flex-row gap-4 justify-between items-center shadow-xs">
            <!-- Category Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-1 md:pb-0">
                <a href="{{ route('items.index', ['search' => $search]) }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap {{ !$categoryId && !$lowStockOnly ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:text-slate-900' }}">
                    All Items
                </a>
                <a href="{{ route('items.index', ['low_stock' => 1, 'search' => $search]) }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap {{ $lowStockOnly ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    ⚠️ Low Stock Only
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('items.index', ['category_id' => $cat->id, 'search' => $search]) }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap {{ $categoryId == $cat->id ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:text-slate-900' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('items.index') }}" class="w-full md:w-80 flex gap-2">
                @if($categoryId) <input type="hidden" name="category_id" value="{{ $categoryId }}"> @endif
                @if($lowStockOnly) <input type="hidden" name="low_stock" value="1"> @endif
                <div class="relative w-full">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search item, code, HSN..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-slate-50 focus:bg-white">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3 py-2 text-xs font-semibold bg-slate-800 text-white rounded-lg hover:bg-slate-700">Search</button>
            </form>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px] tracking-wider">
                            <th class="p-3.5">Item Name</th>
                            <th class="p-3.5">Item Code / HSN</th>
                            <th class="p-3.5">Category</th>
                            <th class="p-3.5 text-right">Sale Price</th>
                            <th class="p-3.5 text-right">Purchase Price</th>
                            <th class="p-3.5 text-center">Stock</th>
                            <th class="p-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5">
                                    <a href="{{ route('items.show', $item) }}" class="font-bold text-slate-900 hover:text-blue-600 text-sm block">
                                        {{ $item->name }}
                                    </a>
                                    <span class="text-[11px] text-slate-400">Unit: {{ $item->unit }} &bull; GST {{ $item->tax_rate }}%</span>
                                </td>
                                <td class="p-3.5 text-slate-600">
                                    <div class="font-mono text-xs">{{ $item->item_code ?: '-' }}</div>
                                    <div class="text-[11px] text-slate-400">HSN: {{ $item->hsn_code ?: '-' }}</div>
                                </td>
                                <td class="p-3.5 text-slate-600">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-700">
                                        {{ $item->category?->name ?: 'General' }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-right font-bold text-slate-900 text-sm">
                                    ₹{{ number_format($item->sale_price, 2) }}
                                </td>
                                <td class="p-3.5 text-right text-slate-600">
                                    ₹{{ number_format($item->purchase_price, 2) }}
                                </td>
                                <td class="p-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $item->current_stock <= 0 ? 'bg-rose-100 text-rose-800' : ($item->isLowStock() ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                        {{ $item->current_stock }} {{ $item->unit }}
                                    </span>
                                    @if($item->isLowStock())
                                        <div class="text-[10px] text-amber-600 font-semibold mt-0.5">Low Stock</div>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <button @click="adjustModal = true; activeItem = { id: {{ $item->id }}, name: '{{ addslashes($item->name) }}', current_stock: {{ $item->current_stock }}, unit: '{{ $item->unit }}' }" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium" title="Adjust Stock">
                                            Adjust
                                        </button>
                                        <a href="{{ route('items.edit', $item) }}" class="p-1 rounded text-slate-500 hover:text-slate-800 hover:bg-slate-100" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form method="POST" action="{{ route('items.destroy', $item) }}" onsubmit="return confirm('Delete this item?');" class="inline">
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
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    No items found. Click "+ Add Item" to populate your catalog.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $items->links() }}
                </div>
            @endif
        </div>

        <!-- Stock Adjustment Modal -->
        <div x-cloak x-show="adjustModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
            <div @click.away="adjustModal = false" class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Adjust Stock Quantity</h3>
                    <button @click="adjustModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <template x-if="activeItem">
                    <form method="POST" :action="'/items/' + activeItem.id + '/adjust-stock'" class="space-y-4">
                        @csrf
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Item</span>
                            <div class="text-sm font-bold text-slate-800" x-text="activeItem.name"></div>
                            <div class="text-xs text-slate-500">Current Stock: <span class="font-bold" x-text="activeItem.current_stock + ' ' + activeItem.unit"></span></div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Action Type</label>
                            <select name="adjustment_type" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
                                <option value="add">+ Add Stock (Physical count higher / Returned)</option>
                                <option value="reduce">- Reduce Stock (Damaged / Expired / Lost)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Quantity</label>
                            <input type="number" step="0.01" min="0.01" name="quantity" required placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Reason / Notes</label>
                            <input type="text" name="reason" placeholder="e.g. Stock audit adjustment, broken unit" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="adjustModal = false" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white rounded-lg">Save Adjustment</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</x-layouts.app>
