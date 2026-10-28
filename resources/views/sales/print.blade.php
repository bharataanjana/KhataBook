<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->invoice_number }} - Tax Invoice</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #1e293b; background: #f1f5f9; padding: 20px; }
        .invoice-box { max-width: 800px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .business-title { font-size: 20px; font-weight: 800; color: #1e3a8a; text-transform: uppercase; margin-bottom: 4px; }
        .meta-title { font-size: 18px; font-weight: 700; color: #2563eb; text-align: right; text-transform: uppercase; margin-bottom: 4px; }
        .text-muted { color: #64748b; font-size: 11px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; }
        .info-card h4 { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #475569; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 8px 10px; font-size: 10px; font-weight: 700; text-transform: uppercase; color: #334155; text-align: left; }
        td { border: 1px solid #e2e8f0; padding: 8px 10px; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-table { width: 300px; margin-left: auto; margin-bottom: 20px; }
        .totals-table td { border: none; padding: 4px 8px; }
        .totals-table tr.grand-total td { border-top: 2px solid #1e293b; font-size: 14px; font-weight: 800; color: #1e3a8a; padding-top: 8px; }
        .footer-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
        .signatory-box { border: 1px dashed #cbd5e1; border-radius: 6px; height: 100px; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; padding-bottom: 10px; text-align: center; font-size: 10px; color: #64748b; }
        .print-btn { position: fixed; bottom: 20px; right: 20px; background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.15); }
        .print-btn:hover { background: #1d4ed8; }

        @media print {
            body { background: #fff; padding: 0; }
            .invoice-box { border: none; box-shadow: none; padding: 10px; width: 100%; max-width: 100%; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn">🖨️ Print Invoice</button>

    <div class="invoice-box">
        <!-- Header -->
        <div class="header">
            <div>
                <div class="business-title">{{ $business->name }}</div>
                <p class="text-muted">{{ $business->address }}, {{ $business->city }} - {{ $business->pincode }}</p>
                <p class="text-muted">Phone: {{ $business->phone }} | Email: {{ $business->email }}</p>
                <p style="font-weight: 600; margin-top: 3px;">GSTIN: {{ $business->gstin }} | State: {{ $business->state }} ({{ $business->state_code }})</p>
            </div>
            <div style="text-align: right;">
                <div class="meta-title">{{ $invoice->invoice_type === 'quotation' ? 'ESTIMATE / QUOTATION' : 'TAX INVOICE' }}</div>
                <p style="font-weight: 700; font-size: 13px; color: #0f172a;">Invoice #: {{ $invoice->invoice_number }}</p>
                <p class="text-muted">Date: {{ $invoice->date->format('d-m-Y') }}</p>
                @if($invoice->due_date)
                    <p class="text-muted">Due Date: {{ $invoice->due_date->format('d-m-Y') }}</p>
                @endif
                @if($invoice->reference_no)
                    <p class="text-muted">Ref / PO: {{ $invoice->reference_no }}</p>
                @endif
            </div>
        </div>

        <!-- Billing Details -->
        <div class="grid-2">
            <div class="info-card">
                <h4>Billed To (Customer)</h4>
                <p style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">{{ $invoice->party->name }}</p>
                <p class="text-muted">{{ $invoice->party->billing_address ?: 'Address not provided' }}</p>
                <p class="text-muted" style="margin-top: 4px;">Phone: {{ $invoice->party->phone ?: '-' }}</p>
                <p class="text-muted">GSTIN: {{ $invoice->party->gstin ?: 'Unregistered' }}</p>
                <p class="text-muted">State: {{ $invoice->party->state ?: 'Maharashtra' }} (Code: {{ $invoice->party->state_code ?: '27' }})</p>
            </div>

            <div class="info-card">
                <h4>Payment & Tax Summary</h4>
                <p class="text-muted">Tax Applicable: <span style="font-weight: 600; color: #0f172a;">{{ $invoice->tax_type === 'inter_state' ? 'IGST (Inter-State)' : 'CGST + SGST (Intra-State)' }}</span></p>
                <p class="text-muted">Payment Status: <span style="font-weight: 700; text-transform: uppercase; color: {{ $invoice->payment_status === 'paid' ? '#16a34a' : ($invoice->payment_status === 'partial' ? '#d97706' : '#dc2626') }}">{{ $invoice->payment_status }}</span></p>
                <p class="text-muted">Amount Paid: ₹{{ number_format($invoice->paid_amount, 2) }}</p>
                <p class="text-muted" style="font-weight: 700; color: #0f172a; margin-top: 4px;">Balance Due: ₹{{ number_format($invoice->balance_amount, 2) }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 30px;">#</th>
                    <th>Item Description</th>
                    <th class="text-center" style="width: 70px;">HSN/SAC</th>
                    <th class="text-right" style="width: 60px;">Qty</th>
                    <th class="text-center" style="width: 45px;">Unit</th>
                    <th class="text-right" style="width: 75px;">Rate (₹)</th>
                    <th class="text-right" style="width: 50px;">Disc %</th>
                    <th class="text-right" style="width: 50px;">GST %</th>
                    <th class="text-right" style="width: 85px;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $idx => $line)
                    <tr>
                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
                        <td><strong>{{ $line->item_name }}</strong></td>
                        <td class="text-center text-muted">{{ $line->hsn_code ?: '-' }}</td>
                        <td class="text-right">{{ $line->quantity }}</td>
                        <td class="text-center text-muted">{{ $line->unit }}</td>
                        <td class="text-right">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="text-right text-muted">{{ $line->discount_percent > 0 ? $line->discount_percent . '%' : '-' }}</td>
                        <td class="text-right text-muted">{{ $line->tax_percent }}%</td>
                        <td class="text-right" style="font-weight: 700;">{{ number_format($line->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Calculation Table -->
        <table class="totals-table">
            <tr>
                <td class="text-muted">Taxable Subtotal:</td>
                <td class="text-right" style="font-weight: 600;">₹{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
                <tr>
                    <td class="text-muted">Overall Discount:</td>
                    <td class="text-right" style="color: #dc2626;">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
            @endif
            @if($invoice->tax_type === 'inter_state')
                <tr>
                    <td class="text-muted">IGST Tax:</td>
                    <td class="text-right">₹{{ number_format($invoice->igst_amount, 2) }}</td>
                </tr>
            @else
                <tr>
                    <td class="text-muted">CGST Tax:</td>
                    <td class="text-right">₹{{ number_format($invoice->cgst_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-muted">SGST Tax:</td>
                    <td class="text-right">₹{{ number_format($invoice->sgst_amount, 2) }}</td>
                </tr>
            @endif
            @if($invoice->round_off != 0)
                <tr>
                    <td class="text-muted">Round Off:</td>
                    <td class="text-right">₹{{ number_format($invoice->round_off, 2) }}</td>
                </tr>
            @endif
            <tr class="grand-total">
                <td>Grand Total:</td>
                <td class="text-right">₹{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            @if($invoice->paid_amount > 0)
                <tr>
                    <td class="text-muted">Amount Received:</td>
                    <td class="text-right" style="color: #16a34a; font-weight: 600;">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
            @endif
            @if($invoice->balance_amount > 0)
                <tr>
                    <td style="font-weight: 700; color: #dc2626;">Balance Due:</td>
                    <td class="text-right" style="font-weight: 700; color: #dc2626;">₹{{ number_format($invoice->balance_amount, 2) }}</td>
                </tr>
            @endif
        </table>

        <!-- Footer Grid (Bank Info & Signatory) -->
        <div class="footer-grid">
            <div>
                @if($business->bank_name)
                    <div class="info-card" style="margin-bottom: 10px;">
                        <h4>Bank Account Details & UPI</h4>
                        <p class="text-muted">Bank: <strong style="color: #0f172a;">{{ $business->bank_name }}</strong> (Branch: {{ $business->branch ?: 'Main' }})</p>
                        <p class="text-muted">A/C No: <strong style="color: #0f172a;">{{ $business->account_number }}</strong></p>
                        <p class="text-muted">IFSC Code: <strong style="color: #0f172a;">{{ $business->ifsc_code }}</strong></p>
                        @if($business->upi_id)
                            <p class="text-muted" style="margin-top: 3px;">UPI ID: <strong style="color: #1e3a8a;">{{ $business->upi_id }}</strong></p>
                        @endif
                    </div>
                @endif

                @if($invoice->terms)
                    <div style="font-size: 10px; color: #64748b;">
                        <strong style="color: #475569;">Terms & Conditions:</strong>
                        <p style="white-space: pre-line;">{{ $invoice->terms }}</p>
                    </div>
                @endif
            </div>

            <div class="signatory-box">
                <p>For <strong>{{ $business->name }}</strong></p>
                <div style="margin-top: 40px; border-top: 1px solid #cbd5e1; width: 80%; padding-top: 4px;">
                    Authorized Signatory
                </div>
            </div>
        </div>
    </div>

</body>
</html>
