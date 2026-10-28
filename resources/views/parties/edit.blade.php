<x-layouts.app>
    <x-slot:title>Edit {{ $party->name }} - KhataApp</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('parties.show', $party) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                    ← Back to Party
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Party: {{ $party->name }}</h1>
            </div>
        </div>

        <form method="POST" action="{{ route('parties.update', $party) }}" class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Party Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $party->name) }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Party Type <span class="text-rose-500">*</span></label>
                    <select name="type" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white">
                        <option value="customer" {{ old('type', $party->type) === 'customer' ? 'selected' : '' }}>Customer (Buyer)</option>
                        <option value="supplier" {{ old('type', $party->type) === 'supplier' ? 'selected' : '' }}>Supplier / Vendor (Seller)</option>
                        <option value="both" {{ old('type', $party->type) === 'both' ? 'selected' : '' }}>Both (Customer & Supplier)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Mobile / Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $party->phone) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $party->email) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                </div>
            </div>

            <!-- GST & Identification -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">GST & Tax Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">GSTIN</label>
                        <input type="text" name="gstin" value="{{ old('gstin', $party->gstin) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">PAN Number</label>
                        <input type="text" name="pan" value="{{ old('pan', $party->pan) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">State & State Code</label>
                        <div class="flex gap-2">
                            <input type="text" name="state" value="{{ old('state', $party->state) }}" class="w-2/3 px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                            <input type="text" name="state_code" value="{{ old('state_code', $party->state_code) }}" class="w-1/3 px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Balances -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Opening Balance & Credit</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Opening Balance (₹)</label>
                        <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', $party->opening_balance) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Balance Type</label>
                        <select name="opening_balance_type" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white">
                            <option value="to_receive" {{ old('opening_balance_type', $party->opening_balance_type) === 'to_receive' ? 'selected' : '' }}>To Receive (Customer owes you)</option>
                            <option value="to_pay" {{ old('opening_balance_type', $party->opening_balance_type) === 'to_pay' ? 'selected' : '' }}>To Pay (You owe supplier)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Credit Limit (₹)</label>
                        <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $party->credit_limit) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>
                </div>
            </div>

            <!-- Addresses -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Address & Notes</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Billing Address</label>
                        <textarea name="billing_address" rows="3" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">{{ old('billing_address', $party->billing_address) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Shipping Address</label>
                        <textarea name="shipping_address" rows="3" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">{{ old('shipping_address', $party->shipping_address) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('parties.show', $party) }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</a>
                <button type="submit" class="px-5 py-2 text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white rounded-lg shadow-sm">
                    Update Party
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
