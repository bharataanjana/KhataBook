<x-layouts.app>
    <x-slot:title>Reports & Analytics - KhataApp</x-slot:title>

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Reports & Business Insights</h1>
            <p class="text-xs sm:text-sm text-slate-500">Comprehensive accounting reports, tax summaries, and inventory valuation</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Day Book -->
            <a href="{{ route('reports.daybook') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-blue-400 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Day Book</h3>
                <p class="text-xs text-slate-500 mt-1">Chronological record of all transactions (sales, purchases, payments, expenses) on any given date.</p>
                <span class="inline-flex items-center text-xs font-semibold text-blue-600 mt-3">View Day Book →</span>
            </a>

            <!-- Profit & Loss -->
            <a href="{{ route('reports.pnl') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-emerald-400 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">Profit & Loss Statement</h3>
                <p class="text-xs text-slate-500 mt-1">Calculate your true Gross Profit (Revenue - Cost of Goods Sold) and Net Profit after business expenses.</p>
                <span class="inline-flex items-center text-xs font-semibold text-emerald-600 mt-3">View P&L Statement →</span>
            </a>

            <!-- Stock Summary -->
            <a href="{{ route('reports.stock') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-purple-400 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900 group-hover:text-purple-600 transition-colors">Stock Valuation Summary</h3>
                <p class="text-xs text-slate-500 mt-1">Inventory stock balance, purchase valuation, retail value, and profit potential for all catalog items.</p>
                <span class="inline-flex items-center text-xs font-semibold text-purple-600 mt-3">View Inventory Report →</span>
            </a>

            <!-- GST Summary -->
            <a href="{{ route('reports.gst') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-amber-400 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900 group-hover:text-amber-600 transition-colors">GST Tax Summary (GSTR-1 & 2)</h3>
                <p class="text-xs text-slate-500 mt-1">Output tax liability on sales and Input Tax Credit (ITC) on purchases split by CGST, SGST, and IGST.</p>
                <span class="inline-flex items-center text-xs font-semibold text-amber-600 mt-3">View GST Report →</span>
            </a>
        </div>
    </div>
</x-layouts.app>
