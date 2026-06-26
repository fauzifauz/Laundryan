<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-black text-2xl text-gray-900 leading-tight">
                {{ __('Admin Dashboard') }}
            </h2>
            <div class="flex flex-wrap items-center gap-3" x-data="{ exportMonth: 'all', exportYear: 'all', pdfLoading: false, csvLoading: false }">
                <!-- Month Filter for Export (Icon represented dropdown) -->
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">calendar_month</span>
                    <select x-model="exportMonth" class="text-xs font-bold text-gray-700 bg-white border border-gray-150 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="all">All Months</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::create(2026, $m)->format('F') }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[16px] pointer-events-none">expand_more</span>
                </div>

                <!-- Year Filter for Export (Icon represented dropdown) -->
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">event</span>
                    <select x-model="exportYear" class="text-xs font-bold text-gray-700 bg-white border border-gray-150 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="all">All Years</option>
                        @foreach(range(now()->year - 2, now()->year + 1) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[16px] pointer-events-none">expand_more</span>
                </div>

                <a :href="'{{ route('admin.export.pdf') }}?month=' + exportMonth + '&year=' + exportYear"
                    @click="pdfLoading = true; setTimeout(() => pdfLoading = false, 3000)"
                    :class="{ 'opacity-70 pointer-events-none': pdfLoading }"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-rose-100 text-rose-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-rose-50 hover:shadow-md transition-all group shadow-sm whitespace-nowrap">
                    <span x-show="!pdfLoading"
                        class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">picture_as_pdf</span>
                    <svg x-show="pdfLoading" class="animate-spin h-[18px] w-[18px] text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" x-cloak>
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="pdfLoading ? 'Exporting...' : 'Export PDF'"></span>
                </a>
                <a :href="'{{ route('admin.export.csv') }}?month=' + exportMonth + '&year=' + exportYear"
                    @click="csvLoading = true; setTimeout(() => csvLoading = false, 3000)"
                    :class="{ 'opacity-70 pointer-events-none': csvLoading }"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-emerald-100 text-emerald-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-emerald-50 hover:shadow-md transition-all group shadow-sm whitespace-nowrap">
                    <span x-show="!csvLoading"
                        class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">table_view</span>
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

            @php
                $greetingConfig = [
                    'GOOD MORNING' => [
                        'gradient' => 'from-amber-500 via-orange-500 to-rose-500 shadow-orange-100',
                        'icon' => 'wb_sunny',
                        'icon_animate' => 'animate-[spin_20s_linear_infinite]',
                        'subtitle' => 'Rise and shine! Ready to tackle today\'s laundry operations?',
                    ],
                    'GOOD AFTERNOON' => [
                        'gradient' => 'from-sky-500 via-blue-600 to-indigo-700 shadow-blue-100',
                        'icon' => 'wb_twilight',
                        'icon_animate' => 'animate-[pulse_3s_infinite]',
                        'subtitle' => 'Good afternoon! Operations are running in full swing.',
                    ],
                    'GOOD EVENING' => [
                        'gradient' => 'from-rose-500 via-purple-600 to-indigo-800 shadow-purple-100',
                        'icon' => 'filter_drama',
                        'icon_animate' => 'animate-[bounce_2s_infinite]',
                        'subtitle' => 'Good evening! Time to review today\'s completed orders.',
                    ],
                    'GOOD NIGHT' => [
                        'gradient' => 'from-slate-950 via-indigo-950 to-slate-900 shadow-slate-900/50',
                        'icon' => 'bedtime',
                        'icon_animate' => 'animate-[pulse_2s_infinite]',
                        'subtitle' => 'Operational hours are ending. Have a peaceful night!',
                    ],
                ];
                $currentConfig = $greetingConfig[(string)$greeting] ?? $greetingConfig['GOOD MORNING'];
            @endphp

            <!-- Welcome Greeting Banner -->
            <div class="bg-gradient-to-r {{ $currentConfig['gradient'] }} rounded-3xl py-10 md:py-12 px-6 md:px-10 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden transition-all duration-700">
                <!-- Soft Glow Overlays -->
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="flex items-center gap-5 relative z-10">
                    <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-inner">
                        <span class="material-symbols-outlined text-4xl {{ $currentConfig['icon_animate'] }}">{{ $currentConfig['icon'] }}</span>
                    </div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight uppercase">
                            HALLO ADMIN, {{ $greeting }}!
                        </h1>
                        <p class="text-sm text-blue-50 font-medium tracking-wide mt-1">
                            {{ $currentConfig['subtitle'] }}
                        </p>
                    </div>
                </div>
                
                <div class="bg-white/10 backdrop-blur-md border border-white/20 px-5 py-2.5 rounded-2xl text-white text-xs font-black uppercase tracking-widest relative z-10 flex items-center gap-2 hover:bg-white/20 transition-all cursor-default">
                    <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                    {{ now()->format('l, d F Y') }}
                </div>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Pesanan -->
                <a href="{{ route('admin.orders.index') }}"
                    class="bg-white overflow-hidden shadow-sm rounded-xl p-6 flex flex-col justify-center border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div
                        class="text-xs font-black text-gray-400 uppercase tracking-widest group-hover:text-blue-600 transition-colors">
                        Total Orders</div>
                    <div class="mt-2 text-4xl font-black text-gray-900">{{ $totalOrders }}</div>
                    <div
                        class="mt-2 text-[10px] font-bold text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
                        Click to view all →</div>
                </a>

                <!-- Dalam Proses -->
                <a href="{{ route('admin.orders.index', ['status' => 'in_progress']) }}"
                    class="bg-white overflow-hidden shadow-sm rounded-xl p-6 flex flex-col justify-center border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div
                        class="text-xs font-black text-gray-400 uppercase tracking-widest group-hover:text-blue-600 transition-colors">
                        In Progress</div>
                    <div class="mt-2 text-4xl font-black text-blue-600">{{ $inProgressOrders }}</div>
                    <div
                        class="mt-2 text-[10px] font-bold text-blue-400 opacity-0 group-hover:opacity-100 transition-opacity">
                        Click to view queue →</div>
                </a>

                <!-- Selesai -->
                <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}"
                    class="bg-white overflow-hidden shadow-sm rounded-xl p-6 flex flex-col justify-center border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div
                        class="text-xs font-black text-gray-400 uppercase tracking-widest group-hover:text-emerald-600 transition-colors">
                        Completed</div>
                    <div class="mt-2 text-4xl font-black text-emerald-600">{{ $completedOrders }}</div>
                    <div
                        class="mt-2 text-[10px] font-bold text-emerald-400 opacity-0 group-hover:opacity-100 transition-opacity">
                        Click for history →</div>
                </a>

                <!-- Rating -->
                <a href="#customer-reviews"
                    class="bg-white overflow-hidden shadow-sm rounded-xl p-6 flex flex-col justify-center border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div
                        class="text-xs font-black text-gray-400 uppercase tracking-widest group-hover:text-amber-500 transition-colors">
                        Average Rating</div>
                    <div class="mt-2 text-4xl font-black text-amber-500 flex items-center gap-2">
                        <span class="material-symbols-outlined text-3xl">star</span>
                        {{ number_format($avgRating, 1) }}
                    </div>
                    <div class="mt-2 text-[10px] font-bold text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">Click to view reviews →</div>
                </a>
            </div>

            <!-- KPIs Row 2 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-4">
                <!-- Today's Revenue -->
                <a href="{{ route('admin.finance.income', ['start_date' => now()->toDateString(), 'end_date' => now()->toDateString()]) }}"
                    class="bg-white overflow-hidden shadow-sm rounded-xl p-5 flex items-center gap-4 border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-600 transition-colors duration-300">
                        <span class="material-symbols-outlined text-emerald-600 text-2xl group-hover:text-white transition-colors duration-300">payments</span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-emerald-600 transition-colors">Today's Revenue</div>
                        <div class="mt-0.5 text-xl font-black text-emerald-600 truncate">
                            Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                        </div>
                        <div class="text-[9px] font-bold text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">Click to view income →</div>
                    </div>
                </a>

                <!-- Cancelled Orders -->
                <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}"
                    class="bg-white overflow-hidden shadow-sm rounded-xl p-5 flex items-center gap-4 border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-600 transition-colors duration-300">
                        <span class="material-symbols-outlined text-rose-600 text-2xl group-hover:text-white transition-colors duration-300">cancel</span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-rose-600 transition-colors">Cancelled Orders</div>
                        <div class="mt-0.5 text-xl font-black text-rose-600">{{ $cancelledOrders }}</div>
                        <div class="text-[9px] font-bold text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">Click to review →</div>
                    </div>
                </a>

                <!-- Active Couriers -->
                <a href="{{ route('admin.tracking.index', ['highlight' => 'active']) }}"
                    class="bg-white overflow-hidden shadow-sm rounded-xl p-5 flex items-center gap-4 border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition-colors duration-300">
                        <span class="material-symbols-outlined text-blue-600 text-2xl group-hover:text-white transition-colors duration-300">delivery_dining</span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-blue-600 transition-colors">Active Couriers</div>
                        <div class="mt-0.5 text-xl font-black text-blue-600">{{ $activeCouriersCount }}</div>
                        <div class="text-[9px] font-bold text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">Click to track map →</div>
                    </div>
                </a>

                <!-- Pending Payment -->
                <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}"
                    class="bg-white overflow-hidden shadow-sm rounded-xl p-5 flex items-center gap-4 border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-500 transition-colors duration-300">
                        <span class="material-symbols-outlined text-amber-500 text-2xl group-hover:text-white transition-colors duration-300">pending_actions</span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-amber-500 transition-colors">Pending Payment</div>
                        <div class="mt-0.5 text-xl font-black text-amber-500">{{ $pendingPaymentOrders }}</div>
                        <div class="text-[9px] font-bold text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">Click to verify →</div>
                    </div>
                </a>
            </div>

            <div id="customer-reviews-anchor"></div> <!-- Invisible anchor -->

            <!-- Customer Growth Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <a href="{{ route('admin.users.index', ['role' => 'pelanggan', 'start_date' => now()->startOfWeek()->format('Y-m-d')]) }}"
                    class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-3xl p-6 shadow-xl shadow-blue-100 flex items-center justify-between group overflow-hidden relative hover:scale-[1.02] transition-all duration-300">
                    <div
                        class="absolute -right-4 -top-4 w-32 h-32 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700">
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-blue-100 uppercase tracking-widest mb-1">New
                            Customers (This Week)</div>
                        <div class="text-4xl font-black text-white flex items-end gap-2">
                            {{ $newCustomersThisWeek }}
                            <span
                                class="text-xs font-bold text-blue-100 mb-1.5 uppercase tracking-tighter">Registered</span>
                        </div>
                    </div>
                    <div
                        class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white border border-white/20 group-hover:bg-white group-hover:text-blue-600 transition-all duration-500">
                        <span class="material-symbols-outlined text-3xl">person_add</span>
                    </div>
                </a>

                <a href="{{ route('admin.users.index', ['role' => 'pelanggan', 'returning' => '1']) }}"
                    class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 shadow-xl shadow-emerald-100 flex items-center justify-between group overflow-hidden relative hover:scale-[1.02] transition-all duration-300">
                    <div
                        class="absolute -right-4 -top-4 w-32 h-32 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700">
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-emerald-50 uppercase tracking-widest mb-1">Customer
                            Retention</div>
                        <div class="text-4xl font-black text-white flex items-end gap-2">
                            {{ $retentionRate }}%
                            <span
                                class="text-xs font-bold text-emerald-50 mb-1.5 uppercase tracking-tighter">Loyalty</span>
                        </div>
                    </div>
                    <div
                        class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white border border-white/20 group-hover:bg-white group-hover:text-emerald-600 transition-all duration-500">
                        <span class="material-symbols-outlined text-3xl">verified</span>
                    </div>
                </a>
            </div>

            <style>
                @keyframes flow-line {
                    0% {
                        background-position: 200% 0;
                    }

                    100% {
                        background-position: -200% 0;
                    }
                }

                .animate-flow-line {
                    background-size: 200% 100%;
                    animation: flow-line 3s linear infinite;
                }

                @keyframes marquee {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }

                .animate-marquee {
                    display: flex;
                    width: max-content;
                    animation: marquee 25s linear infinite;
                }

                .animate-marquee:hover {
                    animation-play-state: paused;
                }

                /* New Animations for Top Couriers */
                @keyframes slideUpFade {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes shimmer {
                    0% { transform: translateX(-100%) skewX(-15deg); }
                    100% { transform: translateX(200%) skewX(-15deg); }
                }

                @keyframes pulse-star {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.2); }
                }

                @keyframes rank-pop {
                    0% { transform: scale(0); }
                    70% { transform: scale(1.2); }
                    100% { transform: scale(1); }
                }

                .courier-card-animate {
                    opacity: 0;
                    animation: slideUpFade 0.6s ease-out forwards;
                }

                .winner-shine {
                    position: relative;
                    overflow: hidden;
                }

                .winner-shine::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 50%;
                    height: 100%;
                    background: linear-gradient(
                        to right,
                        transparent,
                        rgba(255, 255, 255, 0.4),
                        transparent
                    );
                    transform: skewX(-15deg);
                    animation: shimmer 3s infinite;
                    pointer-events: none;
                }
            </style>

            <div class="space-y-6">
                <!-- Status Breakdown Horizontal Timeline -->
                <div class="bg-white shadow-sm rounded-lg p-6 md:p-8 overflow-hidden">
                    <h3 class="text-lg font-black text-gray-900 mb-10">Laundry Processing Status</h3>

                    @php
                        $styles = [
                            'Arrived at Laundry' => ['icon' => 'inventory_2', 'border' => 'border-blue-100', 'text' => 'text-blue-500', 'bg' => 'bg-blue-500', 'en' => 'Received'],
                            'Washing' => ['icon' => 'water_drop', 'border' => 'border-cyan-100', 'text' => 'text-cyan-500', 'bg' => 'bg-cyan-500', 'en' => 'Washing'],
                            'Drying & Ironing' => ['icon' => 'iron', 'border' => 'border-orange-100', 'text' => 'text-orange-500', 'bg' => 'bg-orange-500', 'en' => 'Ironing'],
                            'Packing' => ['icon' => 'package', 'border' => 'border-amber-100', 'text' => 'text-amber-500', 'bg' => 'bg-amber-500', 'en' => 'Packing'],
                            'Completed' => ['icon' => 'check_circle', 'border' => 'border-emerald-100', 'text' => 'text-emerald-500', 'bg' => 'bg-emerald-500', 'en' => 'Completed']
                        ];
                    @endphp

                    <div class="relative flex items-start justify-between w-full mx-auto px-4 sm:px-12">
                        <!-- Animated Connecting Line -->
                        <div
                            class="absolute left-16 right-16 top-[2rem] -translate-y-1/2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-gradient-to-r from-blue-400 via-blue-500 to-emerald-400 w-full opacity-70 animate-flow-line">
                            </div>
                        </div>

                        @php
                            $statusMap = [
                                'Arrived at Laundry' => 'arrived_at_laundry',
                                'Washing' => 'washing',
                                'Drying & Ironing' => 'drying_ironing',
                                'Packing' => 'packing',
                                'Completed' => 'completed'
                            ];
                        @endphp

                        @foreach($statusProses as $status => $count)
                            <a href="{{ route('admin.orders.index', ['status' => $statusMap[$status]]) }}"
                                class="relative flex flex-col items-center group z-10 w-32 hover:scale-105 transition-transform">
                                <!-- Icon Circle -->
                                <div
                                    class="w-16 h-16 rounded-full bg-white border-4 {{ $styles[$status]['border'] }} flex items-center justify-center shadow-lg transition-all duration-300 group-hover:shadow-xl group-hover:-translate-y-1 mb-4 relative z-10 {{ $styles[$status]['text'] }}">
                                    <span class="material-symbols-outlined text-3xl">{{ $styles[$status]['icon'] }}</span>

                                    <!-- Counter Badge -->
                                    <div
                                        class="absolute -top-2 -right-2 {{ $styles[$status]['bg'] }} text-white text-[11px] font-black px-2 py-0.5 rounded-full min-w-[24px] text-center border-2 border-white shadow-md">
                                        {{ $count }}
                                    </div>
                                </div>
                                <!-- Label -->
                                <span
                                    class="text-[10px] font-black text-gray-700 uppercase tracking-widest text-center leading-tight group-hover:text-blue-600 transition-colors">{{ $styles[$status]['en'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Statistics Chart -->
                    <div class="lg:col-span-2 bg-white shadow-sm rounded-lg p-6 flex flex-col">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center gap-3">
                                <div>
                                    <h3 class="text-lg font-black text-gray-900">Order Statistics
                                        <span class="text-blue-600">({{ $period === 'daily' ? 'This Week by Day' : ($period === 'weekly' ? 'This Month by Week' : ($period === 'monthly' ? 'This Year by Month' : 'All Years')) }})</span>
                                    </h3>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                                        @if($period === 'daily') Mon – Sun of the current week
                                        @elseif($period === 'weekly') Week ranges within {{ now()->format('F Y') }}
                                        @elseif($period === 'monthly') Jan – Dec of {{ now()->year }}
                                        @else All recorded years
                                        @endif
                                    </p>
                                </div>

                                <!-- Period Filter Dropdown -->
                                <div class="relative inline-block text-left" x-data="{ open: false }">
                                    <button @click="open = !open"
                                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-all border border-gray-100 group">
                                        <span
                                            class="material-symbols-outlined text-[18px] transition-transform group-hover:rotate-12">tune</span>
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95" style="display: none;"
                                        class="absolute left-0 mt-2 w-48 rounded-2xl bg-white shadow-2xl border border-gray-100 z-50 overflow-hidden py-1.5">
                                        <div class="px-3 py-2 border-b border-gray-50 mb-1">
                                            <span
                                                class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Select
                                                Period</span>
                                        </div>
                                        @foreach([
                                            'daily'   => ['label' => 'Day',   'icon' => 'calendar_today'],
                                            'weekly'  => ['label' => 'Week',  'icon' => 'date_range'],
                                            'monthly' => ['label' => 'Month', 'icon' => 'calendar_month'],
                                            'yearly'  => ['label' => 'Year',  'icon' => 'event_note'],
                                        ] as $p => $info)
                                            <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => $p])) }}"
                                                class="flex items-center justify-between px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $period == $p ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                                                <span class="flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-[14px]">{{ $info['icon'] }}</span>
                                                    {{ $info['label'] }}
                                                </span>
                                                @if($period == $p)
                                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                <span
                                    class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Real-time
                                    Data</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                            <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => 'daily'])) }}"
                                class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center hover:shadow-sm transition-all {{ $period == 'daily' ? 'border-blue-200 bg-blue-50/30 ring-1 ring-blue-200' : '' }}">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">This Week</div>
                                <div class="text-xl font-black text-gray-800">{{ $stats['daily'] }}</div>
                                <div class="text-[9px] font-bold text-gray-400 mt-1">Mon – Sun</div>
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => 'weekly'])) }}"
                                class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center hover:shadow-sm transition-all {{ $period == 'weekly' ? 'border-blue-200 bg-blue-50/30 ring-1 ring-blue-200' : '' }}">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">This Month</div>
                                <div class="text-xl font-black text-gray-800">{{ $stats['weekly'] }}</div>
                                <div class="text-[9px] font-bold text-gray-400 mt-1">By week range</div>
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => 'monthly'])) }}"
                                class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center hover:shadow-sm transition-all {{ $period == 'monthly' ? 'border-blue-200 bg-blue-50/30 ring-1 ring-blue-200' : '' }}">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">This Year</div>
                                <div class="text-xl font-black text-gray-800">{{ $stats['monthly'] }}</div>
                                <div class="text-[9px] font-bold text-gray-400 mt-1">Jan – Dec {{ now()->year }}</div>
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => 'yearly'])) }}"
                                class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center hover:shadow-sm transition-all {{ $period == 'yearly' ? 'border-blue-200 bg-blue-50/30 ring-1 ring-blue-200' : '' }}">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">All Time</div>
                                <div class="text-xl font-black text-gray-800">{{ $totalOrders }}</div>
                                <div class="text-[9px] font-bold text-gray-400 mt-1">All recorded years</div>
                            </a>
                        </div>
                        <div class="flex-1 mt-auto h-[350px]">
                            <canvas id="ordersChart"></canvas>
                        </div>
                    </div>

                    <!-- Popular Services (Pie Chart) -->
                    <div class="bg-white shadow-sm rounded-lg p-6 flex flex-col">
                        <div class="flex items-center gap-2 mb-6">
                            <span class="material-symbols-outlined text-blue-600">pie_chart</span>
                            <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Most Popular
                                Services
                            </h3>
                            
                            <!-- Service Period Filter Dropdown -->
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-50 text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-all border border-gray-100 group">
                                    <span class="material-symbols-outlined text-[16px] transition-transform group-hover:rotate-12">tune</span>
                                </button>

                                <div x-show="open" @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    style="display: none;"
                                    class="absolute left-0 mt-2 w-48 rounded-2xl bg-white shadow-2xl border border-gray-100 z-50 overflow-hidden py-1.5">
                                    <div class="px-3 py-2 border-b border-gray-50 mb-1">
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Filter By Period</span>
                                    </div>
                                    @foreach(['daily' => 'Day', 'weekly' => 'Week', 'monthly' => 'Month', 'yearly' => 'Year'] as $p => $label)
                                        <a href="{{ route('dashboard', array_merge(request()->query(), ['service_period' => $p])) }}"
                                            class="flex items-center justify-between px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $servicePeriod == $p ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                                            {{ $label }}
                                            @if($servicePeriod == $p)
                                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="flex-1 flex flex-col items-center justify-center relative">
                            <div class="w-full h-[280px]">
                                <canvas id="servicePieChart"></canvas>
                            </div>
                            <div class="mt-6 w-full space-y-2">
                                @foreach($serviceDistribution->take(4) as $index => $item)
                                    <a href="{{ route('admin.orders.index', ['service_id' => $item['service_id'], 'period' => $servicePeriod]) }}"
                                        class="flex justify-between items-center text-[10px] font-black text-gray-400 uppercase tracking-widest hover:bg-gray-50 p-2 rounded-xl transition-all group/service">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full"
                                                style="background-color: {{ ['#005bc0', '#10B981', '#F59E0B', '#EF4444'][$index % 4] }}"></span>
                                            <span
                                                class="group-hover/service:text-blue-600 transition-colors">{{ Str::limit($item['label'], 20) }}</span>
                                        </div>
                                        <span class="text-gray-900">{{ $item['count'] }} Orders</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Order Feed -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                            </span>
                            <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Live Order Feed</h3>
                            <span
                                class="ml-2 bg-rose-100 text-rose-600 text-[10px] font-black px-2 py-0.5 rounded-full border border-rose-200">
                                {{ $latestOrders->count() }} RECENT
                            </span>
                        </div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Auto Update</span>
                    </div>
                    <div class="flex-1 overflow-y-auto max-h-[600px] divide-y divide-gray-50 custom-scrollbar">
                        <style>
                            .custom-scrollbar::-webkit-scrollbar {
                                width: 5px;
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
                        @php
                            $statusMap = [
                                'pending_payment' => [
                                    'label' => 'Pending Payment',
                                    'icon' => 'payments',
                                    'bg' => 'bg-amber-50 text-amber-700 border-amber-100'
                                ],
                                'waiting_pickup' => [
                                    'label' => 'Waiting Pickup',
                                    'icon' => 'schedule',
                                    'bg' => 'bg-blue-50 text-blue-700 border-blue-100'
                                ],
                                'picking_up' => [
                                    'label' => 'Picking Up',
                                    'icon' => 'local_shipping',
                                    'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-100'
                                ],
                                'penjemputan' => [
                                    'label' => 'Picking Up',
                                    'icon' => 'local_shipping',
                                    'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-100'
                                ],
                                'picked_up' => [
                                    'label' => 'Picked Up',
                                    'icon' => 'hail',
                                    'bg' => 'bg-sky-50 text-sky-700 border-sky-100'
                                ],
                                'dijemput' => [
                                    'label' => 'Picked Up',
                                    'icon' => 'hail',
                                    'bg' => 'bg-sky-50 text-sky-700 border-sky-100'
                                ],
                                'in_transit_to_laundry' => [
                                    'label' => 'To Laundry',
                                    'icon' => 'airport_shuttle',
                                    'bg' => 'bg-cyan-50 text-cyan-700 border-cyan-100'
                                ],
                                'diantar' => [
                                    'label' => 'To Laundry',
                                    'icon' => 'airport_shuttle',
                                    'bg' => 'bg-cyan-50 text-cyan-700 border-cyan-100'
                                ],
                                'arrived_at_laundry' => [
                                    'label' => 'Arrived',
                                    'icon' => 'inventory_2',
                                    'bg' => 'bg-teal-50 text-teal-700 border-teal-100'
                                ],
                                'sampai' => [
                                    'label' => 'Arrived',
                                    'icon' => 'inventory_2',
                                    'bg' => 'bg-teal-50 text-teal-700 border-teal-100'
                                ],
                                'washing' => [
                                    'label' => 'Washing',
                                    'icon' => 'water_drop',
                                    'bg' => 'bg-blue-50 text-blue-600 border-blue-100'
                                ],
                                'drying_ironing' => [
                                    'label' => 'Ironing',
                                    'icon' => 'iron',
                                    'bg' => 'bg-orange-50 text-orange-700 border-orange-100'
                                ],
                                'packing' => [
                                    'label' => 'Packing',
                                    'icon' => 'inventory',
                                    'bg' => 'bg-amber-50 text-amber-700 border-amber-100'
                                ],
                                'ready_for_delivery' => [
                                    'label' => 'Ready for Delivery',
                                    'icon' => 'check_box',
                                    'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                ],
                                'pengantaran' => [
                                    'label' => 'Ready for Delivery',
                                    'icon' => 'check_box',
                                    'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                ],
                                'delivering' => [
                                    'label' => 'On Delivery',
                                    'icon' => 'delivery_dining',
                                    'bg' => 'bg-purple-50 text-purple-700 border-purple-100'
                                ],
                                'diantarkan' => [
                                    'label' => 'On Delivery',
                                    'icon' => 'delivery_dining',
                                    'bg' => 'bg-purple-50 text-purple-700 border-purple-100'
                                ],
                                'completed' => [
                                    'label' => 'Completed',
                                    'icon' => 'check_circle',
                                    'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                ],
                                'selesai' => [
                                    'label' => 'Completed',
                                    'icon' => 'check_circle',
                                    'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                ],
                                'cancelled' => [
                                    'label' => 'Cancelled',
                                    'icon' => 'cancel',
                                    'bg' => 'bg-rose-50 text-rose-700 border-rose-100'
                                ],
                            ];
                        @endphp
                        @forelse($latestOrders as $order)
                            @php
                                $statusInfo = $statusMap[$order->status] ?? [
                                    'label' => str_replace('_', ' ', $order->status),
                                    'icon' => 'info',
                                    'bg' => 'bg-gray-50 text-gray-700 border-gray-100'
                                ];
                            @endphp
                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                class="block p-4 hover:bg-blue-50/50 transition-colors group">
                                <div class="flex items-start gap-4">
                                    <!-- Customer Profile Photo -->
                                    <img src="{{ $order->customer->photo ? asset('storage/' . $order->customer->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($order->customer->name ?? 'P') . '&background=005bc0&color=fff' }}"
                                        class="w-12 h-12 rounded-2xl object-cover border-2 border-white shadow-md flex-shrink-0">

                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start mb-1">
                                            <div class="min-w-0">
                                                <div
                                                    class="text-sm font-black text-gray-900 truncate group-hover:text-blue-600 transition-colors">
                                                    {{ $order->customer->name ?? 'Customer' }}</div>
                                                <div
                                                    class="text-[9px] font-bold text-gray-400 truncate uppercase tracking-tight">
                                                    {{ $order->customer->phone ?? 'Phone -' }} •
                                                    {{ Str::limit($order->customer->address ?? 'Address -', 40) }}
                                                </div>
                                            </div>
                                            <div class="text-right flex-shrink-0 flex items-start gap-3">
                                                <div>
                                                    <div
                                                        class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                                        {{ $order->order_code }}</div>
                                                    <div class="text-[8px] font-bold text-gray-400 mt-1 uppercase">
                                                        {{ $order->created_at->format('d M Y, H:i') }}</div>
                                                    <div class="text-[8px] font-bold text-gray-300 mt-0.5 uppercase">
                                                        {{ $order->created_at->diffForHumans() }}</div>
                                                </div>
                                                <!-- QR Verification in Feed -->
                                                <div onclick="event.preventDefault(); event.stopPropagation(); openQr('{{ $order->order_code }}', 'https://quickchart.io/qr?text={{ urlencode(route('orders.scan', $order->id)) }}&size=300&margin=1')"
                                                    class="bg-white p-1.5 rounded-xl border border-gray-100 shadow-sm group-hover:border-blue-200 hover:scale-105 transition-all cursor-zoom-in"
                                                    title="Click to preview QR Code">
                                                    <img src="https://quickchart.io/qr?text={{ urlencode(route('orders.scan', $order->id)) }}&size=120&margin=1"
                                                        width="48" height="48" alt="QR" class="rounded-lg">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Detail Badges -->
                                        <div class="flex flex-wrap items-center gap-1.5 mb-3 mt-2">
                                            <span
                                                class="px-2 py-0.5 rounded-md bg-slate-50 text-slate-600 text-[8px] font-black uppercase tracking-tighter border border-slate-100 shadow-sm"
                                                title="Service">
                                                {{ $order->service->name ?? 'Service' }}
                                            </span>
                                            <span
                                                class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 text-[8px] font-black uppercase tracking-tighter border border-blue-100 shadow-sm"
                                                title="Item Type">
                                                {{ $order->itemType->name ?? 'Type' }}
                                            </span>
                                            <span
                                                class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[8px] font-black uppercase tracking-tighter border border-emerald-100 shadow-sm"
                                                title="Soap">
                                                🫧 {{ $order->soap ?? 'Standard Soap' }}
                                            </span>
                                            <span
                                                class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 text-[8px] font-black uppercase tracking-tighter border border-rose-100 shadow-sm"
                                                title="Fragrance">
                                                🌸 {{ $order->fragrance ?? 'No Fragrance' }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex justify-between items-center bg-gray-50/50 p-2 rounded-xl border border-gray-100/50">
                                            <span
                                                class="inline-flex items-center gap-1 text-[9px] font-black px-2.5 py-1 rounded-full border shadow-sm {{ $statusInfo['bg'] }} uppercase tracking-tighter">
                                                <span class="material-symbols-outlined text-[12px] font-bold">{{ $statusInfo['icon'] }}</span>
                                                {{ $statusInfo['label'] }}
                                            </span>
                                            <div class="text-xs font-black text-gray-900 font-sans">Rp
                                                {{ number_format($order->total_price, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="py-12 text-center">
                                <p class="text-gray-400 font-bold text-sm">No incoming orders yet.</p>
                            </div>
                        @endforelse
                    </div>
                    <a href="{{ route('admin.orders.index') }}"
                        class="p-4 bg-gray-50 text-center text-xs font-black text-gray-500 hover:text-blue-600 hover:bg-gray-100 transition-all uppercase tracking-widest border-t border-gray-100">
                        View All Orders
                    </a>
                </div>
            </div>

            <!-- Mini Tracking Map -->
            <div class="lg:col-span-2 bg-white shadow-sm rounded-lg overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-500">map</span>
                        <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Courier Tracker (Mini)</h3>
                    </div>
                    <a href="{{ route('admin.tracking.index') }}"
                        class="text-[10px] font-black text-blue-600 uppercase tracking-widest hover:underline">Open
                        Full Map</a>
                </div>
                <div id="miniMap" class="h-[350px] w-full z-0"></div>
            </div>
        </div>
    </div>

    <!-- Top Couriers -->
    <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="flex items-center gap-2 mb-6">
            <span class="material-symbols-outlined text-amber-500 text-2xl">workspace_premium</span>
            <h3 class="text-lg font-black text-gray-900">Top Performing Couriers</h3>
        </div>
        <div class="flex flex-wrap justify-center gap-6">
            @forelse($topCouriers as $courier)
                <a href="{{ route('admin.orders.index', ['courier_id' => $courier->id, 'has_review' => '1']) }}"
                    class="w-full sm:w-[calc(50%-12px)] md:w-[calc(25%-18px)] min-w-[200px] border border-gray-100 bg-gray-50 rounded-2xl p-5 text-center hover:shadow-2xl hover:border-blue-200 hover:-translate-y-2 transition-all duration-300 group courier-card-animate {{ $loop->first ? 'winner-shine ring-2 ring-amber-100' : '' }}"
                    style="animation-delay: {{ $loop->index * 150 }}ms">
                    <div class="relative inline-block mb-3">
                        <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-white shadow-md mx-auto">
                            <img src="{{ $courier->photo ?? 'https://ui-avatars.com/api/?name=' . urlencode($courier->name) . '&background=005bc0&color=fff' }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <div
                            class="absolute -bottom-2 -right-2 bg-amber-400 text-white text-xs font-black px-2 py-0.5 rounded-full border-2 border-white shadow-sm"
                            style="animation: rank-pop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) both; animation-delay: {{ ($loop->index * 150) + 400 }}ms">
                            #{{ $loop->iteration }}</div>
                    </div>
                    <div class="font-black text-sm text-gray-900 truncate group-hover:text-blue-600 transition-colors">
                        {{ $courier->name }}</div>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 truncate">
                        {{ $courier->email }}</div>
                    <div
                        class="bg-white inline-flex items-center gap-1 px-3 py-1 rounded-full shadow-sm text-sm font-black text-amber-500 group-hover:bg-amber-50 transition-colors">
                        <span class="material-symbols-outlined text-[14px] group-hover:animate-[pulse-star_0.8s_ease-in-out_infinite]">star</span>
                        {{ number_format($courier->avg_rating, 1) }}
                    </div>
                </a>
            @empty
                <div class="col-span-full py-8 text-center text-gray-400 font-bold text-sm">No courier performance data
                    available.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Latest Reviews -->
    <div id="customer-reviews" class="bg-white shadow-sm rounded-lg p-6 scroll-mt-24 overflow-hidden">
        <div class="flex items-center gap-2 mb-6">
            <span class="material-symbols-outlined text-amber-500">reviews</span>
            <h3 class="text-lg font-black text-gray-900">Latest Customer Reviews</h3>
        </div>
        
        <div class="relative w-full overflow-hidden group">
            <div class="@if($latestReviews->count() > 0) animate-marquee @endif flex gap-6">
                @forelse($latestReviews->merge($latestReviews) as $review)
                    <a href="{{ route('admin.orders.show', $review->order->id) }}"
                        class="flex-none w-[350px] bg-gray-50 rounded-2xl p-5 border border-gray-100 flex flex-col justify-between hover:shadow-xl hover:scale-[1.02] transition-all group/card">
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <img src="{{ $review->order->customer->photo ? asset('storage/' . $review->order->customer->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($review->order->customer->name ?? 'P') . '&background=005bc0&color=fff' }}"
                                    class="w-10 h-10 rounded-xl object-cover shadow-sm">
                                <div>
                                    <div class="text-sm font-black text-gray-900 leading-none mb-1 group-hover/card:text-blue-600 transition-colors">
                                        {{ $review->order->customer->name ?? 'Customer' }}</div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        {{ $review->order->order_code }}</div>
                                </div>
                            </div>
                            <div class="flex gap-0.5 mb-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="material-symbols-outlined text-[16px] {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}"
                                        style="{{ $i <= $review->rating ? 'font-variation-settings: \'FILL\' 1' : '' }}">star</span>
                                @endfor
                            </div>
                            <p class="text-sm text-gray-600 italic leading-relaxed line-clamp-2">
                                "{{ $review->comment ?? 'No comment.' }}"</p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100 text-[10px] font-bold text-gray-400 uppercase tracking-widest flex justify-between">
                            <span>{{ $review->created_at->diffForHumans() }}</span>
                            <span class="text-blue-500 opacity-0 group-hover/card:opacity-100 transition-opacity">Details →</span>
                        </div>
                    </a>
                @empty
                    <div class="w-full py-12 text-center text-gray-400 font-bold text-sm">No reviews yet.</div>
                @endforelse
            </div>
            
            <!-- Shadow Overlays for smooth entry/exit -->
            <div class="absolute inset-y-0 left-0 w-20 bg-gradient-to-r from-white to-transparent pointer-events-none z-10"></div>
            <div class="absolute inset-y-0 right-0 w-20 bg-gradient-to-l from-white to-transparent pointer-events-none z-10"></div>
        </div>
    </div>

    <!-- Employees and Couriers Tables -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Employees -->
        <div class="bg-white shadow-sm rounded-lg p-0 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-base font-black text-gray-900">Internal Employees</h3>
                <span
                    class="bg-blue-100 text-blue-700 text-xs font-black px-2 py-1 rounded-full border border-blue-200">
                    {{ $employees->count() }} STAFF
                </span>
            </div>
            <div class="overflow-x-auto max-h-[268px] custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-100">
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @forelse($employees as $emp)
                            <tr class="group cursor-pointer hover:bg-blue-50/30 transition-colors"
                                onclick="window.location.href='{{ route('admin.users.show', $emp->id) }}'">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $emp->photo ?? 'https://ui-avatars.com/api/?name=' . urlencode($emp->name) }}"
                                            class="w-10 h-10 rounded-xl object-cover shadow-sm group-hover:scale-110 transition-transform">
                                        <div>
                                            <div
                                                class="text-sm font-black text-gray-900 group-hover:text-blue-600 transition-colors">
                                                {{ $emp->name }}</div>
                                            <div class="text-xs font-bold text-gray-400">{{ $emp->email }}</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-8 text-center text-gray-400 text-sm font-bold">No staff members found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Couriers -->
        <div class="bg-white shadow-sm rounded-lg p-0 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-base font-black text-gray-900">Couriers</h3>
                <span
                    class="bg-emerald-100 text-emerald-700 text-xs font-black px-2 py-1 rounded-full border border-emerald-200">
                    {{ $couriers->count() }} COURIERS
                </span>
            </div>
            <div class="overflow-x-auto max-h-[268px] custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-100">
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @forelse($couriers as $courier)
                            <tr class="group cursor-pointer hover:bg-emerald-50/30 transition-colors"
                                onclick="window.location.href='{{ route('admin.users.show', $courier->id) }}'">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $courier->photo ?? 'https://ui-avatars.com/api/?name=' . urlencode($courier->name) }}"
                                            class="w-10 h-10 rounded-xl object-cover shadow-sm group-hover:scale-110 transition-transform">
                                        <div>
                                            <div
                                                class="text-sm font-black text-gray-900 group-hover:text-emerald-600 transition-colors">
                                                {{ $courier->name }}</div>
                                            <div class="text-xs font-bold text-gray-400">{{ $courier->email }}</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-8 text-center text-gray-400 text-sm font-bold">No couriers found.
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

    @push('scripts')
        <!-- Leaflet CSS & JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // ─── ORDERS CHART ───
                const ctx = document.getElementById('ordersChart').getContext('2d');
                const chartData = @json($chartData);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Incoming Orders',
                            data: chartData.data,
                            backgroundColor: '#005bc0',
                            borderRadius: 6,
                            barPercentage: 0.6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#111827',
                                titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13 },
                                bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 14, weight: 'bold' },
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false
                            }
                        },
                        onClick: (event, elements) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const filterType = chartData.filter_type;
                                const raw = chartData.full_dates[index];
                                const baseUrl = "{{ route('admin.orders.index') }}";

                                let url = baseUrl;
                                if (filterType === 'daily') {
                                    // raw = 'YYYY-MM-DD' (a specific day in this week)
                                    url = `${baseUrl}?date=${raw}`;
                                } else if (filterType === 'weekly') {
                                    // raw = 'YYYY-MM-DD|YYYY-MM-DD' (week range)
                                    const [start, end] = raw.split('|');
                                    url = `${baseUrl}?start_date=${start}&end_date=${end}`;
                                } else if (filterType === 'monthly') {
                                    // raw = 'YYYY-MM'
                                    const [year, month] = raw.split('-');
                                    url = `${baseUrl}?year=${year}&month=${month}`;
                                } else if (filterType === 'yearly') {
                                    // raw = '2024' (year only)
                                    url = `${baseUrl}?year=${raw}`;
                                }
                                window.location.href = url;
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 } },
                                grid: { color: '#F3F4F6', drawBorder: false }
                            },
                            x: {
                                ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: 'bold' } },
                                grid: { display: false, drawBorder: false }
                            }
                        }
                    }
                });

                // ─── SERVICE PIE CHART ───
                const serviceData = @json($serviceDistribution);
                new Chart(document.getElementById('servicePieChart'), {
                    type: 'doughnut',
                    data: {
                        labels: serviceData.map(d => d.label),
                        datasets: [{
                            data: serviceData.map(d => d.count),
                            backgroundColor: ['#005bc0', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'],
                            borderWidth: 0,
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#111827',
                                titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
                                bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13, weight: 'bold' },
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: true
                            }
                        },
                        onClick: (event, elements, chart) => {
                            const activePoints = elements.length > 0 ? elements : (chart ? chart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, true) : []);
                            if (activePoints.length > 0) {
                                const index = activePoints[0].index;
                                const serviceId = serviceData[index].service_id;
                                const period = "{{ $servicePeriod }}";
                                const baseUrl = "{{ route('admin.orders.index') }}";
                                window.location.href = `${baseUrl}?service_id=${serviceId}&period=${period}`;
                            }
                        }
                    }
                });

                // ─── MINI MAP (COURIER MONITOR) ───
                const miniMap = L.map('miniMap', {
                    zoomControl: false,
                    attributionControl: false
                }).setView([-6.1664983, 106.5602886], 13);

                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(miniMap);

                // Laundry Base HQ Marker
                const laundryIcon = L.divIcon({
                    html: `<div class="bg-blue-900 text-white h-10 w-10 rounded-full flex items-center justify-center shadow-2xl border-4 border-white animate-pulse"><span class="material-symbols-outlined text-xl">local_laundry_service</span></div>`,
                    className: '', iconSize: [40, 40], iconAnchor: [20, 20], popupAnchor: [0, -20]
                });
                L.marker([-6.1664983, 106.5602886], { icon: laundryIcon })
                    .bindPopup('<div class="p-2 font-black text-center text-blue-900 font-sans">LAUNDRYAN HQ<br><span class="text-[9px] text-gray-400 uppercase tracking-widest font-bold">Base Operations</span></div>')
                    .addTo(miniMap);

                // Fetch real courier data with high fidelity markers
                fetch("{{ route('admin.tracking.data') }}")
                    .then(res => res.json())
                    .then(data => {
                        const markers = [];
                        data.tracking.forEach(item => {
                            if (item.location) {
                                const latLng = [item.location.lat, item.location.lng];

                                // Determine status color
                                let statusColor = 'blue'; // Idle
                                if (item.orders?.length > 0) {
                                    statusColor = item.orders.some(o => o.type === 'pickup') ? 'amber' : 'emerald';
                                }

                                // Custom DivIcon consistent with main tracking
                                const iconHtml = `
                                    <div class="relative">
                                        <div class="w-10 h-10 rounded-full border-4 border-${statusColor === 'blue' ? 'blue' : statusColor}-500 bg-white shadow-lg overflow-hidden flex items-center justify-center transition-all hover:scale-110">
                                            <img src="${item.courier.photo}" class="w-full h-full object-cover">
                                        </div>
                                        ${item.orders?.length > 0 ? `<div class="absolute -top-1 -right-1 bg-white text-blue-600 rounded-full h-4 w-4 flex items-center justify-center shadow-md border border-blue-100"><span class="material-symbols-outlined text-[10px] font-black">inventory_2</span></div>` : ''}
                                    </div>`;

                                const icon = L.divIcon({
                                    html: iconHtml,
                                    className: '',
                                    iconSize: [40, 40],
                                    iconAnchor: [20, 20],
                                    popupAnchor: [0, -20]
                                });

                                const marker = L.marker(latLng, { icon })
                                    .on('click', () => {
                                        const trackingUrl = "{{ route('admin.tracking.index') }}";
                                        window.location.href = `${trackingUrl}?focus_courier=${item.courier.id}`;
                                    })
                                    .addTo(miniMap);

                                // Richer Popup
                                const popupContent = `
                                    <div class="p-2 font-['Plus_Jakarta_Sans'] min-w-[150px]">
                                        <div class="font-black text-gray-900 text-sm mb-1">${item.courier.name}</div>
                                        <div class="flex items-center gap-1.5 mb-2">
                                            <span class="w-2 h-2 rounded-full bg-${statusColor === 'blue' ? 'blue' : statusColor}-500"></span>
                                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">${item.orders?.length > 0 ? 'ON MISSION' : 'IDLE'}</span>
                                        </div>
                                        <div class="text-[8px] font-bold text-gray-300 uppercase">${item.location.updated_at}</div>
                                    </div>
                                `;
                                marker.bindPopup(popupContent);
                                markers.push(marker);
                            }
                        });

                        if (markers.length > 0) {
                            const group = new L.featureGroup(markers);
                            const bounds = group.getBounds();
                            bounds.extend([-6.1664983, 106.5602886]);
                            miniMap.fitBounds(bounds.pad(0.3));
                        } else {
                            miniMap.setView([-6.1664983, 106.5602886], 14);
                        }
                    });
            });

            window.openQr = function(code, src) {
                document.getElementById('qrTitle').textContent = 'QR Code: ' + code;
                document.getElementById('qrImg').src = src;
                const m = document.getElementById('qrModal');
                m.classList.remove('hidden');
                m.classList.add('flex');
            };
            window.closeQr = function() {
                const m = document.getElementById('qrModal');
                m.classList.add('hidden');
                m.classList.remove('flex');
            };
            document.getElementById('qrModal').addEventListener('click', function(e) {
                if (e.target === this) closeQr();
            });
        </script>
    @endpush

    {{-- Zoom Modal QR Code --}}
    <div id="qrModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-6 w-80 shadow-2xl relative">
            <button onclick="closeQr()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 id="qrTitle" class="text-sm font-black text-gray-900 text-center mb-4 uppercase tracking-wider"></h3>
            <div class="bg-gray-50 rounded-2xl p-4 flex items-center justify-center border border-gray-100 shadow-inner">
                <img id="qrImg" src="" class="w-64 h-64 object-contain" alt="QR Code">
            </div>
            <p class="text-center text-[10px] text-gray-400 mt-4 leading-relaxed font-bold uppercase tracking-wide">
                Scan QR to instantly access operational details.
            </p>
        </div>
    </div>
</x-app-layout>