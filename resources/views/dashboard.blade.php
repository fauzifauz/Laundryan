<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-900 tracking-tight">
                    {{ __('Customer Dashboard') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Welcome back! Monitor your laundry activities easily.</p>
            </div>
            <a id="tour-book-btn" href="{{ route('customer.orders.create') }}" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-brand to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-6 rounded-2xl shadow-[0_10px_20px_rgba(0,91,192,0.15)] hover:shadow-[0_10px_20px_rgba(0,91,192,0.3)] transition-all duration-300 transform hover:-translate-y-0.5">
                <span class="material-symbols-outlined text-[20px]">local_laundry_service</span>
                Book Laundry Now
            </a>
        </div>
    </x-slot>

    <div class="py-2 space-y-8">
        <!-- Welcoming Hero & Stats Overview Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Glassmorphic Promo Card -->
            <div id="tour-welcome-card" class="lg:col-span-2 bg-gradient-to-br from-brand via-blue-600 to-blue-800 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden flex flex-col justify-between min-h-[220px] group">
                <div class="absolute -right-10 -top-10 w-44 h-44 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-700"></div>
                <div class="absolute -left-10 -bottom-10 w-44 h-44 bg-blue-400/20 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-700"></div>
                
                <div class="z-10">
                    <span class="bg-white/20 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full backdrop-blur-sm">Premium Service</span>
                    <h3 class="text-3xl font-black mt-4 tracking-tight leading-tight">Hello, {{ auth()->user()->name }}!</h3>
                    <p class="mt-2 text-white/80 text-sm max-w-md">Leave your dirty clothes to our experts. Clean, fresh, neat, and delivered straight to your door.</p>
                </div>
                
                <div class="z-10 mt-6 flex items-center gap-4">
                    <a href="{{ route('customer.orders.create') }}" class="bg-white text-brand font-extrabold px-6 py-3 rounded-xl hover:bg-blue-50 transition-all duration-300 shadow-lg text-xs tracking-wider uppercase">
                        Book Service
                    </a>
                    <a href="{{ route('customer.orders.index') }}" class="text-white/80 hover:text-white font-bold text-xs flex items-center gap-1 group/link">
                        View All Orders
                        <span class="material-symbols-outlined text-sm group-hover/link:translate-x-1 transition-transform">arrow_forward</span>
                    </a>
                </div>
            </div>

            <!-- Stats Overview (Spending & Count) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-6">
                <!-- Stat Card 1 (Total Spending) -->
                <a id="tour-spending-card" href="{{ route('customer.payments.index', ['status' => 'success']) }}" class="block bg-white rounded-3xl p-6 shadow-md border border-gray-100 flex items-center justify-between hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Total Spending</p>
                        <h4 class="text-2xl font-black text-gray-900 mt-1">Rp {{ number_format($totalSpending, 0, ',', '.') }}</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Paid transactions</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                    </div>
                </a>

                <!-- Stat Card 2 (Order Activity) -->
                <div id="tour-activity-card" class="bg-white rounded-3xl p-6 shadow-md border border-gray-100 flex items-center justify-between hover:shadow-lg transition-all duration-300">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2">Order Activity</p>
                        <div class="flex items-center gap-3">
                            <!-- Active Link -->
                            <a href="{{ route('customer.orders.index', ['status' => 'active']) }}" class="flex items-baseline gap-1 hover:text-brand group transition-colors">
                                <span class="text-2xl font-black text-brand group-hover:scale-105 transition-transform">{{ $activeOrdersCount }}</span>
                                <span class="text-xs text-gray-500 font-bold group-hover:underline">Active</span>
                            </a>
                            
                            <span class="text-gray-300 font-light">|</span>
                            
                            <!-- Completed Link -->
                            <a href="{{ route('customer.orders.index', ['status' => 'completed']) }}" class="flex items-baseline gap-1 hover:text-brand group transition-colors">
                                <span class="text-2xl font-black text-gray-700 group-hover:scale-105 transition-transform">{{ $completedOrdersCount }}</span>
                                <span class="text-xs text-gray-500 font-bold group-hover:underline">Completed</span>
                            </a>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">local_shipping</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Order Tracking Timeline Section -->
        <div id="tour-active-orders" class="bg-white rounded-3xl p-8 shadow-md border border-gray-100 space-y-8">
            <div class="flex justify-between items-center pb-2 border-b border-gray-50">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand">track_changes</span>
                    Active Laundry Orders Status
                </h3>
            </div>

            @forelse($activeOrders as $orderIndex => $order)
                <div class="order-tracking-card transition-all duration-500" data-order-index="{{ $orderIndex }}"
                     style="{{ $orderIndex >= 2 ? 'display:none;' : '' }}">
                <div class="bg-gray-50/50 rounded-3xl p-6 border border-gray-100 shadow-sm relative overflow-hidden transition-all hover:bg-white hover:shadow-md duration-300">
                    <!-- Order Information Header -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-center border-b border-gray-100/80 pb-6 mb-6">
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Order Code</span>
                            <a href="{{ route('customer.orders.show', $order->id) }}" class="text-sm font-black text-brand mt-0.5 hover:underline flex items-center gap-1">
                                {{ $order->order_code }}
                                <span class="material-symbols-outlined text-xs">open_in_new</span>
                            </a>
                        </div>
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Service & Category</span>
                            <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $order->service->name }} ({{ $order->itemType->name }})</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Soap & Fragrance</span>
                            <p class="text-sm font-medium text-gray-600 mt-0.5">{{ $order->soap ?? '-' }} / {{ $order->fragrance ?? '-' }}</p>
                        </div>
                        <div class="flex justify-between items-center gap-2">
                            <div>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Payment Status</span>
                                <p class="mt-0.5">
                                    @if($order->payment_status === 'paid')
                                        <span class="px-2 py-0.5 text-[9px] font-black bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200 uppercase">PAID</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[9px] font-black bg-yellow-50 text-yellow-700 rounded-full border border-yellow-200 uppercase">PENDING</span>
                                    @endif
                                </p>
                            </div>
                            <a href="{{ route('customer.orders.show', $order->id) }}" class="inline-flex items-center gap-1 bg-brand text-white text-xs font-black px-4 py-2.5 rounded-xl hover:bg-blue-700 transition-all shadow-sm transform active:scale-95 uppercase tracking-wider shrink-0">
                                Track Details
                            </a>
                        </div>
                    </div>

                    <!-- 9-Stage Timeline -->
                    @php
                        $stages = [
                            ['label' => 'Order Placed', 'statuses' => ['pending_payment', 'waiting_pickup'], 'icon' => 'shopping_cart'],
                            ['label' => 'Picking Up', 'statuses' => ['picking_up'], 'icon' => 'hail'],
                            ['label' => 'In Transit', 'statuses' => ['picked_up', 'in_transit_to_laundry'], 'icon' => 'local_shipping'],
                            ['label' => 'Arrived at Laundry', 'statuses' => ['arrived_at_laundry'], 'icon' => 'storefront'],
                            ['label' => 'Washing', 'statuses' => ['washing'], 'icon' => 'local_laundry_service'],
                            ['label' => 'Drying & Ironing', 'statuses' => ['drying_ironing'], 'icon' => 'iron'],
                            ['label' => 'Packing', 'statuses' => ['packing'], 'icon' => 'inventory_2'],
                            ['label' => 'Delivering', 'statuses' => ['ready_for_delivery', 'delivering'], 'icon' => 'delivery_dining'],
                            ['label' => 'Completed', 'statuses' => ['completed'], 'icon' => 'task_alt']
                        ];

                        $currentStatus = $order->status;
                        $currentIdx = 0;
                        foreach($stages as $idx => $stage) {
                            if (in_array($currentStatus, $stage['statuses'])) {
                                $currentIdx = $idx;
                                break;
                            }
                        }
                    @endphp

                    <div class="relative w-full overflow-x-auto timeline-scroll-container py-6 px-4" id="timeline-container-{{ $order->id }}">
                        <div class="flex items-center min-w-[950px] relative py-2">
                            <!-- Connecting Line Background -->
                            <div class="absolute left-10 right-10 top-1/2 -translate-y-1/2 h-0.5 bg-gray-200 z-0">
                                <!-- Active Progress Line -->
                                <div class="h-full bg-brand rounded-full transition-all duration-1000" style="width: {{ ($currentIdx / 8) * 100 }}%"></div>
                            </div>

                            <!-- Steps -->
                            @foreach($stages as $index => $stage)
                                @php
                                    $isCompleted = $index < $currentIdx;
                                    $isActive = $index === $currentIdx;
                                    $isFuture = $index > $currentIdx;
                                @endphp
                                <div class="flex-1 flex flex-col items-center relative z-10 step-item-{{ $order->id }}-{{ $index }} {{ $isActive ? 'step-active' : '' }}" data-index="{{ $index }}">
                                    <!-- Step Circle -->
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-500 shadow-sm
                                        {{ $isCompleted ? 'bg-brand text-white border-2 border-brand' : '' }}
                                        {{ $isActive ? 'bg-blue-600 text-white ring-4 ring-blue-100 border-2 border-brand scale-110 font-bold' : '' }}
                                        {{ $isFuture ? 'bg-white text-gray-400 border-2 border-gray-200' : '' }}
                                    ">
                                        <span class="material-symbols-outlined text-[20px] {{ $isActive ? 'font-bold' : '' }}">
                                            {{ $stage['icon'] }}
                                        </span>
                                    </div>
                                    
                                    <!-- Step Label -->
                                    <span class="text-[11px] font-bold mt-3 text-center transition-colors duration-300 {{ $isActive ? 'text-blue-700 font-extrabold scale-105' : ($isCompleted ? 'text-gray-900 font-semibold' : 'text-gray-400') }}">
                                        {{ $stage['label'] }}
                                    </span>
                                    
                                    <!-- Status Badge -->
                                    <span class="text-[9px] mt-1 text-center block">
                                        @if($isActive)
                                            <span class="text-brand font-black uppercase tracking-wider bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-full">Active</span>
                                        @elseif($isCompleted)
                                            <span class="text-emerald-600 font-bold uppercase tracking-wider">Done</span>
                                        @else
                                            <span class="text-gray-300 font-medium">Pending</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if(in_array($currentStatus, ['picking_up', 'delivering']))
                        <div class="mt-4 p-4 bg-blue-50/50 border border-blue-100/60 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-brand text-2xl animate-bounce">local_shipping</span>
                                <div class="text-left">
                                    <p class="text-xs font-bold text-blue-900">Courier is currently handling your order!</p>
                                    <p class="text-[11px] text-blue-700">Track courier live position and contact them directly on the order details page.</p>
                                </div>
                            </div>
                            <a href="{{ route('customer.orders.show', $order->id) }}" class="bg-brand text-white text-[11px] font-bold px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow">
                                Track Live
                            </a>
                        </div>
                    @endif
                </div>
                </div>{{-- close .order-tracking-card --}}
            @empty
                <div class="text-center py-10 bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                    <span class="material-symbols-outlined text-gray-300 text-5xl">inventory_2</span>
                    <p class="text-gray-500 font-medium mt-3 text-sm">No active orders at the moment.</p>
                    <a href="{{ route('customer.orders.create') }}" class="text-brand font-bold text-xs mt-2 inline-block hover:underline">Create your first laundry order</a>
                </div>
            @endforelse

            @if($activeOrders->count() > 2)
                <!-- Show more / collapse toggle -->
                <div class="flex justify-center pt-2">
                    <button id="toggle-orders-btn"
                        onclick="toggleMoreOrders()"
                        class="inline-flex items-center gap-2 text-xs font-black text-brand bg-blue-50 hover:bg-blue-100 border border-blue-100 px-6 py-3 rounded-2xl transition-all duration-300 shadow-sm group">
                        <span id="toggle-orders-label">Show {{ $activeOrders->count() - 2 }} More Order{{ $activeOrders->count() - 2 > 1 ? 's' : '' }}</span>
                        <span id="toggle-orders-icon" class="material-symbols-outlined text-[18px] transition-transform duration-300 group-hover:translate-y-0.5">expand_more</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- Assigned Courier History -->
        <div id="tour-courier-history" class="bg-white rounded-3xl p-8 shadow-md border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-6">
                <span class="material-symbols-outlined text-brand">contacts</span>
                Assigned Courier History
            </h3>

            @if($pickupCourierHistory->count() > 0 || $deliveryCourierHistory->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    {{-- LEFT: Pickup Couriers --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-brand flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[18px]">hail</span>
                            </div>
                            <div>
                                <p class="text-xs font-black text-gray-800 uppercase tracking-wider">Pickup Courier</p>
                                <p class="text-[10px] text-gray-400">Couriers who picked up your laundry</p>
                            </div>
                        </div>

                        @if($pickupCourierHistory->count() > 0)
                            <div class="space-y-3">
                                @foreach($pickupCourierHistory as $history)
                                    @include('dashboard._courier_card', ['history' => $history, 'accentClass' => 'bg-blue-50 text-brand border-blue-100'])
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 border border-dashed border-gray-200 rounded-2xl">
                                <span class="material-symbols-outlined text-gray-300 text-3xl">person_off</span>
                                <p class="text-xs text-gray-400 mt-2 italic">No pickup courier assigned yet.</p>
                            </div>
                        @endif
                    </div>

                    {{-- RIGHT: Delivery Couriers --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[18px]">delivery_dining</span>
                            </div>
                            <div>
                                <p class="text-xs font-black text-gray-800 uppercase tracking-wider">Delivery Courier</p>
                                <p class="text-[10px] text-gray-400">Couriers who delivered your laundry back</p>
                            </div>
                        </div>

                        @if($deliveryCourierHistory->count() > 0)
                            <div class="space-y-3">
                                @foreach($deliveryCourierHistory as $history)
                                    @include('dashboard._courier_card', ['history' => $history, 'accentClass' => 'bg-emerald-50 text-emerald-700 border-emerald-100'])
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 border border-dashed border-gray-200 rounded-2xl">
                                <span class="material-symbols-outlined text-gray-300 text-3xl">person_off</span>
                                <p class="text-xs text-gray-400 mt-2 italic">No delivery courier assigned yet.</p>
                            </div>
                        @endif
                    </div>

                </div>
            @else
                <div class="text-center py-6 text-gray-400 text-sm italic bg-gray-50 border border-dashed border-gray-200 rounded-3xl">
                    No courier assignment history yet.
                </div>
            @endif
        </div>
    </div>

    <!-- Courier Detail Modal -->
    <div id="courier-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm hidden" onclick="closeCourierModalOnBgClick(event)" x-cloak>
        <div class="bg-white rounded-3xl p-8 max-w-xl w-full shadow-2xl border border-gray-100 relative overflow-hidden flex flex-col max-h-[85vh] transition-all transform animate-fade-in">
            <!-- Close Button -->
            <button onclick="closeCourierModal()" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined text-[24px]">close</span>
            </button>

            <!-- Modal Header / Courier Info -->
            <div class="flex items-center gap-4 border-b border-gray-100 pb-6">
                <div id="modal-courier-avatar" class="w-16 h-16 rounded-2xl bg-gradient-to-br from-brand to-blue-600 text-white flex items-center justify-center font-black text-2xl shadow-sm shrink-0">
                    C
                </div>
                <div class="text-left">
                    <h3 id="modal-courier-name" class="text-xl font-black text-gray-900 leading-none">Courier Name</h3>
                    <span id="modal-courier-role" class="inline-block px-2.5 py-0.5 text-[9px] font-black bg-blue-50 border border-blue-100 text-brand rounded-full uppercase tracking-wider mt-2.5">
                        Courier Role
                    </span>
                    <p id="modal-courier-phone" class="text-sm text-gray-500 font-mono mt-2">Phone Number</p>
                    <p id="modal-courier-email" class="text-xs text-gray-400 mt-0.5">Email Address</p>
                </div>
            </div>

            <!-- Orders Listing -->
            <div class="flex-1 overflow-y-auto mt-6 pr-2 custom-scrollbar text-left">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-4">Orders Handled For You</h4>
                <div id="modal-orders-list" class="space-y-3">
                    <!-- Dynamic orders injected here -->
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end">
                <button onclick="closeCourierModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-extrabold text-xs py-3 px-6 rounded-xl transition-all">
                    Close Details
                </button>
            </div>
        </div>
    </div>

    <style>
        .timeline-scroll-container {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .timeline-scroll-container::-webkit-scrollbar {
            display: none;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-fade-in {
            animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        /* Tour active highlights */
        .tour-highlight-active {
            position: relative !important;
            z-index: 99999 !important;
            pointer-events: none !important;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1) !important;
        }
        @keyframes popIn {
            from { opacity: 0; transform: translateY(10px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        #tour-tooltip {
            animation: popIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    <script>
        // ── Toggle show more / collapse order cards ───────────────────────────
        let ordersExpanded = false;

        function toggleMoreOrders() {
            ordersExpanded = !ordersExpanded;
            const cards = document.querySelectorAll('.order-tracking-card');
            const label = document.getElementById('toggle-orders-label');
            const icon  = document.getElementById('toggle-orders-icon');
            const hiddenCount = cards.length - 2;

            cards.forEach(function(card, i) {
                if (i >= 2) {
                    if (ordersExpanded) {
                        card.style.display = '';
                        card.style.animation = 'fadeIn 0.3s ease forwards';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });

            if (ordersExpanded) {
                label.textContent = 'Collapse Orders';
                icon.textContent  = 'expand_less';
                icon.style.transform = 'rotate(0deg)';
            } else {
                label.textContent = 'Show ' + hiddenCount + ' More Order' + (hiddenCount > 1 ? 's' : '');
                icon.textContent  = 'expand_more';
                icon.style.transform = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // For each timeline container, scroll the active step into center focus
            setTimeout(function() {
                document.querySelectorAll('.timeline-scroll-container').forEach(function(container) {
                    const activeStep = container.querySelector('.step-active');
                    if (activeStep) {
                        const containerWidth = container.clientWidth;
                        const stepOffset = activeStep.offsetLeft;
                        const stepWidth = activeStep.clientWidth;
                        const scrollTarget = stepOffset - (containerWidth / 2) + (stepWidth / 2);
                        container.scrollTo({
                            left: scrollTarget,
                            behavior: 'smooth'
                        });
                    }
                });
            }, 300);

            // Register Laravel Echo listener for each active order
            if (window.Echo) {
                @foreach($activeOrders as $order)
                    window.Echo.private('order.{{ $order->id }}')
                        .listen('OrderStatusUpdated', (e) => {
                            console.log('Order {{ $order->id }} status updated to:', e.order.status);
                            window.location.reload();
                        });
                @endforeach
            }
        });

        function openCourierModal(data) {
            document.getElementById('modal-courier-avatar').textContent = data.initial;
            document.getElementById('modal-courier-name').textContent = data.name;
            document.getElementById('modal-courier-role').textContent = data.role;
            document.getElementById('modal-courier-phone').textContent = data.phone;
            document.getElementById('modal-courier-email').textContent = data.email || '-';

            const ordersList = document.getElementById('modal-orders-list');
            ordersList.innerHTML = '';

            if (data.orders.length === 0) {
                ordersList.innerHTML = '<p class="text-xs text-gray-400 italic">No orders found.</p>';
            } else {
                data.orders.forEach(order => {
                    const statusMapping = {
                        'pending_payment': 'Awaiting Payment',
                        'waiting_pickup': 'Awaiting Courier',
                        'picking_up': 'Courier Picking Up',
                        'picked_up': 'Picked Up',
                        'in_transit_to_laundry': 'Transit to Laundry',
                        'arrived_at_laundry': 'Arrived at Laundry',
                        'washing': 'Washing',
                        'drying_ironing': 'Drying & Ironing',
                        'packing': 'Packing Clothes',
                        'ready_for_delivery': 'Ready for Delivery',
                        'delivering': 'Out for Delivery',
                        'completed': 'Completed',
                    };
                    const statusLabel = statusMapping[order.status] || order.status.replace(/_/g, ' ');

                    ordersList.insertAdjacentHTML('beforeend', `
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-xs text-brand">${order.order_code}</span>
                                    <span class="px-2 py-0.5 text-[9px] font-black bg-blue-50 text-blue-700 border border-blue-100 rounded-full uppercase">${order.role_in_order}</span>
                                </div>
                                <p class="text-xs font-bold text-gray-700 mt-1">${order.service_name} (${order.item_type_name})</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">${order.date}</p>
                            </div>
                            <div class="flex sm:flex-col items-end gap-2 w-full sm:w-auto justify-between sm:justify-start">
                                <span class="px-2.5 py-0.5 text-[10px] font-black rounded-full border bg-white border-gray-200 text-gray-700 uppercase">
                                    ${statusLabel}
                                </span>
                                <a href="${order.url}" class="text-[11px] font-extrabold text-brand hover:underline flex items-center gap-0.5">
                                    Track Order <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                                </a>
                            </div>
                        </div>
                    `);
                });
            }

            const modal = document.getElementById('courier-modal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCourierModal() {
            const modal = document.getElementById('courier-modal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function closeCourierModalOnBgClick(event) {
            if (event.target.id === 'courier-modal') {
                closeCourierModal();
            }
        }

        // Onboarding Tour logic
        const tourSteps = [
            {
                element: '#tour-welcome-card',
                title: 'Welcome to Your Dashboard!',
                description: 'Here you can see a quick summary of premium services and easily check out what is new.',
                position: 'bottom'
            },
            {
                element: '#tour-book-btn',
                title: 'Book a Laundry Service',
                description: 'Ready to clean your clothes? Click this button to place a new order, choose scents, soaps, and assign couriers.',
                position: 'bottom'
            },
            {
                element: '#tour-spending-card',
                title: 'Track Your Spending',
                description: 'Keep tabs on your successful transactions and total spending details in real-time.',
                position: 'left'
            },
            {
                element: '#tour-activity-card',
                title: 'Order Activity Stats',
                description: 'Quickly see how many orders are active and how many are already completed.',
                position: 'left'
            },
            {
                element: '#tour-sidebar-my-laundry',
                title: 'My Laundry Menu',
                description: 'Click here to view your laundry order history, track ongoing orders, and view order receipts.',
                position: 'right'
            },
            {
                element: '#tour-sidebar-payments',
                title: 'Payments History Menu',
                description: 'Click here to access your full list of transactions, check invoice documents, and filter logs.',
                position: 'right'
            }
        ];

        let currentTourStep = 0;
        let tourDirection = 'next';

        function preventDefault(e) {
            e.preventDefault();
        }

        const keysToBlock = {
            'ArrowUp': 1, 'ArrowDown': 1, 'Space': 1, ' ': 1, 'PageUp': 1, 'PageDown': 1, 'End': 1, 'Home': 1
        };

        function preventDefaultForScrollKeys(e) {
            if (keysToBlock[e.key]) {
                e.preventDefault();
                return false;
            }
        }

        function disableScroll() {
            window.addEventListener('DOMMouseScroll', preventDefault, { passive: false });
            window.addEventListener('wheel', preventDefault, { passive: false });
            window.addEventListener('touchmove', preventDefault, { passive: false });
            window.addEventListener('keydown', preventDefaultForScrollKeys, { passive: false });
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        }

        function enableScroll() {
            window.removeEventListener('DOMMouseScroll', preventDefault, { passive: false });
            window.removeEventListener('wheel', preventDefault, { passive: false });
            window.removeEventListener('touchmove', preventDefault, { passive: false });
            window.removeEventListener('keydown', preventDefaultForScrollKeys, { passive: false });
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
        }

        function startOnboardingTour() {
            currentTourStep = 0;
            tourDirection = 'next';
            const spotlight = document.getElementById('tour-spotlight');
            const tooltip = document.getElementById('tour-tooltip');
            const backdrop = document.getElementById('tour-backdrop');
            
            backdrop.classList.remove('hidden');
            spotlight.classList.remove('hidden');
            tooltip.classList.remove('hidden');
            disableScroll();
            
            showTourStep();
        }

        function showTourStep() {
            if (currentTourStep >= tourSteps.length) {
                finishOnboardingTour();
                return;
            }
            if (currentTourStep < 0) {
                currentTourStep = 0;
            }

            const step = tourSteps[currentTourStep];
            const el = document.querySelector(step.element);
            
            if (!el) {
                // If element is not present on page (e.g. no active orders card exists), skip step
                if (tourDirection === 'prev') {
                    currentTourStep--;
                    showTourStep();
                } else {
                    currentTourStep++;
                    showTourStep();
                }
                return;
            }
            
            // Scroll to element gently
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Wait slightly for scroll to finish before positioning spotlight & tooltip
            setTimeout(() => {
                const rect = el.getBoundingClientRect();
                const spotlight = document.getElementById('tour-spotlight');
                const tooltip = document.getElementById('tour-tooltip');
                
                // Position spotlight around element
                const pad = 10;
                spotlight.style.top = `${rect.top - pad}px`;
                spotlight.style.left = `${rect.left - pad}px`;
                spotlight.style.width = `${rect.width + (pad * 2)}px`;
                spotlight.style.height = `${rect.height + (pad * 2)}px`;
                
                // Update tooltip content
                document.getElementById('tour-step-progress').textContent = `Step ${currentTourStep + 1} of ${tourSteps.length}`;
                document.getElementById('tour-step-title').textContent = step.title;
                document.getElementById('tour-step-desc').textContent = step.description;
                
                // Manage Buttons
                document.getElementById('tour-prev-btn').disabled = currentTourStep === 0;
                const nextBtn = document.getElementById('tour-next-btn');
                if (currentTourStep === tourSteps.length - 1) {
                    nextBtn.textContent = 'Finish';
                } else {
                    nextBtn.textContent = 'Next';
                }
                
                // Position Tooltip relative to spotlight
                const toolWidth = tooltip.offsetWidth || 340;
                const toolHeight = tooltip.offsetHeight || 180;
                let top = 0;
                let left = 0;
                
                const sTop = rect.top - pad;
                const sLeft = rect.left - pad;
                const sWidth = rect.width + (pad * 2);
                const sHeight = rect.height + (pad * 2);
                
                if (step.position === 'bottom') {
                    top = sTop + sHeight + 15;
                    left = sLeft + (sWidth / 2) - (toolWidth / 2);
                } else if (step.position === 'top') {
                    top = sTop - toolHeight - 15;
                    left = sLeft + (sWidth / 2) - (toolWidth / 2);
                } else if (step.position === 'left') {
                    top = sTop + (sHeight / 2) - (toolHeight / 2);
                    left = sLeft - toolWidth - 15;
                } else if (step.position === 'right') {
                    top = sTop + (sHeight / 2) - (toolHeight / 2);
                    left = sLeft + sWidth + 15;
                }
                
                // Boundaries check
                if (left < 10) left = 10;
                if (left + toolWidth > window.innerWidth) left = window.innerWidth - toolWidth - 10;
                if (top < 10) top = 10;
                if (top + toolHeight > window.innerHeight) top = window.innerHeight - toolHeight - 10;
                
                tooltip.style.top = `${top}px`;
                tooltip.style.left = `${left}px`;
            }, 300);
        }

        function nextOnboardingStep() {
            tourDirection = 'next';
            if (currentTourStep < tourSteps.length - 1) {
                currentTourStep++;
                showTourStep();
            } else {
                finishOnboardingTour();
            }
        }

        function prevOnboardingStep() {
            tourDirection = 'prev';
            if (currentTourStep > 0) {
                currentTourStep--;
                showTourStep();
            }
        }

        function skipOnboardingTour() {
            finishOnboardingTour();
        }

        function finishOnboardingTour() {
            document.getElementById('tour-spotlight').classList.add('hidden');
            document.getElementById('tour-tooltip').classList.add('hidden');
            document.getElementById('tour-backdrop').classList.add('hidden');
            enableScroll();
            sessionStorage.setItem('onboarding_tour_played', 'true');
        }

        function updateTourLayout() {
            const spotlight = document.getElementById('tour-spotlight');
            if (spotlight && !spotlight.classList.contains('hidden')) {
                window.requestAnimationFrame(() => {
                    const step = tourSteps[currentTourStep];
                    const el = document.querySelector(step.element);
                    if (el) {
                        const rect = el.getBoundingClientRect();
                        const pad = 10;
                        spotlight.style.top = `${rect.top - pad}px`;
                        spotlight.style.left = `${rect.left - pad}px`;
                        spotlight.style.width = `${rect.width + (pad * 2)}px`;
                        spotlight.style.height = `${rect.height + (pad * 2)}px`;
                        
                        const tooltip = document.getElementById('tour-tooltip');
                        const toolWidth = tooltip.offsetWidth;
                        const toolHeight = tooltip.offsetHeight;
                        
                        let top = 0;
                        let left = 0;
                        
                        const sTop = rect.top - pad;
                        const sLeft = rect.left - pad;
                        const sWidth = rect.width + (pad * 2);
                        const sHeight = rect.height + (pad * 2);
                        
                        if (step.position === 'bottom') {
                            top = sTop + sHeight + 15;
                            left = sLeft + (sWidth / 2) - (toolWidth / 2);
                        } else if (step.position === 'top') {
                            top = sTop - toolHeight - 15;
                            left = sLeft + (sWidth / 2) - (toolWidth / 2);
                        } else if (step.position === 'left') {
                            top = sTop + (sHeight / 2) - (toolHeight / 2);
                            left = sLeft - toolWidth - 15;
                        } else if (step.position === 'right') {
                            top = sTop + (sHeight / 2) - (toolHeight / 2);
                            left = sLeft + sWidth + 15;
                        }
                        
                        if (left < 10) left = 10;
                        if (left + toolWidth > window.innerWidth) left = window.innerWidth - toolWidth - 10;
                        if (top < 10) top = 10;
                        if (top + toolHeight > window.innerHeight) top = window.innerHeight - toolHeight - 10;
                        
                        tooltip.style.top = `${top}px`;
                        tooltip.style.left = `${left}px`;
                    }
                });
            }
        }

        // Recalculate spotlight and tooltip positioning on scroll/resize
        window.addEventListener('resize', updateTourLayout);
        window.addEventListener('scroll', updateTourLayout);

        // Auto start tour on page load if session storage is empty
        window.addEventListener('load', () => {
            setTimeout(() => {
                if (!sessionStorage.getItem('onboarding_tour_played')) {
                    startOnboardingTour();
                }
            }, 800);
        });
    </script>

    <!-- Onboarding Tour Elements -->
    <div id="tour-backdrop" class="fixed inset-0 z-[99997] bg-transparent hidden pointer-events-auto"></div>

    <div id="tour-spotlight" class="fixed pointer-events-auto rounded-[24px] transition-all duration-300 z-[99998] hidden" style="box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.75); border: 2px solid #005bc0;"></div>
    
    <div id="tour-tooltip" class="fixed z-[99999] hidden w-[340px] bg-white/90 backdrop-blur-xl border border-white/30 rounded-3xl shadow-2xl p-6 transition-all duration-300 flex flex-col text-left">
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3 mb-4">
            <span id="tour-step-progress" class="text-[10px] font-black text-brand uppercase tracking-wider bg-blue-50 px-2.5 py-1 rounded-full">Step 1 of 6</span>
            <button onclick="skipOnboardingTour()" class="text-xs font-bold text-gray-400 hover:text-gray-600 transition-colors">Skip</button>
        </div>
        <h4 id="tour-step-title" class="text-base font-black text-gray-900 leading-tight">Step Title</h4>
        <p id="tour-step-desc" class="text-xs text-gray-500 font-medium mt-2 leading-relaxed">Step description goes here.</p>
        <div class="flex items-center justify-between gap-3 mt-6">
            <button id="tour-prev-btn" onclick="prevOnboardingStep()" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-extrabold text-xs py-2.5 px-4 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                Back
            </button>
            <button id="tour-next-btn" onclick="nextOnboardingStep()" class="bg-brand hover:bg-blue-700 text-white font-extrabold text-xs py-2.5 px-5 rounded-xl transition-all shadow-md">
                Next
            </button>
        </div>
    </div>

    <!-- Floating Help Tour Replay Button -->
    <button id="tour-restart-btn" onclick="startOnboardingTour()" class="fixed bottom-6 right-6 z-[9999] w-12 h-12 rounded-full bg-brand text-white shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all flex items-center justify-center group" title="Restart Product Tour">
        <span class="material-symbols-outlined text-2xl group-hover:rotate-45 transition-transform duration-300">help</span>
    </button>
</x-app-layout>
