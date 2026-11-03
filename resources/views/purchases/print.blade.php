<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $purchase->bill_number }} - Purchase Voucher</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #1e293b; background: #f1f5f9; padding: 20px; }
        .voucher-box { max-width: 800px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
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
        .print-btn { position: fixed; bottom: 20px; right: 20px; background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; cursor: pointer; }

        @media print {
            body { background: #fff; padding: 0; }
            .voucher-box { border: none; box-shadow: none; padding: 10px; width: 100%; max-width: 100%; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn">🖨️ Print Voucher</button>

    <div class="voucher-box">
        <div class="header">
            <div>
                <div class="business-title">{{ $business->name }}</div>
                <p class="text-muted">{{ $business->address }}, {{ $business->city }}</p>
                <p class="text-muted">GSTIN: {{ $business->gstin }}</p>
            </div>
            <div style="text-align: right;">
                <div class="meta-title">{{ $purchase->purchase_type === 'purchase_return' ? 'PURCHASE RETURN' : 'PURCHASE VOUCHER' }}</div>
                <p style="font-weight: 700; font-size: 13px;">Bill #: {{ $purchase->bill_number }}</p>
                <p class="text-muted">Date: {{ $purchase->date->format('d-m-Y') }}</p>
            </div>
        </div>

        <div class="grid-2">
            <div class="info-card">
                <h4>Supplier Details</h4>
                <p style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $purchase->party->name }}</p>
                <p class="text-muted">{{ $purchase->party->billing_address ?: 'Address not provided' }}</p>
                <p class="text-muted">Phone: {{ $purchase->party->phone ?: '-' }}</p>
                <p class="text-muted">GSTIN: {{ $purchase->party->gstin ?: 'Unregistered' }}</p>
            </div>

            <div class="info-card">
                <h4>Payment Status</h4>
                <p class="text-muted">Status: <strong style="text-transform: uppercase;">{{ $purchase->payment_status }}</strong></p>
                <p class="text-muted">Amount Paid: ₹{{ number_format($purchase->paid_amount, 2) }}</p>
                <p class="text-muted" style="font-weight: 700; color: #dc2626; margin-top: 4px;">Balance Due: ₹{{ number_format($purchase->balance_amount, 2) }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 30px;">#</th>
                    <th>Item Description</th>
                    <th class="text-center" style="width: 70px;">HSN</th>
                    <th class="text-right" style="width: 60px;">Qty</th>
                    <th class="text-center" style="width: 45px;">Unit</th>
                    <th class="text-right" style="width: 80px;">Rate (₹)</th>
                    <th class="text-right" style="width: 60px;">GST %</th>
                    <th class="text-right" style="width: 90px;">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $idx => $line)
                    <tr>
                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
                        <td><strong>{{ $line->item_name }}</strong></td>
                        <td class="text-center text-muted">{{ $line->hsn_code ?: '-' }}</td>
                        <td class="text-right">{{ $line->quantity }}</td>
                        <td class="text-center text-muted">{{ $line->unit }}</td>
                        <td class="text-right">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="text-right text-muted">{{ $line->tax_percent }}%</td>
                        <td class="text-right" style="font-weight: 700;">{{ number_format($line->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td class="text-muted">Subtotal:</td>
                <td class="text-right" style="font-weight: 600;">₹{{ number_format($purchase->subtotal, 2) }}</td>
            </tr>
            @if($purchase->discount_amount > 0)
                <tr>
                    <td class="text-muted">Discount:</td>
                    <td class="text-right" style="color: #dc2626;">-₹{{ number_format($purchase->discount_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-muted">Total GST:</td>
                <td class="text-right">₹{{ number_format($purchase->tax_amount, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td>Grand Total:</td>
                <td class="text-right">₹{{ number_format($purchase->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="text-muted">Paid:</td>
                <td class="text-right" style="color: #16a34a; font-weight: 600;">₹{{ number_format($purchase->paid_amount, 2) }}</td>
            </tr>
            @if($purchase->balance_amount > 0)
                <tr>
                    <td style="font-weight: 700; color: #dc2626;">Balance Owed:</td>
                    <td class="text-right" style="font-weight: 700; color: #dc2626;">₹{{ number_format($purchase->balance_amount, 2) }}</td>
                </tr>
            @endif
        </table>
    </div>

</body>
</html>
