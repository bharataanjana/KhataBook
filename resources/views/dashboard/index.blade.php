<x-layouts.app>
    <x-slot:title>Dashboard - KhataApp</x-slot:title>

    <div class="space-y-6">
        <!-- Page Title & Top Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
                <p class="text-xs sm:text-sm text-slate-500">Real-time overview of sales, purchases, cashflow, and stock</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mr-1.5"></span>
                    {{ now()->format('D, d M Y') }}
                </span>
            </div>
        </div>

        <!-- 5 Vyapar KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total Sales -->
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs hover:border-slate-300 transition-all">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Total Sales</span>
                    <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </span>
                </div>
                <div class="mt-2">
                    <div class="text-xl font-bold text-slate-900">₹{{ number_format($totalSales, 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">This Month: <span class="font-semibold text-emerald-600">₹{{ number_format($monthSales, 2) }}</span></div>
                </div>
            </div>

            <!-- Total Purchases -->
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs hover:border-slate-300 transition-all">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Total Purchases</span>
                    <span class="p-1.5 bg-blue-50 text-blue-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </span>
                </div>
                <div class="mt-2">
                    <div class="text-xl font-bold text-slate-900">₹{{ number_format($totalPurchases, 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">This Month: <span class="font-semibold text-blue-600">₹{{ number_format($monthPurchases, 2) }}</span></div>
                </div>
            </div>

            <!-- You'll Receive (Receivables) -->
            <div class="bg-white rounded-xl p-4 border border-emerald-100 shadow-xs bg-linear-to-b from-white to-emerald-50/20">
                <div class="flex items-center justify-between text-emerald-700">
                    <span class="text-xs font-semibold uppercase tracking-wider">You'll Receive</span>
                    <span class="p-1.5 bg-emerald-100 text-emerald-700 rounded-lg font-bold text-xs">Dr</span>
                </div>
                <div class="mt-2">
                    <div class="text-xl font-bold text-emerald-600">₹{{ number_format($toReceive, 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">From Customers</div>
                </div>
            </div>

            <!-- You'll Pay (Payables) -->
            <div class="bg-white rounded-xl p-4 border border-rose-100 shadow-xs bg-linear-to-b from-white to-rose-50/20">
                <div class="flex items-center justify-between text-rose-700">
                    <span class="text-xs font-semibold uppercase tracking-wider">You'll Pay</span>
                    <span class="p-1.5 bg-rose-100 text-rose-700 rounded-lg font-bold text-xs">Cr</span>
                </div>
                <div class="mt-2">
                    <div class="text-xl font-bold text-rose-600">₹{{ number_format($toPay, 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">To Suppliers</div>
                </div>
            </div>

            <!-- Cash & Bank Balance -->
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs hover:border-slate-300 transition-all">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Cash & Bank</span>
                    <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                </div>
                <div class="mt-2">
                    <div class="text-xl font-bold {{ $netCashBalance >= 0 ? 'text-indigo-600' : 'text-rose-600' }}">₹{{ number_format($netCashBalance, 2) }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Net Cash in hand</div>
                </div>
            </div>
        </div>

        <!-- 7-Day Performance & Quick Navigation Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 7-Day Trend Chart -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Weekly Business Trend</h2>
                        <p class="text-xs text-slate-500">Sales vs Purchases for the last 7 days</p>
                    </div>
                    <div class="flex items-center space-x-3 text-xs">
                        <span class="inline-flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5"></span>Sales</span>
                        <span class="inline-flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-1.5"></span>Purchases</span>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-2 pt-4 border-t border-slate-100">
                    @php
                        $maxVal = max(max($chartSales), max($chartPurchases), 100);
                    @endphp
                    @foreach($chartLabels as $idx => $label)
                        <div class="flex flex-col items-center">
                            <div class="h-32 w-full flex items-end justify-center gap-1.5 pb-2">
                                <!-- Sales Bar -->
                                <div class="w-3.5 bg-emerald-500 rounded-t transition-all hover:bg-emerald-600" 
                                     style="height: {{ max(4, round(($chartSales[$idx] / $maxVal) * 110)) }}px"
                                     title="Sales: ₹{{ number_format($chartSales[$idx], 2) }}"></div>
                                <!-- Purchases Bar -->
                                <div class="w-3.5 bg-blue-500 rounded-t transition-all hover:bg-blue-600" 
                                     style="height: {{ max(4, round(($chartPurchases[$idx] / $maxVal) * 110)) }}px"
                                     title="Purchases: ₹{{ number_format($chartPurchases[$idx], 2) }}"></div>
                            </div>
                            <span class="text-[11px] font-medium text-slate-500">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Action Hub -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 mb-1">Quick Actions</h2>
                    <p class="text-xs text-slate-500 mb-4">Fast access to everyday business operations</p>

                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="{{ route('sales.create') }}" class="p-3 rounded-lg border border-slate-100 bg-slate-50/70 hover:bg-emerald-50 hover:border-emerald-200 text-left transition-all group">
                            <div class="w-7 h-7 rounded-md bg-emerald-100 text-emerald-700 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-800 block">Create Sale</span>
                            <span class="text-[10px] text-slate-500">GST Invoice</span>
                        </a>

                        <a href="{{ route('purchases.create') }}" class="p-3 rounded-lg border border-slate-100 bg-slate-50/70 hover:bg-blue-50 hover:border-blue-200 text-left transition-all group">
                            <div class="w-7 h-7 rounded-md bg-blue-100 text-blue-700 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-800 block">Record Purchase</span>
                            <span class="text-[10px] text-slate-500">Inward Bill</span>
                        </a>

                        <a href="{{ route('parties.create') }}" class="p-3 rounded-lg border border-slate-100 bg-slate-50/70 hover:bg-amber-50 hover:border-amber-200 text-left transition-all group">
                            <div class="w-7 h-7 rounded-md bg-amber-100 text-amber-700 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-800 block">Add Party</span>
                            <span class="text-[10px] text-slate-500">Customer/Vendor</span>
                        </a>

                        <a href="{{ route('items.create') }}" class="p-3 rounded-lg border border-slate-100 bg-slate-50/70 hover:bg-purple-50 hover:border-purple-200 text-left transition-all group">
                            <div class="w-7 h-7 rounded-md bg-purple-100 text-purple-700 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-800 block">Add Item</span>
                            <span class="text-[10px] text-slate-500">Product / Stock</span>
                        </a>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <a href="{{ route('reports.daybook') }}" class="font-medium text-blue-600 hover:text-blue-800 flex items-center">
                        Today's Day Book
                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('reports.pnl') }}" class="font-medium text-slate-600 hover:text-slate-800">
                        Profit & Loss →
                    </a>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts & Recent Invoices -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Low Stock Warnings -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-amber-50/30">
                    <div class="flex items-center space-x-2">
                        <span class="p-1 rounded bg-amber-100 text-amber-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Low Stock Alert</h2>
                            <p class="text-xs text-slate-500">{{ $lowStockCount }} items need replenishment</p>
                        </div>
                    </div>
                    <a href="{{ route('items.index', ['low_stock' => 1]) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">View All</a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($lowStockItems as $lItem)
                        <div class="p-3.5 flex items-center justify-between hover:bg-slate-50/80 transition-colors">
                            <div>
                                <a href="{{ route('items.show', $lItem) }}" class="text-xs font-semibold text-slate-900 hover:text-blue-600">{{ $lItem->name }}</a>
                                <p class="text-[11px] text-slate-500">Min Threshold: {{ $lItem->low_stock_threshold }} {{ $lItem->unit }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $lItem->current_stock <= 0 ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $lItem->current_stock }} {{ $lItem->unit }} left
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">
                            ✓ All items have healthy stock levels
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Sale Invoices -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Recent Sale Invoices</h2>
                        <p class="text-xs text-slate-500">Latest customer billings</p>
                    </div>
                    <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">View All</a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($recentInvoices as $rInv)
                        <div class="p-3.5 flex items-center justify-between hover:bg-slate-50/80 transition-colors">
                            <div>
                                <a href="{{ route('sales.show', $rInv) }}" class="text-xs font-semibold text-slate-900 hover:text-blue-600">
                                    {{ $rInv->invoice_number }}
                                </a>
                                <p class="text-[11px] text-slate-500">{{ $rInv->party->name }} &bull; {{ $rInv->date->format('d M') }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-bold text-slate-900">₹{{ number_format($rInv->total_amount, 2) }}</div>
                                <div>
                                    @if($rInv->payment_status === 'paid')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">Paid</span>
                                    @elseif($rInv->payment_status === 'partial')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800">Partial</span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-800">Unpaid</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">
                            No invoices generated yet. Click "+ Add Sale" to create your first bill!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
