<x-layouts.app>
    <x-slot:title>GST Report - KhataApp</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('reports.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                    ← Back to Reports
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">GST Summary Report (GSTR-1 & GSTR-2)</h1>
                <p class="text-xs sm:text-sm text-slate-500">Output tax liability on sales & Input Tax Credit (ITC) on purchases</p>
            </div>

            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('reports.gst') }}" class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg">
                    <span class="text-xs text-slate-400">to</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg">
                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-slate-800 text-white rounded-lg">Apply</button>
                </form>
                <button onclick="window.print()" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-xs">
                    🖨️ Print
                </button>
            </div>
        </div>

        @php
            $outputTax = $gstData['sales']['total_tax'];
            $inputTax = $gstData['purchases']['total_tax'];
            $netTaxPayable = $outputTax - $inputTax;
        @endphp

        <!-- GST Overview Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Output GST Liability (Sales)</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">₹{{ number_format($outputTax, 2) }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Tax collected from buyers</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Input Tax Credit (Purchases)</span>
                <div class="text-2xl font-bold text-blue-600 mt-1">₹{{ number_format($inputTax, 2) }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Eligible ITC from vendors</p>
            </div>

            <div class="bg-white rounded-xl p-4 border {{ $netTaxPayable >= 0 ? 'border-amber-200 bg-amber-50/20' : 'border-emerald-200 bg-emerald-50/20' }} shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider {{ $netTaxPayable >= 0 ? 'text-amber-800' : 'text-emerald-800' }}">Net GST Payable / (Credit)</span>
                <div class="text-2xl font-bold {{ $netTaxPayable >= 0 ? 'text-amber-600' : 'text-emerald-600' }} mt-1">
                    ₹{{ number_format(abs($netTaxPayable), 2) }} {{ $netTaxPayable >= 0 ? 'Payable' : 'Credit Carryforward' }}
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">Output GST minus Input Tax Credit</p>
            </div>
        </div>

        <!-- GSTR-1 Sales Breakdown Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">GSTR-1: Outward Supplies (Sales)</h3>
                <span class="text-xs font-bold text-slate-900">Total Invoices: {{ count($gstData['sales']['invoices']) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase text-[10px]">
                            <th class="p-3">Invoice #</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3">GSTIN</th>
                            <th class="p-3 text-right">Taxable Value</th>
                            <th class="p-3 text-right">CGST</th>
                            <th class="p-3 text-right">SGST</th>
                            <th class="p-3 text-right">IGST</th>
                            <th class="p-3 text-right">Total Invoice Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($gstData['sales']['invoices'] as $sInv)
                            <tr>
                                <td class="p-3 font-semibold text-blue-600"><a href="{{ route('sales.show', $sInv) }}">{{ $sInv->invoice_number }}</a></td>
                                <td class="p-3 text-slate-800">{{ $sInv->party->name }}</td>
                                <td class="p-3 font-mono text-slate-500">{{ $sInv->party->gstin ?: 'Unregistered' }}</td>
                                <td class="p-3 text-right text-slate-700">₹{{ number_format($sInv->subtotal, 2) }}</td>
                                <td class="p-3 text-right text-slate-500">₹{{ number_format($sInv->cgst_amount, 2) }}</td>
                                <td class="p-3 text-right text-slate-500">₹{{ number_format($sInv->sgst_amount, 2) }}</td>
                                <td class="p-3 text-right text-slate-500">₹{{ number_format($sInv->igst_amount, 2) }}</td>
                                <td class="p-3 text-right font-bold text-slate-900">₹{{ number_format($sInv->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="p-4 text-center text-slate-400">No sales invoices in this period.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100 font-bold border-t border-slate-200 text-slate-900">
                            <td colspan="3" class="p-3 text-right uppercase text-[10px]">Total:</td>
                            <td class="p-3 text-right">₹{{ number_format($gstData['sales']['total_taxable'], 2) }}</td>
                            <td class="p-3 text-right">₹{{ number_format($gstData['sales']['total_cgst'], 2) }}</td>
                            <td class="p-3 text-right">₹{{ number_format($gstData['sales']['total_sgst'], 2) }}</td>
                            <td class="p-3 text-right">₹{{ number_format($gstData['sales']['total_igst'], 2) }}</td>
                            <td class="p-3 text-right text-blue-600">₹{{ number_format($gstData['sales']['total_invoice_value'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- GSTR-2 Purchases Breakdown Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">GSTR-2: Inward Supplies & ITC (Purchases)</h3>
                <span class="text-xs font-bold text-slate-900">Total Bills: {{ count($gstData['purchases']['bills']) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase text-[10px]">
                            <th class="p-3">Bill #</th>
                            <th class="p-3">Supplier</th>
                            <th class="p-3">GSTIN</th>
                            <th class="p-3 text-right">Taxable Value</th>
                            <th class="p-3 text-right">CGST</th>
                            <th class="p-3 text-right">SGST</th>
                            <th class="p-3 text-right">IGST</th>
                            <th class="p-3 text-right">Total Bill Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($gstData['purchases']['bills'] as $pBill)
                            <tr>
                                <td class="p-3 font-semibold text-blue-600"><a href="{{ route('purchases.show', $pBill) }}">{{ $pBill->bill_number }}</a></td>
                                <td class="p-3 text-slate-800">{{ $pBill->party->name }}</td>
                                <td class="p-3 font-mono text-slate-500">{{ $pBill->party->gstin ?: 'Unregistered' }}</td>
                                <td class="p-3 text-right text-slate-700">₹{{ number_format($pBill->subtotal, 2) }}</td>
                                <td class="p-3 text-right text-slate-500">₹{{ number_format($pBill->cgst_amount, 2) }}</td>
                                <td class="p-3 text-right text-slate-500">₹{{ number_format($pBill->sgst_amount, 2) }}</td>
                                <td class="p-3 text-right text-slate-500">₹{{ number_format($pBill->igst_amount, 2) }}</td>
                                <td class="p-3 text-right font-bold text-slate-900">₹{{ number_format($pBill->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="p-4 text-center text-slate-400">No purchase bills in this period.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100 font-bold border-t border-slate-200 text-slate-900">
                            <td colspan="3" class="p-3 text-right uppercase text-[10px]">Total:</td>
                            <td class="p-3 text-right">₹{{ number_format($gstData['purchases']['total_taxable'], 2) }}</td>
                            <td class="p-3 text-right">₹{{ number_format($gstData['purchases']['total_cgst'], 2) }}</td>
                            <td class="p-3 text-right">₹{{ number_format($gstData['purchases']['total_sgst'], 2) }}</td>
                            <td class="p-3 text-right">₹{{ number_format($gstData['purchases']['total_igst'], 2) }}</td>
                            <td class="p-3 text-right text-blue-600">₹{{ number_format($gstData['purchases']['total_bill_value'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
