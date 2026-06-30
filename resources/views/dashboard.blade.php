<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-900 tracking-tight">
                    {{ __('Customer Dashboard') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Welcome back! Monitor your laundry activities easily.</p>
            </div>
            <a href="{{ route('customer.orders.create') }}" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-brand to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-6 rounded-2xl shadow-[0_10px_20px_rgba(0,91,192,0.15)] hover:shadow-[0_10px_20px_rgba(0,91,192,0.3)] transition-all duration-300 transform hover:-translate-y-0.5">
                <span class="material-symbols-outlined text-[20px]">local_laundry_service</span>
                Book Laundry Now
            </a>
        </div>
    </x-slot>

    <div class="py-2 space-y-8">
        <!-- Welcoming Hero & Stats Overview Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Glassmorphic Promo Card -->
            <div class="lg:col-span-2 bg-gradient-to-br from-brand via-blue-600 to-blue-800 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden flex flex-col justify-between min-h-[220px] group">
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
                <!-- Stat Card 1 -->
                <div class="bg-white rounded-3xl p-6 shadow-md border border-gray-100 flex items-center justify-between hover:shadow-lg transition-shadow duration-300">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Total Spending</p>
                        <h4 class="text-2xl font-black text-gray-900 mt-1">Rp {{ number_format($totalSpending, 0, ',', '.') }}</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Paid transactions</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                    </div>
                </div>

                <!-- Stat Card 2 -->
                <div class="bg-white rounded-3xl p-6 shadow-md border border-gray-100 flex items-center justify-between hover:shadow-lg transition-shadow duration-300">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Order Activity</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-black text-brand">{{ $activeOrdersCount }}</span>
                            <span class="text-xs text-gray-500">Active</span>
                            <span class="text-gray-300 font-light">|</span>
                            <span class="text-lg font-black text-gray-700">{{ $completedOrdersCount }}</span>
                            <span class="text-xs text-gray-500">Completed</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">local_shipping</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Order Tracking Timeline Section -->
        <div class="bg-white rounded-3xl p-8 shadow-md border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand">track_changes</span>
                    Latest Laundry Order Status
                </h3>
                @if($latestOrder)
                    <a href="{{ route('customer.orders.show', $latestOrder->id) }}" class="text-xs font-bold text-brand hover:underline flex items-center gap-1">
                        Tracking Details
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                    </a>
                @endif
            </div>

            @if($latestOrder)
                <!-- Order Information Overview -->
                <div class="bg-gray-50 rounded-2xl p-6 mb-8 grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Order Code</span>
                        <p class="text-sm font-black text-brand mt-0.5">{{ $latestOrder->order_code }}</p>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Service & Category</span>
                        <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $latestOrder->service->name }} ({{ $latestOrder->itemType->name }})</p>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Soap & Fragrance</span>
                        <p class="text-sm font-medium text-gray-600 mt-0.5">{{ $latestOrder->soap ?? '-' }} / {{ $latestOrder->fragrance ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Payment Status</span>
                        <p class="mt-0.5">
                            @if($latestOrder->payment_status === 'paid')
                                <span class="px-2 py-0.5 text-[10px] font-black bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200">PAID</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-black bg-yellow-50 text-yellow-700 rounded-full border border-yellow-200">PENDING</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Timeline Visualization -->
                @php
                    $stages = [
                        'ordered' => ['label' => 'Order Placed', 'statuses' => ['pending_payment', 'waiting_pickup']],
                        'transit' => ['label' => 'Pickup & Transit', 'statuses' => ['picking_up', 'picked_up', 'in_transit_to_laundry']],
                        'processing' => ['label' => 'Processing', 'statuses' => ['arrived_at_laundry', 'washing', 'drying_ironing', 'packing']],
                        'delivery' => ['label' => 'Delivery & Done', 'statuses' => ['ready_for_delivery', 'delivering', 'completed']]
                    ];

                    // Find current stage index
                    $currentStageIdx = 0;
                    $status = $latestOrder->status;
                    $stageKeys = array_keys($stages);
                    foreach($stageKeys as $idx => $key) {
                        if (in_array($status, $stages[$key]['statuses'])) {
                            $currentStageIdx = $idx;
                            break;
                        }
                    }
                    if ($status === 'completed') {
                        $currentStageIdx = 3;
                    }
                @endphp

                <div class="relative py-8">
                    <!-- Progress Bar Line -->
                    <div class="absolute left-4 md:left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-gray-100 hidden md:block">
                        <div class="h-full bg-brand rounded-full transition-all duration-1000" style="width: {{ ($currentStageIdx / 3) * 100 }}%"></div>
                    </div>

                    <!-- Steps Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative z-10">
                        @foreach(array_values($stages) as $index => $stage)
                            @php
                                $isCompleted = $index < $currentStageIdx || $status === 'completed';
                                $isActive = $index === $currentStageIdx && $status !== 'completed';
                            @endphp
                            <div class="flex md:flex-col items-center md:text-center gap-4 md:gap-3">
                                <!-- Dot Circle icon -->
                                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-500 shrink-0
                                    {{ $isCompleted ? 'bg-brand text-white shadow-[0_0_15px_rgba(0,91,192,0.4)]' : '' }}
                                    {{ $isActive ? 'bg-blue-100 text-brand ring-4 ring-blue-50 border-2 border-brand animate-pulse' : '' }}
                                    {{ !$isCompleted && !$isActive ? 'bg-gray-100 text-gray-400' : '' }}
                                ">
                                    @if($isCompleted)
                                        <span class="material-symbols-outlined text-[18px] font-bold">check</span>
                                    @else
                                        <span class="font-bold text-xs">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                
                                <div>
                                    <h4 class="text-xs font-bold md:mt-2 transition-colors duration-300 {{ $isActive || $isCompleted ? 'text-gray-900' : 'text-gray-400' }}">
                                        {{ $stage['label'] }}
                                    </h4>
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        @if($isActive)
                                            <span class="text-brand font-medium">In Progress</span>
                                        @elseif($isCompleted)
                                            <span class="text-emerald-600 font-medium">Done</span>
                                        @else
                                            Not Started
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if(in_array($status, ['picking_up', 'delivering']))
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-100 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-brand text-3xl animate-bounce">local_shipping</span>
                            <div class="text-left">
                                <p class="text-sm font-bold text-blue-900">Courier is on the way!</p>
                                <p class="text-xs text-blue-700">Track delivery location in real-time on the order details page.</p>
                            </div>
                        </div>
                        <a href="{{ route('customer.orders.show', $latestOrder->id) }}" class="bg-brand text-white text-xs font-bold px-5 py-2.5 rounded-xl hover:bg-blue-700 transition-colors shadow">
                            Track Live
                        </a>
                    </div>
                @endif

            @else
                <div class="text-center py-10">
                    <span class="material-symbols-outlined text-gray-300 text-5xl">inventory_2</span>
                    <p class="text-gray-500 font-medium mt-3 text-sm">No active orders at the moment.</p>
                    <a href="{{ route('customer.orders.create') }}" class="text-brand font-bold text-xs mt-2 inline-block hover:underline">Create your first laundry order</a>
                </div>
            @endif
        </div>

        <!-- Assigned Courier History -->
        <div class="bg-white rounded-3xl p-8 shadow-md border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-6">
                <span class="material-symbols-outlined text-brand">contacts</span>
                Assigned Courier History
            </h3>

            @if($couriers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($couriers as $courier)
                        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex items-center justify-between hover:shadow-md transition-shadow duration-300 group">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand to-blue-600 text-white flex items-center justify-center font-black text-lg shadow-sm shrink-0">
                                    {{ substr($courier->name, 0, 1) }}
                                </div>
                                <div class="text-left">
                                    <h4 class="text-sm font-bold text-gray-800">{{ $courier->name }}</h4>
                                    <p class="text-[10px] text-gray-400 mt-0.5 uppercase font-bold tracking-wider font-jakarta">Laundryan Courier</p>
                                    <p class="text-xs text-gray-500 mt-1 font-mono">{{ $courier->phone }}</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-2">
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $courier->phone) }}" target="_blank" class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors shadow-sm" title="Contact Courier via WhatsApp">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.333 4.993L2 22l5.13-1.347a9.948 9.948 0 004.877 1.277h.005c5.505 0 9.989-4.478 9.99-9.985A9.97 9.97 0 0012.012 2zm6.069 13.985c-.25.702-1.246 1.285-1.71 1.342-.463.057-.927.278-3.003-.57a11.144 11.144 0 01-4.71-3.125 12.08 12.08 0 01-2.228-3.807c-.156-.475-.417-.79-.408-1.312.008-.521.217-.775.392-.953.175-.178.384-.263.576-.263.192 0 .384.004.549.012.176.009.349-.06.529.378.188.459.645 1.57.701 1.685.056.115.093.248.016.4-.076.152-.152.247-.29.414-.138.167-.296.347-.42.493-.138.156-.282.327-.122.602.16.275.711 1.17 1.523 1.89.963.854 1.867 1.13 2.143 1.268.275.137.435.114.596-.069.16-.183.69-.803.873-1.077.184-.275.367-.229.62-.137.253.091 1.6.753 1.875.891.275.137.458.206.52.312.062.106.062.612-.188 1.314z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-gray-500 text-sm italic">
                    No courier assignment history yet.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
