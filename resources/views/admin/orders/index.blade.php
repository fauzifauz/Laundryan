<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
            x-data="{ exportMonth: '{{ request('month', 'all') }}', exportYear: '{{ request('year', 'all') }}', exportPdfLoading: false, exportCsvLoading: false }">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Order Management</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Monitor, assign couriers, and
                    track laundry progress in real‑time.</p>
            </div>

            <!-- Top-right: Period filter + Export buttons matching User page -->
            <div class="flex flex-wrap items-center gap-3">
                {{-- Month Filter (Export Only) --}}
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">calendar_month</span>
                    <select x-model="exportMonth"
                        class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="all">All Months</option>
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[14px] pointer-events-none">expand_more</span>
                </div>
                {{-- Year Filter (Export Only) --}}
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">event</span>
                    <select x-model="exportYear"
                        class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="all">All Years</option>
                        @foreach($years as $yr)
                            <option value="{{ $yr }}">{{ $yr }}</option>
                        @endforeach
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[14px] pointer-events-none">expand_more</span>
                </div>
                {{-- Export PDF --}}
                <a :href="'{{ route('admin.orders.export.pdf') }}?month='+exportMonth+'&year='+exportYear"
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
                {{-- Export CSV --}}
                <a :href="'{{ route('admin.orders.export.csv') }}?month='+exportMonth+'&year='+exportYear"
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

    <div class="py-6 space-y-6"
         x-data="{ 
            gridLoading: false,
            showDeleteModal: false,
            deleteOrderId: '',
            deleteOrderCode: '',
            deleteCustomerName: '',
            deleteOrderDate: '',
            deleteAction: '',
            deleteOrderStatus: '',
            deleteIsUnassigned: false,
            deleteIsToday: false,
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
         @submit.window="gridLoading = true"
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
                    <a href="{{ route('admin.orders.index') }}"
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
                    <a href="{{ route('admin.orders.index') }}"
                        class="text-xs font-black text-blue-600 hover:text-blue-800 bg-white border border-blue-100 px-3 py-1 rounded-xl shadow-sm transition-all hover:scale-105">Clear
                        Filter</a>
                </div>
            @endif

            <!-- Statistics Grid (KPI Cards) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Card 1: Total Orders -->
                <a href="{{ route('admin.orders.index') }}"
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
                <a href="{{ route('admin.orders.index', ['filter_period' => 'today']) }}"
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
                <a href="{{ route('admin.orders.index', ['courier_assigned' => 'unassigned']) }}"
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
                <a href="{{ route('admin.orders.index', ['status' => 'active_processing']) }}"
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

                <!-- Card 5: Pending Payments -->
                <a href="{{ route('admin.orders.index', ['status' => 'pending_payment']) }}"
                    class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm flex items-center gap-4 hover:border-rose-300 hover:shadow-md transition-all cursor-pointer group hover:scale-[1.02] duration-200">
                    <div
                        class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-rose-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">Pending
                            Pay</p>
                        <h3 class="text-2xl font-black text-gray-800 mt-0.5 pending-payment-stat">{{ number_format($stats['pending_payment_count']) }}</h3>
                    </div>
                </a>

                <!-- Card 6: Ready for Delivery -->
                <a href="{{ route('admin.orders.index', ['status' => 'ready_for_delivery']) }}"
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
                <a href="{{ route('admin.orders.index', ['status' => 'delivering']) }}"
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
                <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}"
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
                <form action="{{ route('admin.orders.index') }}" method="GET"
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
                            <option value="active_processing" {{ request('status') === 'active_processing' ? 'selected' : ''     }}>Active Processing</option>
                            <option value="pending_payment" {{ request('status') === 'pending_payment' ? 'selected' : ''       }}>
                                Pending Payment</option>
                            <option value="waiting_pickup" {{ request('status') === 'waiting_pickup' ? 'selected' : ''        }}>
                                Waiting Pickup</option>
                            <option value="picking_up" {{ request('status') === 'picking_up' ? 'selected' : ''            }}>Picking Up
                            </option>
                            <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : ''             }}>Picked Up
                            </option>
                            <option value="in_transit_to_laundry" {{ request('status') === 'in_transit_to_laundry' ? 'selected' : '' }}>In Transit</option>
                            <option value="arrived_at_laundry" {{ request('status') === 'arrived_at_laundry' ? 'selected' : ''    }}>Arrived at Laundry</option>
                            <option value="washing" {{ request('status') === 'washing' ? 'selected' : ''               }}>Washing</option>
                            <option value="drying_ironing" {{ request('status') === 'drying_ironing' ? 'selected' : ''        }}>
                                Drying & Ironing</option>
                            <option value="packing" {{ request('status') === 'packing' ? 'selected' : ''               }}>Packing</option>
                            <option value="ready_for_delivery" {{ request('status') === 'ready_for_delivery' ? 'selected' : ''    }}>Ready for Delivery</option>
                            <option value="delivering" {{ request('status') === 'delivering' ? 'selected' : ''            }}>Delivering
                            </option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : ''             }}>Completed
                            </option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : ''             }}>Cancelled
                            </option>
                        </select>
                    </div>

                    <!-- Action buttons matching User style -->
                    <div class="col-span-12 sm:col-span-6 md:col-span-3 flex items-end gap-2">
                        <button type="submit"
                            class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black shadow-lg shadow-blue-200 uppercase tracking-widest flex items-center justify-center gap-1.5 transition-all">
                            <span class="material-symbols-outlined text-[16px]">filter_alt</span> Filter
                        </button>
                        <a href="{{ route('admin.orders.index') }}"
                            class="py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center transition-all"
                            title="Reset Filters">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        </a>
                        <a href="{{ route('admin.orders.create') }}"
                            class="py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center transition-all shadow-md"
                            title="Create New Order">
                            <span class="material-symbols-outlined text-[16px]">add</span>
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
                                <tr id="order-row-{{ $order->id }}" class="hover:bg-blue-50/20 transition-all duration-150 group">

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
                                                    <a href="{{ route('admin.orders.show', $order->id) }}"
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

                                    {{-- Col 4: Interactive Couriers Selectors --}}
                                    <td class="px-6 py-4 space-y-2">
                                        @if(session('success') && session('action_type') === 'courier_assigned' && session('target_order_id') == $order->id)
                                            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-150"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[9px] font-bold rounded-lg shadow-sm w-full justify-center">
                                                <span>✓ Assignment Updated</span>
                                            </div>
                                        @endif
                                        <!-- Pickup Assignment Form -->
                                        <form action="{{ route('admin.orders.assign', $order->id) }}" method="POST"
                                            class="flex flex-col gap-1">
                                            @csrf
                                            <input type="hidden" name="delivery_courier_id"
                                                value="{{ $order->delivery_courier_id }}">
                                            <label
                                                class="text-[8px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1">
                                                <span
                                                    class="material-symbols-outlined text-[12px] text-blue-500 font-bold">directions_run</span>
                                                Pickup Courier
                                            </label>
                                            <select name="pickup_courier_id" onchange="this.form.submit()"
                                                class="bg-gray-50 border border-gray-200 rounded-xl text-[10px] font-bold text-gray-700 py-1 px-1.5 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer">
                                                <option value="">Unassigned</option>
                                                @foreach($couriers as $c)
                                                    <option value="{{ $c->id }}" {{ $order->pickup_courier_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </form>

                                        <!-- Delivery Assignment Form -->
                                        <form action="{{ route('admin.orders.assign', $order->id) }}" method="POST"
                                            class="flex flex-col gap-1">
                                            @csrf
                                            <input type="hidden" name="pickup_courier_id"
                                                value="{{ $order->pickup_courier_id }}">
                                            <label
                                                class="text-[8px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1">
                                                <span
                                                    class="material-symbols-outlined text-[12px] text-emerald-500 font-bold">local_shipping</span>
                                                Delivery Courier
                                            </label>
                                            <select name="delivery_courier_id" onchange="this.form.submit()"
                                                class="bg-gray-50 border border-gray-200 rounded-xl text-[10px] font-bold text-gray-700 py-1 px-1.5 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer">
                                                <option value="">Unassigned</option>
                                                @foreach($couriers as $c)
                                                    <option value="{{ $c->id }}" {{ $order->delivery_courier_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>

                                    {{-- Col 5: Clickable Progress Timeline --}}
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
                                                                                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST"
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
                                                                                <span
                                                                                    class="text-[9px] font-black mt-2.5 uppercase tracking-wider text-center whitespace-nowrap leading-none
                                                                                                 {{ $isActive ? 'text-blue-600 font-black scale-105' : ($isDone ? 'text-emerald-600' : 'text-gray-400') }}">
                                                                                    {{ $stepLabel }}
                                                                                </span>
                                                                                @if(session('success') && session('action_type') === 'status_updated' && session('target_order_id') == $order->id && $isActive)
                                                                                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                                                                                        x-transition:enter="transition ease-out duration-200"
                                                                                        x-transition:enter-start="opacity-0 scale-95"
                                                                                        x-transition:enter-end="opacity-100 scale-100"
                                                                                        x-transition:leave="transition ease-in duration-150"
                                                                                        x-transition:leave-start="opacity-100 scale-100"
                                                                                        x-transition:leave-end="opacity-0 scale-95"
                                                                                        class="inline-flex items-center gap-0.5 px-1 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[8px] font-bold rounded-md mt-1 scale-90 whitespace-nowrap">
                                                                                        <span>✓ Status Updated</span>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                            @endforeach
                                        </div>
                                    </td>

                                    {{-- Col 6: CRUD Actions --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- View details --}}
                                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                                class="p-1.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition-colors"
                                                title="Detail">
                                                <span class="material-symbols-outlined text-[16px] block">visibility</span>
                                            </a>
                                            {{-- Edit details --}}
                                            <a href="{{ route('admin.orders.edit', $order->id) }}"
                                                class="p-1.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-amber-600 transition-colors"
                                                title="Edit">
                                                <span class="material-symbols-outlined text-[16px] block">edit</span>
                                            </a>
                                            {{-- Delete --}}
                                            <button type="button"
                                                @click="
                                                    deleteOrderId = '{{ $order->id }}';
                                                    deleteOrderCode = '{{ $order->order_code }}';
                                                    deleteCustomerName = '{{ $order->customer?->name ?? 'Walk-in Guest' }}';
                                                    deleteOrderDate = '{{ $order->created_at->format('d M Y · H:i') }}';
                                                    deleteAction = '{{ route('admin.orders.destroy', $order->id) }}';
                                                    deleteOrderStatus = '{{ $order->status }}';
                                                    deleteIsUnassigned = {{ ($order->pickup_courier_id && $order->delivery_courier_id) ? 'false' : 'true' }};
                                                    deleteIsToday = {{ $order->created_at->isToday() ? 'true' : 'false' }};
                                                    showDeleteModal = true;
                                                "
                                                class="p-1.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-600 hover:bg-rose-50 hover:text-rose-600 transition-colors"
                                                title="Delete Order">
                                                <span class="material-symbols-outlined text-[16px] block">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-2 text-gray-400">
                                            <span class="material-symbols-outlined text-5xl text-gray-200">inbox</span>
                                            <p class="text-sm font-semibold">No orders found</p>
                                            <p class="text-xs">Try adjusting your filters or create a new laundry order.</p>
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

        {{-- Custom Delete Confirmation Modal --}}
        <div x-show="showDeleteModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             style="display:none;" x-cloak>
            <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-gray-100 overflow-hidden"
                 @click.away="showDeleteModal = false">
                
                {{-- Modal Header --}}
                <div class="px-7 py-5 border-b border-gray-100 bg-rose-50/30 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-rose-500">warning</span>
                        <h4 class="font-black text-lg text-gray-900">Delete Order</h4>
                    </div>
                    <button @click="showDeleteModal = false"
                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-all">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-7 space-y-6">
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600 leading-relaxed font-semibold">
                            Are you sure you want to delete this order?
                        </p>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            This action cannot be undone and the order data will be permanently removed.
                        </p>
                    </div>

                    <!-- Order Details Summary Card -->
                    <div class="bg-gray-50 rounded-2xl border border-gray-100 p-4 space-y-2.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-400 font-bold uppercase tracking-wider">Order ID</span>
                            <span class="font-black text-gray-800" x-text="deleteOrderCode"></span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-400 font-bold uppercase tracking-wider">Customer Name</span>
                            <span class="font-extrabold text-gray-800" x-text="deleteCustomerName"></span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-400 font-bold uppercase tracking-wider">Order Date</span>
                            <span class="font-bold text-gray-600" x-text="deleteOrderDate"></span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-50">
                        <button type="button" @click="showDeleteModal = false"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                            Cancel
                        </button>
                        <form @submit.prevent.stop="
                            gridLoading = true;
                            fetch(deleteAction, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    _method: 'DELETE'
                                })
                            })
                            .then(response => {
                                gridLoading = false;
                                if (response.ok) {
                                    showDeleteModal = false;
                                    triggerToast('Order deleted successfully.');
                                    
                                    // Animate out row
                                    const row = document.getElementById('order-row-' + deleteOrderId);
                                    if (row) {
                                        row.style.transition = 'all 0.3s ease';
                                        row.style.opacity = '0';
                                        row.style.transform = 'translateX(20px)';
                                        setTimeout(() => row.remove(), 300);
                                    }

                                    // Update total count
                                    const totalEl = document.querySelector('.orders-total-count');
                                    if (totalEl) {
                                        let currentCount = parseInt(totalEl.innerText) || 0;
                                        if (currentCount > 0) {
                                            totalEl.innerText = (currentCount - 1) + ' Orders found';
                                        }
                                    }

                                    // Update stats cards
                                    document.querySelectorAll('.total-orders-stat').forEach(el => {
                                        let val = parseInt(el.textContent.replace(/,/g, '')) || 0;
                                        el.textContent = Math.max(0, val - 1).toLocaleString();
                                    });

                                    if (deleteOrderStatus === 'completed') {
                                        document.querySelectorAll('.completed-orders-stat').forEach(el => {
                                            let val = parseInt(el.textContent.replace(/,/g, '')) || 0;
                                            el.textContent = Math.max(0, val - 1).toLocaleString();
                                        });
                                    }
                                    if (deleteOrderStatus === 'pending_payment') {
                                        document.querySelectorAll('.pending-payment-stat').forEach(el => {
                                            let val = parseInt(el.textContent.replace(/,/g, '')) || 0;
                                            el.textContent = Math.max(0, val - 1).toLocaleString();
                                        });
                                    }
                                    if (deleteOrderStatus === 'ready_for_delivery') {
                                        document.querySelectorAll('.ready-delivery-stat').forEach(el => {
                                            let val = parseInt(el.textContent.replace(/,/g, '')) || 0;
                                            el.textContent = Math.max(0, val - 1).toLocaleString();
                                        });
                                    }
                                    if (deleteOrderStatus === 'delivering') {
                                        document.querySelectorAll('.delivering-orders-stat').forEach(el => {
                                            let val = parseInt(el.textContent.replace(/,/g, '')) || 0;
                                            el.textContent = Math.max(0, val - 1).toLocaleString();
                                        });
                                    }
                                    if (deleteOrderStatus !== 'completed' && deleteOrderStatus !== 'cancelled' && deleteOrderStatus !== 'pending_payment') {
                                        document.querySelectorAll('.processing-orders-stat').forEach(el => {
                                            let val = parseInt(el.textContent.replace(/,/g, '')) || 0;
                                            el.textContent = Math.max(0, val - 1).toLocaleString();
                                        });
                                    }
                                    if (deleteIsUnassigned) {
                                        document.querySelectorAll('.unassigned-orders-stat').forEach(el => {
                                            let val = parseInt(el.textContent.replace(/,/g, '')) || 0;
                                            el.textContent = Math.max(0, val - 1).toLocaleString();
                                        });
                                    }
                                    if (deleteIsToday) {
                                        document.querySelectorAll('.today-orders-stat').forEach(el => {
                                            let val = parseInt(el.textContent.replace(/,/g, '')) || 0;
                                            el.textContent = Math.max(0, val - 1).toLocaleString();
                                        });
                                    }
                                } else {
                                    alert('Failed to delete order.');
                                }
                            })
                            .catch(error => {
                                gridLoading = false;
                                console.error('Error:', error);
                                alert('An error occurred.');
                            });
                        ">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-sm hover:shadow-md">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                                Delete Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>
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
</x-app-layout>