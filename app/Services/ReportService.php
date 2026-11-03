<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Party;
use App\Models\Payment;
use App\Models\Purchase;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Day Book: All transactions that happened on a specific date.
     *
     * @return array<string, mixed>
     */
    public function getDayBook(string $date): array
    {
        $sales = Invoice::with('party')
            ->whereDate('date', $date)
            ->where('invoice_type', '!=', 'quotation')
            ->get();

        $purchases = Purchase::with('party')
            ->whereDate('date', $date)
            ->get();

        $paymentsIn = Payment::with('party')
            ->where('payment_type', 'payment_in')
            ->whereDate('date', $date)
            ->get();

        $paymentsOut = Payment::with('party')
            ->where('payment_type', 'payment_out')
            ->whereDate('date', $date)
            ->get();

        $expenses = Expense::with('category')
            ->whereDate('date', $date)
            ->get();

        $moneyIn = (float) $paymentsIn->sum('amount');
        $moneyOut = (float) $paymentsOut->sum('amount') + (float) $expenses->sum('amount');

        return [
            'date' => $date,
            'sales' => $sales,
            'purchases' => $purchases,
            'payments_in' => $paymentsIn,
            'payments_out' => $paymentsOut,
            'expenses' => $expenses,
            'total_sales' => (float) $sales->sum('total_amount'),
            'total_purchases' => (float) $purchases->sum('total_amount'),
            'money_in' => $moneyIn,
            'money_out' => $moneyOut,
            'net_cashflow' => $moneyIn - $moneyOut,
        ];
    }

    /**
     * Profit & Loss Report for a date range.
     *
     * @return array<string, mixed>
     */
    public function getProfitAndLoss(string $startDate, string $endDate): array
    {
        $sales = (float) Invoice::where('invoice_type', 'sale')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('subtotal');

        $saleReturns = (float) Invoice::where('invoice_type', 'sale_return')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('subtotal');

        $netSales = $sales - $saleReturns;

        // Cost of Goods Sold estimated from sales invoice items * item purchase price
        $itemsSold = Invoice::where('invoice_type', 'sale')
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['items.item'])
            ->get()
            ->flatMap(fn ($inv) => $inv->items);

        $cogs = 0.00;
        foreach ($itemsSold as $row) {
            $purchaseRate = $row->item ? (float) $row->item->purchase_price : 0;
            $cogs += ((float) $row->quantity * $purchaseRate);
        }

        $grossProfit = $netSales - $cogs;

        $totalExpenses = (float) Expense::whereBetween('date', [$startDate, $endDate])->sum('amount');
        $netProfit = $grossProfit - $totalExpenses;

        $expenseBreakdown = Expense::whereBetween('date', [$startDate, $endDate])
            ->with('category')
            ->selectRaw('expense_category_id, sum(amount) as total')
            ->groupBy('expense_category_id')
            ->get();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'gross_sales' => $sales,
            'sale_returns' => $saleReturns,
            'net_sales' => $netSales,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'total_expenses' => $totalExpenses,
            'expense_breakdown' => $expenseBreakdown,
            'net_profit' => $netProfit,
        ];
    }

    /**
     * Stock Summary & Inventory Valuation.
     *
     * @return Collection<int, Item>
     */
    public function getStockSummary(): Collection
    {
        return Item::with('category')
            ->orderBy('name')
            ->get()
            ->map(function (Item $item): Item {
                $item->stock_value = (float) $item->current_stock * (float) $item->purchase_price;
                $item->retail_value = (float) $item->current_stock * (float) $item->sale_price;

                return $item;
            });
    }

    /**
     * Party Statement / Ledger.
     *
     * @return array<string, mixed>
     */
    public function getPartyStatement(Party $party, ?string $startDate = null, ?string $endDate = null): array
    {
        $transactions = collect();

        // Add opening balance entry
        $transactions->push([
            'date' => $party->created_at->toDateString(),
            'type' => 'Opening Balance',
            'ref_no' => '-',
            'debit' => $party->opening_balance_type === 'to_receive' ? (float) $party->opening_balance : 0,
            'credit' => $party->opening_balance_type === 'to_pay' ? (float) $party->opening_balance : 0,
            'notes' => 'Opening Balance',
        ]);

        // Sales & returns
        $invoicesQuery = $party->invoices()->where('invoice_type', '!=', 'quotation');
        if ($startDate && $endDate) {
            $invoicesQuery->whereBetween('date', [$startDate, $endDate]);
        }
        foreach ($invoicesQuery->get() as $inv) {
            if ($inv->invoice_type === 'sale') {
                $transactions->push([
                    'date' => $inv->date->toDateString(),
                    'type' => 'Sale Invoice',
                    'ref_no' => $inv->invoice_number,
                    'debit' => (float) $inv->total_amount,
                    'credit' => 0,
                    'notes' => $inv->notes,
                ]);
            } else {
                $transactions->push([
                    'date' => $inv->date->toDateString(),
                    'type' => 'Sale Return',
                    'ref_no' => $inv->invoice_number,
                    'debit' => 0,
                    'credit' => (float) $inv->total_amount,
                    'notes' => $inv->notes,
                ]);
            }
        }

        // Purchases & returns
        $purchasesQuery = $party->purchases();
        if ($startDate && $endDate) {
            $purchasesQuery->whereBetween('date', [$startDate, $endDate]);
        }
        foreach ($purchasesQuery->get() as $pur) {
            if ($pur->purchase_type === 'purchase') {
                $transactions->push([
                    'date' => $pur->date->toDateString(),
                    'type' => 'Purchase Bill',
                    'ref_no' => $pur->bill_number,
                    'debit' => 0,
                    'credit' => (float) $pur->total_amount,
                    'notes' => $pur->notes,
                ]);
            } else {
                $transactions->push([
                    'date' => $pur->date->toDateString(),
                    'type' => 'Purchase Return',
                    'ref_no' => $pur->bill_number,
                    'debit' => (float) $pur->total_amount,
                    'credit' => 0,
                    'notes' => $pur->notes,
                ]);
            }
        }

        // Payments
        $paymentsQuery = $party->payments();
        if ($startDate && $endDate) {
            $paymentsQuery->whereBetween('date', [$startDate, $endDate]);
        }
        foreach ($paymentsQuery->get() as $pmt) {
            if ($pmt->payment_type === 'payment_in') {
                $transactions->push([
                    'date' => $pmt->date->toDateString(),
                    'type' => 'Payment In',
                    'ref_no' => $pmt->payment_number,
                    'debit' => 0,
                    'credit' => (float) $pmt->amount,
                    'notes' => $pmt->notes,
                ]);
            } else {
                $transactions->push([
                    'date' => $pmt->date->toDateString(),
                    'type' => 'Payment Out',
                    'ref_no' => $pmt->payment_number,
                    'debit' => (float) $pmt->amount,
                    'credit' => 0,
                    'notes' => $pmt->notes,
                ]);
            }
        }

        $sorted = $transactions->sortBy('date')->values();

        // Calculate running balance
        $runningBalance = 0.00;
        $ledger = $sorted->map(function ($row) use (&$runningBalance) {
            $runningBalance += ($row['debit'] - $row['credit']);
            $row['running_balance'] = $runningBalance;

            return $row;
        });

        return [
            'party' => $party,
            'ledger' => $ledger,
            'closing_balance' => $runningBalance,
        ];
    }

    /**
     * GST Summary Report for Sales (GSTR-1) and Purchases (GSTR-2).
     *
     * @return array<string, mixed>
     */
    public function getGstReport(string $startDate, string $endDate): array
    {
        $salesInvoices = Invoice::where('invoice_type', 'sale')
            ->whereBetween('date', [$startDate, $endDate])
            ->with('party')
            ->get();

        $purchaseBills = Purchase::where('purchase_type', 'purchase')
            ->whereBetween('date', [$startDate, $endDate])
            ->with('party')
            ->get();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sales' => [
                'total_taxable' => (float) $salesInvoices->sum('subtotal'),
                'total_cgst' => (float) $salesInvoices->sum('cgst_amount'),
                'total_sgst' => (float) $salesInvoices->sum('sgst_amount'),
                'total_igst' => (float) $salesInvoices->sum('igst_amount'),
                'total_tax' => (float) $salesInvoices->sum('tax_amount'),
                'total_invoice_value' => (float) $salesInvoices->sum('total_amount'),
                'invoices' => $salesInvoices,
            ],
            'purchases' => [
                'total_taxable' => (float) $purchaseBills->sum('subtotal'),
                'total_cgst' => (float) $purchaseBills->sum('cgst_amount'),
                'total_sgst' => (float) $purchaseBills->sum('sgst_amount'),
                'total_igst' => (float) $purchaseBills->sum('igst_amount'),
                'total_tax' => (float) $purchaseBills->sum('tax_amount'),
                'total_bill_value' => (float) $purchaseBills->sum('total_amount'),
                'bills' => $purchaseBills,
            ],
        ];
    }
}
