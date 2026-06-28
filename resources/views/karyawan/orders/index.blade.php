<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Order Management</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Monitor, assign couriers, and
                    track laundry progress in real‑time.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6"
         x-data="{ 
            gridLoading: false,
            showToast: {{ session('success') ? 'true' : 'false' }},
            toastMessage: '{{ session('success', '') }}',
            triggerToast(msg) {
                this.toastMessage = msg;
                this.showToast = true;
                setTimeout(() => { this.showToast = false; }, 5000);
            }
         }"
         x-init="
            gridLoading = false;
            if (showToast) {
                setTimeout(() => { showToast = false; }, 5000);
            }
            window.addEventListener('karyawan-order-status-success', (e) => {
                triggerToast(e.detail?.message || 'Order status updated successfully.');
            });
            document.addEventListener('karyawan-order-status-success', (e) => {
                triggerToast(e.detail?.message || 'Order status updated successfully.');
            });
         "
         @submit.window="if (!$event.defaultPrevented) gridLoading = true"
         @click.document="
            const link = $event.target.closest('a');
            if (link) {
                const href = link.getAttribute('href') || link.getAttribute(':href') || '';
                if (href.includes('export')) return;
                if (href.includes('orders') || link.closest('.pagination') || link.closest('.page-link')) {
                    gridLoading = true;
                }
            }
         ">
        <div class="max-w-[92rem] mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Toast alert -->
            <div x-show="showToast" 
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed top-6 right-6 z-50 max-w-sm w-full bg-emerald-50 border border-emerald-200 rounded-3xl p-5 shadow-2xl text-emerald-800 flex items-center justify-between overflow-hidden" x-cloak>
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-600/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100/50 border border-emerald-200 flex items-center justify-center shadow-inner">
                        <span class="material-symbols-outlined text-emerald-600 text-xl">check_circle</span>
                    </div>
                    <div>
                        <h4 class="font-black text-xs uppercase tracking-wider">Changes Saved</h4>
                        <p class="text-[11px] text-emerald-700 font-medium mt-0.5" x-text="toastMessage"></p>
                    </div>
                </div>
                <button @click="showToast = false" class="text-emerald-600/60 hover:text-emerald-800 transition-colors p-2 rounded-xl hover:bg-emerald-100/50 relative z-10">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            @if(isset($selectedCourier) && $selectedCourier)
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-2xl flex justify-between items-center shadow-sm"
                    role="alert">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-600">badge</span>
                        <span class="text-sm font-bold">Showing orders assigned to Courier: <span
                                class="underline font-black">{{ $selectedCourier->name }}</span></span>
                    </div>
                    <a href="{{ route('karyawan.orders.index') }}"
                        class="text-xs font-black text-blue-600 hover:text-blue-800 bg-white border border-blue-100 px-3 py-1 rounded-xl shadow-sm transition-all hover:scale-105">Clear
                        Filter</a>
                </div>
            @endif

            @if(request()->anyFilled(['service_id', 'date', 'start_date', 'end_date', 'period']))
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-2xl flex justify-between items-center shadow-sm"
                    role="alert">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-600">filter_alt</span>
                        <span class="text-sm font-bold">
                            Showing orders filtered by: 
                            @if(isset($selectedService) && $selectedService)
                                Service: <span class="underline font-black">{{ $selectedService->name }}</span>
                            @endif
                            @if(request()->filled('date'))
                                Date: <span class="underline font-black">{{ Carbon\Carbon::parse(request('date'))->format('d M Y') }}</span>
                            @endif
                            @if(request()->filled('start_date') && request()->filled('end_date'))
                                Date Range: <span class="underline font-black">{{ Carbon\Carbon::parse(request('start_date'))->format('d M Y') }} - {{ Carbon\Carbon::parse(request('end_date'))->format('d M Y') }}</span>
                            @endif
                            @if(request()->filled('period') && !request()->anyFilled(['date', 'start_date']))
                                Period: <span class="underline font-black uppercase">{{ request('period') }}</span>
                            @endif
                        </span>
                    </div>
                    <a href="{{ route('karyawan.orders.index') }}"
                        class="text-xs font-black text-blue-600 hover:text-blue-800 bg-white border border-blue-100 px-3 py-1 rounded-xl shadow-sm transition-all hover:scale-105">Clear
                        Filter</a>
                </div>
            @endif

            <!-- Statistics Grid (KPI Cards) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Card 1: Total Orders -->
                <a href="{{ route('karyawan.orders.index') }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-gray-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-gray-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">receipt_long</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Total
                            Orders</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5 total-orders-stat">{{ number_format($stats['total_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 2: Today's Orders -->
                <a href="{{ route('karyawan.orders.index', ['filter_period' => 'today']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">today</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Today's
                            Orders</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5 today-orders-stat">{{ number_format($stats['today_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 3: Unassigned Courier -->
                <a href="{{ route('karyawan.orders.index', ['courier_assigned' => 'unassigned']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-amber-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-amber-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">local_shipping</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">
                            Unassigned</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5 unassigned-orders-stat">{{ number_format($stats['unassigned_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 4: Active Processing -->
                <a href="{{ route('karyawan.orders.index', ['status' => 'active_processing']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-indigo-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">sync</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">
                            Processing</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5 processing-orders-stat">{{ number_format($stats['active_processing_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 5: Arrived at Laundry -->
                <a href="{{ route('karyawan.orders.index', ['status' => 'arrived_at_laundry']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-orange-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-orange-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">store</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Arrived
                            Laundry</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5 arrived-laundry-stat">{{ number_format($stats['arrived_at_laundry_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 6: Ready for Delivery -->
                <a href="{{ route('karyawan.orders.index', ['status' => 'ready_for_delivery']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-emerald-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">inventory_2</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Ready
                            Deliver</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5 ready-delivery-stat">{{ number_format($stats['ready_delivery_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 7: Out for Delivery -->
                <a href="{{ route('karyawan.orders.index', ['status' => 'delivering']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-sky-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-sky-50 text-sky-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-sky-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">departure_board</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">
                            Delivering</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5 delivering-orders-stat">{{ number_format($stats['delivering_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 8: Completed Orders -->
                <a href="{{ route('karyawan.orders.index', ['status' => 'completed']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-teal-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-teal-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">task_alt</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Completed
                        </p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5 completed-orders-stat">{{ number_format($stats['completed_count']) }}</h3>
                    </div>
                </a>
            </div>

            <!-- ── Search / Filter bar matching User page ── -->
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                <form action="{{ route('karyawan.orders.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Search query -->
                    <div class="col-span-12 md:col-span-6">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Search
                            Query</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <span class="material-symbols-outlined text-sm">search</span>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3"
                                placeholder="Search by code, customer name, phone number, address...">
                        </div>
                    </div>

                    <!-- Status select -->
                    <div class="col-span-12 sm:col-span-6 md:col-span-3">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Order
                            Status</label>
                        <select name="status"
                            class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3">
                            <option value="all" {{ request('status') === 'all' || !request()->has('status') ? 'selected' : '' }}>All Statuses</option>
                            <option value="active_processing" {{ request('status') === 'active_processing' ? 'selected' : '' }}>Active Processing</option>
                            <option value="arrived_at_laundry" {{ request('status') === 'arrived_at_laundry' ? 'selected' : '' }}>Arrived at Laundry</option>
                            <option value="washing" {{ request('status') === 'washing' ? 'selected' : '' }}>Washing</option>
                            <option value="drying_ironing" {{ request('status') === 'drying_ironing' ? 'selected' : '' }}>Drying & Ironing</option>
                            <option value="packing" {{ request('status') === 'packing' ? 'selected' : '' }}>Packing</option>
                            <option value="ready_for_delivery" {{ request('status') === 'ready_for_delivery' ? 'selected' : '' }}>Ready for Delivery</option>
                            <option value="delivering" {{ request('status') === 'delivering' ? 'selected' : '' }}>Delivering</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <!-- Action buttons matching User style -->
                    <div class="col-span-12 sm:col-span-6 md:col-span-3 flex items-end gap-2">
                        <button type="submit"
                            class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black shadow-lg shadow-blue-200 uppercase tracking-widest flex items-center justify-center gap-1.5 transition-all">
                            <span class="material-symbols-outlined text-[16px]">filter_alt</span> Filter
                        </button>
                        <a href="{{ route('karyawan.orders.index') }}"
                            class="py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center transition-all"
                            title="Reset Filters">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        </a>
                    </div>
                </form>
            </div>

            <!-- ── Table Ledger ── -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden relative">
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
                        <p class="text-xs font-black text-blue-600 uppercase tracking-widest animate-pulse">Load Orders</p>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <span class="orders-total-count">{{ $orders->total() }} Orders found</span>
                    <span>Page {{ $orders->currentPage() }} / {{ $orders->lastPage() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-4 text-left w-[200px]">Order &amp; Date</th>
                                <th class="px-6 py-4 text-left w-[180px]">Customer</th>
                                <th class="px-6 py-4 text-left w-[180px] whitespace-nowrap">Service specs</th>
                                <th class="px-6 py-4 text-left w-[200px]">Kurir Assignment</th>
                                <th class="px-6 py-4 text-left min-w-[100px] w-[100px]">Interactive Progress</th>
                                <th class="px-6 py-4 text-center w-[120px]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($orders as $order)
                                @php
                                    $statusColors = [
                                        'pending_payment' => ['dot' => 'bg-gray-400', 'badge' => 'bg-gray-100 text-gray-600'],
                                        'waiting_pickup' => ['dot' => 'bg-blue-400', 'badge' => 'bg-blue-50 text-blue-700'],
                                        'picking_up' => ['dot' => 'bg-blue-500', 'badge' => 'bg-blue-50 text-blue-700'],
                                        'picked_up' => ['dot' => 'bg-blue-600', 'badge' => 'bg-blue-50 text-blue-700'],
                                        'in_transit_to_laundry' => ['dot' => 'bg-yellow-500', 'badge' => 'bg-yellow-50 text-yellow-700'],
                                        'arrived_at_laundry' => ['dot' => 'bg-orange-500', 'badge' => 'bg-orange-50 text-orange-700'],
                                        'washing' => ['dot' => 'bg-cyan-500', 'badge' => 'bg-cyan-50 text-cyan-700'],
                                        'drying_ironing' => ['dot' => 'bg-teal-500', 'badge' => 'bg-teal-50 text-teal-700'],
                                        'packing' => ['dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-50 text-emerald-700'],
                                        'ready_for_delivery' => ['dot' => 'bg-lime-500', 'badge' => 'bg-lime-50 text-lime-700'],
                                        'delivering' => ['dot' => 'bg-sky-500', 'badge' => 'bg-sky-50 text-sky-700'],
                                        'completed' => ['dot' => 'bg-green-500', 'badge' => 'bg-green-50 text-green-700'],
                                        'cancelled' => ['dot' => 'bg-red-400', 'badge' => 'bg-red-50 text-red-700'],
                                    ];
                                    $sc = $statusColors[$order->status] ?? ['dot' => 'bg-gray-300', 'badge' => 'bg-gray-100 text-gray-500'];

                                    // 7 operational timeline steps mapping
                                    $progressMap = [
                                        'arrived_at_laundry' => 1,
                                        'washing' => 2,
                                        'drying_ironing' => 3,
                                        'packing' => 4,
                                        'ready_for_delivery' => 5,
                                        'delivering' => 6,
                                        'completed' => 7,
                                    ];
                                    $currentStep = $progressMap[$order->status] ?? 0;

                                    $progressSteps = [
                                        'arrived_at_laundry' => 'Arrived',
                                        'washing' => 'Washing',
                                        'drying_ironing' => 'Drying/Ironing',
                                        'packing' => 'Packing',
                                        'ready_for_delivery' => 'Ready',
                                        'delivering' => 'Delivering',
                                        'completed' => 'Completed',
                                    ];
                                @endphp
                                <tr id="order-row-{{ $order->id }}" data-order-status="{{ $order->status }}" class="hover:bg-blue-50/20 transition-all duration-150 group">

                                    {{-- Col 1: QR & ID --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-start gap-3">
                                            @if(!empty($qrCodes[$order->id]))
                                                <button
                                                    onclick="openQr('{{ $order->order_code }}','{{ $qrCodes[$order->id] }}')"
                                                    class="flex-shrink-0 w-11 h-11 rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm hover:border-blue-400 hover:shadow-md hover:scale-105 transition-all">
                                                    <img src="{{ $qrCodes[$order->id] }}"
                                                        class="w-full h-full object-contain p-0.5" alt="QR">
                                                </button>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <a href="{{ route('karyawan.orders.show', $order->id) }}"
                                                        class="font-black text-sm text-blue-600 hover:text-blue-800 hover:underline truncate block tracking-tight">
                                                        {{ $order->order_code }}
                                                    </a>
                                                    @if(session('success') && session('action_type') === 'order_updated' && session('target_order_id') == $order->id)
                                                        <span x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                                                            class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[8px] font-bold rounded-md shrink-0">
                                                            ✓ Successfully Updated
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-[9px] text-gray-400 font-bold uppercase mt-0.5">
                                                    {{ $order->created_at->format('d M Y · H:i') }}
                                                </p>
                                                <span
                                                    data-order-status-badge
                                                    class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 rounded-full text-[9px] font-black uppercase {{ $sc['badge'] }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                                    {{ str_replace('_', ' ', $order->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Col 2: Customer --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2.5">
                                            @if($order->customer?->photo)
                                                <img src="{{ Storage::url($order->customer->photo) }}"
                                                    class="w-9 h-9 rounded-xl object-cover border border-gray-100 shadow-sm flex-shrink-0"
                                                    alt="">
                                            @else
                                                <div
                                                    class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-black text-sm flex items-center justify-center shadow-sm flex-shrink-0">
                                                    {{ strtoupper(substr($order->customer?->name ?? 'U', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-gray-800 truncate">
                                                    {{ $order->customer?->name ?? 'Walk-in Guest' }}</p>
                                                <p
                                                    class="text-[9px] text-gray-400 font-bold tracking-tight truncate mt-0.5">
                                                    {{ $order->customer?->phone ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Col 3: Specs --}}
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-black text-gray-800">{{ $order->service?->name ?? '-' }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold mt-0.5">
                                            {{ $order->itemType?->name ?? '-' }}</p>
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            @if($order->soap)
                                                <span
                                                    class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[9px] font-bold rounded-lg border border-blue-100">
                                                    🧼 {{ Str::limit($order->soap, 12) }}
                                                </span>
                                            @endif
                                            @if($order->fragrance)
                                                <span
                                                    class="px-2 py-0.5 bg-purple-50 text-purple-600 text-[9px] font-bold rounded-lg border border-purple-100">
                                                    🌸 {{ Str::limit($order->fragrance, 12) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Col 4: Courier Assignment (Read-only) --}}
                                    <td class="px-6 py-4 space-y-2">
                                        <div class="flex flex-col gap-1">
                                            <label
                                                class="text-[8px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1">
                                                <span
                                                    class="material-symbols-outlined text-[12px] text-blue-500 font-bold">directions_run</span>
                                                Pickup Courier
                                            </label>
                                            <div
                                                class="bg-gray-50 border border-gray-200 rounded-xl text-[10px] font-bold text-gray-700 py-1 px-1.5">
                                                {{ $order->pickupCourier?->name ?? 'Unassigned' }}
                                            </div>
                                        </div>

                                        <div class="flex flex-col gap-1">
                                            <label
                                                class="text-[8px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1">
                                                <span
                                                    class="material-symbols-outlined text-[12px] text-emerald-500 font-bold">local_shipping</span>
                                                Delivery Courier
                                            </label>
                                            <div
                                                class="bg-gray-50 border border-gray-200 rounded-xl text-[10px] font-bold text-gray-700 py-1 px-1.5">
                                                {{ $order->deliveryCourier?->name ?? 'Unassigned' }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Col 5: Clickable Progress Timeline --}}
                                    <td class="px-6 py-4 min-w-[900px] w-[900px]">
                                        <div class="relative flex items-center justify-between w-full py-1">
                                            <!-- Line Background -->
                                            <div class="absolute left-4 right-4 top-[12px] h-[2px] bg-gray-100 z-0"></div>
                                            <!-- Active Color overlay line -->
                                            <div data-progress-line class="absolute left-4 top-[12px] h-[2px] bg-emerald-400 z-10 transition-all duration-300"
                                                style="width: calc({{ $currentStep > 1 ? (($currentStep - 1) / 6) * 100 : 0 }}%);">
                                            </div>

                                            @php
                                                $stepIndex = 1;
                                            @endphp
                                            @foreach($progressSteps as $statusVal => $stepLabel)
                                                                            @php
                                                                                $isDone = $currentStep > $stepIndex;
                                                                                $isActive = $currentStep === $stepIndex;
                                                                                $employeeEditableSteps = ['arrived_at_laundry', 'washing', 'drying_ironing', 'packing', 'ready_for_delivery'];
                                                                                $isEditableStep = in_array($statusVal, $employeeEditableSteps);
                                                                                $currentStepIndex = $stepIndex;
                                                                                $stepIndex++;
                                                                            @endphp
                                                                            <div class="flex flex-col items-center flex-1 relative z-20" data-progress-step="{{ $statusVal }}" data-progress-index="{{ $currentStepIndex }}">
                                                                                @if($isEditableStep)
                                                                                <form action="{{ route('karyawan.orders.status', $order->id) }}" method="POST" data-karyawan-status-form
                                                                                    class="inline" title="Click to update order to: {{ $stepLabel }}">
                                                                                    @csrf
                                                                                    <input type="hidden" name="status" value="{{ $statusVal }}">
                                                                                    <button type="submit"
                                                                                        class="w-6 h-6 rounded-full flex items-center justify-center text-[9px] font-black transition-all hover:scale-125 hover:shadow-lg relative z-30
                                                                                                       {{ $isDone ? 'bg-emerald-500 text-white' :
                                                ($isActive ? 'bg-blue-600 text-white ring-2 ring-blue-200' :
                                                    'bg-white border border-gray-200 text-gray-400 hover:bg-gray-50') }}">
                                                                                        @if($isDone) ✓ @else {{ $loop->iteration }} @endif
                                                                                    </button>
                                                                                </form>
                                                                                @else
                                                                                <span
                                                                                    class="w-6 h-6 rounded-full flex items-center justify-center text-[9px] font-black relative z-30 cursor-not-allowed
                                                                                                   {{ $isDone ? 'bg-emerald-500 text-white' :
                                            ($isActive ? 'bg-blue-600 text-white ring-2 ring-blue-200' :
                                                'bg-white border border-gray-200 text-gray-300') }}"
                                                                                    title="{{ $stepLabel }} (read-only)">
                                                                                    @if($isDone) ✓ @else {{ $loop->iteration }} @endif
                                                                                </span>
                                                                                @endif
                                                                                <span data-progress-label
                                                                                    class="text-[9px] font-black mt-2.5 uppercase tracking-wider text-center whitespace-nowrap leading-none
                                                                                                 {{ $isActive ? 'text-blue-600 font-black scale-105' : ($isDone ? 'text-emerald-600' : 'text-gray-400') }}">
                                                                                    {{ $stepLabel }}
                                                                                </span>
                                                                                <div data-status-updated-container class="h-6 mt-1 flex items-center justify-center">
                                                                                    @if(session('success') && session('action_type') === 'status_updated' && session('target_order_id') == $order->id && $isActive)
                                                                                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                                                                                            x-transition:enter="transition ease-out duration-200"
                                                                                            x-transition:enter-start="opacity-0 scale-95"
                                                                                            x-transition:enter-end="opacity-100 scale-100"
                                                                                            x-transition:leave="transition ease-in duration-150"
                                                                                            x-transition:leave-start="opacity-100 scale-100"
                                                                                            x-transition:leave-end="opacity-0 scale-95"
                                                                                            class="inline-flex items-center gap-0.5 px-1 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[8px] font-bold rounded-md scale-90 whitespace-nowrap">
                                                                                            <span>Status Updated</span>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                            @endforeach
                                        </div>
                                    </td>

                                    {{-- Col 6: Actions --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- View details --}}
                                            <a href="{{ route('karyawan.orders.show', $order->id) }}"
                                                class="p-1.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition-colors"
                                                title="Detail">
                                                <span class="material-symbols-outlined text-[16px] block">visibility</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-2 text-gray-400">
                                            <span class="material-symbols-outlined text-5xl text-gray-200">inbox</span>
                                            <p class="text-sm font-semibold">No orders found</p>
                                            <p class="text-xs">Try adjusting your filters to find orders.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination links --}}
                @if($orders->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- HUGE Zoom Modal QR Code --}}
    <div id="qrModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-6 w-80 shadow-2xl relative">
            <button onclick="closeQr()"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 id="qrTitle" class="text-sm font-black text-gray-900 text-center mb-4 uppercase tracking-wider"></h3>
            <div
                class="bg-gray-50 rounded-2xl p-4 flex items-center justify-center border border-gray-100 shadow-inner">
                <img id="qrImg" src="" class="w-64 h-64 object-contain" alt="QR Code">
            </div>
            <p class="text-center text-[10px] text-gray-400 mt-4 leading-relaxed font-bold uppercase tracking-wide">
                Scan QR to instantly access operational details on standard scanner, mobile web browser or mobile app.
            </p>
        </div>
    </div>

    <script>
        function openQr(code, src) {
            document.getElementById('qrTitle').textContent = 'QR Code: ' + code;
            document.getElementById('qrImg').src = src;
            const m = document.getElementById('qrModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        function closeQr() {
            const m = document.getElementById('qrModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }
        document.getElementById('qrModal').addEventListener('click', function (e) {
            if (e.target === this) closeQr();
        });
    </script>
    @include('karyawan.partials.order-status-sync')
</x-app-layout>