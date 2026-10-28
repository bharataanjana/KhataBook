<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Party;
use App\Models\Payment;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $business = Business::current();
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $today = $now->toDateString();

        // Sales Metrics
        $totalSales = (float) Invoice::where('invoice_type', 'sale')->sum('total_amount');
        $monthSales = (float) Invoice::where('invoice_type', 'sale')->where('date', '>=', $startOfMonth)->sum('total_amount');

        // Purchases Metrics
        $totalPurchases = (float) Purchase::where('purchase_type', 'purchase')->sum('total_amount');
        $monthPurchases = (float) Purchase::where('purchase_type', 'purchase')->where('date', '>=', $startOfMonth)->sum('total_amount');

        // Receivables and Payables
        $parties = Party::all();
        $toReceive = (float) $parties->where('current_balance', '>', 0)->sum('current_balance');
        $toPay = abs((float) $parties->where('current_balance', '<', 0)->sum('current_balance'));

        // Cash flow / Bank
        $totalPaymentsIn = (float) Payment::where('payment_type', 'payment_in')->sum('amount');
        $totalPaymentsOut = (float) Payment::where('payment_type', 'payment_out')->sum('amount');
        $totalExpenses = (float) Expense::sum('amount');
        $netCashBalance = $totalPaymentsIn - ($totalPaymentsOut + $totalExpenses);

        // Low stock items
        $lowStockItems = Item::whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->orderBy('current_stock')
            ->take(8)
            ->get();
        $lowStockCount = Item::whereColumn('current_stock', '<=', 'low_stock_threshold')->count();

        // Recent Invoices / Activity
        $recentInvoices = Invoice::with('party')
            ->where('invoice_type', 'sale')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        // Last 7 days chart data
        $chartLabels = [];
        $chartSales = [];
        $chartPurchases = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayLabel = Carbon::now()->subDays($i)->format('d M');
            $chartLabels[] = $dayLabel;

            $chartSales[] = (float) Invoice::where('invoice_type', 'sale')->whereDate('date', $day)->sum('total_amount');
            $chartPurchases[] = (float) Purchase::where('purchase_type', 'purchase')->whereDate('date', $day)->sum('total_amount');
        }

        return view('dashboard.index', compact(
            'business',
            'totalSales',
            'monthSales',
            'totalPurchases',
            'monthPurchases',
            'toReceive',
            'toPay',
            'netCashBalance',
            'lowStockItems',
            'lowStockCount',
            'recentInvoices',
            'chartLabels',
            'chartSales',
            'chartPurchases'
        ));
    }
}
