<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(): View
    {
        return view('reports.index');
    }

    public function daybook(Request $request): View
    {
        $date = $request->query('date', now()->toDateString());
        $daybook = $this->reportService->getDayBook($date);
        $business = Business::current();

        return view('reports.daybook', compact('daybook', 'date', 'business'));
    }

    public function profitAndLoss(Request $request): View
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $pnl = $this->reportService->getProfitAndLoss($startDate, $endDate);
        $business = Business::current();

        return view('reports.pnl', compact('pnl', 'startDate', 'endDate', 'business'));
    }

    public function stockSummary(): View
    {
        $items = $this->reportService->getStockSummary();
        $totalStockValuation = (float) $items->sum('stock_value');
        $totalRetailValuation = (float) $items->sum('retail_value');
        $business = Business::current();

        return view('reports.stock', compact('items', 'totalStockValuation', 'totalRetailValuation', 'business'));
    }

    public function gst(Request $request): View
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $gstData = $this->reportService->getGstReport($startDate, $endDate);
        $business = Business::current();

        return view('reports.gst', compact('gstData', 'startDate', 'endDate', 'business'));
    }
}
