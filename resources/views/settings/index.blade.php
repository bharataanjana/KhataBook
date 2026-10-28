<x-layouts.app>
    <x-slot:title>Business Settings - KhataApp</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Business Profile & Bill Settings</h1>
            <p class="text-xs sm:text-sm text-slate-500">Customize company info, GST credentials, bank account, and invoice terms</p>
        </div>

        <form method="POST" action="{{ route('settings.update') }}" class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Company Profile -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">General Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Company / Firm Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $business->name) }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 text-sm font-bold text-slate-900">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $business->phone) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $business->email) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Shop / Office Address</label>
                        <textarea name="address" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">{{ old('address', $business->address) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city', $business->city) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Pin Code</label>
                        <input type="text" name="pincode" value="{{ old('pincode', $business->pincode) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>
                </div>
            </div>

            <!-- GST & State -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">GST & Tax Registration</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">GSTIN Number</label>
                        <input type="text" name="gstin" value="{{ old('gstin', $business->gstin) }}" placeholder="27ABCDE1234F1Z5" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">PAN Number</label>
                        <input type="text" name="pan" value="{{ old('pan', $business->pan) }}" placeholder="ABCDE1234F" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Home State & State Code</label>
                        <div class="flex gap-2">
                            <input type="text" name="state" value="{{ old('state', $business->state) }}" placeholder="State" class="w-2/3 px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                            <input type="text" name="state_code" value="{{ old('state_code', $business->state_code) }}" placeholder="Code" class="w-1/3 px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank & UPI -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Bank Details (Prints on Invoices for Customer Payments)</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $business->bank_name) }}" placeholder="e.g. HDFC Bank" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $business->account_number) }}" placeholder="e.g. 50200012345678" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">IFSC Code</label>
                        <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $business->ifsc_code) }}" placeholder="e.g. HDFC0001234" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Branch</label>
                        <input type="text" name="branch" value="{{ old('branch', $business->branch) }}" placeholder="e.g. Fort, Mumbai" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">UPI ID (VPA)</label>
                        <input type="text" name="upi_id" value="{{ old('upi_id', $business->upi_id) }}" placeholder="e.g. yourbusiness@okaxis / yourstore@upi" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono">
                    </div>
                </div>
            </div>

            <!-- Invoice Numbering & Terms -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Numbering Prefixes & Terms</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Sale Invoice Prefix</label>
                        <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $business->invoice_prefix) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Estimate / Quotation Prefix</label>
                        <input type="text" name="quotation_prefix" value="{{ old('quotation_prefix', $business->quotation_prefix) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Purchase Bill Prefix</label>
                        <input type="text" name="purchase_prefix" value="{{ old('purchase_prefix', $business->purchase_prefix) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Default Terms & Conditions</label>
                    <textarea name="terms_and_conditions" rows="3" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">{{ old('terms_and_conditions', $business->terms_and_conditions) }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white rounded-lg shadow-sm">
                    ✓ Save Settings
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
