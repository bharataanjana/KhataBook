<x-layouts.app>
    <x-slot:title>Profit & Loss - KhataApp</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('reports.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center mb-1">
                    ← Back to Reports
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Profit & Loss Statement</h1>
                <p class="text-xs sm:text-sm text-slate-500">Financial performance from {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('reports.pnl') }}" class="flex items-center gap-2">
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

        <!-- Profit Highlights Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Net Sales Revenue</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">₹{{ number_format($pnl['net_sales'], 2) }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">After deducting sales returns</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Gross Profit</span>
                <div class="text-2xl font-bold {{ $pnl['gross_profit'] >= 0 ? 'text-blue-600' : 'text-rose-600' }} mt-1">
                    ₹{{ number_format($pnl['gross_profit'], 2) }}
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">Revenue minus inventory COGS</p>
            </div>

            <div class="bg-white rounded-xl p-4 border {{ $pnl['net_profit'] >= 0 ? 'border-emerald-200 bg-emerald-50/20' : 'border-rose-200 bg-rose-50/20' }} shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider {{ $pnl['net_profit'] >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">Net Business Profit</span>
                <div class="text-2xl font-bold {{ $pnl['net_profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1">
                    ₹{{ number_format($pnl['net_profit'], 2) }}
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">After deducting operational expenses</p>
            </div>
        </div>

        <!-- Comprehensive P&L Statement Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden max-w-4xl mx-auto">
            <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-900">Income Statement Breakdown</h3>
                <span class="text-xs font-mono text-slate-500">{{ $business->name }}</span>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                <!-- 1. Revenue -->
                <div class="p-4 bg-slate-50/40 font-bold uppercase text-[11px] text-slate-600 tracking-wider">
                    I. Trading Revenue (Sales)
                </div>
                <div class="p-3 pl-8 flex justify-between text-slate-700">
                    <span>Gross Sales</span>
                    <span>₹{{ number_format($pnl['gross_sales'], 2) }}</span>
                </div>
                <div class="p-3 pl-8 flex justify-between text-slate-700">
                    <span>Less: Sales Returns & Allowances</span>
                    <span class="text-rose-600">-₹{{ number_format($pnl['sale_returns'], 2) }}</span>
                </div>
                <div class="p-3 pl-6 flex justify-between font-bold text-slate-900 bg-slate-50/80">
                    <span>Net Sales Revenue</span>
                    <span>₹{{ number_format($pnl['net_sales'], 2) }}</span>
                </div>

                <!-- 2. Cost of Goods Sold -->
                <div class="p-4 bg-slate-50/40 font-bold uppercase text-[11px] text-slate-600 tracking-wider">
                    II. Cost of Goods Sold (COGS)
                </div>
                <div class="p-3 pl-8 flex justify-between text-slate-700">
                    <span>Direct Cost of Items Sold (Purchase Rates)</span>
                    <span class="text-rose-600">-₹{{ number_format($pnl['cogs'], 2) }}</span>
                </div>

                <!-- 3. Gross Profit -->
                <div class="p-3 pl-6 flex justify-between font-bold text-sm bg-blue-50 text-blue-900">
                    <span>GROSS PROFIT (Trading Margin)</span>
                    <span>₹{{ number_format($pnl['gross_profit'], 2) }}</span>
                </div>

                <!-- 4. Operating Expenses -->
                <div class="p-4 bg-slate-50/40 font-bold uppercase text-[11px] text-slate-600 tracking-wider">
                    III. Operating Expenses
                </div>
                @forelse($pnl['expense_breakdown'] as $expRow)
                    <div class="p-3 pl-8 flex justify-between text-slate-700">
                        <span>{{ $expRow->category?->name ?: 'General Expenses' }}</span>
                        <span class="text-rose-600">₹{{ number_format($expRow->total, 2) }}</span>
                    </div>
                @empty
                    <div class="p-3 pl-8 text-slate-400">No expenses recorded for this period.</div>
                @endforelse
                <div class="p-3 pl-6 flex justify-between font-bold text-slate-900 bg-slate-50/80">
                    <span>Total Operating Expenses</span>
                    <span class="text-rose-600">-₹{{ number_format($pnl['total_expenses'], 2) }}</span>
                </div>

                <!-- 5. Net Profit -->
                <div class="p-4 flex justify-between font-bold text-base {{ $pnl['net_profit'] >= 0 ? 'bg-emerald-50 text-emerald-900' : 'bg-rose-50 text-rose-900' }}">
                    <span>NET PROFIT / (LOSS)</span>
                    <span>₹{{ number_format($pnl['net_profit'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
