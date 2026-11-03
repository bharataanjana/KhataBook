<x-layouts.app>
    <x-slot:title>Add Item - KhataApp</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <a href="{{ route('items.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                ← Back to Items
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add New Product / Item</h1>
            <p class="text-xs text-slate-500">Add item pricing, GST tax rates, units, and opening stock</p>
        </div>

        <form method="POST" action="{{ route('items.store') }}" class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-6">
            @csrf

            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Item Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Wireless Mouse 2.4GHz / Basmati Rice 10kg" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Item Code / SKU</label>
                    <input type="text" name="item_code" value="{{ old('item_code') }}" placeholder="e.g. ITM-001" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">HSN / SAC Code</label>
                    <input type="text" name="hsn_code" value="{{ old('hsn_code') }}" placeholder="e.g. 8471" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Category</label>
                    <select name="category_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white">
                        <option value="">General (No Category)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Unit of Measure <span class="text-rose-500">*</span></label>
                    <select name="unit" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white">
                        <option value="PCS" {{ old('unit') == 'PCS' ? 'selected' : '' }}>PCS (Pieces)</option>
                        <option value="BOX" {{ old('unit') == 'BOX' ? 'selected' : '' }}>BOX (Box)</option>
                        <option value="KG" {{ old('unit') == 'KG' ? 'selected' : '' }}>KG (Kilograms)</option>
                        <option value="PKT" {{ old('unit') == 'PKT' ? 'selected' : '' }}>PKT (Packet)</option>
                        <option value="LTR" {{ old('unit') == 'LTR' ? 'selected' : '' }}>LTR (Litres)</option>
                        <option value="MTR" {{ old('unit') == 'MTR' ? 'selected' : '' }}>MTR (Meters)</option>
                        <option value="NOS" {{ old('unit') == 'NOS' ? 'selected' : '' }}>NOS (Numbers)</option>
                    </select>
                </div>
            </div>

            <!-- Pricing & GST -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Pricing & GST Tax Rate</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Sale Price (₹) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', '0.00') }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Purchase Price (₹) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', '0.00') }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">GST Tax Rate (%) <span class="text-rose-500">*</span></label>
                        <select name="tax_rate" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white font-semibold">
                            <option value="0" {{ old('tax_rate') == '0' ? 'selected' : '' }}>0% (Exempted / Nil)</option>
                            <option value="5" {{ old('tax_rate') == '5' ? 'selected' : '' }}>5% GST</option>
                            <option value="12" {{ old('tax_rate') == '12' ? 'selected' : '' }}>12% GST</option>
                            <option value="18" {{ old('tax_rate', '18') == '18' ? 'selected' : '' }}>18% GST (Standard)</option>
                            <option value="28" {{ old('tax_rate') == '28' ? 'selected' : '' }}>28% GST</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stock & Alert Levels -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Stock & Low-Stock Alerts</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Opening Stock Quantity</label>
                        <input type="number" step="0.01" name="opening_stock" value="{{ old('opening_stock', '0') }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                        <p class="text-[11px] text-slate-400 mt-1">Initial quantity in your store right now</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Low Stock Warning Limit</label>
                        <input type="number" step="0.01" name="low_stock_threshold" value="{{ old('low_stock_threshold', '5') }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                        <p class="text-[11px] text-slate-400 mt-1">Alerts you when stock falls to or below this quantity</p>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('items.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</a>
                <button type="submit" class="px-5 py-2 text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white rounded-lg shadow-sm">
                    Save Item
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
