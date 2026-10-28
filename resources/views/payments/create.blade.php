<x-layouts.app>
    <x-slot:title>Record Payment - KhataApp</x-slot:title>

    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <a href="{{ route('payments.index', ['type' => $type]) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                ← Back to Payments
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                {{ $type === 'payment_in' ? 'Record Payment In (Receipt)' : 'Record Payment Out (Payment)' }}
            </h1>
            <p class="text-xs text-slate-500">
                {{ $type === 'payment_in' ? 'Money collected from a customer against an invoice or on-account' : 'Money disbursed to a vendor or supplier' }}
            </p>
        </div>

        <form method="POST" action="{{ route('payments.store') }}" class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-6">
            @csrf

            <!-- Type toggle -->
            <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1 rounded-lg">
                <a href="{{ route('payments.create', ['type' => 'payment_in']) }}" class="text-center py-2 text-xs font-bold rounded-md transition-colors {{ $type === 'payment_in' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Payment In (Money Received)
                </a>
                <a href="{{ route('payments.create', ['type' => 'payment_out']) }}" class="text-center py-2 text-xs font-bold rounded-md transition-colors {{ $type === 'payment_out' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Payment Out (Money Paid)
                </a>
            </div>
            <input type="hidden" name="payment_type" value="{{ $type }}">

            <!-- Reference Number & Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Receipt / Voucher # <span class="text-rose-500">*</span></label>
                    <input type="text" name="payment_number" value="{{ $paymentNumber }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="date" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                </div>
            </div>

            <!-- Party Picker -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Select {{ $type === 'payment_in' ? 'Customer' : 'Supplier' }} <span class="text-rose-500">*</span>
                </label>
                <select name="party_id" required onchange="window.location.href = '{{ route('payments.create') }}?type={{ $type }}&party_id=' + this.value" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white">
                    <option value="">-- Choose Party --</option>
                    @foreach($parties as $party)
                        <option value="{{ $party->id }}" {{ ($selectedParty && $selectedParty->id == $party->id) ? 'selected' : '' }}>
                            {{ $party->name }} ({{ $party->phone ?: 'No phone' }}) - Net Balance: {{ $party->current_balance >= 0 ? '₹' . number_format($party->current_balance, 2) . ' Dr' : '₹' . number_format(abs($party->current_balance), 2) . ' Cr' }}
                        </option>
                    @endforeach
                </select>
                @if($selectedParty)
                    <div class="mt-2 p-3 rounded-lg border {{ $selectedParty->current_balance > 0 ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200' }} text-xs flex justify-between items-center">
                        <span class="text-slate-600">Current Outstanding:</span>
                        <span class="font-bold {{ $selectedParty->current_balance > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            ₹{{ number_format(abs($selectedParty->current_balance), 2) }} {{ $selectedParty->current_balance >= 0 ? '(You will receive)' : '(You have to pay)' }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Optional Invoice / Bill Link -->
            @if($type === 'payment_in' && count($unpaidInvoices) > 0)
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Link to Unpaid Sale Invoice (Optional)</label>
                    <select name="invoice_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white">
                        <option value="">-- On Account (No Specific Bill) --</option>
                        @foreach($unpaidInvoices as $uInv)
                            <option value="{{ $uInv->id }}" {{ ($selectedInvoice && $selectedInvoice->id == $uInv->id) ? 'selected' : '' }}>
                                {{ $uInv->invoice_number }} (Date: {{ $uInv->date->format('d/m/Y') }} &bull; Balance Due: ₹{{ number_format($uInv->balance_amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @elseif($type === 'payment_out' && count($unpaidPurchases) > 0)
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Link to Unpaid Purchase Bill (Optional)</label>
                    <select name="purchase_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white">
                        <option value="">-- On Account (No Specific Bill) --</option>
                        @foreach($unpaidPurchases as $uPur)
                            <option value="{{ $uPur->id }}" {{ ($selectedPurchase && $selectedPurchase->id == $uPur->id) ? 'selected' : '' }}>
                                {{ $uPur->bill_number }} (Date: {{ $uPur->date->format('d/m/Y') }} &bull; Balance Due: ₹{{ number_format($uPur->balance_amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Amount & Payment Mode -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $selectedInvoice ? $selectedInvoice->balance_amount : ($selectedPurchase ? $selectedPurchase->balance_amount : '')) }}" required placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 text-lg font-bold text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Payment Mode <span class="text-rose-500">*</span></label>
                    <select name="payment_mode" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white font-medium">
                        <option value="cash">Cash in Hand</option>
                        <option value="bank">Bank Account / NEFT / IMPS</option>
                        <option value="upi">UPI / QR Code</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
            </div>

            <!-- Reference Number & Notes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Reference / UTR / Cheque #</label>
                    <input type="text" name="reference_number" placeholder="Optional transaction ID" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Notes / Remarks</label>
                    <input type="text" name="notes" placeholder="e.g. Cleared full month settlement" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('payments.index', ['type' => $type]) }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-xs font-bold {{ $type === 'payment_in' ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-blue-600 hover:bg-blue-500' }} text-white rounded-lg shadow-sm">
                    ✓ Save Payment Record
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
