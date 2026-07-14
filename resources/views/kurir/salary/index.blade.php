<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
             style="font-family: 'Plus Jakarta Sans', sans-serif;"
             x-data="{ exportMonth: 'all', exportYear: 'all', loadingPdf: false, loadingCsv: false }">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">
                    {{ __('Salary & Earnings') }}
                </h2>
                <p class="text-xs text-gray-500 font-bold mt-1 uppercase tracking-wider">
                    View your monthly payroll slips, manage Stripe/E-Wallet payouts, and export reports
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <!-- Month Filter for Export -->
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

                <!-- Year Filter for Export -->
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

                <!-- Export PDF -->
                <a :href="'{{ route('kurir.salary.export.pdf') }}?month=' + exportMonth + '&year=' + exportYear"
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
                
                <!-- Export CSV -->
                <a :href="'{{ route('kurir.salary.export.csv') }}?month=' + exportMonth + '&year=' + exportYear"
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

    <!-- Page Wrapper for Alpine States -->
    <div class="py-6" style="font-family: 'Plus Jakarta Sans', sans-serif;" x-data="{
        loading: false,
        loadingPayslipPdf: false,
        showWithdrawModal: false,
        showSlipModal: false,
        activePayroll: {},
        activeTab: 'history',
        paymentMethod: 'stripe',
        stripeAccountId: '{{ auth()->user()->stripe_account_id }}',
        ewalletProvider: 'dana',
        ewalletPhone: '{{ auth()->user()->phone }}',
        formatRupiah(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(value);
        },
        getMonthName(m) {
            return ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'][m - 1];
        },
        showToast: {{ session('success') ? 'true' : 'false' }},
        toastType: 'success',
        toastTitle: '{{ session('toast_title') ?: 'Salary Withdrawn Successfully' }}',
        toastMessage: '{{ session('success') }}'
    }"
    x-init="
        if (showToast) {
            setTimeout(() => { showToast = false; }, 5000);
        }
    ">
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

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center gap-3" role="alert">
                    <span class="material-symbols-outlined text-rose-600">error</span>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Top Grid: Latest Salary Card & Payout Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Salary Info Card -->
                <div class="lg:col-span-2 bg-white shadow-sm rounded-3xl border border-gray-100 overflow-hidden flex flex-col justify-between">
                    <div class="p-6 md:p-8">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-lg uppercase tracking-wider border border-blue-100 shadow-sm">
                                        Current Statement
                                    </span>
                                    @if($statementPayroll)
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black px-2.5 py-0.5 rounded-full border border-emerald-100 bg-emerald-50 text-emerald-700 shadow-xs uppercase tracking-tighter">
                                            <span class="material-symbols-outlined text-[12px] font-bold">check_circle</span>
                                            Withdrawn
                                        </span>
                                    @elseif($statementType === 'none')
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black px-2.5 py-0.5 rounded-full border border-gray-200 bg-gray-50 text-gray-500 shadow-xs uppercase tracking-tighter">
                                            <span class="material-symbols-outlined text-[12px] font-bold">info</span>
                                            No Withdrawal Yet
                                        </span>
                                    @endif
                                </div>
                                @if($statementPayroll)
                                    <h3 class="text-xl font-black text-gray-900 mt-3">
                                        Payroll Period: {{ \Carbon\Carbon::create($statementPayroll->year, $statementPayroll->month, 1)->format('F Y') }}
                                    </h3>
                                    <p class="text-[10px] font-bold text-emerald-600 mt-1.5 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                        Last successfully withdrawn on {{ \Carbon\Carbon::parse($statementPayroll->payment_date)->format('d F Y, H:i') }}
                                    </p>
                                @else
                                    <h3 class="text-xl font-black text-gray-900 mt-3">No Withdrawn Salary Yet</h3>
                                    <p class="text-[10px] font-bold text-gray-400 mt-1.5">Your last successful withdrawal will appear here.</p>
                                @endif
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Detailed breakdown of earnings and deductions</p>
                            </div>
                            @if($statementPayroll)
                                <div class="text-right">
                                    <div class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Payroll ID</div>
                                    <div class="text-xs font-black text-blue-600 mt-0.5">PAY-{{ sprintf('%04d', $statementPayroll->id) }}</div>
                                </div>
                            @endif
                        </div>

                        @if($statementPayroll)
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 py-6 border-t border-b border-gray-100">
                                <div>
                                    <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Basic Salary</div>
                                    <div class="text-lg font-black text-gray-800 mt-1">Rp {{ number_format($statementPayroll->amount, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Bonus / Incentives</div>
                                    <div class="text-lg font-black text-emerald-600 mt-1">+Rp {{ number_format($statementPayroll->bonus, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-[9px] font-black text-rose-600 uppercase tracking-widest">Deductions</div>
                                    <div class="text-lg font-black text-rose-600 mt-1">-Rp {{ number_format($statementPayroll->potongan, 0, ',', '.') }}</div>
                                    @if(($statementPayroll->alpha_deduction ?? 0) > 0)
                                        <p class="text-[8px] font-bold text-rose-500 mt-1">
                                            Alpha penalty: {{ $statementPayroll->alpha_count }} days (5%)
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Payment Status</div>
                                    <span class="inline-flex items-center gap-1 text-[9px] font-black px-2.5 py-0.5 mt-2 rounded-full border shadow-xs uppercase tracking-tighter {{ $statementPayroll->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100' }}">
                                        <span class="material-symbols-outlined text-[12px] font-bold">{{ $statementPayroll->status === 'paid' ? 'check_circle' : 'pending_actions' }}</span>
                                        {{ $statementPayroll->status }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Payment Method</div>
                                    <div class="text-xs font-black text-gray-700 uppercase tracking-wider mt-2">{{ $statementPayroll->payment_method ?: 'Not Withdrawn' }}</div>
                                </div>
                                <div>
                                    <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Payment Date</div>
                                    <div class="text-xs font-bold text-gray-700 mt-2">
                                        {{ $statementPayroll->payment_date ? \Carbon\Carbon::parse($statementPayroll->payment_date)->format('d F Y, H:i') : '-' }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="py-12 text-center text-gray-400 font-bold border-t border-gray-100">
                                <span class="material-symbols-outlined text-4xl text-gray-200 block mb-2">receipt_long</span>
                                No withdrawn salary records found yet.
                            </div>
                        @endif
                    </div>
                    @if($statementPayroll)
                        <div class="bg-gray-50/50 p-6 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Net Payout Amount</span>
                                <div class="text-2xl font-black text-gray-900 mt-0.5">
                                    Rp {{ number_format($statementPayroll->amount + $statementPayroll->bonus - $statementPayroll->potongan, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <button @click="activePayroll = @js($statementPayroll); showSlipModal = true"
                                    class="w-full sm:w-auto text-center px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 active:scale-95 transition-all shadow-sm">
                                    View Full slip
                                </button>
                                <div class="px-4 py-2.5 bg-emerald-50 text-emerald-700 text-xs font-black uppercase tracking-widest rounded-xl border border-emerald-150 flex items-center gap-1.5 shadow-sm">
                                    <span class="material-symbols-outlined text-[16px] font-bold">check_circle</span>
                                    Withdrawn
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Salary Withdrawal Panel -->
                <div class="bg-gradient-to-br from-blue-700 to-blue-900 shadow-xl rounded-3xl p-6 md:p-8 text-white flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/10 rounded-full blur-3xl"></div>
                    
                    <div>
                        <div class="flex items-center gap-2 mb-6">
                            <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                            <h3 class="text-lg font-black uppercase tracking-widest">Withdrawal Hub</h3>
                        </div>
                        
                        <p class="text-xs text-blue-150 font-bold leading-relaxed mb-6">
                            Claim your finalized salary directly to your Stripe Connected account or popular Local E-Wallets.
                        </p>

                        <div class="space-y-4">
                            <div class="bg-white/10 border border-white/10 p-4 rounded-2xl">
                                <div class="text-[9px] font-black text-blue-200 uppercase tracking-widest">Total Earnings Withdrawn</div>
                                <div class="text-xl font-black mt-1">
                                    Rp {{ number_format($withdrawals->sum(fn($w) => $w->amount + $w->bonus - $w->potongan), 0, ',', '.') }}
                                </div>
                            </div>
                            
                            <div class="bg-white/10 border border-white/10 p-4 rounded-2xl">
                                <div class="text-[9px] font-black text-blue-200 uppercase tracking-widest">Linked Stripe Connected ID</div>
                                <div class="text-xs font-black mt-1 tracking-wider">
                                    {{ auth()->user()->stripe_account_id ?: 'NOT CONNECTED' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($withdrawablePayrolls->count())
                        <div class="mt-6 space-y-3 max-h-52 overflow-y-auto">
                            @foreach($withdrawablePayrolls as $withdrawable)
                                @php
                                    $withdrawableNet = $withdrawable->amount + $withdrawable->bonus - $withdrawable->potongan;
                                @endphp
                                <div class="bg-white/10 border border-white/10 p-4 rounded-2xl">
                                    <div class="text-[9px] font-black text-blue-200 uppercase tracking-widest">Available for Withdrawal</div>
                                    <div class="text-xs font-black mt-1">
                                        {{ \Carbon\Carbon::create($withdrawable->year, $withdrawable->month, 1)->format('F Y') }}
                                        &mdash; Rp {{ number_format($withdrawableNet, 0, ',', '.') }}
                                    </div>
                                    <button @click="activePayroll = @js($withdrawable); showWithdrawModal = true"
                                        class="mt-3 w-full text-center py-2.5 bg-white text-blue-700 hover:bg-blue-50 text-[10px] font-black uppercase tracking-widest rounded-xl hover:shadow-lg active:scale-95 transition-all shadow-md">
                                        Withdraw Salary
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-8 text-center text-xs font-bold text-blue-200 bg-white/5 border border-white/5 py-3 rounded-xl uppercase tracking-widest">
                            No pending withdrawals
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tabs Section: History Tables -->
            <div class="bg-white shadow-sm rounded-3xl border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center gap-3">
                        <button @click="activeTab = 'history'" 
                            :class="activeTab === 'history' ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                            class="px-4 py-2 text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                            Payroll History
                        </button>
                        <button @click="activeTab = 'withdrawals'" 
                            :class="activeTab === 'withdrawals' ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                            class="px-4 py-2 text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                            Withdrawal History
                        </button>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">All Records Statement</span>
                    </div>
                </div>

                <!-- Tab 1: Payroll History -->
                <div x-show="activeTab === 'history'" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-400 text-[10px] font-black uppercase tracking-widest">
                                <th class="p-6">Payroll ID</th>
                                <th class="p-6">Payroll Period</th>
                                <th class="p-6">Payment Date</th>
                                <th class="p-6">Payment Method</th>
                                <th class="p-6">Net Salary</th>
                                <th class="p-6">Payment Status</th>
                                <th class="p-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs font-bold text-gray-700">
                            @forelse($payrolls as $pay)
                                @php
                                    $net = $pay->amount + $pay->bonus - $pay->potongan;
                                @endphp
                                <tr class="hover:bg-blue-50/20 transition-colors">
                                    <td class="p-6 text-blue-600">PAY-{{ sprintf('%04d', $pay->id) }}</td>
                                    <td class="p-6 font-black text-gray-900">{{ \Carbon\Carbon::create($pay->year, $pay->month, 1)->format('F Y') }}</td>
                                    <td class="p-6 text-gray-500">{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="p-6 uppercase text-gray-500">{{ $pay->payment_method ?: '-' }}</td>
                                    <td class="p-6 font-black text-gray-900">Rp {{ number_format($net, 0, ',', '.') }}</td>
                                    <td class="p-6">
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black px-2.5 py-0.5 rounded-full border shadow-xs uppercase tracking-tighter {{ $pay->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100' }}">
                                            <span class="material-symbols-outlined text-[12px] font-bold">{{ $pay->status === 'paid' ? 'check_circle' : 'pending_actions' }}</span>
                                            {{ $pay->status }}
                                        </span>
                                    </td>
                                    <td class="p-6 text-right">
                                        <button @click="activePayroll = @js($pay); showSlipModal = true"
                                            class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-100 rounded-lg hover:shadow-xs active:scale-95 transition-all uppercase tracking-wider text-[10px] font-black">
                                            View Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-gray-400 font-bold">
                                        No payroll history available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Tab 2: Withdrawal History -->
                <div x-show="activeTab === 'withdrawals'" class="overflow-x-auto" x-cloak>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-400 text-[10px] font-black uppercase tracking-widest">
                                <th class="p-6">Withdrawal ID</th>
                                <th class="p-6">Withdrawal Date</th>
                                <th class="p-6">Payment Method</th>
                                <th class="p-6">Amount</th>
                                <th class="p-6">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs font-bold text-gray-700">
                            @forelse($withdrawals as $wdl)
                                @php
                                    $net = $wdl->amount + $wdl->bonus - $wdl->potongan;
                                @endphp
                                <tr class="hover:bg-blue-50/20 transition-colors">
                                    <td class="p-6 text-gray-800">{{ $wdl->stripe_transfer_id ?: 'WDL-ID-' . $wdl->id }}</td>
                                    <td class="p-6 text-gray-500">{{ $wdl->payment_date ? \Carbon\Carbon::parse($wdl->payment_date)->format('d F Y, H:i') : '-' }}</td>
                                    <td class="p-6 uppercase font-black text-gray-900">{{ $wdl->payment_method }}</td>
                                    <td class="p-6 font-black text-gray-900">Rp {{ number_format($net, 0, ',', '.') }}</td>
                                    <td class="p-6">
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black px-2.5 py-0.5 rounded-full border border-emerald-100 bg-emerald-50 text-emerald-700 shadow-xs uppercase tracking-tighter">
                                            <span class="material-symbols-outlined text-[12px] font-bold">check_circle</span>
                                            SUCCESS
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-12 text-center text-gray-400 font-bold">
                                        No withdrawal history found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODAL: Withdrawal Form -->
            <div x-show="showWithdrawModal" 
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="display: none;"
                x-cloak>
                
                <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden"
                    @click.away="showWithdrawModal = false"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                    
                    <div class="p-6 border-b border-gray-100 flex items-center gap-3 bg-gradient-to-r from-gray-50 to-white">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                            <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Withdrawal Setup</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Select transfer destination</p>
                        </div>
                        <button @click="showWithdrawModal = false" class="ml-auto text-gray-400 hover:text-gray-650 transition-colors p-1.5 rounded-lg hover:bg-gray-100">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form :action="'{{ route('kurir.salary.withdraw', ['payroll' => '__ID__']) }}'.replace('__ID__', activePayroll.id)" method="POST" class="p-6 space-y-6" @submit="loading = true">
                        @csrf
                        
                        <!-- Payout Amount Info -->
                        <div class="bg-gray-50 border border-gray-100 p-4 rounded-2xl flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Withdrawal Amount</span>
                            <span class="text-lg font-black text-blue-600" x-text="formatRupiah(activePayroll.amount + activePayroll.bonus - activePayroll.potongan)"></span>
                        </div>

                        <!-- Payment Method Toggle -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2.5">Payout Method</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label :class="paymentMethod === 'stripe' ? 'border-blue-600 bg-blue-50 text-blue-600 ring-2 ring-blue-50' : 'border-gray-200 text-gray-500 hover:bg-gray-50'"
                                    class="border-2 rounded-2xl p-4 flex flex-col items-center justify-center cursor-pointer transition-all gap-2 text-center">
                                    <input type="radio" name="payment_method" value="stripe" x-model="paymentMethod" class="hidden">
                                    <span class="material-symbols-outlined text-2xl">credit_card</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider">Stripe</span>
                                </label>
                                <label :class="paymentMethod === 'e-wallet' ? 'border-blue-600 bg-blue-50 text-blue-600 ring-2 ring-blue-50' : 'border-gray-200 text-gray-500 hover:bg-gray-50'"
                                    class="border-2 rounded-2xl p-4 flex flex-col items-center justify-center cursor-pointer transition-all gap-2 text-center">
                                    <input type="radio" name="payment_method" value="e-wallet" x-model="paymentMethod" class="hidden">
                                    <span class="material-symbols-outlined text-2xl">payments</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider">E-Wallet</span>
                                </label>
                            </div>
                        </div>

                        <!-- Stripe Details -->
                        <div x-show="paymentMethod === 'stripe'" class="space-y-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Stripe Connected Account ID</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-[18px]">account_circle</span>
                                    <input type="text" name="stripe_account_id" x-model="stripeAccountId" placeholder="acct_1x..." required
                                        class="w-full text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-50 shadow-inner">
                                </div>
                                <span class="text-[9px] text-gray-400 font-bold block mt-1 uppercase tracking-wide">Enter your Stripe account ID for direct payout transfer.</span>
                            </div>
                        </div>

                        <!-- E-Wallet Details -->
                        <div x-show="paymentMethod === 'e-wallet'" class="space-y-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-cloak>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">E-Wallet Provider</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-[18px]">account_balance</span>
                                    <select name="ewallet_provider" x-model="ewalletProvider" required
                                        class="w-full text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-10 pr-10 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-50 shadow-inner appearance-none cursor-pointer">
                                        <option value="dana">DANA</option>
                                        <option value="gopay">GoPay</option>
                                        <option value="ovo">OVO</option>
                                        <option value="linkaja">LinkAja</option>
                                    </select>
                                    <span class="material-symbols-outlined absolute right-3 top-2.5 text-gray-400 text-[18px] pointer-events-none">expand_more</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Registered Phone Number</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-[18px]">phone_iphone</span>
                                    <input type="text" name="ewallet_phone" x-model="ewalletPhone" placeholder="08..." required
                                        class="w-full text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-50 shadow-inner">
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="showWithdrawModal = false"
                                class="w-1/3 text-center py-3 bg-white border border-gray-200 text-gray-700 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 active:scale-95 transition-all">
                                Cancel
                            </button>
                            <button type="submit"
                                class="w-2/3 text-center py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg active:scale-95 transition-all">
                                Submit Payout
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL: Payslip Detail View -->
            <div x-show="showSlipModal" 
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="display: none;"
                x-cloak>
                
                <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden"
                    @click.away="showSlipModal = false"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                    
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                                <span class="material-symbols-outlined text-[20px]">receipt</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900">Payslip Slip</h3>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Official Salary Records</p>
                            </div>
                        </div>
                        <button @click="showSlipModal = false" class="text-gray-400 hover:text-gray-650 transition-colors p-1.5 rounded-lg hover:bg-gray-100">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <!-- Slip content layout -->
                    <div class="p-6 space-y-6">
                        <!-- Courier Header -->
                        <div class="flex justify-between items-start bg-gray-50 border border-gray-100 p-4 rounded-2xl">
                            <div>
                                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Recipient</div>
                                <div class="text-sm font-black text-gray-800 mt-0.5">{{ auth()->user()->name }}</div>
                                <div class="text-[9px] font-bold text-gray-500 uppercase tracking-wider mt-0.5">Role: {{ auth()->user()->role }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Payroll ID</div>
                                <div class="text-sm font-black text-blue-600 mt-0.5" x-text="'PAY-' + String(activePayroll.id).padStart(4, '0')"></div>
                                <div class="text-[8px] font-bold text-gray-500 uppercase mt-0.5" x-text="getMonthName(activePayroll.month) + ' ' + activePayroll.year"></div>
                            </div>
                        </div>

                        <!-- Detailed Calculations -->
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Earnings breakdown</h4>
                            
                            <div class="border border-gray-100 rounded-2xl overflow-hidden divide-y divide-gray-100 text-xs font-bold text-gray-700">
                                <div class="flex justify-between p-4 bg-white">
                                    <span>Base Salary</span>
                                    <span class="text-gray-900" x-text="formatRupiah(activePayroll.amount)"></span>
                                </div>
                                <div class="flex justify-between p-4 bg-white">
                                    <span class="text-emerald-600">Bonuses & Incentives</span>
                                    <span class="text-emerald-600" x-text="'+' + formatRupiah(activePayroll.bonus)"></span>
                                </div>
                                <div class="flex justify-between p-4 bg-white">
                                    <span class="text-rose-600">Other Deductions</span>
                                    <span class="text-rose-600" x-text="'-' + formatRupiah(Math.max(0, (activePayroll.potongan || 0) - (activePayroll.alpha_deduction || 0)))"></span>
                                </div>
                                <template x-if="(activePayroll.alpha_deduction || 0) > 0">
                                    <div class="flex justify-between p-4 bg-rose-50/30">
                                        <div>
                                            <span class="text-rose-700">Alpha Penalty (5%)</span>
                                            <span class="block text-[9px] font-medium text-rose-500 mt-0.5" x-text="(activePayroll.alpha_count || 0) + ' unexcused absences'"></span>
                                        </div>
                                        <span class="text-rose-700" x-text="'-' + formatRupiah(activePayroll.alpha_deduction)"></span>
                                    </div>
                                </template>
                                <div class="flex justify-between p-4 bg-gray-50/50 font-black text-gray-900">
                                    <span>Net Salary</span>
                                    <span class="text-blue-600 text-sm" x-text="formatRupiah(activePayroll.amount + activePayroll.bonus - activePayroll.potongan)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Audit Info -->
                        <div class="grid grid-cols-2 gap-4 text-xs font-bold text-gray-550">
                            <div class="border border-gray-100 p-3 rounded-xl">
                                <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Withdrawal status</div>
                                <span class="inline-flex items-center gap-1 text-[8px] font-black px-2 py-0.5 mt-1 rounded-full border shadow-xs uppercase tracking-tighter"
                                    :class="activePayroll.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100'">
                                    <span class="material-symbols-outlined text-[10px] font-bold" x-text="activePayroll.status === 'paid' ? 'check_circle' : 'pending_actions'"></span>
                                    <span x-text="activePayroll.status"></span>
                                </span>
                            </div>
                            <div class="border border-gray-100 p-3 rounded-xl">
                                <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Payout Method</div>
                                <span class="text-gray-900 block mt-1 uppercase" x-text="activePayroll.payment_method || '-'"></span>
                            </div>
                            <div class="border border-gray-100 p-3 rounded-xl">
                                <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Payment Settlement Date</div>
                                <span class="text-gray-900 block mt-1" x-text="activePayroll.payment_date ? new Date(activePayroll.payment_date).toLocaleString('id-ID', {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit'}) : '-'"></span>
                            </div>
                            <div class="border border-gray-100 p-3 rounded-xl">
                                <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Reference Code</div>
                                <span class="text-gray-900 block mt-1 text-[10px] truncate" x-text="activePayroll.stripe_transfer_id || '-'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action buttons -->
                    <div class="bg-gray-50 p-6 border-t border-gray-100 flex items-center justify-end gap-3">
                        <a :href="'{{ route('kurir.salary.payslip.pdf', ['payroll' => '__PAYROLL__']) }}'.replace('__PAYROLL__', activePayroll.id)"
                            @click="if(!loadingPayslipPdf) { loadingPayslipPdf = true; setTimeout(() => loadingPayslipPdf = false, 4000); }"
                            :class="loadingPayslipPdf ? 'opacity-70 pointer-events-none cursor-not-allowed' : 'hover:bg-rose-700'"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-rose-600 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-md">
                            <template x-if="!loadingPayslipPdf">
                                <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                            </template>
                            <template x-if="loadingPayslipPdf">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="loadingPayslipPdf ? 'Downloading...' : 'Download PDF'"></span>
                        </a>
                        <button type="button" @click="showSlipModal = false"
                            class="px-4 py-2 bg-white border border-gray-200 text-gray-700 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 active:scale-95 transition-all">
                            Close
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
