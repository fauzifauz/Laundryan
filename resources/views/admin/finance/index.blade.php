<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-black text-2xl text-gray-900 leading-tight">
                {{ __('Financial Overview') }}
            </h2>
            <div class="flex flex-wrap items-center gap-3" x-data="{ pdfLoading: false, csvLoading: false }">

                <!-- Hidden form for month/year filter (auto-submits) -->
                <form id="financeFilterForm" action="{{ route('admin.finance.index') }}" method="GET">
                    @foreach(request()->except(['month', 'year', 'start_date', 'end_date']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <input type="hidden" name="month" id="hiddenMonth" value="{{ $filterMonth }}">
                    <input type="hidden" name="year"  id="hiddenYear"  value="{{ $filterYear }}">
                </form>

                <!-- Date Range icon — moved LEFT of Month filter -->
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center justify-center w-10 h-10 bg-white border {{ request('start_date') ? 'border-blue-500 text-blue-600' : 'border-gray-200 text-gray-500' }} rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all shadow-sm group"
                        title="Custom Date Range">
                        <span class="material-symbols-outlined text-[20px] transition-transform group-hover:scale-110">date_range</span>
                    </button>
                    <div x-show="open" @click.away="open = false" style="display: none;"
                        class="absolute left-0 mt-2 w-72 rounded-2xl bg-white shadow-2xl border border-gray-100 z-50 p-4">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-3">Custom Date Range</p>
                        <form action="{{ route('admin.finance.index') }}" method="GET" class="space-y-3">
                            @foreach(request()->except(['start_date', 'end_date', 'month', 'year']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Start Date</label>
                                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-xl border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500 font-medium text-gray-700 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">End Date</label>
                                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-xl border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500 font-medium text-gray-700 shadow-sm" required>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.finance.index') }}" class="flex-1 bg-gray-100 text-gray-600 font-black py-2 rounded-xl hover:bg-gray-200 transition-all text-xs uppercase tracking-widest text-center">Reset</a>
                                <button type="submit" class="flex-1 bg-blue-600 text-white font-black py-2 rounded-xl hover:bg-blue-700 transition-all text-xs uppercase tracking-widest">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Month Filter (auto-submit on change) -->
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">calendar_month</span>
                    <select id="monthSelect"
                        onchange="document.getElementById('hiddenMonth').value=this.value; document.getElementById('financeFilterForm').submit();"
                        class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="all" {{ $filterMonth === 'all' ? 'selected' : '' }}>All Months</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $filterMonth == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(2026, $m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[16px] pointer-events-none">expand_more</span>
                </div>

                <!-- Year Filter (auto-submit on change) -->
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">event</span>
                    <select id="yearSelect"
                        onchange="document.getElementById('hiddenYear').value=this.value; document.getElementById('financeFilterForm').submit();"
                        class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="all" {{ $filterYear === 'all' ? 'selected' : '' }}>All Years</option>
                        @foreach(range(now()->year - 2, now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ $filterYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[16px] pointer-events-none">expand_more</span>
                </div>

                <!-- Export PDF — respects date_range OR month/year -->
                <a href="{{ route('admin.finance.export.pdf') }}?month={{ $filterMonth }}&year={{ $filterYear }}{{ $startDate ? '&start_date='.$startDate.'&end_date='.$endDate : '' }}"
                    @click="pdfLoading = true; setTimeout(() => pdfLoading = false, 3000)"
                    :class="{ 'opacity-70 pointer-events-none': pdfLoading }"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-rose-100 text-rose-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-rose-50 hover:shadow-md transition-all shadow-sm group whitespace-nowrap">
                    <span x-show="!pdfLoading" class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">picture_as_pdf</span>
                    <svg x-show="pdfLoading" class="animate-spin h-[18px] w-[18px] text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" x-cloak>
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="pdfLoading ? 'Exporting...' : 'Export PDF'"></span>
                </a>

                <!-- Export CSV — respects date_range OR month/year -->
                <a href="{{ route('admin.finance.export.csv') }}?month={{ $filterMonth }}&year={{ $filterYear }}{{ $startDate ? '&start_date='.$startDate.'&end_date='.$endDate : '' }}"
                    @click="csvLoading = true; setTimeout(() => csvLoading = false, 3000)"
                    :class="{ 'opacity-70 pointer-events-none': csvLoading }"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-emerald-100 text-emerald-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-emerald-50 hover:shadow-md transition-all shadow-sm group whitespace-nowrap">
                    <span x-show="!csvLoading" class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">table_view</span>
                    <svg x-show="csvLoading" class="animate-spin h-[18px] w-[18px] text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" x-cloak>
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="csvLoading ? 'Exporting...' : 'Export CSV'"></span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Income -->
                <a href="#income-history" class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 shadow-xl shadow-emerald-100 flex items-center justify-between group overflow-hidden relative hover:scale-[1.02] active:scale-95 transition-all duration-300 cursor-pointer">
                    <div class="absolute -right-4 -top-4 w-32 h-32 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div>
                        <div class="text-[10px] font-black text-emerald-50 uppercase tracking-widest mb-1">
                            Total Income &mdash; {{ $periodLabel }}
                        </div>
                        <div class="text-3xl font-black text-white flex items-end gap-2 mb-2">
                            Rp {{ number_format($filteredIncomeSum, 0, ',', '.') }}
                        </div>
                        <div class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white/20 backdrop-blur-sm border border-white/10 group/badge">
                            <span class="material-symbols-outlined text-[14px] {{ $incomeTrend['class'] }}">{{ $incomeTrend['icon'] }}</span>
                            <span class="text-[10px] font-bold text-white">{{ $incomeTrend['label'] }}</span>
                        </div>
                    </div>
                    <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white border border-white/20 group-hover:bg-white group-hover:text-emerald-600 transition-all duration-500">
                        <span class="material-symbols-outlined text-3xl">trending_up</span>
                    </div>
                </a>

                <!-- Total Expenses -->
                <a href="#expense-history" class="bg-gradient-to-br from-rose-500 to-red-600 rounded-3xl p-6 shadow-xl shadow-rose-100 flex items-center justify-between group overflow-hidden relative hover:scale-[1.02] active:scale-95 transition-all duration-300 cursor-pointer">
                    <div class="absolute -right-4 -top-4 w-32 h-32 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div>
                        <div class="text-[10px] font-black text-rose-50 uppercase tracking-widest mb-1">
                            Total Expenses &mdash; {{ $periodLabel }}
                        </div>
                        <div class="text-3xl font-black text-white flex items-end gap-2 mb-2">
                            Rp {{ number_format($filteredExpenseSum, 0, ',', '.') }}
                        </div>
                        <div class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white/20 backdrop-blur-sm border border-white/10 group/badge">
                            <span class="material-symbols-outlined text-[14px] {{ $expenseTrend['class'] }}">{{ $expenseTrend['icon'] }}</span>
                            <span class="text-[10px] font-bold text-white">{{ $expenseTrend['label'] }}</span>
                        </div>
                    </div>
                    <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white border border-white/20 group-hover:bg-white group-hover:text-rose-600 transition-all duration-500">
                        <span class="material-symbols-outlined text-3xl">trending_down</span>
                    </div>
                </a>

                <!-- Net Profit -->
                <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl p-6 shadow-xl shadow-indigo-100 flex items-center justify-between group overflow-hidden relative hover:scale-[1.02] transition-all duration-300">
                    <div class="absolute -right-4 -top-4 w-32 h-32 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div>
                        <div class="text-[10px] font-black text-indigo-50 uppercase tracking-widest mb-1">Net Profit &mdash; {{ $periodLabel }}</div>
                        <div class="text-3xl font-black text-white flex items-end gap-2 mb-2">
                            Rp {{ number_format($netProfit, 0, ',', '.') }}
                        </div>
                        <div class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white/20 backdrop-blur-sm border border-white/10">
                            <span class="material-symbols-outlined text-[14px] {{ is_numeric($profitMargin) ? ($profitMargin >= 0 ? 'text-green-300' : 'text-red-300') : 'text-gray-300' }}">monitoring</span>
                            <span class="text-[10px] font-bold text-white">Profit Margin: {{ is_numeric($profitMargin) ? $profitMargin . '%' : $profitMargin }}</span>
                        </div>
                    </div>
                    <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white border border-white/20 group-hover:bg-white group-hover:text-indigo-600 transition-all duration-500">
                        <span class="material-symbols-outlined text-3xl">payments</span>
                    </div>
                </div>
            </div>



            <!-- Charts: Full-Width Trend Chart -->
            <div id="trend-chart" class="bg-white shadow-sm rounded-2xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600 text-[20px]">stacked_line_chart</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Income vs Expense Trend</h3>
                            <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">{{ ucfirst($period) }} View</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1.5 text-[10px] font-black text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span> Income
                        </span>
                        <span class="flex items-center gap-1.5 text-[10px] font-black text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-100">
                            <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span> Expense
                        </span>
                    </div>
                </div>
                <div class="w-full h-[280px]">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Charts: Two Columns on Large Screens -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Revenue by Service -->
                <div class="bg-white shadow-sm rounded-2xl p-6 flex flex-col">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-emerald-500 text-[20px]">donut_large</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Top Revenue by Service</h3>
                            <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">{{ ucfirst($period) }}</p>
                        </div>
                    </div>
                    @if($revenueByServiceData->count() > 0)
                        <div class="flex-1 w-full h-[260px] flex items-center justify-center">
                            <canvas id="revenueServiceChart"></canvas>
                        </div>
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center text-gray-300 py-10">
                            <span class="material-symbols-outlined text-5xl mb-3">data_usage</span>
                            <p class="font-bold text-sm text-gray-400">No revenue data.</p>
                        </div>
                    @endif
                </div>

                <!-- Expense Allocation -->
                <div class="bg-white shadow-sm rounded-2xl p-6 flex flex-col">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-rose-500 text-[20px]">pie_chart</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Expense Allocation</h3>
                            <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">{{ ucfirst($period) }}</p>
                        </div>
                    </div>
                    @if($expensePieData->count() > 0)
                        <div class="flex-1 w-full h-[260px] flex items-center justify-center">
                            <canvas id="expensePieChart"></canvas>
                        </div>
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center text-gray-300 py-10">
                            <span class="material-symbols-outlined text-5xl mb-3">data_usage</span>
                            <p class="font-bold text-sm text-gray-400">No expense data.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activities Grid (Side-by-Side) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Income Table -->
                <div id="income-history" class="bg-white shadow-sm rounded-3xl overflow-hidden border border-gray-100 flex flex-col h-full">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-emerald-500 text-[18px]">history</span>
                                </div>
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">Recent Income</h3>
                            </div>
                            <a href="{{ route('admin.finance.income') }}" class="text-[10px] font-black text-blue-600 hover:text-blue-700 transition-colors uppercase tracking-widest flex items-center gap-1">
                                View All <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                        <div class="overflow-x-auto max-h-[400px] custom-scrollbar">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <th class="px-6 py-4 whitespace-nowrap">Date & Time</th>
                                        <th class="px-6 py-4">Source & Ref</th>
                                        <th class="px-6 py-4">Method</th>
                                        <th class="px-6 py-4 text-right">Amount</th>
                                        <th class="px-6 py-4 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-sm bg-white">
                                    @forelse($incomeHistory as $item)
                                        <tr class="hover:bg-emerald-50/30 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col gap-0.5">
                                                    <span class="text-gray-900 font-bold text-[11px] whitespace-nowrap">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</span>
                                                    <span class="text-[10px] text-gray-400 font-medium whitespace-nowrap">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }} WIB</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $isStripe = str_contains(strtolower($item->description), 'stripe') || str_contains(strtolower($item->description), 'payment intent');
                                                    $orderCode = '';
                                                    if (preg_match('/ORD-[A-Z0-9]+/', $item->description, $matches)) {
                                                        $orderCode = $matches[0];
                                                    }
                                                @endphp

                                                @if($orderCode)
                                                    @php
                                                        // Pastikan kode bersih dari spasi dan cari ID-nya
                                                        $cleanCode = trim($orderCode);
                                                        $targetOrder = \App\Models\Order::where('order_code', $cleanCode)->first();
                                                        $targetId = $targetOrder ? $targetOrder->id : null;
                                                    @endphp
                                                    <a href="{{ $targetId ? route('admin.orders.show', $targetId) : route('admin.orders.index', ['search' => $cleanCode]) }}" class="flex flex-col gap-1.5 group/ref">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md uppercase tracking-tighter group-hover/ref:bg-blue-600 group-hover/ref:text-white transition-all">
                                                                {{ $item->category }}
                                                            </span>
                                                            <span class="text-[10px] font-black text-white bg-blue-500 px-2 py-0.5 rounded-md uppercase shadow-sm group-hover/ref:bg-blue-700 transition-colors">
                                                                {{ $orderCode }}
                                                            </span>
                                                        </div>
                                                        <span class="text-xs font-bold text-gray-500 truncate max-w-[180px] group-hover/ref:text-blue-600 transition-colors">
                                                            {{ str_replace($orderCode, '', $item->description) }}
                                                            <span class="material-symbols-outlined text-[12px] align-middle opacity-0 group-hover/ref:opacity-100 transition-opacity">visibility</span>
                                                        </span>
                                                    </a>
                                                @else
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md w-fit uppercase tracking-tighter">
                                                            {{ $item->category }}
                                                        </span>
                                                        <span class="text-xs font-bold text-gray-900 truncate max-w-[180px]">
                                                            {{ $item->description }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    @if($isStripe)
                                                        <span class="material-symbols-outlined text-indigo-500 text-[18px]">credit_card</span>
                                                        <span class="text-[10px] font-black text-gray-600 uppercase">Stripe</span>
                                                    @else
                                                        <span class="material-symbols-outlined text-emerald-500 text-[18px]">payments</span>
                                                        <span class="text-[10px] font-black text-gray-600 uppercase">Cash</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right font-black text-emerald-600">
                                                + Rp {{ number_format($item->amount, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex justify-center">
                                                    @if($orderCode)
                                                        <a href="{{ route('admin.orders.index', ['search' => $orderCode]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-all shadow-sm group/btn border border-gray-100" title="Lihat Order">
                                                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                                                        </a>
                                                    @else
                                                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-300 cursor-not-allowed border border-gray-100" title="Manual Entry">
                                                            <span class="material-symbols-outlined text-[18px]">info</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-400">
                                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">receipt_long</span>
                                                    <p class="font-bold text-sm">No income recorded for this period.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Expense Table -->
                    <div id="expense-history" class="bg-white shadow-sm rounded-3xl overflow-hidden border border-gray-100 flex flex-col h-full">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-rose-50 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-rose-500 text-[18px]">receipt_long</span>
                                </div>
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">Recent Expenses</h3>
                            </div>
                            <a href="{{ route('admin.finance.expense') }}" class="text-[10px] font-black text-rose-600 hover:text-rose-700 transition-colors uppercase tracking-widest flex items-center gap-1">
                                View All & Log <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                        <div class="overflow-x-auto max-h-[400px] custom-scrollbar">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <th class="px-6 py-4 whitespace-nowrap">Date & Time</th>
                                        <th class="px-6 py-4">Category & Details</th>
                                        <th class="px-6 py-4">Method</th>
                                        <th class="px-6 py-4 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-sm bg-white">
                                    @forelse($expenseHistory as $item)
                                        <tr class="hover:bg-rose-50/30 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col gap-0.5">
                                                    <span class="text-gray-900 font-bold text-[11px] whitespace-nowrap">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</span>
                                                    <span class="text-[10px] text-gray-400 font-medium whitespace-nowrap">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }} WIB</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md w-fit uppercase tracking-tighter">
                                                        {{ $item->category }}
                                                    </span>
                                                    <div class="text-xs font-bold text-gray-900 truncate max-w-[180px]">{{ $item->description }}</div>
                                                    @if($item->attachment)
                                                        <a href="{{ Storage::url($item->attachment) }}" target="_blank" class="inline-flex items-center gap-1 mt-1 text-[9px] font-black text-blue-600 hover:text-blue-700 transition-colors uppercase tracking-widest">
                                                            <span class="material-symbols-outlined text-[14px]">image</span> Lihat Struk
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    @if($item->payment_method === 'CASH')
                                                        <span class="material-symbols-outlined text-rose-500 text-[18px]">payments</span>
                                                        <span class="text-[10px] font-black text-gray-600 uppercase">CASH</span>
                                                    @elseif($item->payment_method === 'TRANSFER')
                                                        <span class="material-symbols-outlined text-blue-500 text-[18px]">account_balance</span>
                                                        <span class="text-[10px] font-black text-gray-600 uppercase">TRANSFER</span>
                                                    @elseif($item->payment_method === 'STRIPE')
                                                        <span class="material-symbols-outlined text-indigo-500 text-[18px]">credit_card</span>
                                                        <span class="text-[10px] font-black text-gray-600 uppercase">STRIPE</span>
                                                    @else
                                                        <span class="material-symbols-outlined text-gray-400 text-[18px]">more_horiz</span>
                                                        <span class="text-[10px] font-black text-gray-600 uppercase">{{ $item->payment_method ?? 'CASH' }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right font-black text-rose-600">
                                                - Rp {{ number_format($item->amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-400">
                                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">receipt_long</span>
                                                    <p class="font-bold text-sm">No expenses recorded for this period.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>
        </div>
    </div>

            <style>
                .custom-scrollbar::-webkit-scrollbar {
                    width: 5px;
                    height: 5px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: #f9fafb;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: #e5e7eb;
                    border-radius: 10px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: #d1d5db;
                }
            </style>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // ─── TREND CHART (Bar/Line) ───
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            const trendData = @json($chartData);
            
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: trendData.labels,
                    datasets: [
                        {
                            label: 'Income',
                            data: trendData.income,
                            backgroundColor: '#10b981', // emerald-500
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.4
                        },
                        {
                            label: 'Expense',
                            data: trendData.expense,
                            backgroundColor: '#f43f5e', // rose-500
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            backgroundColor: '#111827',
                            titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13 },
                            bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 14, weight: 'bold' },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                callback: function(value) {
                                    if (value === 0) return '0';
                                    return 'Rp ' + (value / 1000) + 'k';
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    onClick: (event, elements) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const fullDate = trendData.full_dates[index];
                            
                            // Redirect with filter and scroll to history
                            const url = new URL(window.location.href);
                            url.searchParams.set('filter_date', fullDate);
                            url.hash = 'income-history';
                            window.location.href = url.toString();
                        }
                    }
                }
            });

            // ─── REVENUE BY SERVICE PIE CHART ───
            @if($revenueByServiceData->count() > 0)
                const revCtx = document.getElementById('revenueServiceChart').getContext('2d');
                const revDataRaw = @json($revenueByServiceData);
                
                const revLabels = revDataRaw.map(item => item.label);
                const revValues = revDataRaw.map(item => item.value);
                const revColors = revDataRaw.map(item => item.color);
                const revIds = revDataRaw.map(item => item.service_id);
                
                new Chart(revCtx, {
                    type: 'doughnut',
                    data: {
                        labels: revLabels,
                        datasets: [{
                            data: revValues,
                            backgroundColor: revColors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 10, weight: 'bold' }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#111827',
                                titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13 },
                                bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 14, weight: 'bold' },
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed !== null) {
                                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        onClick: (event, elements) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const serviceId = revIds[index];
                                
                                // Redirect to the dedicated Income History page with service filter
                                const url = new URL('{{ route("admin.finance.income") }}');
                                url.searchParams.set('filter_service', serviceId);
                                @if($filterMonth)
                                    url.searchParams.set('month', '{{ $filterMonth }}');
                                @endif
                                @if($filterYear)
                                    url.searchParams.set('year', '{{ $filterYear }}');
                                @endif
                                @if($activeStartDate)
                                    url.searchParams.set('start_date', '{{ $activeStartDate }}');
                                @endif
                                @if($activeEndDate)
                                    url.searchParams.set('end_date', '{{ $activeEndDate }}');
                                @endif
                                window.location.href = url.toString();
                            }
                        }
                    }
                });
            @endif

            // ─── EXPENSE PIE CHART ───
            @if($expensePieData->count() > 0)
                const pieCtx = document.getElementById('expensePieChart').getContext('2d');
                const pieDataRaw = @json($expensePieData);
                
                const pieLabels = pieDataRaw.map(item => item.label);
                const pieValues = pieDataRaw.map(item => item.value);
                const pieColors = pieDataRaw.map(item => item.color);
                
                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: pieLabels,
                        datasets: [{
                            data: pieValues,
                            backgroundColor: pieColors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 10, weight: 'bold' }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#111827',
                                titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13 },
                                bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 14, weight: 'bold' },
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed !== null) {
                                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        onClick: (event, elements) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const category = pieLabels[index];
                                
                                // Redirect to the dedicated Expense History page with category filter
                                const url = new URL('{{ route("admin.finance.expense") }}');
                                url.searchParams.set('filter_category', category);
                                @if($filterMonth)
                                    url.searchParams.set('month', '{{ $filterMonth }}');
                                @endif
                                @if($filterYear)
                                    url.searchParams.set('year', '{{ $filterYear }}');
                                @endif
                                @if($activeStartDate)
                                    url.searchParams.set('start_date', '{{ $activeStartDate }}');
                                @endif
                                @if($activeEndDate)
                                    url.searchParams.set('end_date', '{{ $activeEndDate }}');
                                @endif
                                window.location.href = url.toString();
                            }
                        }
                    }
                });
            @endif
        });
    </script>
    @endpush
</x-app-layout>
