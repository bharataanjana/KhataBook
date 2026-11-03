<x-layouts.app>
    <x-slot:title>New Sale Bill - KhataApp</x-slot:title>

    <div class="max-w-6xl mx-auto space-y-6" x-data="invoiceBuilder()">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                    ← Back to Invoices
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $type === 'quotation' ? 'Create Estimate / Quotation' : ($type === 'sale_return' ? 'Record Sale Return' : 'New Sale Invoice') }}
                </h1>
            </div>
            <div class="text-xs text-slate-500 font-mono">
                Bill # <span class="font-bold text-slate-900">{{ $nextInvoiceNumber }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('sales.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="invoice_type" value="{{ $type }}">

            <!-- Customer & Invoice Metadata Box -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Customer Selector -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Select Customer <span class="text-rose-500">*</span></label>
                        <select name="party_id" x-model="selectedPartyId" @change="onPartyChange()" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white font-medium">
                            <option value="">-- Choose Customer --</option>
                            @foreach($parties as $party)
                                <option value="{{ $party->id }}" data-balance="{{ $party->current_balance }}" data-state="{{ $party->state_code }}">
                                    {{ $party->name }} ({{ $party->phone ?: 'No phone' }}) - {{ $party->current_balance >= 0 ? '₹' . number_format($party->current_balance, 2) . ' Dr' : '₹' . number_format(abs($party->current_balance), 2) . ' Cr' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-1 flex items-center justify-between text-[11px]">
                            <span class="text-slate-500">Party State: <span class="font-semibold text-slate-700" x-text="partyState || 'Default'"></span></span>
                            <a href="{{ route('parties.create') }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">+ Add New Party</a>
                        </div>
                    </div>

                    <!-- Invoice Number -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Invoice Number <span class="text-rose-500">*</span></label>
                        <input type="text" name="invoice_number" value="{{ $nextInvoiceNumber }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono font-semibold">
                    </div>

                    <!-- Invoice Date -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Invoice Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="date" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Payment Due Date</label>
                        <input type="date" name="due_date" value="{{ now()->addDays(15)->toDateString() }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <!-- Reference / PO -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Reference / PO #</label>
                        <input type="text" name="reference_no" placeholder="Optional PO reference" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <!-- Tax Type Banner -->
                    <div class="md:col-span-2 flex items-center justify-end">
                        <div class="px-3 py-2 rounded-lg text-xs" :class="isInterState ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200'">
                            <span class="font-bold" x-text="isInterState ? 'Inter-State GST (IGST Applied)' : 'Intra-State GST (CGST + SGST Split)'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Line Items & Products</h2>
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
                                <th class="p-3 w-28 text-right">Rate (₹)</th>
                                <th class="p-3 w-20 text-right">Disc %</th>
                                <th class="p-3 w-24 text-right">GST %</th>
                                <th class="p-3 w-28 text-right">Amount (₹)</th>
                                <th class="p-3 w-12 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(row, index) in items" :key="index">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3 text-center text-slate-400 font-mono" x-text="index + 1"></td>
                                    
                                    <!-- Item Selector / Custom Name -->
                                    <td class="p-3">
                                        <select :name="'items[' + index + '][item_id]'" x-model="row.item_id" @change="onItemSelect(index)" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded focus:outline-blue-500 bg-white mb-1">
                                            <option value="">-- Custom Item or Choose --</option>
                                            @foreach($items as $itm)
                                                <option value="{{ $itm->id }}" data-name="{{ $itm->name }}" data-price="{{ $itm->sale_price }}" data-tax="{{ $itm->tax_rate }}" data-unit="{{ $itm->unit }}" data-hsn="{{ $itm->hsn_code }}">
                                                    {{ $itm->name }} (Stock: {{ $itm->current_stock }} {{ $itm->unit }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" :name="'items[' + index + '][item_name]'" x-model="row.item_name" placeholder="Item description" required class="w-full px-2 py-1 text-[11px] border border-slate-200 rounded focus:outline-blue-500">
                                    </td>

                                    <!-- HSN -->
                                    <td class="p-3">
                                        <input type="text" :name="'items[' + index + '][hsn_code]'" x-model="row.hsn_code" placeholder="HSN" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded">
                                    </td>

                                    <!-- Qty -->
                                    <td class="p-3">
                                        <input type="number" step="0.01" min="0.01" :name="'items[' + index + '][quantity]'" x-model.number="row.quantity" @input="recalculate()" required class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded text-right font-medium">
                                    </td>

                                    <!-- Unit -->
                                    <td class="p-3">
                                        <input type="text" :name="'items[' + index + '][unit]'" x-model="row.unit" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded text-center">
                                    </td>

                                    <!-- Rate -->
                                    <td class="p-3">
                                        <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_price]'" x-model.number="row.unit_price" @input="recalculate()" required class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded text-right font-medium">
                                    </td>

                                    <!-- Discount % -->
                                    <td class="p-3">
                                        <input type="number" step="0.01" min="0" max="100" :name="'items[' + index + '][discount_percent]'" x-model.number="row.discount_percent" @input="recalculate()" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded text-right">
                                    </td>

                                    <!-- GST % -->
                                    <td class="p-3">
                                        <select :name="'items[' + index + '][tax_percent]'" x-model.number="row.tax_percent" @change="recalculate()" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded bg-white text-right">
                                            <option value="0">0%</option>
                                            <option value="5">5%</option>
                                            <option value="12">12%</option>
                                            <option value="18">18%</option>
                                            <option value="28">28%</option>
                                        </select>
                                    </td>

                                    <!-- Row Total -->
                                    <td class="p-3 text-right font-bold text-slate-800" x-text="'₹' + row.total.toFixed(2)"></td>

                                    <!-- Remove Row -->
                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeItemRow(index)" class="text-rose-400 hover:text-rose-600" title="Remove">&times;</button>
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

            <!-- Calculation Summary & Payment Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Notes & Terms -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Customer Notes</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Thanks for your business!" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Terms & Conditions</label>
                        <textarea name="terms" rows="3" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">{{ $business->terms_and_conditions }}</textarea>
                    </div>
                </div>

                <!-- Totals & Payment Box -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 space-y-3">
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Subtotal (Taxable):</span>
                        <span class="font-semibold text-slate-900" x-text="'₹' + subtotal.toFixed(2)"></span>
                    </div>

                    <!-- Overall Discount -->
                    <div class="flex justify-between items-center text-xs text-slate-600 pt-2 border-t border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <span>Discount:</span>
                            <select name="discount_type" x-model="discountType" @change="recalculate()" class="px-1.5 py-0.5 text-[11px] border border-slate-300 rounded bg-white">
                                <option value="fixed">Fixed (₹)</option>
                                <option value="percentage">%</option>
                            </select>
                        </div>
                        <input type="number" step="0.01" min="0" name="discount_value" x-model.number="discountValue" @input="recalculate()" class="w-24 px-2 py-1 text-xs border border-slate-300 rounded text-right">
                    </div>

                    <!-- Tax Breakdown -->
                    <template x-if="!isInterState">
                        <div class="space-y-1 pt-2 border-t border-slate-100 text-xs text-slate-600">
                            <div class="flex justify-between">
                                <span>CGST:</span>
                                <span class="font-medium" x-text="'₹' + (totalTax / 2).toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span>SGST:</span>
                                <span class="font-medium" x-text="'₹' + (totalTax / 2).toFixed(2)"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="isInterState">
                        <div class="flex justify-between pt-2 border-t border-slate-100 text-xs text-slate-600">
                            <span>IGST (Integrated Tax):</span>
                            <span class="font-medium" x-text="'₹' + totalTax.toFixed(2)"></span>
                        </div>
                    </template>

                    <!-- Round Off -->
                    <div class="flex justify-between items-center text-xs text-slate-600 pt-2 border-t border-slate-100">
                        <span>Round Off:</span>
                        <input type="number" step="0.01" name="round_off" x-model.number="roundOff" @input="recalculate()" class="w-24 px-2 py-1 text-xs border border-slate-300 rounded text-right">
                    </div>

                    <!-- Grand Total -->
                    <div class="flex justify-between items-center pt-3 border-t-2 border-slate-900 text-sm font-bold text-slate-900">
                        <span>Grand Total:</span>
                        <span class="text-xl text-blue-600" x-text="'₹' + grandTotal.toFixed(2)"></span>
                    </div>

                    @if($type === 'sale')
                        <!-- Immediate Payment Section -->
                        <div class="pt-3 border-t border-slate-200 space-y-2 bg-slate-50 p-3 rounded-lg">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-semibold text-slate-700">Amount Received:</label>
                                <input type="number" step="0.01" min="0" name="paid_amount" x-model.number="paidAmount" @input="recalculate()" class="w-32 px-2.5 py-1 text-xs border border-emerald-400 bg-white rounded font-bold text-emerald-600 text-right">
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-600">Payment Mode:</span>
                                <select name="payment_mode" class="px-2 py-1 text-xs border border-slate-300 rounded bg-white">
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer / NEFT</option>
                                    <option value="upi">UPI / QR Code</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="flex justify-between items-center text-xs pt-1 font-semibold">
                                <span class="text-slate-600">Balance Due:</span>
                                <span :class="balanceDue > 0 ? 'text-rose-600 font-bold' : 'text-emerald-600'" x-text="'₹' + balanceDue.toFixed(2)"></span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200">
                <a href="{{ route('sales.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow-md transition-all">
                    ✓ Save & Generate Invoice
                </button>
            </div>
        </form>
    </div>

    <!-- Alpine Invoice Builder Logic -->
    <script>
        function invoiceBuilder() {
            return {
                selectedPartyId: '{{ request('party_id') }}',
                partyState: '',
                isInterState: false,
                businessStateCode: '{{ $business->state_code ?: '27' }}',
                items: [
                    { item_id: '', item_name: '', hsn_code: '', quantity: 1, unit: 'PCS', unit_price: 0, discount_percent: 0, tax_percent: 18, total: 0 }
                ],
                discountType: 'fixed',
                discountValue: 0,
                roundOff: 0,
                subtotal: 0,
                totalTax: 0,
                grandTotal: 0,
                paidAmount: 0,
                balanceDue: 0,

                init() {
                    if (this.selectedPartyId) {
                        this.onPartyChange();
                    }
                    this.recalculate();
                },

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
                        discount_percent: 0,
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
                        row.unit_price = parseFloat(selected.getAttribute('data-price') || 0);
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
                        const disc = parseFloat(row.discount_percent) || 0;
                        const taxRate = parseFloat(row.tax_percent) || 0;

                        const gross = qty * rate;
                        const discAmt = (gross * disc) / 100;
                        const taxable = gross - discAmt;
                        const taxAmt = (taxable * taxRate) / 100;
                        row.total = taxable + taxAmt;

                        sub += taxable;
                        tax += taxAmt;
                    });

                    this.subtotal = sub;
                    this.totalTax = tax;

                    let discountAmt = 0;
                    if (this.discountType === 'percentage') {
                        discountAmt = (this.subtotal * (parseFloat(this.discountValue) || 0)) / 100;
                    } else {
                        discountAmt = parseFloat(this.discountValue) || 0;
                    }

                    const rawTotal = Math.max(0, this.subtotal - discountAmt + this.totalTax + (parseFloat(this.roundOff) || 0));
                    this.grandTotal = Math.round(rawTotal * 100) / 100;

                    if (this.paidAmount === 0 && '{{ $type }}' === 'sale' && this.items[0]?.unit_price > 0 && !this.manualPaid) {
                        // Keep paid amount as specified by user
                    }
                    this.balanceDue = Math.max(0, this.grandTotal - (parseFloat(this.paidAmount) || 0));
                }
            }
        }
    </script>
</x-layouts.app>
