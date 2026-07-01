<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-gray-900 tracking-tight">
            {{ __('Payment History') }}
        </h2>
        <p class="text-xs text-gray-500 mt-1">Manage transaction logs of your laundry payments.</p>
    </x-slot>

    <div class="py-2 space-y-6">
        <!-- Active Filter Alert -->
        @if(request()->anyFilled(['status', 'period', 'method']))
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-2xl flex justify-between items-center shadow-sm"
                role="alert">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-blue-600">filter_alt</span>
                    <span class="text-sm font-bold">
                        Showing payments filtered by: 
                        @if(request()->filled('status') && request('status') !== 'all')
                            Status: <span class="underline font-black">{{ ucfirst(request('status')) }}</span>
                        @endif
                        @if(request()->filled('period') && request('period') !== 'all')
                            @php
                                $periodLabels = ['harian' => 'Hari', 'hari' => 'Hari', 'mingguan' => 'Minggu', 'minggu' => 'Minggu', 'bulanan' => 'Bulan', 'bulan' => 'Bulan', 'tahunan' => 'Tahun', 'tahun' => 'Tahun'];
                            @endphp
                            Period: <span class="underline font-black">{{ $periodLabels[request('period')] ?? request('period') }}</span>
                        @endif
                        @if(request()->filled('method') && request('method') !== 'all')
                            @php
                                $methodLabels = ['qris' => 'QRIS', 'card_online' => 'Card / Online', 'bank_transfer' => 'Bank Transfer'];
                            @endphp
                            Method: <span class="underline font-black">{{ $methodLabels[request('method')] ?? request('method') }}</span>
                        @endif
                    </span>
                </div>
                <a href="{{ route('customer.payments.index') }}"
                    class="text-xs font-black text-blue-600 hover:text-blue-800 bg-white border border-blue-100 px-3 py-1 rounded-xl shadow-sm transition-all hover:scale-105">Clear Filter</a>
            </div>
        @endif

        <!-- Statistics Grid (KPI Cards) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Card 1: Total Payments -->
            <a href="{{ route('customer.payments.index') }}"
                class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-gray-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200 text-left">
                <div class="w-12 h-12 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-gray-100 transition-colors">
                    <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Total Payments</p>
                    <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['total_count']) }}</h3>
                </div>
            </a>

            <!-- Card 2: Success Payments -->
            <a href="{{ route('customer.payments.index', ['status' => 'success']) }}"
                class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-emerald-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200 text-left">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-100 transition-colors">
                    <span class="material-symbols-outlined text-2xl">check_circle</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Success</p>
                    <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['success_count']) }}</h3>
                </div>
            </a>

            <!-- Card 3: Pending Payments -->
            <a href="{{ route('customer.payments.index', ['status' => 'pending']) }}"
                class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-yellow-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200 text-left">
                <div class="w-12 h-12 bg-yellow-50 text-yellow-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-yellow-100 transition-colors">
                    <span class="material-symbols-outlined text-2xl">pending</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Pending</p>
                    <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['pending_count']) }}</h3>
                </div>
            </a>

            <!-- Card 4: Today's Payments -->
            <a href="{{ route('customer.payments.index', ['period' => 'harian']) }}"
                class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200 text-left">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition-colors">
                    <span class="material-symbols-outlined text-2xl">today</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Hari Ini</p>
                    <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['today_count']) }}</h3>
                </div>
            </a>

            <!-- Card 5: This Month's Payments -->
            <a href="{{ route('customer.payments.index', ['period' => 'bulanan']) }}"
                class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-indigo-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200 text-left">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 transition-colors">
                    <span class="material-symbols-outlined text-2xl">calendar_month</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Bulan Ini</p>
                    <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['month_count']) }}</h3>
                </div>
            </a>

            <!-- Card 6: QRIS Payments -->
            <a href="{{ route('customer.payments.index', ['method' => 'qris']) }}"
                class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-purple-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200 text-left">
                <div class="w-12 h-12 bg-purple-50 text-purple-700 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-purple-100 transition-colors">
                    <span class="material-symbols-outlined text-2xl">qr_code_2</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">QRIS</p>
                    <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['qris_count']) }}</h3>
                </div>
            </a>

            <!-- Card 7: Card / Online -->
            <a href="{{ route('customer.payments.index', ['method' => 'card_online']) }}"
                class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-rose-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200 text-left">
                <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-rose-100 transition-colors">
                    <span class="material-symbols-outlined text-2xl">credit_card</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Card / Online</p>
                    <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['card_count']) }}</h3>
                </div>
            </a>

            <!-- Card 8: Bank Transfer -->
            <a href="{{ route('customer.payments.index', ['method' => 'bank_transfer']) }}"
                class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-cyan-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200 text-left">
                <div class="w-12 h-12 bg-cyan-50 text-cyan-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-cyan-100 transition-colors">
                    <span class="material-symbols-outlined text-2xl">account_balance</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Transfer Bank</p>
                    <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['transfer_count']) }}</h3>
                </div>
            </a>
        </div>

        <!-- Dropdown Filter Container -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100"
             x-data="{ openFilter: null }">
            <p class="text-[10px] font-black uppercase text-gray-400 tracking-wider mb-4">Filter Transactions</p>
            <div class="flex flex-wrap gap-3">

                <!-- Period Filter -->
                <div class="relative">
                    <button type="button"
                        @click="openFilter = (openFilter === 'period') ? null : 'period'"
                        @keydown.escape.window="openFilter = null"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold border transition-all
                            {{ $period !== 'all' ? 'bg-brand border-brand text-white shadow-sm' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                        <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                        <span>
                            @php
                                $periodLabel = ['all' => 'All Time', 'harian' => 'Hari', 'mingguan' => 'Minggu', 'bulanan' => 'Bulan', 'tahunan' => 'Tahun'];
                            @endphp
                            {{ $periodLabel[$period] ?? 'All Time' }}
                        </span>
                        <span class="material-symbols-outlined text-[14px] transition-transform duration-200"
                              :class="openFilter === 'period' ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="openFilter === 'period'"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         @click.outside="openFilter = null"
                         class="absolute left-0 top-full mt-2 w-44 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 overflow-hidden py-1"
                         x-cloak>
                        @foreach([
                            'all'     => ['label' => 'All Time',  'icon' => 'all_inclusive'],
                            'harian'  => ['label' => 'Hari',      'icon' => 'today'],
                            'mingguan'=> ['label' => 'Minggu',    'icon' => 'date_range'],
                            'bulanan' => ['label' => 'Bulan',     'icon' => 'calendar_month'],
                            'tahunan' => ['label' => 'Tahun',     'icon' => 'event'],
                        ] as $key => $opt)
                            <a href="{{ route('customer.payments.index', array_merge(request()->except('page'), ['period' => $key])) }}"
                               @click="openFilter = null"
                               class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold transition-all
                                   {{ $period === $key ? 'bg-brand/5 text-brand' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="material-symbols-outlined text-[16px]">{{ $opt['icon'] }}</span>
                                {{ $opt['label'] }}
                                @if($period === $key)
                                    <span class="material-symbols-outlined text-[14px] ml-auto">check</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="relative">
                    <button type="button"
                        @click="openFilter = (openFilter === 'status') ? null : 'status'"
                        @keydown.escape.window="openFilter = null"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold border transition-all
                            {{ $status !== 'all' ? 'bg-brand border-brand text-white shadow-sm' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                        <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                        <span>
                            @php
                                $statusLabel = ['all' => 'All Status', 'success' => 'Success', 'pending' => 'Pending'];
                            @endphp
                            {{ $statusLabel[$status] ?? 'All Status' }}
                        </span>
                        <span class="material-symbols-outlined text-[14px] transition-transform duration-200"
                              :class="openFilter === 'status' ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="openFilter === 'status'"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         @click.outside="openFilter = null"
                         class="absolute left-0 top-full mt-2 w-44 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 overflow-hidden py-1"
                         x-cloak>
                        @foreach([
                            'all'     => ['label' => 'All Status', 'icon' => 'rule'],
                            'success' => ['label' => 'Success',    'icon' => 'check_circle'],
                            'pending' => ['label' => 'Pending',    'icon' => 'schedule'],
                        ] as $key => $opt)
                            <a href="{{ route('customer.payments.index', array_merge(request()->except('page'), ['status' => $key])) }}"
                               @click="openFilter = null"
                               class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold transition-all
                                   {{ $status === $key ? 'bg-brand/5 text-brand' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="material-symbols-outlined text-[16px]">{{ $opt['icon'] }}</span>
                                {{ $opt['label'] }}
                                @if($status === $key)
                                    <span class="material-symbols-outlined text-[14px] ml-auto">check</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Payment Method Filter -->
                <div class="relative">
                    <button type="button"
                        @click="openFilter = (openFilter === 'method') ? null : 'method'"
                        @keydown.escape.window="openFilter = null"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold border transition-all
                            {{ $method !== 'all' ? 'bg-brand border-brand text-white shadow-sm' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                        <span class="material-symbols-outlined text-[16px]">payments</span>
                        <span>
                            @php
                                $methodLabel = ['all' => 'All Methods', 'qris' => 'QRIS', 'card_online' => 'Card / Online', 'bank_transfer' => 'Bank Transfer'];
                            @endphp
                            {{ $methodLabel[$method] ?? 'All Methods' }}
                        </span>
                        <span class="material-symbols-outlined text-[14px] transition-transform duration-200"
                              :class="openFilter === 'method' ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="openFilter === 'method'"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         @click.outside="openFilter = null"
                         class="absolute left-0 top-full mt-2 w-48 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 overflow-hidden py-1"
                         x-cloak>
                        @foreach([
                            'all'          => ['label' => 'All Methods',   'icon' => 'credit_card'],
                            'qris'         => ['label' => 'QRIS',          'icon' => 'qr_code_2'],
                            'card_online'  => ['label' => 'Card / Online', 'icon' => 'contactless'],
                            'bank_transfer'=> ['label' => 'Bank Transfer', 'icon' => 'account_balance'],
                        ] as $key => $opt)
                            <a href="{{ route('customer.payments.index', array_merge(request()->except('page'), ['method' => $key])) }}"
                               @click="openFilter = null"
                               class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold transition-all
                                   {{ $method === $key ? 'bg-brand/5 text-brand' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="material-symbols-outlined text-[16px]">{{ $opt['icon'] }}</span>
                                {{ $opt['label'] }}
                                @if($method === $key)
                                    <span class="material-symbols-outlined text-[14px] ml-auto">check</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Reset (shown only when a filter is active) -->
                @if($period !== 'all' || $status !== 'all' || $method !== 'all')
                    <a href="{{ route('customer.payments.index') }}"
                       class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-all">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        Reset Filters
                    </a>
                @endif
            </div>
        </div>

        <!-- Payments Log List Card -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 text-left">
                <h3 class="text-base font-bold text-gray-900">Transaction Listing</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-400 font-black uppercase tracking-wider border-b border-gray-100">
                            <th class="p-4">Payment Code</th>
                            <th class="p-4">Order Code</th>
                            <th class="p-4">Service</th>
                            <th class="p-4">Payment Date</th>
                            <th class="p-4">Amount</th>
                            <th class="p-4">Method</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="p-4 font-mono font-bold text-gray-900">{{ $payment->payment_code }}</td>
                                <td class="p-4">
                                    <a href="{{ route('customer.orders.show', $payment->order_id) }}" class="font-bold text-brand hover:underline">
                                        {{ $payment->order->order_code }}
                                    </a>
                                </td>
                                <td class="p-4 font-semibold text-gray-700">
                                    {{ $payment->order->service->name }} ({{ $payment->order->itemType->name }})
                                </td>
                                <td class="p-4 text-gray-500 font-medium">
                                    {{ $payment->payment_date ? $payment->payment_date->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                                </td>
                                <td class="p-4 font-extrabold text-gray-950">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="p-4">
                                    @if($payment->payment_method === 'qris')
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200 rounded-full">
                                            QRIS
                                        </span>
                                    @elseif($payment->payment_method === 'stripe')
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-full">
                                            CARD / ONLINE
                                        </span>
                                    @elseif(in_array($payment->payment_method, ['transfer', 'bank_transfer']))
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200 rounded-full">
                                            BANK TRANSFER
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider bg-gray-100 text-gray-700 border border-gray-200 rounded-full">
                                            {{ strtoupper($payment->payment_method) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($payment->status === 'success')
                                        <span class="px-2.5 py-0.5 text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full uppercase">
                                            SUCCESS
                                        </span>
                                    @elseif($payment->status === 'pending')
                                        <span class="px-2.5 py-0.5 text-[10px] font-black bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full uppercase">
                                            PENDING
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 text-[10px] font-black bg-red-50 text-red-700 border border-red-200 rounded-full uppercase">
                                            {{ $payment->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <div class="inline-flex gap-2">
                                        <a href="{{ route('customer.orders.invoice', $payment->order_id) }}" class="bg-brand/5 hover:bg-brand/10 text-brand font-bold py-1.5 px-3 rounded-lg flex items-center gap-1 transition-all">
                                            <span class="material-symbols-outlined text-sm">download</span> Invoice
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-12 text-center text-gray-400 italic font-medium text-xs bg-white">
                                    No payment transactions recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="p-4 bg-gray-50 border-t border-gray-100">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
