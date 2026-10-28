<x-layouts.app>
    <x-slot:title>Expenses - KhataApp</x-slot:title>

    <div class="space-y-6" x-data="{ addExpenseModal: false, addCategoryModal: false }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Expenses</h1>
                <p class="text-xs sm:text-sm text-slate-500">Record and track operational costs (Rent, Salaries, Utilities, Logistics)</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="addCategoryModal = true" class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50">
                    + New Category
                </button>
                <button @click="addExpenseModal = true" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-sm">
                    + Record Expense
                </button>
            </div>
        </div>

        <!-- Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-lg">
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">This Month's Expenses</span>
                <div class="text-2xl font-bold text-rose-600 mt-1">₹{{ number_format($monthExpenses, 2) }}</div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Recorded</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">₹{{ number_format($totalExpenses, 2) }}</div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col md:flex-row gap-4 justify-between items-center shadow-xs">
            <!-- Category Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0">
                <a href="{{ route('expenses.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ !$categoryId ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:text-slate-900' }}">
                    All Categories
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('expenses.index', ['category_id' => $cat->id]) }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap {{ $categoryId == $cat->id ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:text-slate-900' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('expenses.index') }}" class="w-full md:w-80 flex gap-2">
                @if($categoryId) <input type="hidden" name="category_id" value="{{ $categoryId }}"> @endif
                <div class="relative w-full">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search voucher #, vendor..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-slate-50 focus:bg-white">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3 py-2 text-xs font-semibold bg-slate-800 text-white rounded-lg hover:bg-slate-700">Search</button>
            </form>
        </div>

        <!-- Expenses Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px] tracking-wider">
                            <th class="p-3.5">Voucher #</th>
                            <th class="p-3.5">Date</th>
                            <th class="p-3.5">Category</th>
                            <th class="p-3.5">Paid To</th>
                            <th class="p-3.5">Mode</th>
                            <th class="p-3.5">Notes</th>
                            <th class="p-3.5 text-right">Amount</th>
                            <th class="p-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($expenses as $exp)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5 font-bold font-mono text-slate-900">{{ $exp->expense_number }}</td>
                                <td class="p-3.5 text-slate-600 whitespace-nowrap">{{ $exp->date->format('d M Y') }}</td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-slate-100 font-medium text-slate-700">
                                        {{ $exp->category?->name ?: 'General' }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-800 font-medium">{{ $exp->paid_to ?: '-' }}</td>
                                <td class="p-3.5 uppercase text-slate-500 font-mono">{{ $exp->payment_mode }}</td>
                                <td class="p-3.5 text-slate-500 text-[11px] max-w-xs truncate">{{ $exp->notes ?: '-' }}</td>
                                <td class="p-3.5 text-right font-bold text-rose-600 text-sm">
                                    ₹{{ number_format($exp->amount, 2) }}
                                </td>
                                <td class="p-3.5 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('expenses.destroy', $exp) }}" onsubmit="return confirm('Delete this expense?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded text-rose-400 hover:text-rose-600 hover:bg-rose-50" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400">
                                    No expenses recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expenses->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>

        <!-- Add Expense Modal -->
        <div x-cloak x-show="addExpenseModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
            <div @click.away="addExpenseModal = false" class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Record Business Expense</h3>
                    <button @click="addExpenseModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Voucher # <span class="text-rose-500">*</span></label>
                            <input type="text" name="expense_number" value="{{ $nextExpenseNumber }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="date" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Expense Category</label>
                        <select name="expense_category_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white">
                            <option value="">General / Uncategorized</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Amount (₹) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 font-bold text-rose-600">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Payment Mode <span class="text-rose-500">*</span></label>
                            <select name="payment_mode" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500 bg-white">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank / NEFT</option>
                                <option value="upi">UPI</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Paid To / Recipient</label>
                        <input type="text" name="paid_to" placeholder="e.g. Landlord, Electricity Board, Staff" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Reference / Bill #</label>
                        <input type="text" name="reference_number" placeholder="Optional bill / transaction id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Notes</label>
                        <input type="text" name="notes" placeholder="e.g. Shop maintenance charges" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="addExpenseModal = false" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white rounded-lg">Save Expense</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Category Modal -->
        <div x-cloak x-show="addCategoryModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
            <div @click.away="addCategoryModal = false" class="bg-white rounded-xl max-w-sm w-full p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">New Expense Category</h3>
                    <button @click="addCategoryModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form method="POST" action="{{ route('expenses.categories.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Category Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Rent, Electricity, Tea & Snacks" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Description</label>
                        <input type="text" name="description" placeholder="Optional notes" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-blue-500">
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="addCategoryModal = false" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white rounded-lg">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
