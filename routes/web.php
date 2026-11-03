<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Parties
Route::resource('parties', PartyController::class);

// Items & Inventory
Route::post('items/{item}/adjust-stock', [ItemController::class, 'adjustStock'])->name('items.adjust-stock');
Route::resource('items', ItemController::class);

// Sales & Quotations
Route::get('sales/{invoice}/print', [SaleController::class, 'print'])->name('sales.print');
Route::post('sales/{invoice}/convert', [SaleController::class, 'convertToSale'])->name('sales.convert');
Route::resource('sales', SaleController::class)->parameters(['sales' => 'invoice']);

// Purchases
Route::get('purchases/{purchase}/print', [PurchaseController::class, 'print'])->name('purchases.print');
Route::resource('purchases', PurchaseController::class);

// Payments (In & Out)
Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'destroy']);

// Expenses
Route::post('expenses/categories', [ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');
Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'destroy']);

// Reports
Route::prefix('reports')->name('reports.')->group(function (): void {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/daybook', [ReportController::class, 'daybook'])->name('daybook');
    Route::get('/profit-and-loss', [ReportController::class, 'profitAndLoss'])->name('pnl');
    Route::get('/stock-summary', [ReportController::class, 'stockSummary'])->name('stock');
    Route::get('/gst', [ReportController::class, 'gst'])->name('gst');
});

// Settings
Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
