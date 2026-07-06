<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Courier Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if(session('success'))
                <div
                    id="flash-message"
                    class="flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800"
                >
                    <span class="material-symbols-outlined text-base text-green-600">
                        check_circle
                    </span>

                    {{ session('success') }}
                </div>
            @endif

            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">
                    Active Tasks
                </h3>

                <span
                    id="gps-status"
                    class="rounded-full bg-gray-100 px-2 py-1 text-[10px] font-bold text-gray-500"
                >
                    📍 GPS Waiting...
                </span>
            </div>

            <div class="space-y-4" id="order-list">
                @forelse($orders as $order)
                    @php
                        $pickupStatuses = [
                            'waiting_pickup',
                            'picking_up',
                            'picked_up',
                            'in_transit_to_laundry',
                            'penjemputan',
                            'dijemput',
                            'diantar',
                            'sampai',
                        ];

                        $statusLabels = [
                            'waiting_pickup' => 'Menunggu Penjemputan',
                            'picking_up' => 'Proses Penjemputan',
                            'picked_up' => 'Laundry Dijemput',
                            'in_transit_to_laundry' => 'Dalam Perjalanan ke Laundry',
                            'arrived_at_laundry' => 'Sampai di Laundry',

                            'ready_for_delivery' => 'Siap Diantar',
                            'delivering' => 'Dalam Pengantaran',
                            'completed' => 'Selesai Diantar',

                            'penjemputan' => 'Proses Penjemputan',
                            'dijemput' => 'Laundry Dijemput',
                            'diantar' => 'Dalam Perjalanan ke Laundry',
                            'sampai' => 'Sampai di Laundry',
                            'pengantaran' => 'Dalam Pengantaran',
                            'diantarkan' => 'Selesai Diantar',
                            'selesai' => 'Selesai',
                        ];

                        $isPickup = in_array(
                            $order->status,
                            $pickupStatuses,
                            true
                        );

                        $typeLabel = $isPickup
                            ? 'Pickup'
                            : 'Delivery';

                        $badgeColor = $isPickup
                            ? 'bg-amber-100 text-amber-700'
                            : 'bg-emerald-100 text-emerald-700';

                        $statusLabel = $statusLabels[$order->status]
                            ?? ucfirst(str_replace('_', ' ', $order->status));

                        $address = $isPickup
                            ? $order->pickup_address
                            : $order->delivery_address;
                    @endphp

                    <div
                        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
                        data-order-id="{{ $order->id }}"
                    >
                        <div class="flex items-start justify-between border-b border-gray-100 px-5 py-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">
                                    {{ $order->order_code }}
                                </p>

                                <h4 class="mt-1 text-base font-bold text-gray-900">
                                    {{ $order->customer->name }}
                                </h4>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $order->service->name }}
                                    •
                                    {{ $order->itemType->name }}
                                </p>
                            </div>

                            <div class="text-right">
                                <span
                                    class="rounded-full px-3 py-1 text-[11px] font-black uppercase {{ $badgeColor }}"
                                >
                                    {{ $typeLabel }}
                                </span>

                                <p class="mt-2 max-w-40 text-xs font-bold text-gray-500">
                                    {{ $statusLabel }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 bg-gray-50 px-5 py-4 text-xs sm:grid-cols-2">
                            <div>
                                <p class="mb-1 font-bold uppercase tracking-wider text-gray-400">
                                    {{ $isPickup
                                        ? 'Pickup Address'
                                        : 'Delivery Address' }}
                                </p>

                                <p class="font-medium leading-relaxed text-gray-700">
                                    {{ $address }}
                                </p>
                            </div>

                            <div>
                                <p class="mb-1 font-bold uppercase tracking-wider text-gray-400">
                                    Schedule
                                </p>

                                <p class="font-medium text-gray-700">
                                    {{ $order->pickup_time
                                        ? $order->pickup_time->format('H:i, d M Y')
                                        : '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="px-5 py-4">
                            <p class="mb-3 text-center text-xs text-gray-500">
                                Update status dan unggah foto bukti dilakukan melalui detail order.
                            </p>

                            <a
                                href="{{ route('kurir.orders.show', $order->id) }}"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-sm font-bold text-white transition-all hover:bg-blue-700 active:scale-95"
                            >
                                <span class="material-symbols-outlined text-base">
                                    open_in_new
                                </span>

                                Buka Detail Order
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-20 text-center text-gray-400">
                        <span class="material-symbols-outlined mb-3 block text-5xl">
                            task_alt
                        </span>

                        <p class="font-medium">
                            Tidak ada tugas aktif.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusElement = document.getElementById('gps-status');
            const locationUrl = '{{ route("kurir.location.update") }}';
            const csrfToken = '{{ csrf_token() }}';

            const firstOrderElement = document.querySelector(
                '[data-order-id]'
            );

            const orderId = firstOrderElement
                ? firstOrderElement.dataset.orderId
                : null;

            function sendLocation(latitude, longitude) {
                fetch(locationUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        latitude: latitude,
                        longitude: longitude,
                        order_id: orderId,
                    }),
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(
                                'Location request failed.'
                            );
                        }

                        return response.json();
                    })
                    .then(() => {
                        const currentTime = new Date()
                            .toLocaleTimeString(
                                'id-ID',
                                {
                                    hour12: false,
                                }
                            );

                        statusElement.textContent =
                            `📍 GPS Active — ${currentTime}`;

                        statusElement.className =
                            'rounded-full bg-green-100 px-2 py-1 text-[10px] font-bold text-green-700';
                    })
                    .catch(() => {
                        statusElement.textContent =
                            '⚠️ GPS Failed';

                        statusElement.className =
                            'rounded-full bg-red-100 px-2 py-1 text-[10px] font-bold text-red-600';
                    });
            }

            function startTracking() {
                if (!navigator.geolocation) {
                    statusElement.textContent =
                        '❌ GPS not supported';

                    return;
                }

                navigator.geolocation.watchPosition(
                    position => {
                        sendLocation(
                            position.coords.latitude,
                            position.coords.longitude
                        );
                    },
                    () => {
                        statusElement.textContent =
                            '⚠️ GPS Permission Denied';

                        statusElement.className =
                            'rounded-full bg-yellow-100 px-2 py-1 text-[10px] font-bold text-yellow-700';
                    },
                    {
                        enableHighAccuracy: true,
                        maximumAge: 5000,
                        timeout: 10000,
                    }
                );
            }

            @if($orders->isNotEmpty())
                startTracking();
            @endif

            const flashMessage = document.getElementById(
                'flash-message'
            );

            if (flashMessage) {
                setTimeout(
                    () => flashMessage.remove(),
                    4000
                );
            }
        });
    </script>
</x-app-layout>