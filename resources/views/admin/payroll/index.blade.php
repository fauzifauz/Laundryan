<x-app-layout>
    <x-slot name="header">
        <!-- Local Alpine scope for the header to make exports functional and clickable -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
             x-data="{ exportMonth: 'all', exportYear: 'all', loadingPdf: false, loadingCsv: false }">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">
                    {{ __('Payroll Operations') }}
                </h2>
                <p class="text-xs text-gray-500 font-bold mt-1 uppercase tracking-wider">
                    Manage and distribute employee & courier salaries digitally
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
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

                <!-- Export Actions bound dynamically to header Alpine values -->
                <a :href="'{{ route('admin.payroll.export.pdf') }}?month=' + exportMonth + '&year=' + exportYear + '&status={{ $status }}&role={{ $role }}&search={{ $search }}'"
                    @click="if(!loadingPdf) { loadingPdf = true; setTimeout(() => loadingPdf = false, 3000); }"
                    :class="loadingPdf ? 'opacity-70 pointer-events-none cursor-not-allowed bg-rose-50 border-rose-200' : 'hover:bg-rose-50 hover:shadow-md'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-rose-100 text-rose-600 text-xs font-black uppercase tracking-widest rounded-xl transition-all group shadow-sm whitespace-nowrap">
                    <template x-if="!loadingPdf">
                        <span class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">picture_as_pdf</span>
                    </template>
                    <template x-if="loadingPdf">
                        <svg class="animate-spin h-4.5 w-4.5 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="loadingPdf ? 'Exporting...' : 'Export PDF'"></span>
                </a>
                
                <a :href="'{{ route('admin.payroll.export.csv') }}?month=' + exportMonth + '&year=' + exportYear + '&status={{ $status }}&role={{ $role }}&search={{ $search }}'"
                    @click="if(!loadingCsv) { loadingCsv = true; setTimeout(() => loadingCsv = false, 3000); }"
                    :class="loadingCsv ? 'opacity-70 pointer-events-none cursor-not-allowed bg-emerald-50 border-emerald-200' : 'hover:bg-emerald-50 hover:shadow-md'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-emerald-100 text-emerald-600 text-xs font-black uppercase tracking-widest rounded-xl transition-all group shadow-sm whitespace-nowrap">
                    <template x-if="!loadingCsv">
                        <span class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">table_view</span>
                    </template>
                    <template x-if="loadingCsv">
                        <svg class="animate-spin h-4.5 w-4.5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="loadingCsv ? 'Exporting...' : 'Export CSV'"></span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Page Wrapper for Alpine Modal States -->
    <div class="py-6" x-data="{ 
        loading: false,
        showEditModal: false, 
        showSlipModal: false,
        showCreateModal: false,
        activePayroll: {},
        activeUser: {},
        activeTab: '{{ $status === "paid" ? "history" : "active" }}',
        activeSubTab: 'karyawan',
        historySubTab: 'karyawan',
        exportMonth: '{{ $month }}',
        exportYear: '{{ $year }}',
        formatRupiah(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(value);
        },
        getMonthName(m) {
            return ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'][m - 1];
        },
        showToast: {{ (session('success') || session('warning')) ? 'true' : 'false' }},
        toastType: '{{ session('warning') ? "warning" : "success" }}',
        toastTitle: '{{ session('toast_title') ?: (session('new_payroll_created') ? "Payroll Created" : (session('warning') ? "Already Exists" : (str_contains(session('success', ''), 'generated') ? "Payroll Generated" : "Changes Saved"))) }}',
        toastMessage: '{{ session('new_payroll_created') ? (session('new_payroll_employee') ? "Payroll for " . session('new_payroll_employee') . " has been created successfully." : "New payroll record has been created successfully.") : (session('success') ?: session('warning', '')) }}',
        showConfirmModal: false,
        confirmTitle: '',
        confirmMessage: '',
        confirmActionUrl: '',
        confirmActionMethod: 'POST',
        confirmButtonText: 'Confirm',
        confirmButtonColorClass: 'bg-emerald-600 hover:bg-emerald-700'
    }"
    x-init="
        loading = false;
        document.querySelectorAll('a').forEach(link => {
            if (link.hostname === window.location.hostname && !link.hasAttribute('download') && !link.href.includes('export') && !link.href.startsWith('#') && link.target !== '_blank') {
                link.addEventListener('click', () => { loading = true; });
            }
        });
        if (showToast) {
            setTimeout(() => { showToast = false; }, 5000);
        }
    "
    @submit.window="loading = true">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Toast Alert Notification -->
            <div x-show="showToast" 
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="{
                    'bg-emerald-50 border-emerald-200 text-emerald-800': toastType === 'success',
                    'bg-amber-50 border-amber-200 text-amber-800': toastType === 'warning'
                }"
                class="fixed top-6 right-6 z-[110] max-w-sm w-full border rounded-3xl p-5 shadow-2xl flex items-center justify-between overflow-hidden" 
                x-cloak>
                <div :class="{
                    'bg-emerald-600/10': toastType === 'success',
                    'bg-amber-600/10': toastType === 'warning'
                }" class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full blur-xl pointer-events-none"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div :class="{
                        'bg-emerald-100/50 border-emerald-200': toastType === 'success',
                        'bg-amber-100/50 border-amber-200': toastType === 'warning'
                    }" class="w-10 h-10 rounded-2xl border flex items-center justify-center shadow-inner">
                        <span class="material-symbols-outlined text-xl" x-text="toastType === 'success' ? 'check_circle' : 'warning'" :class="{
                            'text-emerald-600': toastType === 'success',
                            'text-amber-600': toastType === 'warning'
                        }"></span>
                    </div>
                    <div>
                        <h4 class="font-black text-xs uppercase tracking-wider" x-text="toastTitle"></h4>
                        <p class="text-[11px] font-medium mt-0.5" :class="{
                            'text-emerald-700': toastType === 'success',
                            'text-amber-700': toastType === 'warning'
                        }" x-text="toastMessage"></p>
                    </div>
                </div>
                <button @click="showToast = false" :class="{
                    'text-emerald-600/60 hover:text-emerald-800 hover:bg-emerald-100/50': toastType === 'success',
                    'text-amber-600/60 hover:text-amber-800 hover:bg-amber-100/50': toastType === 'warning'
                }" class="transition-colors p-2 rounded-xl relative z-10">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            <!-- Confirmation Modal -->
            <div x-show="showConfirmModal" 
                 class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/65 backdrop-blur-xs"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="display: none;"
                 x-cloak>
                 
                <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden"
                     @click.away="showConfirmModal = false"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                     
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-100 flex items-center gap-3 bg-gradient-to-r from-gray-50 to-white">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-xs">
                            <span class="material-symbols-outlined text-[20px]">help_center</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900" x-text="confirmTitle">Confirm Action</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Please review the details below</p>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <p class="text-xs text-gray-650 font-bold leading-relaxed" x-text="confirmMessage"></p>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
                        <button @click="showConfirmModal = false" type="button"
                                class="py-2.5 px-5 bg-gray-150 hover:bg-gray-205 text-gray-700 text-[10px] font-black rounded-xl uppercase tracking-widest transition-all">
                            Cancel
                        </button>
                        <form :action="confirmActionUrl" method="POST">
                            @csrf
                            <input type="hidden" name="_method" :value="confirmActionMethod">
                            <button type="submit" 
                                    :class="confirmButtonColorClass + ' py-2.5 px-5 text-white text-[10px] font-black rounded-xl uppercase tracking-widest shadow-md transition-all active:scale-95 border border-black/5'">
                                <span x-text="confirmButtonText">Confirm</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center gap-3" role="alert">
                    <span class="material-symbols-outlined text-rose-600">error</span>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
            @endif
            <!-- Summary KPI Cards Grid (Clickable links to filtered lists) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Payroll Card (Redirects to active month) -->
                <a href="{{ route('admin.payroll.index') }}?month={{ now()->month }}&year={{ now()->year }}" 
                    class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between hover:border-blue-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
                    <div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1 group-hover:text-blue-500 transition-colors">Total Payroll (This Month)</span>
                        <h3 class="text-2xl font-black text-gray-900">Rp {{ number_format($totalPayroll, 0, ',', '.') }}</h3>
                    </div>
                    <div class="mt-4 flex items-center gap-1.5 text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md self-start border border-blue-100">
                        <span class="material-symbols-outlined text-xs">payments</span>
                        Net salary outflow
                    </div>
                </a>

                <!-- Staff Paid Card (Redirects to paid status) -->
                <a href="{{ route('admin.payroll.index') }}?month={{ $month }}&year={{ $year }}&status=paid" 
                    class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between hover:border-emerald-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
                    <div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1 group-hover:text-emerald-500 transition-colors">Staff Paid</span>
                        <h3 class="text-2xl font-black text-emerald-600">{{ $paidEmployees }} / {{ $totalEmployeesCount }}</h3>
                    </div>
                    <div class="mt-4 flex items-center gap-1.5 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md self-start border border-emerald-100">
                        <span class="material-symbols-outlined text-xs">group</span>
                        Employees and Couriers
                    </div>
                </a>

                <!-- Success Transactions Card (Redirects to paid status) -->
                <a href="{{ route('admin.payroll.index') }}?month={{ $month }}&year={{ $year }}&status=paid" 
                    class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between hover:border-emerald-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
                    <div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1 group-hover:text-emerald-500 transition-colors">Successful Payouts</span>
                        <h3 class="text-2xl font-black text-gray-900">{{ $successfulTransactions }}</h3>
                    </div>
                    <div class="mt-4 flex items-center gap-1.5 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md self-start border border-emerald-100">
                        <span class="material-symbols-outlined text-xs">check_circle</span>
                        Paid status
                    </div>
                </a>

                <!-- Pending Transactions Card (Redirects to pending status) -->
                <a href="{{ route('admin.payroll.index') }}?month={{ $month }}&year={{ $year }}&status=pending" 
                    class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between hover:border-amber-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
                    <div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1 group-hover:text-amber-500 transition-colors">Pending Payouts</span>
                        <h3 class="text-2xl font-black text-amber-500">{{ $pendingTransactions }}</h3>
                    </div>
                    <div class="mt-4 flex items-center gap-1.5 text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md self-start border border-amber-100">
                        <span class="material-symbols-outlined text-xs">pending</span>
                        Awaiting payout
                    </div>
                </a>
            </div>

            <!-- Chart and Generation Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Monthly Salary Expenses Chart (2/3) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col justify-between">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <div>
                            <h4 class="text-base font-black text-gray-900">Monthly Salary Expenses Chart</h4>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Total payout per month (Click bars to view details)</p>
                        </div>
                        <div class="flex items-center gap-3 self-end sm:self-auto">
                            <!-- Year Filter for Chart (Icon represented select) -->
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">event</span>
                                <select onchange="window.location.href = '{{ route('admin.payroll.index') }}?year={{ $year }}&month={{ $month }}&status={{ $status }}&role={{ $role }}&search={{ $search }}&chart_year=' + this.value"
                                    class="text-xs font-bold text-gray-700 bg-gray-50 border border-gray-150 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                                    <option value="all" {{ $chartYear === 'all' ? 'selected' : '' }}>All Years</option>
                                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                        <option value="{{ $y }}" {{ $chartYear != 'all' && $chartYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[16px] pointer-events-none">expand_more</span>
                            </div>
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                            </span>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="payrollExpensesChart"></canvas>
                    </div>
                </div>

                <!-- Generation Panel (1/3) -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-blue-600">published_with_changes</span>
                            <h4 class="text-base font-black text-gray-900">Generate Salary Sheets</h4>
                        </div>
                        <p class="text-xs text-gray-500 font-bold leading-relaxed mb-6">
                            Use the controls below to automatically calculate and create new payroll sheets for all active employees and couriers for the selected period.
                        </p>
                    </div>
                    <form action="{{ route('admin.payroll.generate') }}" method="POST" class="space-y-4" x-data="{ generating: false }" @submit="generating = true">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Month</label>
                                <select name="month" class="w-full text-xs font-bold text-gray-700 bg-gray-50 border border-gray-100 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2026, $m)->format('F') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Year</label>
                                <select name="year" class="w-full text-xs font-bold text-gray-700 bg-gray-50 border border-gray-100 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" :disabled="generating"
                            :class="generating ? 'opacity-70 cursor-not-allowed bg-blue-500' : 'hover:bg-blue-700'"
                            class="w-full bg-blue-600 text-white text-xs font-black uppercase tracking-widest py-3 px-4 rounded-xl shadow-lg shadow-blue-100 hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 active:scale-98">
                            <template x-if="!generating">
                                <span class="material-symbols-outlined text-[16px]">autorenew</span>
                            </template>
                            <template x-if="generating">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="generating ? 'Generating...' : 'Generate Payroll'"></span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Search and Filter Panel (Flex wrap layout to prevent truncating) -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <form action="{{ route('admin.payroll.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
                    
                    <!-- Text Search Name -->
                    <div class="flex-1 min-w-[220px] relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-2.5 text-gray-400 text-[18px]">search</span>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search employee name..."
                            class="w-full text-xs font-bold text-gray-700 bg-gray-50 border border-gray-150 rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>

                    <!-- Filter Month (Icon represented dropdown) -->
                    <div class="w-full sm:w-36 relative">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-[18px]">calendar_month</span>
                        <select name="month" class="w-full text-xs font-bold text-gray-700 bg-gray-50 border border-gray-150 rounded-xl pl-10 pr-8 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                            <option value="all" {{ $month === 'all' ? 'selected' : '' }}>All Months</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $month != 'all' && $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2026, $m)->format('F') }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-2.5 text-gray-400 text-[18px] pointer-events-none">expand_more</span>
                    </div>

                    <!-- Filter Year (Icon represented dropdown) -->
                    <div class="w-full sm:w-28 relative">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-[18px]">event</span>
                        <select name="year" class="w-full text-xs font-bold text-gray-700 bg-gray-50 border border-gray-150 rounded-xl pl-10 pr-8 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                            <option value="all" {{ $year === 'all' ? 'selected' : '' }}>All Years</option>
                            @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                <option value="{{ $y }}" {{ $year != 'all' && $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-2.5 text-gray-400 text-[18px] pointer-events-none">expand_more</span>
                    </div>

                    <!-- Filter Role (Icon represented dropdown) -->
                    <div class="w-full sm:w-44 relative">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-[18px]">group</span>
                        <select name="role" class="w-full text-xs font-bold text-gray-700 bg-gray-50 border border-gray-150 rounded-xl pl-10 pr-8 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                            <option value="">All Roles</option>
                            <option value="karyawan" {{ $role === 'karyawan' ? 'selected' : '' }}>Internal Employees</option>
                            <option value="kurir" {{ $role === 'kurir' ? 'selected' : '' }}>Couriers</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-2.5 text-gray-400 text-[18px] pointer-events-none">expand_more</span>
                    </div>

                    <!-- Filter Status (Icon represented dropdown) -->
                    <div class="w-full sm:w-40 relative">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-[18px]">payments</span>
                        <select name="status" class="w-full text-xs font-bold text-gray-700 bg-gray-50 border border-gray-150 rounded-xl pl-10 pr-8 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-2.5 text-gray-400 text-[18px] pointer-events-none">expand_more</span>
                    </div>

                    <!-- Apply Filters and Reset Buttons -->
                    <div class="flex items-center gap-2 w-full sm:w-auto sm:ml-auto">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black uppercase tracking-widest py-2.5 px-4 rounded-xl transition-all shadow-sm hover:shadow active:scale-98 flex items-center gap-1.5 whitespace-nowrap">
                            <span class="material-symbols-outlined text-sm">filter_alt</span>
                            Filter
                        </button>
                        <a href="{{ route('admin.payroll.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-[10px] font-black uppercase tracking-widest py-2.5 px-4 rounded-xl transition-all text-center flex items-center gap-1.5 active:scale-98 whitespace-nowrap">
                            <span class="material-symbols-outlined text-sm">restart_alt</span>
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabbed Main Display -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden relative">
                <!-- Grid Loading Overlay -->
                <div x-show="loading" 
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
                        <p class="text-xs font-black text-blue-600 uppercase tracking-widest animate-pulse">Load Payroll</p>
                    </div>
                </div>
                <!-- Navigation Tabs -->
                <div class="flex border-b border-gray-100 bg-gray-50/50 justify-between items-center pr-6">
                    <div class="flex">
                        <button @click="activeTab = 'active'"
                            :class="activeTab === 'active' ? 'border-blue-600 text-blue-600 bg-white' : 'border-transparent text-gray-400 hover:text-gray-600'"
                            class="flex items-center gap-2 px-6 py-4 border-b-2 text-xs font-black uppercase tracking-widest transition-all">
                            <span class="material-symbols-outlined text-[18px]">list_alt</span>
                            Active Payroll ({{ $payrolls->count() }})
                        </button>
                        <button @click="activeTab = 'history'"
                            :class="activeTab === 'history' ? 'border-blue-600 text-blue-600 bg-white' : 'border-transparent text-gray-400 hover:text-gray-600'"
                            class="flex items-center gap-2 px-6 py-4 border-b-2 text-xs font-black uppercase tracking-widest transition-all">
                            <span class="material-symbols-outlined text-[18px]">history</span>
                            Payment History ({{ $historyPayrolls->count() }})
                        </button>
                    </div>

                    <!-- Create Button aligned on the right -->
                    <button type="button" @click="showCreateModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:shadow-md transition-all group shadow-sm whitespace-nowrap">
                        <span class="material-symbols-outlined text-[18px] group-hover:rotate-90 transition-transform">add_circle</span>
                        Create Payroll
                    </button>
                </div>

                <!-- Tab content: ACTIVE PAYROLL -->
                <div x-show="activeTab === 'active'" class="p-0">
                    
                    <!-- Sub-tabs for Employee & Courier Separation -->
                    <div class="flex items-center gap-2 p-4 bg-gray-50/30 border-b border-gray-100">
                        <button type="button" @click="activeSubTab = 'karyawan'"
                            :class="activeSubTab === 'karyawan' ? 'bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'bg-white border border-gray-150 text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-xs">badge</span>
                            Internal Employees ({{ $payrolls->where('user.role', 'karyawan')->count() }})
                        </button>
                        <button type="button" @click="activeSubTab = 'kurir'"
                            :class="activeSubTab === 'kurir' ? 'bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'bg-white border border-gray-150 text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-xs">local_shipping</span>
                            Couriers ({{ $payrolls->where('user.role', 'kurir')->count() }})
                        </button>
                    </div>

                    <!-- Employees active list -->
                    <div x-show="activeSubTab === 'karyawan'" class="overflow-x-auto">
                        <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <span>{{ $payrolls->where('user.role', 'karyawan')->count() }} records found</span>
                            <span>Page 1 / 1</span>
                        </div>
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="text-left text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/30">
                                    <th class="px-6 py-4 w-12 text-center">No</th>
                                    <th class="px-6 py-4">Employee</th>
                                    <th class="px-6 py-4">Role</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Created At</th>
                                    <th class="px-6 py-4 text-right">Base Salary</th>
                                    <th class="px-6 py-4 text-right whitespace-nowrap">Bonus & Deductions</th>
                                    <th class="px-6 py-4 text-right">Net Salary</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4">Payment Ref</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs">
                                @forelse($payrolls->where('user.role', 'karyawan') as $pay)
                                    @php
                                        $netSalary = $pay->amount + $pay->bonus - $pay->potongan;
                                        $empId = 'EMP-' . sprintf('%04d', $pay->user->id);
                                    @endphp
                                    <tr class="hover:bg-blue-50/20 transition-all group">
                                        <td class="px-6 py-4 text-center font-bold text-gray-400">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-blue-600/10 flex items-center justify-center font-bold text-blue-600 text-sm border border-blue-100 flex-shrink-0">
                                                    {{ substr($pay->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="font-black text-gray-900 group-hover:text-blue-600 transition-colors block">{{ $pay->user->name }}</span>
                                                    <span class="text-[9px] font-mono text-gray-400 block mt-0.5">ID: {{ $empId }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 text-[9px] font-black rounded-lg uppercase tracking-wider">Employee</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-[10px] font-bold text-gray-700">
                                                {{ $pay->created_at ? $pay->created_at->format('d M Y') : '-' }}
                                            </div>
                                            <div class="text-[9px] text-gray-400 font-medium mt-0.5">
                                                {{ $pay->created_at ? $pay->created_at->format('H:i') . ' WIB' : '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-900">
                                            Rp {{ number_format($pay->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="text-[10px] text-emerald-600 font-bold" title="Bonus">
                                                +Rp {{ number_format($pay->bonus, 0, ',', '.') }}
                                            </div>
                                            <div class="text-[10px] text-rose-500 font-bold" title="Deductions">
                                                -Rp {{ number_format($pay->potongan, 0, ',', '.') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="font-black text-gray-900 text-sm">Rp {{ number_format($netSalary, 0, ',', '.') }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($pay->status === 'paid')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-100 text-emerald-700 text-[9px] font-black rounded-full uppercase tracking-wider shadow-sm">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    PAID
                                                </span>
                                            @elseif($pay->status === 'failed')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 border border-rose-100 text-rose-700 text-[9px] font-black rounded-full uppercase tracking-wider shadow-sm">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                    FAILED
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-100 text-amber-700 text-[9px] font-black rounded-full uppercase tracking-wider shadow-sm">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                    PENDING
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-[10px] font-black text-gray-700 uppercase tracking-widest">
                                                {{ $pay->payment_method ?: 'No payout' }}
                                            </div>
                                            @if($pay->stripe_transfer_id)
                                                <div class="text-[9px] font-mono text-gray-400 mt-1 uppercase truncate max-w-[120px]" title="{{ $pay->stripe_transfer_id }}">{{ $pay->stripe_transfer_id }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                @if($pay->status === 'pending')
                                                    <!-- Stripe Payout Trigger -->
                                                    @if($pay->user->stripe_account_id)
                                                        <button type="button" title="Send Salary via Stripe Connected Account"
                                                            @click="
                                                                confirmTitle = 'Stripe Payout Confirmation';
                                                                confirmMessage = 'Process Stripe connected account salary payout of Rp ' + new Intl.NumberFormat('id-ID').format({{ $pay->amount + $pay->bonus - $pay->potongan }}) + ' for {{ $pay->user->name }}?';
                                                                confirmActionUrl = '{{ route('admin.payroll.payout', $pay->id) }}';
                                                                confirmActionMethod = 'POST';
                                                                confirmButtonText = 'Process Stripe Payout';
                                                                confirmButtonColorClass = 'bg-blue-600 hover:bg-blue-700 shadow-blue-200';
                                                                showConfirmModal = true;
                                                            "
                                                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-xl text-[10px] font-black transition-all uppercase tracking-widest active:scale-95 shadow-md border border-blue-500">
                                                            <span class="material-symbols-outlined text-[15px] font-bold">send</span>
                                                            Send Stripe
                                                        </button>
                                                    @else
                                                        <button type="button" disabled title="No Stripe Connected Account Linked"
                                                            class="inline-flex items-center gap-2 bg-gray-50 text-gray-400 cursor-not-allowed px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-gray-200">
                                                            <span class="material-symbols-outlined text-[15px] font-bold">send</span>
                                                            Send Stripe
                                                        </button>
                                                    @endif

                                                    <!-- Cash Payout Trigger -->
                                                    <button type="button" title="Record salary payment in Cash"
                                                        @click="
                                                            confirmTitle = 'Cash Payment Confirmation';
                                                            confirmMessage = 'Record cash salary payment of Rp ' + new Intl.NumberFormat('id-ID').format({{ $pay->amount + $pay->bonus - $pay->potongan }}) + ' for {{ $pay->user->name }}?';
                                                            confirmActionUrl = '{{ route('admin.payroll.payout.cash', $pay->id) }}';
                                                            confirmActionMethod = 'POST';
                                                            confirmButtonText = 'Record Cash Payment';
                                                            confirmButtonColorClass = 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200';
                                                            showConfirmModal = true;
                                                        "
                                                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-xl text-[10px] font-black transition-all uppercase tracking-widest active:scale-95 shadow-md border border-emerald-500">
                                                        <span class="material-symbols-outlined text-[15px] font-bold">payments</span>
                                                        Send Cash
                                                    </button>
                                                @endif

                                                <!-- Payslip Modal trigger -->
                                                <button type="button" 
                                                    @click="activePayroll = {{ json_encode($pay) }}; activeUser = {{ json_encode($pay->user) }}; showSlipModal = true"
                                                    class="inline-flex items-center justify-center p-2 bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 text-gray-500 rounded-xl transition-all"
                                                    title="View Digital Payslip">
                                                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                                </button>

                                                <!-- Edit Modal Trigger -->
                                                @if($pay->status !== 'paid')
                                                <button type="button"
                                                    @click="activePayroll = {{ json_encode($pay) }}; activeUser = {{ json_encode($pay->user) }}; showEditModal = true"
                                                    class="inline-flex items-center justify-center p-2 bg-gray-50 border border-gray-100 hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200 text-gray-500 rounded-xl transition-all"
                                                    title="Edit Salary Details">
                                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                                </button>
                                                @else
                                                <button type="button" disabled
                                                    class="inline-flex items-center justify-center p-2 bg-gray-50 border border-gray-100 text-gray-300 rounded-xl cursor-not-allowed"
                                                    title="Paid Payroll cannot be edited">
                                                    <span class="material-symbols-outlined text-[18px]">edit_off</span>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-20 text-center text-gray-400 italic font-bold">
                                            No payroll data found for internal employees.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Couriers active list -->
                    <div x-show="activeSubTab === 'kurir'" class="overflow-x-auto">
                        <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <span>{{ $payrolls->where('user.role', 'kurir')->count() }} records found</span>
                            <span>Page 1 / 1</span>
                        </div>
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="text-left text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/30">
                                    <th class="px-6 py-4 w-12 text-center">No</th>
                                    <th class="px-6 py-4">Courier</th>
                                    <th class="px-6 py-4">Role</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Created At</th>
                                    <th class="px-6 py-4 text-right">Base Salary</th>
                                    <th class="px-6 py-4 text-right whitespace-nowrap">Bonus & Deductions</th>
                                    <th class="px-6 py-4 text-right">Net Salary</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4">Payment Ref</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs">
                                @forelse($payrolls->where('user.role', 'kurir') as $pay)
                                    @php
                                        $netSalary = $pay->amount + $pay->bonus - $pay->potongan;
                                        $empId = 'CUR-' . sprintf('%04d', $pay->user->id);
                                    @endphp
                                    <tr class="hover:bg-blue-50/20 transition-all group">
                                        <td class="px-6 py-4 text-center font-bold text-gray-400">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-emerald-600/10 flex items-center justify-center font-bold text-emerald-600 text-sm border border-emerald-100 flex-shrink-0">
                                                    {{ substr($pay->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="font-black text-gray-900 group-hover:text-emerald-600 transition-colors block">{{ $pay->user->name }}</span>
                                                    <span class="text-[9px] font-mono text-gray-400 block mt-0.5">ID: {{ $empId }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 bg-emerald-50 border border-emerald-100 text-emerald-600 text-[9px] font-black rounded-lg uppercase tracking-wider">Courier</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-[10px] font-bold text-gray-700">
                                                {{ $pay->created_at ? $pay->created_at->format('d M Y') : '-' }}
                                            </div>
                                            <div class="text-[9px] text-gray-400 font-medium mt-0.5">
                                                {{ $pay->created_at ? $pay->created_at->format('H:i') . ' WIB' : '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-900">
                                            Rp {{ number_format($pay->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="text-[10px] text-emerald-600 font-bold" title="Bonus">
                                                +Rp {{ number_format($pay->bonus, 0, ',', '.') }}
                                            </div>
                                            <div class="text-[10px] text-rose-500 font-bold" title="Deductions">
                                                -Rp {{ number_format($pay->potongan, 0, ',', '.') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="font-black text-gray-900 text-sm">Rp {{ number_format($netSalary, 0, ',', '.') }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($pay->status === 'paid')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-100 text-emerald-700 text-[9px] font-black rounded-full uppercase tracking-wider shadow-sm">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    PAID
                                                </span>
                                            @elseif($pay->status === 'failed')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 border border-rose-100 text-rose-700 text-[9px] font-black rounded-full uppercase tracking-wider shadow-sm">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                    FAILED
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-100 text-amber-700 text-[9px] font-black rounded-full uppercase tracking-wider shadow-sm">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                    PENDING
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-[10px] font-black text-gray-700 uppercase tracking-widest">
                                                {{ $pay->payment_method ?: 'No payout' }}
                                            </div>
                                            @if($pay->stripe_transfer_id)
                                                <div class="text-[9px] font-mono text-gray-400 mt-1 uppercase truncate max-w-[120px]" title="{{ $pay->stripe_transfer_id }}">{{ $pay->stripe_transfer_id }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                @if($pay->status === 'pending')
                                                    <!-- Stripe Payout Trigger -->
                                                    @if($pay->user->stripe_account_id)
                                                        <button type="button" title="Send Salary via Stripe Connected Account"
                                                            @click="
                                                                confirmTitle = 'Stripe Payout Confirmation';
                                                                confirmMessage = 'Process Stripe connected account salary payout of Rp ' + new Intl.NumberFormat('id-ID').format({{ $pay->amount + $pay->bonus - $pay->potongan }}) + ' for {{ $pay->user->name }}?';
                                                                confirmActionUrl = '{{ route('admin.payroll.payout', $pay->id) }}';
                                                                confirmActionMethod = 'POST';
                                                                confirmButtonText = 'Process Stripe Payout';
                                                                confirmButtonColorClass = 'bg-blue-600 hover:bg-blue-700 shadow-blue-200';
                                                                showConfirmModal = true;
                                                            "
                                                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-xl text-[10px] font-black transition-all uppercase tracking-widest active:scale-95 shadow-md border border-blue-500">
                                                            <span class="material-symbols-outlined text-[15px] font-bold">send</span>
                                                            Send Stripe
                                                        </button>
                                                    @else
                                                        <button type="button" disabled title="No Stripe Connected Account Linked"
                                                            class="inline-flex items-center gap-2 bg-gray-50 text-gray-400 cursor-not-allowed px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-gray-200">
                                                            <span class="material-symbols-outlined text-[15px] font-bold">send</span>
                                                            Send Stripe
                                                        </button>
                                                    @endif

                                                    <!-- Cash Payout Trigger -->
                                                    <button type="button" title="Record salary payment in Cash"
                                                        @click="
                                                            confirmTitle = 'Cash Payment Confirmation';
                                                            confirmMessage = 'Record cash salary payment of Rp ' + new Intl.NumberFormat('id-ID').format({{ $pay->amount + $pay->bonus - $pay->potongan }}) + ' for {{ $pay->user->name }}?';
                                                            confirmActionUrl = '{{ route('admin.payroll.payout.cash', $pay->id) }}';
                                                            confirmActionMethod = 'POST';
                                                            confirmButtonText = 'Record Cash Payment';
                                                            confirmButtonColorClass = 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200';
                                                            showConfirmModal = true;
                                                        "
                                                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-xl text-[10px] font-black transition-all uppercase tracking-widest active:scale-95 shadow-md border border-emerald-500">
                                                        <span class="material-symbols-outlined text-[15px] font-bold">payments</span>
                                                        Send Cash
                                                    </button>
                                                @endif

                                                <!-- Payslip Modal trigger -->
                                                <button type="button" 
                                                    @click="activePayroll = {{ json_encode($pay) }}; activeUser = {{ json_encode($pay->user) }}; showSlipModal = true"
                                                    class="inline-flex items-center justify-center p-2 bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 text-gray-500 rounded-xl transition-all"
                                                    title="View Digital Payslip">
                                                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                                </button>

                                                <!-- Edit Modal Trigger -->
                                                @if($pay->status !== 'paid')
                                                <button type="button"
                                                    @click="activePayroll = {{ json_encode($pay) }}; activeUser = {{ json_encode($pay->user) }}; showEditModal = true"
                                                    class="inline-flex items-center justify-center p-2 bg-gray-50 border border-gray-100 hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200 text-gray-500 rounded-xl transition-all"
                                                    title="Edit Salary Details">
                                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                                </button>
                                                @else
                                                <button type="button" disabled
                                                    class="inline-flex items-center justify-center p-2 bg-gray-50 border border-gray-100 text-gray-300 rounded-xl cursor-not-allowed"
                                                    title="Paid Payroll cannot be edited">
                                                    <span class="material-symbols-outlined text-[18px]">edit_off</span>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-20 text-center text-gray-400 italic font-bold">
                                            No payroll data found for couriers.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- Tab content: PAYMENT HISTORY -->
                <div x-show="activeTab === 'history'" class="p-0">
                    
                    <!-- Sub-tabs for Employee & Courier Separation in History -->
                    <div class="flex items-center gap-2 p-4 bg-gray-50/30 border-b border-gray-100">
                        <button type="button" @click="historySubTab = 'karyawan'"
                            :class="historySubTab === 'karyawan' ? 'bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'bg-white border border-gray-150 text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-xs">badge</span>
                            Internal Employees ({{ $historyPayrolls->where('user.role', 'karyawan')->count() }})
                        </button>
                        <button type="button" @click="historySubTab = 'kurir'"
                            :class="historySubTab === 'kurir' ? 'bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'bg-white border border-gray-150 text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-xs">local_shipping</span>
                            Couriers ({{ $historyPayrolls->where('user.role', 'kurir')->count() }})
                        </button>
                    </div>

                    <!-- Employees historical list -->
                    <div x-show="historySubTab === 'karyawan'" class="overflow-x-auto">
                        <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <span>{{ $historyPayrolls->where('user.role', 'karyawan')->count() }} records found</span>
                            <span>Page 1 / 1</span>
                        </div>
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="text-left text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/30">
                                    <th class="px-6 py-4 w-12 text-center">No</th>
                                    <th class="px-6 py-4">Employee</th>
                                    <th class="px-6 py-4">Period</th>
                                    <th class="px-6 py-4 text-right">Net Paid</th>
                                    <th class="px-6 py-4">Method</th>
                                    <th class="px-6 py-4">Payout Date</th>
                                    <th class="px-6 py-4">Transaction Ref</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs">
                                @forelse($historyPayrolls->where('user.role', 'karyawan') as $pay)
                                    @php
                                        $netSalary = $pay->amount + $pay->bonus - $pay->potongan;
                                        $empId = 'EMP-' . sprintf('%04d', $pay->user->id);
                                    @endphp
                                    <tr class="hover:bg-blue-50/20 transition-all group">
                                        <td class="px-6 py-4 text-center font-bold text-gray-400">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4">
                                            <span class="font-black text-gray-900 group-hover:text-blue-600 transition-colors block">{{ $pay->user->name }}</span>
                                            <span class="text-[9px] font-mono text-gray-400 block mt-0.5">ID: {{ $empId }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-gray-700">{{ \Carbon\Carbon::create($pay->year, $pay->month)->format('F Y') }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-black text-emerald-600">
                                            Rp {{ number_format($netSalary, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 bg-gray-100 border border-gray-200 text-gray-700 text-[9px] font-black rounded-lg uppercase tracking-wider">
                                                {{ $pay->payment_method ?: 'cash' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">
                                            {{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('d M Y, H:i') . ' WIB' : '-' }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-[10px] text-gray-500 truncate max-w-[150px]" title="{{ $pay->stripe_transfer_id }}">
                                            {{ $pay->stripe_transfer_id ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <!-- Slip Trigger -->
                                            <button type="button" 
                                                @click="activePayroll = {{ json_encode($pay) }}; activeUser = {{ json_encode($pay->user) }}; showSlipModal = true"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all text-[10px] font-black uppercase tracking-wider">
                                                <span class="material-symbols-outlined text-[13px] font-bold">receipt_long</span>
                                                Payslip
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-20 text-center text-gray-400 italic font-bold">
                                            No payment records found for internal employees.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Couriers historical list -->
                    <div x-show="historySubTab === 'kurir'" class="overflow-x-auto">
                        <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <span>{{ $historyPayrolls->where('user.role', 'kurir')->count() }} records found</span>
                            <span>Page 1 / 1</span>
                        </div>
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="text-left text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/30">
                                    <th class="px-6 py-4 w-12 text-center">No</th>
                                    <th class="px-6 py-4">Courier</th>
                                    <th class="px-6 py-4">Period</th>
                                    <th class="px-6 py-4 text-right">Net Paid</th>
                                    <th class="px-6 py-4">Method</th>
                                    <th class="px-6 py-4">Payout Date</th>
                                    <th class="px-6 py-4">Transaction Ref</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs">
                                @forelse($historyPayrolls->where('user.role', 'kurir') as $pay)
                                    @php
                                        $netSalary = $pay->amount + $pay->bonus - $pay->potongan;
                                        $empId = 'CUR-' . sprintf('%04d', $pay->user->id);
                                    @endphp
                                    <tr class="hover:bg-blue-50/20 transition-all group">
                                        <td class="px-6 py-4 text-center font-bold text-gray-400">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4">
                                            <span class="font-black text-gray-900 group-hover:text-emerald-600 transition-colors block">{{ $pay->user->name }}</span>
                                            <span class="text-[9px] font-mono text-gray-400 block mt-0.5">ID: {{ $empId }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-gray-700">{{ \Carbon\Carbon::create($pay->year, $pay->month)->format('F Y') }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-black text-emerald-600">
                                            Rp {{ number_format($netSalary, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 bg-gray-100 border border-gray-200 text-gray-700 text-[9px] font-black rounded-lg uppercase tracking-wider">
                                                {{ $pay->payment_method ?: 'cash' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">
                                            {{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('d M Y, H:i') . ' WIB' : '-' }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-[10px] text-gray-500 truncate max-w-[150px]" title="{{ $pay->stripe_transfer_id }}">
                                            {{ $pay->stripe_transfer_id ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <!-- Slip Trigger -->
                                            <button type="button" 
                                                @click="activePayroll = {{ json_encode($pay) }}; activeUser = {{ json_encode($pay->user) }}; showSlipModal = true"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all text-[10px] font-black uppercase tracking-wider">
                                                <span class="material-symbols-outlined text-[13px] font-bold">receipt_long</span>
                                                Payslip
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-20 text-center text-gray-400 italic font-bold">
                                            No payment records found for couriers.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>

        <!-- MODAL 1: EDIT PAYROLL DETAILS (Alpine.js) -->
        <div x-show="showEditModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-cloak
             style="display: none;">
            
            <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden max-h-[90vh] flex flex-col"
                 @click.away="showEditModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                 
                <!-- Header -->
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-xs">
                            <span class="material-symbols-outlined text-[22px]">edit_document</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Edit Payroll Record</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider" x-text="'Modify salary details for ' + activeUser.name"></p>
                        </div>
                    </div>
                    <button @click="showEditModal = false" class="p-2 text-gray-400 hover:text-gray-650 rounded-xl hover:bg-gray-100/85 transition-all">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form :action="'{{ route('admin.payroll.index') }}/' + activePayroll.id" method="POST" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    @method('PUT')
                    
                    <div class="flex-1 overflow-y-auto p-6 space-y-5">
                        <!-- Base Salary -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">payments</span> Base Salary (IDR)
                            </label>
                            <input type="number" name="amount" x-model.number="activePayroll.amount" required min="0"
                                   class="w-full bg-white border border-gray-150 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all"
                                   placeholder="Base Salary Amount">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Bonus -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">add_circle</span> Bonus (+)
                                </label>
                                <input type="number" name="bonus" x-model.number="activePayroll.bonus" required min="0"
                                       class="w-full bg-white border border-gray-150 rounded-2xl text-xs font-bold text-emerald-600 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                            </div>
                            <!-- Deductions -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">remove_circle</span> Deductions (-)
                                </label>
                                <input type="number" name="potongan" x-model.number="activePayroll.potongan" required min="0"
                                       class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold text-rose-600 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Payment Status -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">verified_user</span> Payment Status
                                </label>
                                <select name="status" x-model="activePayroll.status" required
                                        class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                                    <option value="pending">PENDING</option>
                                    <option value="paid">PAID</option>
                                    <option value="failed">FAILED</option>
                                </select>
                            </div>
                            <!-- Payment Method -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">account_balance_wallet</span> Payment Method
                                </label>
                                <select name="payment_method" x-model="activePayroll.payment_method"
                                        class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                                    <option value="">Not Specified</option>
                                    <option value="cash">CASH</option>
                                    <option value="stripe">STRIPE</option>
                                    <option value="bank_transfer">BANK TRANSFER</option>
                                </select>
                            </div>
                        </div>

                        <!-- Payment Reference -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">receipt</span> Payment Reference / Stripe ID
                            </label>
                            <input type="text" name="stripe_transfer_id" x-model="activePayroll.stripe_transfer_id" placeholder="CASH-xxxx or trans_xxxx"
                                   class="w-full bg-white border border-gray-150 rounded-2xl text-xs font-bold font-mono text-gray-700 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                        </div>

                        <!-- Net Salary Outflow Box -->
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600">account_balance</span>
                                <span class="font-bold text-blue-900 uppercase tracking-wide">Net Take Home Pay:</span>
                            </div>
                            <span class="font-black text-blue-600 text-base" x-text="formatRupiah(Number(activePayroll.amount || 0) + Number(activePayroll.bonus || 0) - Number(activePayroll.potongan || 0))"></span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-2.5 bg-gray-50/50">
                        <button type="button" @click="showEditModal = false"
                                class="py-2.5 px-6 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-black rounded-xl uppercase tracking-widest transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl uppercase tracking-widest shadow-md shadow-blue-200 hover:shadow-lg transition-all">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: REDESIGNED PREMIUM DIGITAL PAYSLIP (Alpine.js Printable) -->
        <div x-show="showSlipModal" x-transition.opacity
            class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
            
            <div x-show="showSlipModal" x-transition.scale.95 @click.away="showSlipModal = false"
                class="bg-white rounded-[24px] w-full max-w-md shadow-2xl border border-gray-100 overflow-hidden flex flex-col relative" id="printablePayslipContainer">
                
                <!-- Slip Header -->
                <div class="p-6 bg-gradient-to-br from-slate-900 via-indigo-950 to-blue-900 text-white flex justify-between items-start relative overflow-hidden">
                    <div class="absolute -top-10 -left-10 w-36 h-36 bg-white/5 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-10 -right-10 w-36 h-36 bg-blue-500/15 rounded-full blur-2xl"></div>
                    
                    <div class="z-10 space-y-0.5">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-blue-500/20 rounded-full border border-blue-500/30 text-[8px] font-black uppercase tracking-wider text-blue-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                            Official Payslip
                        </div>
                        <h3 class="text-base font-black tracking-tight uppercase mt-1">LAUNDRYAN</h3>
                        <p class="text-[8px] font-bold uppercase tracking-[0.2em] text-gray-400">Digital Salary Slip</p>
                    </div>
                    
                    <div class="text-right z-10 space-y-1">
                        <div class="text-[8px] font-mono font-black bg-white/10 px-2.5 py-1 rounded-lg border border-white/10 inline-block text-blue-200"
                            x-text="activePayroll.stripe_transfer_id ? activePayroll.stripe_transfer_id : 'CASH-PAYMENT'">
                        </div>
                        <div class="text-[7px] font-black text-gray-400 uppercase tracking-widest block" x-text="'Issued: ' + (activePayroll.payment_date ? activePayroll.payment_date : 'Awaiting')"></div>
                    </div>
                </div>

                <!-- Slip Content -->
                <div class="p-5 space-y-4">
                    <!-- Employee and Period row -->
                    <div class="grid grid-cols-2 gap-4 bg-gray-50/50 p-4 rounded-xl border border-gray-100/80">
                        <div class="space-y-0.5">
                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Recipient Staff</span>
                            <span class="text-xs font-black text-gray-900 block" x-text="activeUser.name"></span>
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-gray-500">
                                <span class="material-symbols-outlined text-[11px]">badge</span>
                                <span x-text="activeUser.role === 'kurir' ? 'Courier' : 'Operations Staff'"></span>
                            </span>
                        </div>
                        <div class="text-right space-y-0.5">
                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Pay Period</span>
                            <span class="text-xs font-black text-blue-600 block" x-text="getMonthName(activePayroll.month) + ' ' + activePayroll.year"></span>
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-gray-500 justify-end">
                                <span class="material-symbols-outlined text-[11px]">fingerprint</span>
                                <span x-text="'ID: ' + (activeUser.role === 'kurir' ? 'CUR-' : 'EMP-') + String(activeUser.id).padStart(4, '0')"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Breakdown Table -->
                    <div class="space-y-2">
                        <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Earnings & Deductions Breakdown</span>
                        
                        <div class="border border-gray-150/80 rounded-xl overflow-hidden shadow-sm">
                            <table class="w-full text-[11px]">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-150 font-black text-[9px] uppercase text-gray-400 text-left">
                                        <th class="px-3.5 py-2">Component Description</th>
                                        <th class="px-3.5 py-2 text-right w-28">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr class="hover:bg-gray-50/40 transition-colors">
                                        <td class="px-3.5 py-2.5 font-bold text-gray-600">Basic Salary</td>
                                        <td class="px-3.5 py-2.5 text-right font-bold text-gray-800" x-text="formatRupiah(activePayroll.amount)"></td>
                                    </tr>
                                    <tr class="hover:bg-gray-50/40 transition-colors">
                                        <td class="px-3.5 py-2.5 font-bold text-emerald-600">Bonus & Incentives</td>
                                        <td class="px-3.5 py-2.5 text-right font-black text-emerald-600" x-text="'+ ' + formatRupiah(activePayroll.bonus)"></td>
                                    </tr>
                                    <tr class="bg-rose-50/5 hover:bg-rose-50/10 transition-colors">
                                        <td class="px-3.5 py-2.5 font-bold text-rose-600">Attendance Deductions</td>
                                        <td class="px-3.5 py-2.5 text-right font-black text-rose-600" x-text="'- ' + formatRupiah(activePayroll.potongan)"></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-blue-50/80 font-black text-xs text-blue-900 border-t border-blue-100">
                                        <td class="px-3.5 py-3 uppercase tracking-wide">Net Take Home Pay</td>
                                        <td class="px-3.5 py-3 text-right text-sm text-blue-600 font-extrabold" x-text="formatRupiah(Number(activePayroll.amount || 0) + Number(activePayroll.bonus || 0) - Number(activePayroll.potongan || 0))"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Meta Details -->
                    <div class="grid grid-cols-3 gap-2 bg-gray-50/30 p-3 rounded-xl border border-gray-100 text-[9px]">
                        <div>
                            <span class="font-bold text-gray-400 block uppercase tracking-wider">Method</span>
                            <span class="font-black text-gray-800 uppercase block mt-0.5" x-text="activePayroll.payment_method ? activePayroll.payment_method : 'N/A'"></span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-400 block uppercase tracking-wider">Paid Date</span>
                            <span class="font-black text-gray-800 block mt-0.5" x-text="activePayroll.payment_date ? activePayroll.payment_date : 'Awaiting'"></span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-400 block uppercase tracking-wider">Status</span>
                            <span class="font-black uppercase block mt-0.5 inline-flex items-center gap-1" 
                                :class="activePayroll.status === 'paid' ? 'text-emerald-600' : 'text-amber-500'">
                                <span class="h-1.5 w-1.5 rounded-full" :class="activePayroll.status === 'paid' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                <span x-text="activePayroll.status"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Signature block -->
                    <div class="flex justify-between items-end pt-4 border-t border-gray-100">
                        <div class="text-[8px] text-gray-400 max-w-[180px] leading-relaxed">
                            * Officially generated and authorized by the Laundryan finance system.
                        </div>
                        <div class="text-center w-36">
                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block mb-8">Finance Manager</span>
                            <div class="border-t border-gray-200 pt-1 font-bold text-[10px] text-gray-600">Finance Dept.</div>
                        </div>
                    </div>
                </div>

                <!-- Actions Footer -->
                <div id="payslip-actions-footer" class="p-4 border-t border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <button type="button" @click="showSlipModal = false"
                        class="px-3.5 py-1.5 bg-white border border-gray-200 text-gray-500 hover:text-gray-800 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all">
                        Close
                    </button>
                    
                    <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black uppercase tracking-widest rounded-lg shadow-md active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-sm">print</span>
                        Print Payslip
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL 3: CREATE PAYROLL RECORD (Alpine.js) -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-cloak
             style="display: none;">
            
            <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden max-h-[90vh] flex flex-col"
                 @click.away="showCreateModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                 
                <!-- Header -->
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-xs">
                            <span class="material-symbols-outlined text-[22px]">add_card</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Create New Payroll Record</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Manually add employee salary</p>
                        </div>
                    </div>
                    <button @click="showCreateModal = false" class="p-2 text-gray-400 hover:text-gray-655 rounded-xl hover:bg-gray-100/85 transition-all">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form action="{{ route('admin.payroll.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden" x-data="{ 
                    selectedUserId: '',
                    amount: 0,
                    bonus: 0,
                    potongan: 0,
                    staffList: @json($staffMembers),
                    updateSalary() {
                        let user = null;
                        for (let item of this.staffList) {
                            if (item.id == this.selectedUserId) {
                                user = item;
                                break;
                            }
                        }
                        if (user) {
                            this.amount = user.role === 'karyawan' ? 2500000 : 2000000;
                        } else {
                            this.amount = 0;
                        }
                    }
                }">
                    @csrf
                    
                    <div class="flex-1 overflow-y-auto p-6 space-y-5">
                        <!-- Select Employee -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">person</span> Select Employee / Courier
                            </label>
                            <select name="user_id" x-model="selectedUserId" @change="updateSalary()" required
                                    class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                                <option value="">-- Choose Staff Member --</option>
                                <optgroup label="Internal Employees">
                                    @foreach($staffMembers->where('role', 'karyawan') as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Couriers">
                                    @foreach($staffMembers->where('role', 'kurir') as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <!-- Base Salary -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">payments</span> Base Salary (IDR)
                            </label>
                            <input type="number" name="amount" x-model.number="amount" required min="0"
                                   class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all"
                                   placeholder="Base Salary Amount">
                        </div>

                        <!-- Bonus & Deductions -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">add_circle</span> Bonus (+)
                                </label>
                                <input type="number" name="bonus" x-model.number="bonus" min="0"
                                       class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold text-emerald-600 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">remove_circle</span> Deductions (-)
                                </label>
                                <input type="number" name="potongan" x-model.number="potongan" min="0"
                                       class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold text-rose-600 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                            </div>
                        </div>

                        <!-- Month & Year -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">calendar_month</span> Month
                                </label>
                                <select name="month" required 
                                        class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2026, $m)->format('F') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">event</span> Year
                                </label>
                                <select name="year" required 
                                        class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                        <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Payment Status & Method -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">verified_user</span> Payment Status
                                </label>
                                <select name="status" required
                                        class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                                    <option value="pending">PENDING</option>
                                    <option value="paid">PAID</option>
                                    <option value="failed">FAILED</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">account_balance_wallet</span> Payment Method
                                </label>
                                <select name="payment_method"
                                        class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                                    <option value="">Not Specified</option>
                                    <option value="cash">CASH</option>
                                    <option value="stripe">STRIPE</option>
                                    <option value="bank_transfer">BANK TRANSFER</option>
                                </select>
                            </div>
                        </div>

                        <!-- Reference ID -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">receipt</span> Payment Reference / Stripe ID
                            </label>
                            <input type="text" name="stripe_transfer_id" placeholder="CASH-xxxx or trans_xxxx"
                                   class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold font-mono text-gray-700 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                        </div>

                        <!-- Outflow Box -->
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600">account_balance</span>
                                <span class="font-bold text-blue-900 uppercase tracking-wide">Net Take Home Pay:</span>
                            </div>
                            <span class="font-black text-blue-600 text-base" x-text="formatRupiah(amount + (Number(bonus) || 0) - (Number(potongan) || 0))"></span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-2.5 bg-gray-50/50">
                        <button type="button" @click="showCreateModal = false"
                                class="py-2.5 px-6 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-black rounded-xl uppercase tracking-widest transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl uppercase tracking-widest shadow-md shadow-blue-200 hover:shadow-lg transition-all">
                            Save Payroll
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('payrollExpensesChart').getContext('2d');
                const chartData = @json($chartData);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Salary Expenses (IDR)',
                            data: chartData.data,
                            backgroundColor: 'rgba(0, 91, 192, 0.85)',
                            borderColor: '#005bc0',
                            borderWidth: 1,
                            borderRadius: 8,
                            barPercentage: 0.55
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#111827',
                                titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
                                bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13, weight: 'bold' },
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: function (context) {
                                        let val = context.raw;
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                    }
                                }
                            }
                        },
                        onClick: (event, elements) => {
                            if (elements && elements.length > 0) {
                                const index = elements[0].index;
                                const isAllYears = @json($chartYear === 'all');
                                const baseUrl = "{{ route('admin.payroll.index') }}";
                                if (isAllYears) {
                                    const selectedYear = chartData.labels[index];
                                    window.location.href = `${baseUrl}?year=${selectedYear}&month=all`;
                                } else {
                                    const selectedMonth = index + 1;
                                    window.location.href = `${baseUrl}?month=${selectedMonth}&year={{ $chartYear }}`;
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 },
                                    callback: function (value) {
                                        if (value >= 1e6) {
                                            return 'Rp ' + (value / 1e6) + 'M';
                                        } else if (value >= 1e3) {
                                            return 'Rp ' + (value / 1e3) + 'K';
                                        }
                                        return 'Rp ' + value;
                                    }
                                },
                                grid: { color: '#F3F4F6', drawBorder: false }
                            },
                            x: {
                                ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 10, weight: 'bold' } },
                                grid: { display: false, drawBorder: false }
                            }
                        }
                    }
                });
            });
        </script>
        
        <!-- Printable Payslip CSS styling for window.print() -->
        <style>
            @media print {
                /* Hide everything except the printable container */
                body * {
                    visibility: hidden;
                }
                #printablePayslipContainer, #printablePayslipContainer * {
                    visibility: visible;
                }
                #printablePayslipContainer {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    max-width: 100%;
                    border: none;
                    box-shadow: none;
                }
                /* Hide close and print buttons in printed copy */
                #payslip-actions-footer {
                    display: none !important;
                }
            }
        </style>
    @endpush
</x-app-layout>
