<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-gray-900 tracking-tight">
            {{ __('Payment History') }}
        </h2>
        <p class="text-xs text-gray-500 mt-1">Manage transaction logs of your laundry payments.</p>
    </x-slot>

    <div class="py-2 space-y-6">


        <!-- Statistics Grid (KPI Cards) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Card 0: Total Payment Amount (Nominal) -->
            <div class="col-span-2 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-5 shadow-sm flex items-center gap-4 text-white hover:shadow-md transition-all duration-200">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-2xl text-white">payments</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest leading-tight">Total Payment Amount</p>
                    <h3 class="text-2xl font-black mt-0.5">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</h3>
                </div>
            </div>

            <!-- Card 1: Total Payments -->
            <a href="{{ route('customer.payments.index') }}"
                class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-gray-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200 text-left">
                <div class="w-12 h-12 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-gray-100 transition-colors">
                    <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Total Transactions</p>
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
            <a href="{{ route('customer.payments.index', ['period' => 'hari']) }}"
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
            <a href="{{ route('customer.payments.index', ['period' => 'bulan']) }}"
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

        <!-- Filter Form Container -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100" x-data="{ period: '{{ request('period', 'all') }}' }">
            <p class="text-[10px] font-black uppercase text-gray-400 tracking-wider mb-4">Filter Transactions</p>
            <form action="{{ route('customer.payments.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                
                <!-- Period Type Selection -->
                <div :class="period === 'all' ? 'col-span-12 sm:col-span-6 md:col-span-4' : 'col-span-12 sm:col-span-6 md:col-span-2'">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Period Type</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <span class="material-symbols-outlined text-sm">calendar_month</span>
                        </span>
                        <select name="period" x-model="period"
                            class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-brand focus:border-brand py-3 appearance-none">
                            <option value="all">All Time</option>
                            <option value="hari">Hari</option>
                            <option value="minggu">Minggu</option>
                            <option value="bulan">Bulan</option>
                            <option value="tahun">Tahun</option>
                        </select>
                    </div>
                </div>

                <!-- Status Filter -->
                <div :class="period === 'all' ? 'col-span-12 sm:col-span-6 md:col-span-3' : 'col-span-12 sm:col-span-6 md:col-span-2'">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Status</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <span class="material-symbols-outlined text-sm">filter_alt</span>
                        </span>
                        <select name="status"
                            class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-brand focus:border-brand py-3 appearance-none">
                            <option value="all" {{ request('status') === 'all' || !request()->has('status') ? 'selected' : '' }}>All Status</option>
                            <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                </div>

                <!-- Payment Method Filter -->
                <div :class="period === 'all' ? 'col-span-12 sm:col-span-6 md:col-span-3' : 'col-span-12 sm:col-span-6 md:col-span-2'">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Method</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <span class="material-symbols-outlined text-sm">payments</span>
                        </span>
                        <select name="method"
                            class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-brand focus:border-brand py-3 appearance-none">
                            <option value="all" {{ request('method') === 'all' || !request()->has('method') ? 'selected' : '' }}>All Methods</option>
                            <option value="qris" {{ request('method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                            <option value="card_online" {{ request('method') === 'card_online' ? 'selected' : '' }}>Card / Online</option>
                            <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Period Sub-Inputs -->
                <!-- Hari Sub-Input (Date Picker) -->
                <div class="col-span-12 sm:col-span-6 md:col-span-4" x-show="period === 'hari'" x-cloak>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Tanggal</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <span class="material-symbols-outlined text-sm">event</span>
                        </span>
                        <input type="date" name="date_val" value="{{ request('date_val', now()->toDateString()) }}"
                            class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-brand focus:border-brand py-3">
                    </div>
                </div>

                <!-- Minggu Sub-Input (1-4 minggu select) -->
                <div class="col-span-12 sm:col-span-6 md:col-span-4" x-show="period === 'minggu'" x-cloak>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Rentang Minggu</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <span class="material-symbols-outlined text-sm">date_range</span>
                        </span>
                        <select name="week_val"
                            class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-brand focus:border-brand py-3 appearance-none">
                            <option value="1" {{ request('week_val') == '1' ? 'selected' : '' }}>1 Minggu Terakhir</option>
                            <option value="2" {{ request('week_val') == '2' ? 'selected' : '' }}>2 Minggu Terakhir</option>
                            <option value="3" {{ request('week_val') == '3' ? 'selected' : '' }}>3 Minggu Terakhir</option>
                            <option value="4" {{ request('week_val') == '4' ? 'selected' : '' }}>4 Minggu Terakhir</option>
                        </select>
                    </div>
                </div>

                <!-- Bulan Sub-Input (1-12 select) -->
                <div class="col-span-12 sm:col-span-6 md:col-span-4" x-show="period === 'bulan'" x-cloak>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Bulan</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <span class="material-symbols-outlined text-sm">calendar_view_month</span>
                        </span>
                        <select name="month_val"
                            class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-brand focus:border-brand py-3 appearance-none">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('month_val', now()->month) == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Tahun Sub-Input (select) -->
                <div class="col-span-12 sm:col-span-6 md:col-span-4" x-show="period === 'tahun'" x-cloak>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Tahun</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <span class="material-symbols-outlined text-sm">calendar_today</span>
                        </span>
                        <select name="year_val"
                            class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-brand focus:border-brand py-3 appearance-none">
                            @for ($y = now()->year; $y >= now()->year - 4; $y--)
                                <option value="{{ $y }}" {{ request('year_val', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 sm:col-span-6 md:col-span-2 flex gap-2">
                    <button type="submit"
                        class="flex-1 py-3 bg-brand hover:bg-blue-700 text-white rounded-2xl text-xs font-black shadow-lg shadow-blue-200 uppercase tracking-widest flex items-center justify-center gap-1.5 transition-all">
                        <span class="material-symbols-outlined text-[16px]">filter_alt</span> Filter
                    </button>
                    <a href="{{ route('customer.payments.index') }}"
                        class="py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center transition-all"
                        title="Reset Filters">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Payments Log List Card -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 text-left">
                <h3 class="text-base font-bold text-gray-900">Transaction Listing</h3>
            </div>

            <!-- Record count bar -->
            <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                <span>{{ $payments->total() }} Payments Found</span>
                <span>Page {{ $payments->currentPage() }} / {{ $payments->lastPage() }}</span>
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
                                    <div class="inline-flex gap-2" x-data="{ loading: false }">
                                        <a href="{{ route('customer.orders.invoice', $payment->order_id) }}"
                                           @click.prevent="
                                               if (loading) return;
                                               loading = true;
                                               fetch($el.href)
                                                   .then(response => {
                                                       if (!response.ok) throw new Error('Download failed');
                                                       return response.blob();
                                                   })
                                                   .then(blob => {
                                                       const url = window.URL.createObjectURL(blob);
                                                       const a = document.createElement('a');
                                                       a.href = url;
                                                       a.download = 'Invoice-{{ $payment->order->order_code }}.pdf';
                                                       document.body.appendChild(a);
                                                       a.click();
                                                       a.remove();
                                                       window.URL.revokeObjectURL(url);
                                                   })
                                                   .catch(err => alert('Failed to download invoice.'))
                                                   .finally(() => { loading = false; });
                                           "
                                           class="bg-brand/5 hover:bg-brand/10 text-brand font-bold py-1.5 px-3 rounded-lg flex items-center gap-1 transition-all">
                                            <span class="material-symbols-outlined text-sm" :class="loading ? 'animate-spin' : ''" x-text="loading ? 'sync' : 'download'">download</span>
                                            <span x-text="loading ? 'Downloading...' : 'Invoice'">Invoice</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-2 text-gray-400">
                                        <span class="material-symbols-outlined text-5xl text-gray-200">payments</span>
                                        <p class="text-sm font-semibold text-gray-800">Transaksi tidak ditemukan</p>
                                        <p class="text-xs">Silakan sesuaikan filter pencarian atau periode Anda.</p>
                                    </div>
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
