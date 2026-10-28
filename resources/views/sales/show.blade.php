<x-layouts.app>
    <x-slot:title>Invoice {{ $invoice->invoice_number }} - KhataApp</x-slot:title>

    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Top Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('sales.index', ['type' => $invoice->invoice_type]) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                    ← Back to Invoices
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $invoice->invoice_number }}</h1>
                    @if($invoice->payment_status === 'paid')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">PAID</span>
                    @elseif($invoice->payment_status === 'partial')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">PARTIAL</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800">UNPAID</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('sales.print', $invoice) }}" target="_blank" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-xs">
                    🖨️ Print GST Invoice
                </a>

                @if($invoice->invoice_type === 'quotation')
                    <form method="POST" action="{{ route('sales.convert', $invoice) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs">
                            ✓ Convert to Sale Bill
                        </button>
                    </form>
                @elseif($invoice->balance_amount > 0)
                    <a href="{{ route('payments.create', ['type' => 'payment_in', 'party_id' => $invoice->party_id, 'invoice_id' => $invoice->id]) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs">
                        + Record Payment
                    </a>
                @endif

                <form method="POST" action="{{ route('sales.destroy', $invoice) }}" onsubmit="return confirm('Delete this invoice?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium text-rose-600 hover:bg-rose-50 border border-rose-200">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Invoice Card Layout -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-6">
            <!-- Header Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-slate-200">
                <!-- Billed To -->
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Customer Details</span>
                    <a href="{{ route('parties.show', $invoice->party) }}" class="text-lg font-bold text-slate-900 hover:text-blue-600 block">
                        {{ $invoice->party->name }}
                    </a>
                    <div class="text-xs text-slate-600 mt-1 space-y-0.5">
                        <p>{{ $invoice->party->billing_address ?: 'No address specified' }}</p>
                        <p><span class="font-semibold text-slate-700">Phone:</span> {{ $invoice->party->phone ?: '-' }}</p>
                        <p><span class="font-semibold text-slate-700">GSTIN:</span> {{ $invoice->party->gstin ?: 'Unregistered' }}</p>
                        <p><span class="font-semibold text-slate-700">State:</span> {{ $invoice->party->state ?: 'Maharashtra' }} ({{ $invoice->party->state_code ?: '27' }})</p>
                    </div>
                </div>

                <!-- Invoice Meta -->
                <div class="md:text-right text-xs text-slate-600 space-y-1">
                    <p><span class="font-semibold text-slate-800">Invoice Date:</span> {{ $invoice->date->format('d M Y') }}</p>
                    @if($invoice->due_date)
                        <p><span class="font-semibold text-slate-800">Due Date:</span> {{ $invoice->due_date->format('d M Y') }}</p>
                    @endif
                    @if($invoice->reference_no)
                        <p><span class="font-semibold text-slate-800">Reference:</span> {{ $invoice->reference_no }}</p>
                    @endif
                    <p><span class="font-semibold text-slate-800">Tax Type:</span> {{ $invoice->tax_type === 'inter_state' ? 'IGST (Inter-State)' : 'CGST + SGST (Intra-State)' }}</p>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px] tracking-wider">
                            <th class="p-3 w-10 text-center">#</th>
                            <th class="p-3">Item Description</th>
                            <th class="p-3">HSN</th>
                            <th class="p-3 text-right">Qty</th>
                            <th class="p-3 text-right">Unit Rate</th>
                            <th class="p-3 text-right">Disc</th>
                            <th class="p-3 text-right">GST %</th>
                            <th class="p-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoice->items as $idx => $line)
                            <tr>
                                <td class="p-3 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="p-3 font-semibold text-slate-900">{{ $line->item_name }}</td>
                                <td class="p-3 text-slate-500 font-mono">{{ $line->hsn_code ?: '-' }}</td>
                                <td class="p-3 text-right text-slate-800">{{ $line->quantity }} {{ $line->unit }}</td>
                                <td class="p-3 text-right text-slate-800">₹{{ number_format($line->unit_price, 2) }}</td>
                                <td class="p-3 text-right text-slate-500">{{ $line->discount_percent > 0 ? $line->discount_percent . '%' : '-' }}</td>
                                <td class="p-3 text-right text-slate-500">{{ $line->tax_percent }}%</td>
                                <td class="p-3 text-right font-bold text-slate-900">₹{{ number_format($line->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Calculation Totals -->
            <div class="pt-4 border-t border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="text-xs text-slate-600 space-y-2">
                    @if($invoice->notes)
                        <div>
                            <span class="font-bold text-slate-700 block">Notes:</span>
                            <p>{{ $invoice->notes }}</p>
                        </div>
                    @endif
                    @if($invoice->terms)
                        <div>
                            <span class="font-bold text-slate-700 block">Terms & Conditions:</span>
                            <p class="whitespace-pre-line">{{ $invoice->terms }}</p>
                        </div>
                    @endif
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Taxable Subtotal:</span>
                        <span class="font-semibold text-slate-900">₹{{ number_format($invoice->subtotal, 2) }}</span>
                    </div>

                    @if($invoice->discount_amount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>Discount:</span>
                            <span class="font-semibold text-rose-600">-₹{{ number_format($invoice->discount_amount, 2) }}</span>
                        </div>
                    @endif

                    @if($invoice->tax_type === 'inter_state')
                        <div class="flex justify-between text-slate-600">
                            <span>IGST (Tax):</span>
                            <span class="font-semibold text-slate-900">₹{{ number_format($invoice->igst_amount, 2) }}</span>
                        </div>
                    @else
                        <div class="flex justify-between text-slate-600">
                            <span>CGST:</span>
                            <span class="font-semibold text-slate-900">₹{{ number_format($invoice->cgst_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>SGST:</span>
                            <span class="font-semibold text-slate-900">₹{{ number_format($invoice->sgst_amount, 2) }}</span>
                        </div>
                    @endif

                    @if($invoice->round_off != 0)
                        <div class="flex justify-between text-slate-600">
                            <span>Round Off:</span>
                            <span>₹{{ number_format($invoice->round_off, 2) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-sm font-bold text-slate-900 pt-2 border-t border-slate-200">
                        <span>Invoice Total:</span>
                        <span class="text-blue-600 text-lg">₹{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-xs pt-1">
                        <span class="text-emerald-700 font-semibold">Amount Paid:</span>
                        <span class="text-emerald-700 font-bold">₹{{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-xs pt-1 border-t border-slate-100">
                        <span class="text-rose-700 font-bold">Balance Due:</span>
                        <span class="text-rose-700 font-bold text-sm">₹{{ number_format($invoice->balance_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Linked Payments Table -->
            @if($invoice->payments->count() > 0)
                <div class="pt-6 border-t border-slate-200">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Payments Received on this Bill</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold">
                                    <th class="p-2.5">Receipt #</th>
                                    <th class="p-2.5">Date</th>
                                    <th class="p-2.5">Mode</th>
                                    <th class="p-2.5 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($invoice->payments as $pmt)
                                    <tr>
                                        <td class="p-2.5 font-medium text-slate-800">{{ $pmt->payment_number }}</td>
                                        <td class="p-2.5 text-slate-600">{{ $pmt->date->format('d M Y') }}</td>
                                        <td class="p-2.5 uppercase text-slate-600">{{ $pmt->payment_mode }}</td>
                                        <td class="p-2.5 text-right font-bold text-emerald-600">₹{{ number_format($pmt->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
