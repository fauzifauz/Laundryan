<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Courier Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div id="flash-message"
                    class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm font-medium flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-600 text-base">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Active Tasks</h3>
                <span id="gps-status" class="text-[10px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-500">
                    📍 GPS Waiting...
                </span>
            </div>

            <div class="space-y-4" id="order-list">
                @forelse($orders as $order)
                    @php
                        // Determine next status buttons
                        $pickupStatuses = ['penjemputan', 'dijemput', 'diantar', 'sampai'];
                        $deliveryStatuses = ['pengantaran', 'diantarkan', 'selesai'];

                        $flowMap = [
                            // Pickup flow
                            'penjemputan' => ['label' => 'Confirm Pickup', 'next' => 'dijemput', 'color' => 'bg-amber-500 hover:bg-amber-600'],
                            'dijemput' => ['label' => 'Delivering to Laundry', 'next' => 'diantar', 'color' => 'bg-amber-600 hover:bg-amber-700'],
                            'diantar' => ['label' => 'Arrived at Laundry', 'next' => 'sampai', 'color' => 'bg-orange-500 hover:bg-orange-600'],
                            'sampai' => null, // Final pickup step — handled by laundry staff

                            // Delivery flow
                            'pengantaran' => ['label' => 'Confirm Delivery', 'next' => 'diantarkan', 'color' => 'bg-emerald-500 hover:bg-emerald-600'],
                            'diantarkan' => ['label' => 'Arrived at Customer', 'next' => 'selesai', 'color' => 'bg-emerald-600 hover:bg-emerald-700'],
                            'selesai' => null, // Final delivery step
                        ];

                        $isPickup = in_array($order->status, $pickupStatuses);
                        $flow = $flowMap[$order->status] ?? null;

                        $badgeColor = match (true) {
                            in_array($order->status, $pickupStatuses) => 'bg-amber-100 text-amber-700',
                            in_array($order->status, $deliveryStatuses) => 'bg-emerald-100 text-emerald-700',
                            default => 'bg-gray-100 text-gray-600',
                        };

                        $typeLabel = $isPickup ? 'Pickup' : 'Delivery';
                    @endphp

                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden"
                        data-order-id="{{ $order->id }}">

                        {{-- Header --}}
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                                    {{ $order->order_code }}</p>
                                <h4 class="text-base font-bold text-gray-900 mt-0.5">{{ $order->customer->name }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $order->service->name }} •
                                    {{ $order->itemType->name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase {{ $badgeColor }}">
                                    {{ $typeLabel }}
                                </span>
                                <p class="text-xs text-gray-400 mt-1 uppercase font-bold">
                                    {{ str_replace('_', ' ', $order->status) }}</p>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="px-5 py-3 bg-gray-50 grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="font-bold text-gray-400 uppercase tracking-wider mb-0.5">
                                    {{ $isPickup ? 'Pickup Address' : 'Delivery Address' }}
                                </p>
                                <p class="text-gray-700 font-medium">
                                    {{ $isPickup ? $order->pickup_address : $order->delivery_address }}
                                </p>
                            </div>
                            <div>
                                <p class="font-bold text-gray-400 uppercase tracking-wider mb-0.5">Schedule</p>
                                <p class="text-gray-700 font-medium">{{ $order->pickup_time->format('H:i, d M Y') }}</p>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="px-5 py-4 flex flex-col gap-2">
                            @if($flow)
                                <form method="POST" action="{{ route('kurir.orders.status', $order->id) }}" class="status-form">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $flow['next'] }}">
                                    <button type="submit"
                                        class="w-full {{ $flow['color'] }} text-white font-bold py-3 rounded-xl text-sm transition-all active:scale-95 flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                                        {{ $flow['label'] }}
                                    </button>
                                </form>
                            @else
                                <div class="text-center py-2 text-sm text-gray-500 font-medium italic">
                                    ✅ Task completed — waiting for admin action.
                                </div>
                            @endif

                            <a href="{{ route('kurir.orders.show', $order->id) }}"
                                class="w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl text-sm transition-all">
                                Order Details
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-20 text-gray-400">
                        <span class="material-symbols-outlined text-5xl mb-3 block">task_alt</span>
                        <p class="font-medium">No active tasks. Great job!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusEl = document.getElementById('gps-status');
            const LOCATION_URL = '{{ route("kurir.location.update") }}';
            const CSRF_TOKEN = '{{ csrf_token() }}';

            // Get first active order id (for tagging location updates)
            const firstOrderEl = document.querySelector('[data-order-id]');
            const orderId = firstOrderEl ? firstOrderEl.dataset.orderId : null;

            function sendLocation(lat, lng) {
                fetch(LOCATION_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng,
                        order_id: orderId,
                    })
                })
                    .then(r => r.json())
                    .then(() => {
                        const now = new Date().toLocaleTimeString('en-US', { hour12: false });
                        statusEl.textContent = `📍 GPS Active — ${now}`;
                        statusEl.className = 'text-[10px] font-bold px-2 py-1 rounded-full bg-green-100 text-green-700';
                    })
                    .catch(() => {
                        statusEl.textContent = '⚠️ GPS Failed';
                        statusEl.className = 'text-[10px] font-bold px-2 py-1 rounded-full bg-red-100 text-red-600';
                    });
            }

            function startTracking() {
                if (!navigator.geolocation) {
                    statusEl.textContent = '❌ GPS not supported';
                    return;
                }

                // Watch position for real GPS movement
                navigator.geolocation.watchPosition(
                    (pos) => {
                        sendLocation(pos.coords.latitude, pos.coords.longitude);
                    },
                    (err) => {
                        statusEl.textContent = '⚠️ GPS Permission Denied';
                        statusEl.className = 'text-[10px] font-bold px-2 py-1 rounded-full bg-yellow-100 text-yellow-700';
                    },
                    { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 }
                );
            }

            // Start sending location immediately if there are active orders
            @if($orders->count() > 0)
                startTracking();
            @endif

        // Auto-dismiss flash message
        const flash = document.getElementById('flash-message');
            if (flash) setTimeout(() => flash.remove(), 4000);
        });
    </script>
</x-app-layout>