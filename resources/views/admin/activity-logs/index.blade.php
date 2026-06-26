<x-app-layout>
    <style>
        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-row {
            animation: slideUpFade 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
    </style>

    @php
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $categoryConfig = [
            'Auth & Security' => [
                'bg' => 'bg-red-50 text-red-700 border-red-200',
                'icon' => 'security'
            ],
            'Order' => [
                'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                'icon' => 'shopping_bag'
            ],
            'Payment' => [
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'icon' => 'payments'
            ],
            'User Management' => [
                'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
                'icon' => 'manage_accounts'
            ],
            'Finance' => [
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'icon' => 'account_balance_wallet'
            ],
            'Payroll & Attendance' => [
                'bg' => 'bg-teal-50 text-teal-700 border-teal-200',
                'icon' => 'timer'
            ],
            'Settings & Configuration' => [
                'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'icon' => 'settings'
            ]
        ];

        $roleConfig = [
            'admin' => ['label' => 'Admin', 'bg' => 'bg-red-100 text-red-800'],
            'karyawan' => ['label' => 'Employee', 'bg' => 'bg-blue-100 text-blue-800'],
            'kurir' => ['label' => 'Courier', 'bg' => 'bg-orange-100 text-orange-800'],
            'pelanggan' => ['label' => 'Customer', 'bg' => 'bg-green-100 text-green-800'],
            'sistem' => ['label' => 'System', 'bg' => 'bg-gray-100 text-gray-800'],
            'system' => ['label' => 'System', 'bg' => 'bg-gray-100 text-gray-800'],
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
             x-data="{ exportMonth: 'all', exportYear: 'all', exportPdfLoading: false, exportCsvLoading: false }">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">System Activity Logs</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Audit trail, monitor activities, security log, and system events.</p>
            </div>

            <!-- Export Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">calendar_month</span>
                    <select x-model="exportMonth" class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="all">All Months</option>
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[14px] pointer-events-none">expand_more</span>
                </div>

                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">event</span>
                    <select x-model="exportYear" class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="all">All Years</option>
                        @foreach($years as $yr)
                            <option value="{{ $yr }}">{{ $yr }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[14px] pointer-events-none">expand_more</span>
                </div>

                <a :href="'{{ route('admin.activity-logs.export.pdf') }}?month='+exportMonth+'&year='+exportYear"
                    @click="exportPdfLoading = true; setTimeout(() => exportPdfLoading = false, 4000)"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-rose-100 text-rose-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-rose-50 hover:shadow-md transition-all group shadow-sm whitespace-nowrap"
                    :class="exportPdfLoading ? 'pointer-events-none opacity-70' : ''">
                    <template x-if="exportPdfLoading">
                        <svg class="animate-spin h-4.5 w-4.5 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!exportPdfLoading">
                        <span class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">picture_as_pdf</span>
                    </template>
                    <span x-text="exportPdfLoading ? 'Exporting...' : 'Export PDF'"></span>
                </a>

                <a :href="'{{ route('admin.activity-logs.export.csv') }}?month='+exportMonth+'&year='+exportYear"
                    @click="exportCsvLoading = true; setTimeout(() => exportCsvLoading = false, 4000)"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-emerald-100 text-emerald-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-emerald-50 hover:shadow-md transition-all group shadow-sm whitespace-nowrap"
                    :class="exportCsvLoading ? 'pointer-events-none opacity-70' : ''">
                    <template x-if="exportCsvLoading">
                        <svg class="animate-spin h-4.5 w-4.5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!exportCsvLoading">
                        <span class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">table_view</span>
                    </template>
                    <span x-text="exportCsvLoading ? 'Exporting...' : 'Export CSV'"></span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-[92rem] mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Card 1: Today Activities -->
                <a href="{{ route('admin.activity-logs.index', ['period' => 'today']) }}" 
                   class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">timeline</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Today's Activities</p>
                        <div class="flex items-baseline gap-2 mt-0.5">
                            <h3 class="text-2xl font-black text-gray-800">{{ number_format($stats['today_count']) }}</h3>
                            <span class="text-[10px] font-extrabold {{ $stats['diff_percent'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $stats['diff_percent'] >= 0 ? '+' : '' }}{{ number_format($stats['diff_percent'], 1) }}%
                            </span>
                        </div>
                    </div>
                </a>

                <!-- Card 2: Security Logs / Failed Logins -->
                <a href="{{ route('admin.activity-logs.index', ['period' => 'today', 'activity_type' => 'failed_login']) }}" 
                   class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-rose-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-rose-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">gpp_maybe</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Failed Logins Today</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['failed_logins_today']) }} <span class="text-xs text-gray-400 font-bold">({{ number_format($stats['failed_logins_total']) }} total)</span></h3>
                    </div>
                </a>

                <!-- Card 3: Active Users -->
                <a href="{{ route('admin.activity-logs.index', ['period' => 'today', 'user_active' => 1]) }}" 
                   class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-purple-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-purple-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">group</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Active Users Today</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['active_users_today']) }}</h3>
                    </div>
                </a>

                <!-- Card 4: Most Active Category -->
                <a href="{{ route('admin.activity-logs.index', ['category' => $stats['most_active_category']]) }}" 
                   class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-amber-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-amber-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">electric_bolt</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Most Active Category</p>
                        <h3 class="text-sm font-black text-gray-800 truncate mt-1 max-w-[180px]">{{ $stats['most_active_category'] }}</h3>
                    </div>
                </a>
            </div>

            <!-- Filters & Search -->
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                <form action="{{ route('admin.activity-logs.index') }}" method="GET" 
                      x-data="{ period: '{{ request('period', 'all') }}' }"
                      class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Search Input -->
                    <div class="col-span-12 md:col-span-3">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Search Logs</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <span class="material-symbols-outlined text-sm">search</span>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3" 
                                   placeholder="Search user, email, reference ID, description...">
                        </div>
                    </div>

                    <!-- Role Filter -->
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Role</label>
                        <select name="role" class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 cursor-pointer">
                            <option value="all" {{ request('role') === 'all' || !request()->has('role') ? 'selected' : '' }}>All Roles</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="karyawan" {{ request('role') === 'karyawan' ? 'selected' : '' }}>Employee</option>
                            <option value="kurir" {{ request('role') === 'kurir' ? 'selected' : '' }}>Courier</option>
                            <option value="pelanggan" {{ request('role') === 'pelanggan' ? 'selected' : '' }}>Customer</option>
                            <option value="sistem" {{ request('role') === 'sistem' ? 'selected' : '' }}>System</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Category</label>
                        <select name="category" class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 cursor-pointer">
                            <option value="all" {{ request('category') === 'all' || !request()->has('category') ? 'selected' : '' }}>All Categories</option>
                            @foreach($categoryConfig as $catName => $config)
                                <option value="{{ $catName }}" {{ request('category') === $catName ? 'selected' : '' }}>{{ $catName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Period Filter -->
                    <div class="col-span-12 md:col-span-3">
                        <div class="grid grid-cols-1" :class="period !== 'all' ? 'sm:grid-cols-2 gap-2' : ''">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Period</label>
                                <select name="period" x-model="period" class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 cursor-pointer">
                                    <option value="all">All Time</option>
                                    <option value="today">Day</option>
                                    <option value="week">Week</option>
                                    <option value="month">Month</option>
                                    <option value="year">Year</option>
                                </select>
                            </div>
                            <div x-show="period !== 'all'" x-transition>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
                                    <span x-show="period === 'today'">Select Day</span>
                                    <span x-show="period === 'week'">Select Week</span>
                                    <span x-show="period === 'month'">Select Month</span>
                                    <span x-show="period === 'year'">Select Year</span>
                                </label>
                                
                                <!-- Day sub-selector -->
                                <div x-show="period === 'today'">
                                    <input type="date" name="filter_date" value="{{ request('filter_date', now()->format('Y-m-d')) }}"
                                           class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 px-3">
                                </div>
                                
                                <!-- Week sub-selector -->
                                <div x-show="period === 'week'">
                                    <input type="week" name="filter_week" value="{{ request('filter_week', now()->format('Y-\WW')) }}"
                                           class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 px-3">
                                </div>
                                
                                <!-- Month sub-selector -->
                                <div x-show="period === 'month'">
                                    <input type="month" name="filter_month" value="{{ request('filter_month', now()->format('Y-m')) }}"
                                           class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 px-3">
                                </div>
                                
                                <!-- Year sub-selector -->
                                <div x-show="period === 'year'">
                                    <select name="filter_year" class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 cursor-pointer">
                                        @foreach($years as $yr)
                                            <option value="{{ $yr }}" {{ request('filter_year', now()->format('Y')) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-span-12 md:col-span-2 flex items-end justify-end gap-2">
                        <div class="w-full flex gap-2">
                            <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black shadow-lg shadow-blue-200 flex items-center justify-center transition-all h-12" title="Filter">
                                <span class="material-symbols-outlined text-[20px]">filter_alt</span>
                            </button>
                            <a href="{{ route('admin.activity-logs.index') }}" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl flex items-center justify-center transition-all h-12" title="Reset Filters">
                                <span class="material-symbols-outlined text-[20px]">restart_alt</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden relative" 
                 x-data="{ gridLoading: false }" 
                 x-init="gridLoading = false" 
                 @submit.window="gridLoading = true" 
                 @click.document="
                    const link = $event.target.closest('a');
                    if (link) {
                        const href = link.getAttribute('href') || link.getAttribute(':href') || '';
                        if (href.includes('export')) return;
                        if (href.includes('activity-logs') || link.closest('.pagination') || link.closest('.page-link')) {
                            gridLoading = true;
                        }
                    }
                 ">
                
                <!-- Grid Loading Overlay -->
                <div x-show="gridLoading" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-0 bg-white/70 backdrop-blur-xs z-30 flex flex-col items-center justify-center min-h-[300px]" x-cloak>
                    <div class="flex flex-col items-center gap-3">
                        <div class="relative w-12 h-12">
                            <div class="absolute inset-0 rounded-full border-4 border-blue-100"></div>
                            <div class="absolute inset-0 rounded-full border-4 border-blue-600 border-t-transparent animate-spin"></div>
                        </div>
                        <p class="text-xs font-black text-blue-600 uppercase tracking-widest animate-pulse">Loading Activity Logs...</p>
                    </div>
                </div>

                <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <span>{{ $logs->total() }} logs found</span>
                    <span>Page {{ $logs->currentPage() }} / {{ $logs->lastPage() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-4 text-left w-[250px]">User</th>
                                <th class="px-6 py-4 text-left">Activity</th>
                                <th class="px-6 py-4 text-left w-[180px]">System / Device</th>
                                <th class="px-6 py-4 text-center w-[120px]">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($logs as $log)
                                @php
                                    $catConf = $categoryConfig[$log->category] ?? ['bg' => 'bg-gray-50 text-gray-700 border-gray-200', 'icon' => 'info'];
                                    $rConf = $roleConfig[strtolower($log->role)] ?? ['label' => $log->role, 'bg' => 'bg-gray-100 text-gray-800'];
                                    $userPhoto = $log->user ? $log->user->photo : null;
                                @endphp
                                <tr class="hover:bg-blue-50/10 transition-all duration-150 group animate-fade-in-row" style="animation-delay: {{ $loop->index * 30 }}ms;">
                                    <!-- User Column -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($userPhoto)
                                                <img src="{{ Storage::url($userPhoto) }}" class="w-10 h-10 rounded-2xl object-cover border border-gray-100 shadow-sm flex-shrink-0" alt="">
                                            @elseif(strtolower($log->role) === 'sistem' || strtolower($log->role) === 'system')
                                                <div class="w-10 h-10 rounded-2xl bg-gray-100 text-gray-600 flex items-center justify-center border border-gray-200 flex-shrink-0">
                                                    <span class="material-symbols-outlined text-lg">smart_toy</span>
                                                </div>
                                            @else
                                                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-black text-sm flex items-center justify-center shadow-sm flex-shrink-0">
                                                    {{ strtoupper(substr($log->user_name ?? 'U', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-gray-800 truncate">{{ $log->user_name ?: 'System' }}</p>
                                                <p class="text-[10px] text-gray-400 font-bold truncate mt-0.5">{{ $log->email ?: '-' }}</p>
                                                <span class="inline-block px-2 py-0.5 rounded text-[8px] font-black uppercase mt-1.5 {{ $rConf['bg'] }}">
                                                    {{ $rConf['label'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Activity Column -->
                                    <td class="px-6 py-4">
                                        <div class="space-y-1.5">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg border text-[9px] font-extrabold uppercase {{ $catConf['bg'] }}">
                                                    <span class="material-symbols-outlined text-[11px]">{{ $catConf['icon'] }}</span>
                                                    {{ $log->category }}
                                                </span>
                                                <span class="text-xs font-black text-gray-900">{{ $log->activity_type }}</span>
                                            </div>
                                            <p class="text-xs text-gray-600 leading-relaxed max-w-lg">{{ $log->description }}</p>
                                            @if($log->reference_id)
                                                <div class="flex items-center gap-1 text-[9px] font-bold text-blue-600 bg-blue-50 border border-blue-100 rounded-md px-1.5 py-0.5 w-max">
                                                    <span class="material-symbols-outlined text-[10px]">link</span>
                                                    Ref: {{ $log->reference_id }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- System Column -->
                                    <td class="px-6 py-4">
                                        <div class="text-[10px] text-gray-500 space-y-1 font-semibold">
                                            <div class="flex items-center gap-1" title="IP Address">
                                                <span class="material-symbols-outlined text-[12px] text-gray-400">lan</span>
                                                <span>{{ $log->ip_address ?: '-' }}</span>
                                            </div>
                                            <div class="flex items-center gap-1" title="Browser & Device">
                                                <span class="material-symbols-outlined text-[12px] text-gray-400">devices</span>
                                                <span class="truncate max-w-[150px]">{{ $log->browser ?: '-' }} ({{ $log->device ?: '-' }})</span>
                                            </div>
                                            <div class="flex items-center gap-1 text-gray-400 font-bold text-[9px]" title="Timestamp">
                                                <span class="material-symbols-outlined text-[12px]">schedule</span>
                                                <span>{{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s') : '-' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Action Column -->
                                    <td class="px-6 py-4 text-center">
                                        <button onclick="openDetailModal({{ json_encode($log) }})"
                                                class="inline-flex items-center gap-1 py-1.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all">
                                            <span class="material-symbols-outlined text-[12px]">visibility</span> Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-2 text-gray-400">
                                            <span class="material-symbols-outlined text-5xl text-gray-200">receipt_long</span>
                                            <p class="text-sm font-semibold">No activity logs found</p>
                                            <p class="text-xs">Try changing your search filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($logs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- ── DETAIL MODAL ── -->
    <div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-6 w-[600px] max-w-full shadow-2xl relative mx-4 flex flex-col max-h-[90vh]">
            <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
            
            <div class="flex items-center gap-2.5 mb-4">
                <span class="material-symbols-outlined text-blue-600 text-2xl">manage_search</span>
                <h3 class="text-lg font-black text-gray-900 tracking-tight">Log Detail & Audit Trail</h3>
            </div>
            
            <div class="flex-1 overflow-y-auto pr-1 space-y-5 custom-scrollbar text-xs">
                
                <!-- Section 1: User Profile -->
                <div>
                    <h4 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Actor Profile</h4>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex items-center gap-3">
                        <div id="modalUserPhotoPlaceholder" class="w-12 h-12 rounded-2xl bg-blue-500 text-white font-black text-base flex items-center justify-center shadow-sm flex-shrink-0"></div>
                        <img id="modalUserPhoto" src="" class="w-12 h-12 rounded-2xl object-cover border border-gray-100 shadow-sm flex-shrink-0 hidden" alt="">
                        <div class="min-w-0">
                            <p id="modalUserName" class="text-sm font-black text-gray-800"></p>
                            <p id="modalUserEmail" class="text-xs text-gray-500 font-bold mt-0.5"></p>
                            <span id="modalUserRole" class="inline-block px-2.5 py-0.5 rounded text-[8px] font-black uppercase mt-1.5"></span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Activity Details -->
                <div>
                    <h4 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Activity Info</h4>
                    <div class="border border-gray-100 rounded-2xl p-4 space-y-3.5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Activity Category</span>
                                <span id="modalCategory" class="font-extrabold text-gray-800 mt-0.5 inline-block"></span>
                            </div>
                            <div>
                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Activity Type</span>
                                <span id="modalType" class="font-black text-gray-900 mt-0.5 inline-block"></span>
                            </div>
                        </div>

                        <div>
                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Description</span>
                            <p id="modalDesc" class="text-gray-700 font-semibold mt-1 leading-relaxed"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Related Module</span>
                                <span id="modalModule" class="font-bold text-gray-800 mt-0.5 inline-block"></span>
                            </div>
                            <div>
                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Reference Data</span>
                                <span id="modalRef" class="font-bold text-blue-600 mt-0.5 inline-block"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Data Payload Changes (Before & After) -->
                <div id="modalDataChangesSection" class="hidden">
                    <h4 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Data Changes Payload (JSON)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block mb-1">State Before (Old)</span>
                            <pre id="modalDataBefore" class="bg-gray-50 p-3.5 rounded-2xl border border-gray-100 text-[10px] text-gray-600 overflow-x-auto font-mono max-h-48 scrollbar-thin"></pre>
                        </div>
                        <div>
                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block mb-1">State After (New)</span>
                            <pre id="modalDataAfter" class="bg-gray-50 p-3.5 rounded-2xl border border-gray-100 text-[10px] text-gray-600 overflow-x-auto font-mono max-h-48 scrollbar-thin"></pre>
                        </div>
                    </div>
                </div>

                <!-- Section 4: System / Technical Details -->
                <div>
                    <h4 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">System & Client Metadata</h4>
                    <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100/50 space-y-3 font-semibold text-gray-600">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">IP Address</span>
                                <span id="modalIP" class="text-gray-800 font-bold"></span>
                            </div>
                            <div>
                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Client Browser</span>
                                <span id="modalBrowser" class="text-gray-800 font-bold"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Client Device / OS</span>
                                <span id="modalDevice" class="text-gray-800 font-bold"></span>
                            </div>
                            <div>
                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Timestamp (Jakarta Time)</span>
                                <span id="modalTime" class="text-gray-800 font-bold"></span>
                            </div>
                        </div>

                        <div>
                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block mb-0.5">Full User Agent</span>
                            <p id="modalUA" class="bg-white p-2.5 rounded-xl border border-gray-100 text-[10px] text-gray-500 font-mono break-all"></p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="flex gap-3 mt-5 pt-3 border-t border-gray-100">
                <button onclick="closeDetailModal()"
                        class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-black uppercase tracking-widest text-center transition-all">
                    Close Detail
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function openDetailModal(log) {
            const roleColors = {
                'admin': 'bg-red-100 text-red-800',
                'karyawan': 'bg-blue-100 text-blue-800',
                'kurir': 'bg-orange-100 text-orange-800',
                'pelanggan': 'bg-green-100 text-green-800',
                'sistem': 'bg-gray-100 text-gray-800',
                'system': 'bg-gray-100 text-gray-800'
            };

            const roleLabels = {
                'admin': 'Admin',
                'karyawan': 'Employee',
                'kurir': 'Courier',
                'pelanggan': 'Customer',
                'sistem': 'System',
                'system': 'System'
            };

            // User Photo & Initial
            const photoEl = document.getElementById('modalUserPhoto');
            const initEl = document.getElementById('modalUserPhotoPlaceholder');
            
            const roleClean = (log.role || 'system').toLowerCase();
            const roleLabel = roleLabels[roleClean] || log.role || 'System';

            if (log.user && log.user.photo) {
                photoEl.src = '/storage/' + log.user.photo;
                photoEl.classList.remove('hidden');
                initEl.classList.add('hidden');
            } else if (roleClean === 'sistem' || roleClean === 'system') {
                initEl.innerHTML = '<span class="material-symbols-outlined text-lg">smart_toy</span>';
                initEl.className = "w-12 h-12 rounded-2xl bg-gray-100 text-gray-600 flex items-center justify-center border border-gray-200 flex-shrink-0";
                initEl.classList.remove('hidden');
                photoEl.classList.add('hidden');
            } else {
                const initial = (log.user_name || 'U').charAt(0).toUpperCase();
                initEl.textContent = initial;
                initEl.className = "w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-black text-base flex items-center justify-center shadow-sm flex-shrink-0";
                initEl.classList.remove('hidden');
                photoEl.classList.add('hidden');
            }

            document.getElementById('modalUserName').textContent = log.user_name || 'System';
            document.getElementById('modalUserEmail').textContent = log.email || '-';
            
            const roleBadge = document.getElementById('modalUserRole');
            roleBadge.textContent = roleLabel;
            roleBadge.className = `inline-block px-2.5 py-0.5 rounded text-[8px] font-black uppercase mt-1.5 ${roleColors[roleClean] || 'bg-gray-100 text-gray-800'}`;

            document.getElementById('modalCategory').textContent = log.category;
            document.getElementById('modalType').textContent = log.activity_type;
            document.getElementById('modalDesc').textContent = log.description;
            document.getElementById('modalModule').textContent = log.module || '-';
            document.getElementById('modalRef').textContent = log.reference_id || '-';

            // Before/After JSON display
            const changesSection = document.getElementById('modalDataChangesSection');
            if (log.data_before || log.data_after) {
                document.getElementById('modalDataBefore').textContent = log.data_before ? JSON.stringify(log.data_before, null, 2) : 'No data (null)';
                document.getElementById('modalDataAfter').textContent = log.data_after ? JSON.stringify(log.data_after, null, 2) : 'No data (null)';
                changesSection.classList.remove('hidden');
            } else {
                changesSection.classList.add('hidden');
            }

            document.getElementById('modalIP').textContent = log.ip_address || '-';
            document.getElementById('modalBrowser').textContent = log.browser || '-';
            document.getElementById('modalDevice').textContent = log.device || '-';
            
            // Format datetime nicely
            const dateStr = log.created_at;
            let formattedDate = '-';
            if (dateStr) {
                const date = new Date(dateStr);
                formattedDate = date.toLocaleString('en-US', { timeZone: 'Asia/Jakarta', dateStyle: 'medium', timeStyle: 'medium' }) + ' WIB';
            }
            document.getElementById('modalTime').textContent = formattedDate;
            document.getElementById('modalUA').textContent = log.user_agent || '-';

            const m = document.getElementById('detailModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeDetailModal() {
            const m = document.getElementById('detailModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        document.getElementById('detailModal').addEventListener('click', function(e) {
            if (e.target === this) closeDetailModal();
        });
    </script>
</x-app-layout>
