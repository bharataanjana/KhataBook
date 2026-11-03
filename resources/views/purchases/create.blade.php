<x-layouts.app>
    <x-slot:title>New Purchase Bill - KhataApp</x-slot:title>

    <div class="max-w-6xl mx-auto space-y-6" x-data="purchaseBuilder()">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('purchases.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                    ← Back to Purchases
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $type === 'purchase_return' ? 'Record Purchase Return (Debit Note)' : 'Record Purchase Bill' }}
                </h1>
            </div>
            <div class="text-xs text-slate-500 font-mono">
                Bill # <span class="font-bold text-slate-900">{{ $nextBillNumber }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('purchases.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="purchase_type" value="{{ $type }}">

            <!-- Supplier & Bill Meta -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Supplier Selector -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Select Supplier / Vendor <span class="text-rose-500">*</span></label>
                        <select name="party_id" x-model="selectedPartyId" @change="onPartyChange()" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white font-medium">
                            <option value="">-- Choose Supplier --</option>
                            @foreach($parties as $party)
                                <option value="{{ $party->id }}" data-state="{{ $party->state_code }}">
                                    {{ $party->name }} ({{ $party->phone ?: 'No phone' }}) - {{ $party->current_balance < 0 ? '₹' . number_format(abs($party->current_balance), 2) . ' You Owe' : '₹0.00' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-1 flex items-center justify-between text-[11px]">
                            <span class="text-slate-500">Supplier State: <span class="font-semibold text-slate-700" x-text="partyState || 'Default'"></span></span>
                            <a href="{{ route('parties.create') }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">+ Add New Supplier</a>
                        </div>
                    </div>

                    <!-- Bill Number -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Vendor Bill # <span class="text-rose-500">*</span></label>
                        <input type="text" name="bill_number" value="{{ $nextBillNumber }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono font-semibold">
                    </div>

                    <!-- Bill Date -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Bill Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="date" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Payment Due Date</label>
                        <input type="date" name="due_date" value="{{ now()->addDays(30)->toDateString() }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <!-- Tax Type Banner -->
                    <div class="md:col-span-3 flex items-center justify-end">
                        <div class="px-3 py-2 rounded-lg text-xs" :class="isInterState ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200'">
                            <span class="font-bold" x-text="isInterState ? 'Inter-State GST (IGST Applied)' : 'Intra-State GST (CGST + SGST Split)'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Inward Items / Stock Received</h2>
                    <button type="button" @click="addItemRow()" class="px-3 py-1 rounded-md text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-xs">
                        + Add Row
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100/70 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[10px] tracking-wider">
                                <th class="p-3 w-10 text-center">#</th>
                                <th class="p-3 min-w-[200px]">Item Description</th>
                                <th class="p-3 w-24">HSN</th>
                                <th class="p-3 w-24 text-right">Qty</th>
                                <th class="p-3 w-20">Unit</th>
                                <th class="p-3 w-28 text-right">Cost Rate (₹)</th>
                                <th class="p-3 w-24 text-right">GST %</th>
                                <th class="p-3 w-28 text-right">Amount (₹)</th>
                                <th class="p-3 w-12 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(row, index) in items" :key="index">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3 text-center text-slate-400 font-mono" x-text="index + 1"></td>
                                    
                                    <td class="p-3">
                                        <select :name="'items[' + index + '][item_id]'" x-model="row.item_id" @change="onItemSelect(index)" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded focus:outline-blue-500 bg-white mb-1">
                                            <option value="">-- Choose Item or Custom --</option>
                                            @foreach($items as $itm)
                                                <option value="{{ $itm->id }}" data-name="{{ $itm->name }}" data-cost="{{ $itm->purchase_price }}" data-tax="{{ $itm->tax_rate }}" data-unit="{{ $itm->unit }}" data-hsn="{{ $itm->hsn_code }}">
                                                    {{ $itm->name }} (Stock: {{ $itm->current_stock }} {{ $itm->unit }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" :name="'items[' + index + '][item_name]'" x-model="row.item_name" placeholder="Item description" required class="w-full px-2 py-1 text-[11px] border border-slate-200 rounded">
                                    </td>

                                    <td class="p-3">
                                        <input type="text" :name="'items[' + index + '][hsn_code]'" x-model="row.hsn_code" placeholder="HSN" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" step="0.01" min="0.01" :name="'items[' + index + '][quantity]'" x-model.number="row.quantity" @input="recalculate()" required class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded text-right font-medium">
                                    </td>

                                    <td class="p-3">
                                        <input type="text" :name="'items[' + index + '][unit]'" x-model="row.unit" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded text-center">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_price]'" x-model.number="row.unit_price" @input="recalculate()" required class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded text-right font-medium">
                                    </td>

                                    <td class="p-3">
                                        <select :name="'items[' + index + '][tax_percent]'" x-model.number="row.tax_percent" @change="recalculate()" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded bg-white text-right">
                                            <option value="0">0%</option>
                                            <option value="5">5%</option>
                                            <option value="12">12%</option>
                                            <option value="18">18%</option>
                                            <option value="28">28%</option>
                                        </select>
                                    </td>

                                    <td class="p-3 text-right font-bold text-slate-800" x-text="'₹' + row.total.toFixed(2)"></td>

                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeItemRow(index)" class="text-rose-400 hover:text-rose-600">&times;</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <button type="button" @click="addItemRow()" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                        + Add Another Item
                    </button>
                    <span class="text-xs text-slate-500" x-text="items.length + ' item(s) in bill'"></span>
                </div>
            </div>

            <!-- Totals & Payment Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Notes -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Bill Notes / Transporter Info</label>
                    <textarea name="notes" rows="4" placeholder="e.g. Received via GATI logistics, LR # 12345" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500"></textarea>
                </div>

                <!-- Totals -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 space-y-3">
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Taxable Subtotal:</span>
                        <span class="font-semibold text-slate-900" x-text="'₹' + subtotal.toFixed(2)"></span>
                    </div>

                    <div class="flex justify-between items-center text-xs text-slate-600 pt-2 border-t border-slate-100">
                        <span>Supplier Discount (₹):</span>
                        <input type="number" step="0.01" min="0" name="discount_amount" x-model.number="discountAmount" @input="recalculate()" class="w-24 px-2 py-1 text-xs border border-slate-300 rounded text-right">
                    </div>

                    <div class="flex justify-between text-xs text-slate-600 pt-2 border-t border-slate-100">
                        <span>Total GST:</span>
                        <span class="font-medium" x-text="'₹' + totalTax.toFixed(2)"></span>
                    </div>

                    <div class="flex justify-between items-center text-xs text-slate-600 pt-2 border-t border-slate-100">
                        <span>Round Off:</span>
                        <input type="number" step="0.01" name="round_off" x-model.number="roundOff" @input="recalculate()" class="w-24 px-2 py-1 text-xs border border-slate-300 rounded text-right">
                    </div>

                    <div class="flex justify-between items-center pt-3 border-t-2 border-slate-900 text-sm font-bold text-slate-900">
                        <span>Grand Total:</span>
                        <span class="text-xl text-blue-600" x-text="'₹' + grandTotal.toFixed(2)"></span>
                    </div>

                    @if($type === 'purchase')
                        <!-- Immediate Payment Out Section -->
                        <div class="pt-3 border-t border-slate-200 space-y-2 bg-slate-50 p-3 rounded-lg">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-semibold text-slate-700">Amount Paid to Supplier:</label>
                                <input type="number" step="0.01" min="0" name="paid_amount" x-model.number="paidAmount" @input="recalculate()" class="w-32 px-2.5 py-1 text-xs border border-blue-400 bg-white rounded font-bold text-blue-600 text-right">
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-600">Payment Mode:</span>
                                <select name="payment_mode" class="px-2 py-1 text-xs border border-slate-300 rounded bg-white">
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer / NEFT</option>
                                    <option value="upi">UPI</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="flex justify-between items-center text-xs pt-1 font-semibold">
                                <span class="text-slate-600">Remaining Balance:</span>
                                <span :class="balanceDue > 0 ? 'text-rose-600 font-bold' : 'text-emerald-600'" x-text="'₹' + balanceDue.toFixed(2)"></span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200">
                <a href="{{ route('purchases.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white rounded-lg shadow-md transition-all">
                    ✓ Save Purchase Bill
                </button>
            </div>
        </form>
    </div>

    <script>
        function purchaseBuilder() {
            return {
                selectedPartyId: '',
                partyState: '',
                isInterState: false,
                businessStateCode: '{{ $business->state_code ?: '27' }}',
                items: [
                    { item_id: '', item_name: '', hsn_code: '', quantity: 1, unit: 'PCS', unit_price: 0, tax_percent: 18, total: 0 }
                ],
                discountAmount: 0,
                roundOff: 0,
                subtotal: 0,
                totalTax: 0,
                grandTotal: 0,
                paidAmount: 0,
                balanceDue: 0,

                onPartyChange() {
                    const select = document.querySelector('select[name="party_id"]');
                    const selectedOption = select.options[select.selectedIndex];
                    const stateCode = selectedOption?.getAttribute('data-state') || '';
                    this.partyState = stateCode;
                    this.isInterState = Boolean(stateCode && this.businessStateCode && stateCode !== this.businessStateCode);
                    this.recalculate();
                },

                addItemRow() {
                    this.items.push({
                        item_id: '',
                        item_name: '',
                        hsn_code: '',
                        quantity: 1,
                        unit: 'PCS',
                        unit_price: 0,
                        tax_percent: 18,
                        total: 0
                    });
                },

                removeItemRow(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                        this.recalculate();
                    }
                },

                onItemSelect(index) {
                    const row = this.items[index];
                    const rowSelect = document.querySelector(`select[name="items[${index}][item_id]"]`);
                    const selected = rowSelect.options[rowSelect.selectedIndex];

                    if (row.item_id && selected) {
                        row.item_name = selected.getAttribute('data-name') || '';
                        row.unit_price = parseFloat(selected.getAttribute('data-cost') || 0);
                        row.tax_percent = parseFloat(selected.getAttribute('data-tax') || 0);
                        row.unit = selected.getAttribute('data-unit') || 'PCS';
                        row.hsn_code = selected.getAttribute('data-hsn') || '';
                    }
                    this.recalculate();
                },

                recalculate() {
                    let sub = 0;
                    let tax = 0;

                    this.items.forEach(row => {
                        const qty = parseFloat(row.quantity) || 0;
                        const rate = parseFloat(row.unit_price) || 0;
                        const taxRate = parseFloat(row.tax_percent) || 0;

                        const taxable = qty * rate;
                        const taxAmt = (taxable * taxRate) / 100;
                        row.total = taxable + taxAmt;

                        sub += taxable;
                        tax += taxAmt;
                    });

                    this.subtotal = sub;
                    this.totalTax = tax;

                    const rawTotal = Math.max(0, this.subtotal - (parseFloat(this.discountAmount) || 0) + this.totalTax + (parseFloat(this.roundOff) || 0));
                    this.grandTotal = Math.round(rawTotal * 100) / 100;
                    this.balanceDue = Math.max(0, this.grandTotal - (parseFloat(this.paidAmount) || 0));
                }
            }
        }
    </script>
</x-layouts.app>
