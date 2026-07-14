<x-app-layout>
    @php
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $statusConfig = [
            'pending' => [
                'label' => 'Pending Verification',
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'dot' => 'bg-amber-500',
                'icon' => 'hourglass_empty'
            ],
            'success' => [
                'label' => 'Success',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'icon' => 'check_circle'
            ],
            'failed' => [
                'label' => 'Failed',
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                'dot' => 'bg-rose-500',
                'icon' => 'cancel'
            ]
        ];

        $methodConfig = [
            'cash' => ['label' => 'Cash', 'icon' => 'payments', 'bg' => 'bg-slate-100 text-slate-700'],
            'transfer' => ['label' => 'Bank Transfer', 'icon' => 'account_balance_wallet', 'bg' => 'bg-blue-50 text-blue-700'],
            'bank_transfer' => ['label' => 'Bank Transfer', 'icon' => 'account_balance_wallet', 'bg' => 'bg-blue-50 text-blue-700'],
            'e-wallet' => ['label' => 'E-Wallet', 'icon' => 'account_balance_wallet', 'bg' => 'bg-purple-50 text-purple-700'],
            'stripe' => ['label' => 'Stripe Card', 'icon' => 'credit_card', 'bg' => 'bg-indigo-50 text-indigo-700'],
            'qris' => ['label' => 'QRIS', 'icon' => 'qr_code_2', 'bg' => 'bg-fuchsia-50 text-fuchsia-700']
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
             x-data="{ exportMonth: '{{ request('month', '') }}', exportYear: '{{ request('year', '') }}', exportPdfLoading: false, exportCsvLoading: false }">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Payment Management</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Manage transactions, receipts, payments, and financial reports.</p>
            </div>

            <!-- Export Buttons with period filters -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">calendar_month</span>
                    <select x-model="exportMonth" class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="">All Months</option>
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[14px] pointer-events-none">expand_more</span>
                </div>

                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">event</span>
                    <select x-model="exportYear" class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="">All Years</option>
                        @foreach($years as $yr)
                            <option value="{{ $yr }}">{{ $yr }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[14px] pointer-events-none">expand_more</span>
                </div>

                <a :href="'{{ route('admin.payments.export.pdf') }}?month='+exportMonth+'&year='+exportYear"
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

                <a :href="'{{ route('admin.payments.export.csv') }}?month='+exportMonth+'&year='+exportYear"
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

            <!-- Toast alert -->
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm" role="alert">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- ── Payment History (Statistics Bar) ── -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Card 1: Total Transactions -->
                <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" 
                   class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:scale-102 hover:shadow-md hover:border-blue-200 transition-all cursor-pointer">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-2xl">receipt_long</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Transactions</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['total_transactions']) }}</h3>
                    </div>
                </a>

                <!-- Card 2: Total Revenue -->
                <a href="{{ request()->fullUrlWithQuery(['status' => 'success']) }}" 
                   class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:scale-102 hover:shadow-md hover:border-blue-200 transition-all cursor-pointer">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Revenue</p>
                        <h3 class="text-xl font-black text-emerald-700 mt-0.5">Rp {{ number_format($stats['total_earnings'], 0, ',', '.') }}</h3>
                    </div>
                </a>

                <!-- Card 3: Success payments count -->
                <a href="{{ request()->fullUrlWithQuery(['status' => 'success']) }}" 
                   class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:scale-102 hover:shadow-md hover:border-blue-200 transition-all cursor-pointer">
                    <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-2xl">task_alt</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Success</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['status_success']) }}</h3>
                    </div>
                </a>

                <!-- Card 4: Pending payments count -->
                <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" 
                   class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:scale-102 hover:shadow-md hover:border-blue-200 transition-all cursor-pointer">
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-2xl">hourglass_empty</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pending Verification</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['status_pending']) }}</h3>
                    </div>
                </a>

                <!-- Card 5: Failed payments count -->
                <a href="{{ request()->fullUrlWithQuery(['status' => 'failed']) }}" 
                   class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:scale-102 hover:shadow-md hover:border-blue-200 transition-all cursor-pointer">
                    <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-2xl">cancel</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Failed</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['status_failed']) }}</h3>
                    </div>
                </a>
            </div>

            <!-- ── Search & Filter Bar ── -->
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                <form action="{{ route('admin.payments.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Search Query -->
                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Search Payment</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <span class="material-symbols-outlined text-sm">search</span>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3" 
                                   placeholder="Search by Payment ID, Order ID, Customer name, phone...">
                        </div>
                    </div>

                    <!-- Payment Status Filter -->
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Payment Status</label>
                        <select name="status" class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 cursor-pointer">
                            <option value="all" {{ request('status') === 'all' || !request()->has('status') ? 'selected' : '' }}>All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                            <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <!-- Payment Method Filter -->
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Payment Method</label>
                        <select name="method" class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 cursor-pointer">
                            <option value="all" {{ request('method') === 'all' || !request()->has('method') ? 'selected' : '' }}>All Methods</option>
                            <option value="transfer" {{ request('method') === 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="e-wallet" {{ request('method') === 'e-wallet' ? 'selected' : '' }}>E-Wallet</option>
                            <option value="stripe" {{ request('method') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                            <option value="qris" {{ request('method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                            <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        </select>
                    </div>

                    <!-- Period: Month -->
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Month</label>
                        <select name="month" class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 cursor-pointer">
                            <option value="" {{ !request()->filled('month') ? 'selected' : '' }}>All Months</option>
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Period: Year -->
                    <div class="col-span-6 md:col-span-2 flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Year</label>
                            <select name="year" class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 cursor-pointer">
                                <option value="" {{ !request()->filled('year') ? 'selected' : '' }}>All Years</option>
                                @foreach($years as $yr)
                                    <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black shadow-lg shadow-blue-200 flex items-center justify-center transition-all" title="Filter">
                            <span class="material-symbols-outlined text-[20px]">filter_alt</span>
                        </button>
                        <a href="{{ route('admin.payments.index') }}" class="p-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl flex items-center justify-center transition-all" title="Reset Filters">
                            <span class="material-symbols-outlined text-[20px]">restart_alt</span>
                        </a>
                    </div>
                </form>
            </div>

            <!-- ── Main Table Payments ── -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden relative"
                 x-data="{ gridLoading: false }"
                 x-init="gridLoading = false"
                 @submit.window="gridLoading = true"
                 @click.document="
                    if ($event.target.closest('a[href*=\'export\']')) return;
                    if ($event.target.closest('a[href*=\'invoice\']')) return;
                    const link = $event.target.closest('a');
                    if (link && (link.getAttribute('href')?.includes('payments') || link.closest('.pagination') || link.closest('.page-link'))) {
                        gridLoading = true;
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
                        <p class="text-xs font-black text-blue-600 uppercase tracking-widest animate-pulse">Loading Payments...</p>
                    </div>
                </div>
                <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <span>{{ $payments->total() }} Payments found</span>
                    <span>Page {{ $payments->currentPage() }} / {{ $payments->lastPage() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-4 text-left w-[180px]">Payment Identity</th>
                                <th class="px-6 py-4 text-left w-[180px]">Customer</th>
                                <th class="px-6 py-4 text-left w-[150px]">Transaction</th>
                                <th class="px-6 py-4 text-center w-[120px]">Proof</th>
                                <th class="px-6 py-4 text-center w-[200px]">Admin Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($payments as $payment)
                                @php
                                    $pConfig = $statusConfig[$payment->status] ?? $statusConfig['pending'];
                                    $mConfig = $methodConfig[$payment->payment_method] ?? ['label' => $payment->payment_method, 'icon' => 'help', 'bg' => 'bg-gray-50 text-gray-600'];
                                    
                                    // Prepare data for the billing modal
                                    $order = $payment->order;
                                    $custName = $order->customer ? $order->customer->name : 'Walk-In Guest';
                                    $custPhone = $order->customer ? $order->customer->phone : '-';
                                    $custEmail = $order->customer ? $order->customer->email : '-';
                                    $custAddress = $order->customer ? $order->customer->address : '-';
                                    $serviceName = $order->service ? $order->service->name : '-';
                                    $itemTypeName = $order->itemType ? $order->itemType->name : '-';
                                    $subtotal = $order->service_price + $order->item_price;
                                    $tax = $order->tax;
                                    $shipping = $order->shipping_cost;
                                    $total = $order->total_price;
                                    
                                    $invoiceData = json_encode([
                                        'payment_id' => $payment->id,
                                        'invoice_id' => $payment->payment_code,
                                        'order_code' => $order->order_code,
                                        'date' => $payment->payment_date ? $payment->payment_date->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-',
                                        'cust_name' => $custName,
                                        'cust_phone' => $custPhone,
                                        'cust_email' => $custEmail,
                                        'cust_address' => $custAddress,
                                        'service' => $serviceName,
                                        'item_type' => $itemTypeName,
                                        'service_price' => number_format($order->service_price, 0, ',', '.'),
                                        'item_price' => number_format($order->item_price, 0, ',', '.'),
                                        'subtotal' => number_format($subtotal, 0, ',', '.'),
                                        'tax' => number_format($tax, 0, ',', '.'),
                                        'shipping' => number_format($shipping, 0, ',', '.'),
                                        'total' => number_format($total, 0, ',', '.'),
                                        'status' => strtoupper($payment->status),
                                        'method' => strtoupper($payment->payment_method),
                                    ]);
                                @endphp
                                <tr class="hover:bg-blue-50/10 transition-all duration-150 group">
                                    
                                    <!-- A. Payment Identity -->
                                    <td class="px-6 py-4">
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.payments.show', $payment->id) }}"
                                               class="font-black text-sm text-blue-600 hover:text-blue-800 hover:underline block tracking-tight">
                                                {{ $payment->payment_code }}
                                            </a>
                                            <span class="text-[10px] text-gray-500 font-bold block mt-0.5">
                                                Order: <a href="{{ route('admin.orders.show', $payment->order_id) }}" class="text-gray-700 hover:underline font-extrabold">{{ $payment->order->order_code }}</a>
                                            </span>
                                            <span class="text-[9px] text-gray-400 font-bold uppercase mt-1 block">
                                                {{ $payment->payment_date ? $payment->payment_date->timezone('Asia/Jakarta')->format('d M Y · H:i') : ($payment->created_at ? $payment->created_at->timezone('Asia/Jakarta')->format('d M Y · H:i') : '-') }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- B. Customer Details -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2.5">
                                            @if($payment->order->customer?->photo)
                                                <img src="{{ Storage::url($payment->order->customer->photo) }}"
                                                     class="w-9 h-9 rounded-xl object-cover border border-gray-100 shadow-sm flex-shrink-0" alt="">
                                            @else
                                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-black text-sm flex items-center justify-center shadow-sm flex-shrink-0">
                                                    {{ strtoupper(substr($payment->order->customer?->name ?? 'U', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-gray-800 truncate">{{ $payment->order->customer?->name ?? 'Walk-in Guest' }}</p>
                                                <p class="text-[9px] text-gray-400 font-bold tracking-tight truncate mt-0.5">{{ $payment->order->customer?->phone ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- C. Transaction Details with inline Status changer -->
                                    <td class="px-6 py-4">
                                        <div>
                                            <span class="font-extrabold text-sm text-gray-950">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-lg text-[9px] font-bold {{ $mConfig['bg'] }}">
                                                    <span class="material-symbols-outlined text-[10px]">{{ $mConfig['icon'] }}</span>
                                                    {{ $mConfig['label'] }}
                                                </span>
                                            </div>
                                            
                                            <!-- Interactive Status Dropdown -->
                                            <form action="{{ route('admin.payments.verify', $payment->id) }}" method="POST" class="mt-1.5 block">
                                                @csrf
                                                <select name="status" onchange="this.form.submit()"
                                                        class="text-[9px] font-black uppercase tracking-wider rounded-full py-0.5 pl-2 pr-6 border cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-500 {{ $pConfig['bg'] }}">
                                                    <option value="pending" {{ $payment->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="success" {{ $payment->status === 'success' ? 'selected' : '' }}>Success</option>
                                                    <option value="failed" {{ $payment->status === 'failed' ? 'selected' : '' }}>Failed</option>
                                                </select>
                                            </form>
                                        </div>
                                    </td>

                                    <!-- D. Proof of Payment -->
                                    <td class="px-6 py-4 text-center">
                                        @if($payment->proof_path)
                                            <div class="flex flex-col items-center gap-1.5">
                                                <button onclick="openReceipt('{{ $payment->payment_code }}', '{{ Storage::url($payment->proof_path) }}')"
                                                        class="w-12 h-12 rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm hover:border-blue-400 hover:scale-105 transition-all">
                                                    <img src="{{ Storage::url($payment->proof_path) }}" class="w-full h-full object-cover p-0.5" alt="Proof">
                                                </button>
                                                <button onclick="openReceipt('{{ $payment->payment_code }}', '{{ Storage::url($payment->proof_path) }}')"
                                                        class="text-[9px] text-blue-600 hover:text-blue-800 hover:underline font-bold uppercase tracking-wider">
                                                    View Proof
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-[10px] text-gray-400 italic">No Upload</span>
                                        @endif
                                    </td>

                                    <!-- E. Admin Actions -->
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-2 justify-center items-stretch max-w-[170px] mx-auto">
                                            
                                            <!-- Quick verify if pending -->
                                            @if($payment->status === 'pending')
                                                <div class="flex items-center gap-1">
                                                    <form action="{{ route('admin.payments.verify', $payment->id) }}" method="POST" class="flex-1">
                                                        @csrf
                                                        <input type="hidden" name="status" value="success">
                                                        <button type="submit" 
                                                                onclick="return confirm('Are you sure you want to approve this payment?')"
                                                                class="w-full py-1.5 px-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[9px] font-black uppercase tracking-wider flex items-center justify-center gap-0.5 transition-all shadow-sm">
                                                            <span class="material-symbols-outlined text-[12px]">check</span> Approve
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.payments.verify', $payment->id) }}" method="POST" class="flex-1">
                                                        @csrf
                                                        <input type="hidden" name="status" value="failed">
                                                        <input type="hidden" name="admin_notes" value="Rejected by admin via quick verification.">
                                                        <button type="submit" 
                                                                onclick="return confirm('Are you sure you want to reject this payment?')"
                                                                class="w-full py-1.5 px-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[9px] font-black uppercase tracking-wider flex items-center justify-center gap-0.5 transition-all shadow-sm">
                                                            <span class="material-symbols-outlined text-[12px]">close</span> Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif

                                            <div class="flex items-center gap-1 justify-center">
                                                <!-- Detail link -->
                                                <a href="{{ route('admin.payments.show', $payment->id) }}"
                                                   class="flex-1 py-1.5 px-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[9px] font-black uppercase tracking-wider flex items-center justify-center gap-0.5 transition-all"
                                                   title="View Payment Details">
                                                    <span class="material-symbols-outlined text-[12px]">visibility</span> Details
                                                </a>

                                                <!-- Detail Invoice Modal Trigger -->
                                                <button onclick="openInvoiceModal({{ $invoiceData }})"
                                                        class="flex-1 py-1.5 px-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-[9px] font-black uppercase tracking-wider flex items-center justify-center gap-0.5 transition-all"
                                                        title="View Invoice Detail">
                                                    <span class="material-symbols-outlined text-[12px]">receipt</span> Invoice
                                                </button>

                                                <!-- Download PDF -->
                                                <a href="{{ route('admin.payments.invoice', $payment->id) }}"
                                                   class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition-all"
                                                   title="Download Invoice PDF">
                                                    <span class="material-symbols-outlined text-[14px] block">picture_as_pdf</span>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-2 text-gray-400">
                                            <span class="material-symbols-outlined text-5xl text-gray-200">payments</span>
                                            <p class="text-sm font-semibold">No payments found</p>
                                            <p class="text-xs">Try changing your search filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($payments->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- ── MODAL 1: PREVIEW BUKTI PEMBAYARAN ── -->
    <div id="receiptModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-6 w-96 max-w-full shadow-2xl relative mx-4">
            <button onclick="closeReceipt()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 id="receiptTitle" class="text-sm font-black text-gray-900 text-center mb-4 uppercase tracking-wider"></h3>
            <div class="bg-gray-50 rounded-2xl p-2 flex items-center justify-center border border-gray-100 shadow-inner overflow-hidden max-h-96">
                <img id="receiptImg" src="" class="max-w-full max-h-80 object-contain rounded-xl" alt="Bukti Transfer">
            </div>
            <div class="flex gap-3 mt-4">
                <a id="receiptDownload" href="" download
                   class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-widest text-center transition-all shadow-md flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">download</span> Download Proof
                </a>
                <button onclick="closeReceipt()" 
                        class="flex-1 py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-black uppercase tracking-widest text-center transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- ── MODAL 2: DETAIL INVOICE MODAL ── -->
    <div id="invoiceModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-6 w-[500px] max-w-full shadow-2xl relative mx-4 flex flex-col max-h-[90vh]">
            <button onclick="closeInvoiceModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
            
            <div class="flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-blue-600 text-2xl">receipt_long</span>
                <h3 class="text-lg font-black text-gray-900 tracking-tight">Invoice Details</h3>
            </div>
            
            <div class="flex-1 overflow-y-auto pr-1 space-y-4 custom-scrollbar">
                <!-- Status & ID -->
                <div class="flex justify-between items-center bg-gray-50 p-3.5 rounded-2xl border border-gray-100">
                    <div>
                        <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Invoice ID</span>
                        <span id="invId" class="text-sm font-black text-gray-800"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Order Code</span>
                        <span id="invOrderCode" class="text-sm font-black text-blue-600"></span>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block mb-0.5">Payment Date</span>
                        <span id="invDate" class="text-xs font-bold text-gray-700"></span>
                    </div>
                    <div>
                        <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block mb-0.5">Payment Method</span>
                        <span id="invMethod" class="text-xs font-bold text-gray-700"></span>
                    </div>
                </div>

                <div class="border-t border-dashed border-gray-200 my-2"></div>

                <!-- Customer info -->
                <div>
                    <h4 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Customer Details</h4>
                    <div class="bg-gray-50/50 p-3 rounded-2xl border border-gray-100/50 space-y-1">
                        <p class="text-xs font-bold text-gray-800" id="invCustName"></p>
                        <p class="text-[10px] text-gray-500 font-bold" id="invCustPhone"></p>
                        <p class="text-[10px] text-gray-500" id="invCustEmail"></p>
                        <p class="text-[10px] text-gray-400 italic mt-1" id="invCustAddress"></p>
                    </div>
                </div>

                <!-- Invoice items -->
                <div>
                    <h4 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Billing Details</h4>
                    <div class="space-y-2.5">
                        <!-- Service Name and Price -->
                        <div class="flex justify-between items-center text-xs text-gray-700">
                            <div>
                                <span class="font-bold text-gray-900" id="invService"></span>
                                <span class="text-[10px] text-gray-400 block">Service Price</span>
                            </div>
                            <span class="font-bold text-gray-800" id="invServicePrice"></span>
                        </div>

                        <!-- Item Type Name and Price -->
                        <div class="flex justify-between items-center text-xs text-gray-700">
                            <div>
                                <span class="font-bold text-gray-900" id="invItemType"></span>
                                <span class="text-[10px] text-gray-400 block">Item Price</span>
                            </div>
                            <span class="font-bold text-gray-800" id="invItemPrice"></span>
                        </div>
                        
                        <div class="border-t border-gray-100 my-1"></div>

                        <!-- Subtotal -->
                        <div class="flex justify-between items-center text-xs text-gray-600">
                            <span>Subtotal (Service + Item)</span>
                            <span class="font-bold text-gray-800" id="invSubtotal"></span>
                        </div>

                        <!-- Shipping Cost -->
                        <div class="flex justify-between items-center text-xs text-gray-600">
                            <span>Shipping Cost</span>
                            <span class="font-bold text-gray-800" id="invShipping"></span>
                        </div>

                        <!-- Tax -->
                        <div class="flex justify-between items-center text-xs text-gray-600">
                            <span>Tax</span>
                            <span class="font-bold text-gray-800" id="invTax"></span>
                        </div>

                        <div class="border-t border-dashed border-gray-200 pt-2 flex justify-between items-center">
                            <span class="text-xs font-black text-gray-900 uppercase tracking-wide">TOTAL BILL</span>
                            <span class="text-base font-black text-emerald-600" id="invTotal"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer actions -->
            <div class="flex gap-3 mt-5 pt-3 border-t border-gray-100">
                <a id="invDownloadBtn" href=""
                   class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-widest text-center transition-all shadow-md flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> Download PDF
                </a>
                <button onclick="closeInvoiceModal()"
                        class="flex-1 py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-black uppercase tracking-widest text-center transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts for Modals -->
    <script>
        // Modal Preview Receipt
        function openReceipt(code, imgUrl) {
            document.getElementById('receiptTitle').textContent = 'Proof: ' + code;
            document.getElementById('receiptImg').src = imgUrl;
            document.getElementById('receiptDownload').href = imgUrl;
            const m = document.getElementById('receiptModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeReceipt() {
            const m = document.getElementById('receiptModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        document.getElementById('receiptModal').addEventListener('click', function(e) {
            if (e.target === this) closeReceipt();
        });

        // Modal Invoice Details
        const downloadBtn = document.getElementById('invDownloadBtn');
        
        function resetDownloadBtn() {
            downloadBtn.classList.remove('pointer-events-none', 'opacity-70');
            downloadBtn.innerHTML = `
                <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> Download PDF
            `;
        }

        downloadBtn.addEventListener('click', function(e) {
            downloadBtn.classList.add('pointer-events-none', 'opacity-70');
            downloadBtn.innerHTML = `
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Downloading...</span>
            `;
            setTimeout(resetDownloadBtn, 4000);
        });

        function openInvoiceModal(data) {
            resetDownloadBtn();
            document.getElementById('invId').textContent = data.invoice_id;
            document.getElementById('invOrderCode').textContent = '#' + data.order_code;
            document.getElementById('invDate').textContent = data.date;
            document.getElementById('invMethod').textContent = data.method;
            document.getElementById('invCustName').textContent = data.cust_name;
            document.getElementById('invCustPhone').textContent = data.cust_phone;
            document.getElementById('invCustEmail').textContent = data.cust_email;
            document.getElementById('invCustAddress').textContent = 'Address: ' + data.cust_address;
            document.getElementById('invService').textContent = data.service;
            document.getElementById('invItemType').textContent = data.item_type;
            document.getElementById('invServicePrice').textContent = 'Rp ' + data.service_price;
            document.getElementById('invItemPrice').textContent = 'Rp ' + data.item_price;
            document.getElementById('invSubtotal').textContent = 'Rp ' + data.subtotal;
            document.getElementById('invShipping').textContent = 'Rp ' + data.shipping;
            document.getElementById('invTax').textContent = 'Rp ' + data.tax;
            document.getElementById('invTotal').textContent = 'Rp ' + data.total;
            
            // Set download pdf link dynamically
            const baseDownloadUrl = "{{ route('admin.payments.invoice', ':id') }}";
            const downloadUrl = baseDownloadUrl.replace(':id', data.payment_id);
            document.getElementById('invDownloadBtn').href = downloadUrl;

            const m = document.getElementById('invoiceModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeInvoiceModal() {
            resetDownloadBtn();
            const m = document.getElementById('invoiceModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        document.getElementById('invoiceModal').addEventListener('click', function(e) {
            if (e.target === this) closeInvoiceModal();
        });
    </script>
</x-app-layout>
