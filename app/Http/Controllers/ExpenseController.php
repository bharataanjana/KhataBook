<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->query('category_id');
        $search = $request->query('search');

        $query = Expense::with('category');

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('expense_number', 'like', "%{$search}%")
                    ->orWhere('paid_to', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $expenses = $query->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();
        $categories = ExpenseCategory::all();

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $monthExpenses = (float) Expense::where('date', '>=', $startOfMonth)->sum('amount');
        $totalExpenses = (float) Expense::sum('amount');

        $lastExpense = Expense::orderByDesc('id')->first();
        $nextExpenseNumber = 'EXP-'.str_pad((string) (($lastExpense ? $lastExpense->id : 0) + 1), 4, '0', STR_PAD_LEFT);

        return view('expenses.index', compact(
            'expenses',
            'categories',
            'categoryId',
            'search',
            'monthExpenses',
            'totalExpenses',
            'nextExpenseNumber'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expense_number' => 'required|string|max:100',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:cash,bank,upi,cheque',
            'paid_to' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully!');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'description' => 'nullable|string',
        ]);

        ExpenseCategory::create($validated);

        return back()->with('success', 'Expense category created successfully!');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
