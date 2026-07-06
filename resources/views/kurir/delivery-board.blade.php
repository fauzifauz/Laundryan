<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-blue-600">
                    Courier Operations
                </p>

                <h2 class="mt-1 text-2xl font-black text-gray-900">
                    Delivery Board
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Daftar tugas pickup dan delivery yang sedang aktif.
                </p>
            </div>

            <span
                id="gps-status"
                class="rounded-full bg-gray-100 px-4 py-2 text-xs font-black text-gray-500"
            >
                📍 GPS menunggu...
            </span>
        </div>
    </x-slot>

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

        $pickupCount = $orders
            ->whereIn('status', $pickupStatuses)
            ->count();

        $deliveryCount = $orders->count() - $pickupCount;
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">
                        Total Aktif
                    </p>

                    <p class="mt-3 text-4xl font-black text-gray-900">
                        {{ $orders->count() }}
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-500">
                        Seluruh tugas kurir
                    </p>
                </div>

                <div class="rounded-3xl border border-orange-100 bg-orange-50 p-6">
                    <p class="text-xs font-black uppercase tracking-widest text-orange-500">
                        Pickup
                    </p>

                    <p class="mt-3 text-4xl font-black text-orange-700">
                        {{ $pickupCount }}
                    </p>

                    <p class="mt-1 text-sm font-semibold text-orange-600">
                        Penjemputan aktif
                    </p>
                </div>

                <div class="rounded-3xl border border-violet-100 bg-violet-50 p-6">
                    <p class="text-xs font-black uppercase tracking-widest text-violet-500">
                        Delivery
                    </p>

                    <p class="mt-3 text-4xl font-black text-violet-700">
                        {{ $deliveryCount }}
                    </p>

                    <p class="mt-1 text-sm font-semibold text-violet-600">
                        Pengantaran aktif
                    </p>
                </div>
            </section>

            <section>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">
                            Urutan Tugas
                        </h3>

                        <p class="text-sm text-gray-500">
                            Diurutkan berdasarkan lokasi terdekat.
                        </p>
                    </div>

                    <a
                        href="{{ route('kurir.orders.index') }}"
                        class="text-xs font-black uppercase tracking-widest text-blue-600 hover:text-blue-800"
                    >
                        Semua Order
                    </a>
                </div>

                <div class="space-y-4">
                    @forelse($orders as $index => $order)
                        @php
                            $isPickup = in_array(
                                $order->status,
                                $pickupStatuses,
                                true
                            );

                            $typeLabel = $isPickup
                                ? 'Pickup'
                                : 'Delivery';

                            $typeClass = $isPickup
                                ? 'bg-orange-50 text-orange-700'
                                : 'bg-violet-50 text-violet-700';

                            $typeIcon = $isPickup
                                ? 'package_2'
                                : 'local_shipping';

                            $statusLabel =
                                $statusLabels[$order->status]
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $order->status
                                    )
                                );

                            $address = $isPickup
                                ? $order->pickup_address
                                : $order->delivery_address;
                        @endphp

                        <article
                            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition hover:shadow-lg"
                            data-order-id="{{ $order->id }}"
                        >
                            <div class="flex flex-col gap-5 p-6 md:flex-row md:items-center">
                                <div class="flex items-center gap-4 md:w-24">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-900 text-sm font-black text-white">
                                        {{ $index + 1 }}
                                    </div>

                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl {{ $typeClass }}">
                                        <span class="material-symbols-outlined text-2xl">
                                            {{ $typeIcon }}
                                        </span>
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-xs font-black uppercase tracking-widest text-blue-600">
                                            {{ $order->order_code }}
                                        </p>

                                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase {{ $typeClass }}">
                                            {{ $typeLabel }}
                                        </span>
                                    </div>

                                    <h4 class="mt-2 text-lg font-black text-gray-900">
                                        {{ $order->customer?->name ?? 'Pelanggan' }}
                                    </h4>

                                    <p class="mt-1 text-xs font-semibold text-gray-500">
                                        {{ $order->service?->name ?? '-' }}
                                        •
                                        {{ $order->itemType?->name ?? '-' }}
                                    </p>

                                    <div class="mt-3 flex items-start gap-2 text-sm text-gray-600">
                                        <span class="material-symbols-outlined text-lg text-gray-400">
                                            location_on
                                        </span>

                                        <p>
                                            {{ $address ?: '-' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-shrink-0 flex-col gap-3 md:items-end">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                                        {{ $statusLabel }}
                                    </span>

                                    <a
                                        href="{{ route('kurir.orders.show', $order) }}"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-900 px-5 py-3 text-xs font-black text-white transition hover:bg-blue-600"
                                    >
                                        Buka Tugas

                                        <span class="material-symbols-outlined text-base">
                                            arrow_forward
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-green-50 text-green-600">
                                <span class="material-symbols-outlined text-4xl">
                                    task_alt
                                </span>
                            </div>

                            <h4 class="mt-5 text-lg font-black text-gray-800">
                                Tidak ada tugas aktif
                            </h4>

                            <p class="mt-2 text-sm text-gray-500">
                                Tugas akan muncul setelah diberikan oleh admin.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const gpsStatus = document.getElementById('gps-status');
            const locationUrl = @json(route('kurir.location.update'));
            const csrfToken = @json(csrf_token());

            const firstOrder = document.querySelector(
                '[data-order-id]'
            );

            const orderId = firstOrder
                ? firstOrder.dataset.orderId
                : null;

            let lastSentAt = 0;

            function setGpsStatus(message, className) {
                gpsStatus.textContent = message;
                gpsStatus.className = className;
            }

            function sendLocation(latitude, longitude) {
                const currentTime = Date.now();

                if (currentTime - lastSentAt < 10000) {
                    return;
                }

                lastSentAt = currentTime;

                fetch(locationUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        latitude: latitude,
                        longitude: longitude,
                        order_id: orderId,
                    }),
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Gagal mengirim lokasi');
                        }

                        return response.json();
                    })
                    .then(() => {
                        const time = new Date().toLocaleTimeString(
                            'id-ID',
                            {
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit',
                            }
                        );

                        setGpsStatus(
                            `📍 GPS aktif — ${time}`,
                            'rounded-full bg-green-100 px-4 py-2 text-xs font-black text-green-700'
                        );
                    })
                    .catch(() => {
                        setGpsStatus(
                            '⚠️ GPS gagal dikirim',
                            'rounded-full bg-red-100 px-4 py-2 text-xs font-black text-red-700'
                        );
                    });
            }

            if (!orderId) {
                setGpsStatus(
                    '📍 Tidak ada tugas aktif',
                    'rounded-full bg-gray-100 px-4 py-2 text-xs font-black text-gray-500'
                );

                return;
            }

            if (!navigator.geolocation) {
                setGpsStatus(
                    '❌ GPS tidak didukung',
                    'rounded-full bg-red-100 px-4 py-2 text-xs font-black text-red-700'
                );

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
                    setGpsStatus(
                        '⚠️ Izin GPS ditolak',
                        'rounded-full bg-yellow-100 px-4 py-2 text-xs font-black text-yellow-700'
                    );
                },
                {
                    enableHighAccuracy: true,
                    maximumAge: 5000,
                    timeout: 10000,
                }
            );
        });
    </script>
</x-app-layout>