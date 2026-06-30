<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Order History</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Monitor your laundry progress and track orders in real‑time.</p>
            </div>
            <a href="{{ route('customer.orders.create') }}" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-brand to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3.5 px-6 rounded-2xl shadow-[0_10px_20px_rgba(0,91,192,0.15)] transition-all active:scale-[0.98]">
                <span class="material-symbols-outlined text-[20px]">add</span>
                New Order
            </a>
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
         "
         @submit.window="if (!$event.defaultPrevented) gridLoading = true"
         @click.document="
            const link = $event.target.closest('a');
            if (link) {
                const href = link.getAttribute('href') || link.getAttribute(':href') || '';
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
                        <h4 class="font-black text-xs uppercase tracking-wider">Success</h4>
                        <p class="text-[11px] text-emerald-700 font-medium mt-0.5" x-text="toastMessage"></p>
                    </div>
                </div>
                <button @click="showToast = false" class="text-emerald-600/60 hover:text-emerald-800 transition-colors p-2 rounded-xl hover:bg-emerald-100/50 relative z-10">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            @if(request()->anyFilled(['status', 'search', 'courier_assigned', 'filter_period']))
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-2xl flex justify-between items-center shadow-sm"
                    role="alert">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-600">filter_alt</span>
                        <span class="text-sm font-bold">
                            Showing orders filtered by: 
                            @if(request()->filled('search'))
                                Search: <span class="underline font-black">"{{ request('search') }}"</span>
                            @endif
                            @if(request()->filled('status') && request('status') !== 'all')
                                Status: <span class="underline font-black">{{ str_replace('_', ' ', request('status')) }}</span>
                            @endif
                            @if(request()->input('courier_assigned') === 'unassigned')
                                Courier: <span class="underline font-black">Unassigned</span>
                            @endif
                            @if(request()->input('filter_period') === 'today')
                                Period: <span class="underline font-black">Today</span>
                            @endif
                        </span>
                    </div>
                    <a href="{{ route('customer.orders.index') }}"
                        class="text-xs font-black text-blue-600 hover:text-blue-800 bg-white border border-blue-100 px-3 py-1 rounded-xl shadow-sm transition-all hover:scale-105">Clear Filter</a>
                </div>
            @endif

            <!-- Statistics Grid (KPI Cards) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Card 1: Total Orders -->
                <a href="{{ route('customer.orders.index') }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-gray-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-gray-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">receipt_long</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Total Orders</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['total_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 2: Today's Orders -->
                <a href="{{ route('customer.orders.index', ['filter_period' => 'today']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">today</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Today's Orders</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['today_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 3: Unassigned Courier -->
                <a href="{{ route('customer.orders.index', ['courier_assigned' => 'unassigned']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-amber-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-amber-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">local_shipping</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Unassigned</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['unassigned_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 4: Active Processing -->
                <a href="{{ route('customer.orders.index', ['status' => 'active_processing']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-indigo-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">sync</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Processing</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['active_processing_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 5: Arrived at Laundry -->
                <a href="{{ route('customer.orders.index', ['status' => 'arrived_at_laundry']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-orange-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-orange-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">store</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Arrived Laundry</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['arrived_at_laundry_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 6: Ready for Delivery -->
                <a href="{{ route('customer.orders.index', ['status' => 'ready_for_delivery']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-emerald-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">inventory_2</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Ready Deliver</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['ready_delivery_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 7: Out for Delivery -->
                <a href="{{ route('customer.orders.index', ['status' => 'delivering']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-sky-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-sky-50 text-sky-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-sky-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">departure_board</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Delivering</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['delivering_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 8: Completed Orders -->
                <a href="{{ route('customer.orders.index', ['status' => 'completed']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-teal-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-teal-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">task_alt</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Completed</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5">{{ number_format($stats['completed_count']) }}</h3>
                    </div>
                </a>
            </div>

            <!-- Search / Filter bar -->
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                <form action="{{ route('customer.orders.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Search query -->
                    <div class="col-span-12 md:col-span-6">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Search Query</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <span class="material-symbols-outlined text-sm">search</span>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3"
                                placeholder="Search by order code, pickup or delivery address...">
                        </div>
                    </div>

                    <!-- Status select -->
                    <div class="col-span-12 sm:col-span-6 md:col-span-3">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Order Status</label>
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

                    <!-- Action buttons -->
                    <div class="col-span-12 sm:col-span-6 md:col-span-3 flex items-end gap-2">
                        <button type="submit"
                            class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black shadow-lg shadow-blue-200 uppercase tracking-widest flex items-center justify-center gap-1.5 transition-all">
                            <span class="material-symbols-outlined text-[16px]">filter_alt</span> Filter
                        </button>
                        <a href="{{ route('customer.orders.index') }}"
                            class="py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center transition-all"
                            title="Reset Filters">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table Ledger -->
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
                    <span>{{ $orders->total() }} Orders found</span>
                    <span>Page {{ $orders->currentPage() }} / {{ $orders->lastPage() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-4 text-left w-[200px]">Order &amp; Date</th>
                                <th class="px-6 py-4 text-left w-[180px]">Payment Info</th>
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

                                    {{-- Col 1: Order & Date --}}
                                    <td class="px-6 py-4">
                                        <div class="min-w-0">
                                            <a href="{{ route('customer.orders.show', $order->id) }}"
                                                class="font-black text-sm text-blue-600 hover:text-blue-800 hover:underline truncate block tracking-tight">
                                                {{ $order->order_code }}
                                            </a>
                                            <p class="text-[9px] text-gray-400 font-bold uppercase mt-0.5">
                                                {{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }} WIB
                                            </p>
                                            <span
                                                class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 rounded-full text-[9px] font-black uppercase {{ $sc['badge'] }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                                {{ str_replace('_', ' ', $order->status) }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Col 2: Payment Info --}}
                                    <td class="px-6 py-4">
                                        <div class="min-w-0">
                                            <p class="text-xs font-black text-gray-800 uppercase">
                                                {{ $order->payment_method ?: 'Not Specified' }}
                                            </p>
                                            <div class="mt-1.5">
                                                @if($order->payment_status === 'paid')
                                                    <span class="px-2 py-0.5 text-[9px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                                                        PAID
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 text-[9px] font-black bg-rose-50 text-rose-700 border border-rose-200 rounded-full">
                                                        UNPAID
                                                    </span>
                                                @endif
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

                                    {{-- Col 4: Courier Assignment --}}
                                    <td class="px-6 py-4 space-y-2">
                                        <div class="flex flex-col gap-1">
                                            <label
                                                class="text-[8px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[12px] text-blue-500 font-bold">directions_run</span>
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
                                                <span class="material-symbols-outlined text-[12px] text-emerald-500 font-bold">local_shipping</span>
                                                Delivery Courier
                                            </label>
                                            <div
                                                class="bg-gray-50 border border-gray-200 rounded-xl text-[10px] font-bold text-gray-700 py-1 px-1.5">
                                                {{ $order->deliveryCourier?->name ?? 'Unassigned' }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Col 5: Progress Timeline (Read-only for Customer) --}}
                                    <td class="px-6 py-4 min-w-[900px] w-[900px]">
                                        <div class="relative flex items-center justify-between w-full py-1">
                                            <!-- Line Background -->
                                            <div class="absolute left-4 right-4 top-[12px] h-[2px] bg-gray-100 z-0"></div>
                                            <!-- Active Color overlay line -->
                                            <div class="absolute left-4 top-[12px] h-[2px] bg-emerald-400 z-10 transition-all duration-300"
                                                style="width: calc({{ $currentStep > 1 ? (($currentStep - 1) / 6) * 100 : 0 }}%);">
                                            </div>

                                            @php
                                                $stepIndex = 1;
                                            @endphp
                                            @foreach($progressSteps as $statusVal => $stepLabel)
                                                @php
                                                    $isDone = $currentStep > $stepIndex;
                                                    $isActive = $currentStep === $stepIndex;
                                                    $stepIndex++;
                                                @endphp
                                                <div class="flex flex-col items-center flex-1 relative z-20">
                                                    <span
                                                        class="w-6 h-6 rounded-full flex items-center justify-center text-[9px] font-black relative z-30
                                                                       {{ $isDone ? 'bg-emerald-500 text-white' :
                                                                ($isActive ? 'bg-blue-600 text-white ring-2 ring-blue-200' :
                                                                    'bg-white border border-gray-200 text-gray-300') }}"
                                                        title="{{ $stepLabel }}">
                                                        @if($isDone) ✓ @else {{ $loop->iteration }} @endif
                                                    </span>
                                                    <span
                                                        class="text-[9px] font-black mt-2.5 uppercase tracking-wider text-center whitespace-nowrap leading-none
                                                                     {{ $isActive ? 'text-blue-600 font-black scale-105' : ($isDone ? 'text-emerald-600' : 'text-gray-400') }}">
                                                        {{ $stepLabel }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>

                                    {{-- Col 6: Actions --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('customer.orders.show', $order->id) }}"
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
                                            <p class="text-xs">You haven't placed any laundry orders matching the active filters.</p>
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
</x-app-layout>
