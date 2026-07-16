<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-gray-900">
                    Delivery Board
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    List of your currently active pickup and delivery tasks.
                </p>
            </div>

            <div
                id="gps-status-card"
                class="inline-flex items-center gap-3 rounded-2xl border border-gray-100 bg-white px-4 py-2.5 shadow-sm transition hover:shadow-md"
            >
                <span class="relative flex h-2.5 w-2.5">
                    <span
                        id="gps-status-ping"
                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-gray-300 opacity-75"
                    ></span>

                    <span
                        id="gps-status-dot"
                        class="relative inline-flex h-2.5 w-2.5 rounded-full bg-gray-400"
                    ></span>
                </span>

                <div class="leading-tight">
                    <p id="gps-status-label" class="text-[11px] font-black uppercase tracking-widest text-gray-600">
                        GPS Pending
                    </p>

                    <p id="gps-status-time" class="text-[10px] font-semibold text-gray-400">
                        Waiting for signal…
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Local toast notifications --}}
    <div
        x-data="{
            toasts: [],
            push(type, message) {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, type, message });
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 4000);
            }
        }"
        x-init="window.addEventListener('board-toast', (e) => push(e.detail.type, e.detail.message))"
        class="pointer-events-none fixed top-6 right-6 z-[9999] flex w-full max-w-sm flex-col gap-3"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="true"
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="pointer-events-auto flex items-start gap-3 rounded-2xl border p-4 shadow-xl"
                :class="{
                    'bg-emerald-50 border-emerald-200 text-emerald-800': toast.type === 'success',
                    'bg-rose-50 border-rose-200 text-rose-800': toast.type === 'error',
                    'bg-amber-50 border-amber-200 text-amber-800': toast.type === 'warning',
                }"
            >
                <span
                    class="material-symbols-outlined text-lg"
                    x-text="toast.type === 'success' ? 'check_circle' : (toast.type === 'error' ? 'error' : 'warning')"
                ></span>

                <p class="text-xs font-bold leading-snug" x-text="toast.message"></p>
            </div>
        </template>
    </div>

    @php
        $pickupCount = $statistics['pickup_active'] ?? 0;
        $deliveryCount = $statistics['delivery_active'] ?? 0;
        $totalCount = $statistics['total'] ?? $orders->count();
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Info Grid --}}
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-900 text-white">
                            <span class="material-symbols-outlined">
                                inventory_2
                            </span>
                        </div>

                        <p class="text-xs font-black uppercase tracking-widest text-gray-400">
                            Total
                        </p>
                    </div>

                    <p class="mt-4 text-4xl font-black text-gray-900">
                        {{ $totalCount }}
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-500">
                        All active tasks
                    </p>
                </div>

                
                    <a href="{{ route('kurir.orders.index', ['scope' => 'active', 'type' => 'pickup']) }}"
                    class="rounded-3xl border border-orange-100 bg-orange-50 p-6 transition hover:-translate-y-1 hover:shadow-lg"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-500 text-white">
                            <span class="material-symbols-outlined">
                                package_2
                            </span>
                        </div>

                        <p class="text-xs font-black uppercase tracking-widest text-orange-500">
                            Pickup
                        </p>
                    </div>

                    <p class="mt-4 text-4xl font-black text-orange-700">
                        {{ $pickupCount }}
                    </p>

                    <p class="mt-1 text-sm font-semibold text-orange-600">
                        Total Active Pickup
                    </p>
                </a>

                
                    <a href="{{ route('kurir.orders.index', ['scope' => 'active', 'type' => 'delivery']) }}"
                    class="rounded-3xl border border-violet-100 bg-violet-50 p-6 transition hover:-translate-y-1 hover:shadow-lg"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-500 text-white">
                            <span class="material-symbols-outlined">
                                local_shipping
                            </span>
                        </div>

                        <p class="text-xs font-black uppercase tracking-widest text-violet-500">
                            Delivery
                        </p>
                    </div>

                    <p class="mt-4 text-4xl font-black text-violet-700">
                        {{ $deliveryCount }}
                    </p>

                    <p class="mt-1 text-sm font-semibold text-violet-600">
                        Total Active Delivery
                    </p>
                </a>
            </section>

            {{-- Task Queue --}}
            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">
                            Task Queue
                        </h3>

                        <p class="text-sm text-gray-500">
                            Sorted by nearest location.
                        </p>
                    </div>

                    
                        <a href="{{ route('kurir.orders.index') }}"
                        class="text-xs font-black uppercase tracking-widest text-blue-600 hover:text-blue-800"
                    >
                        All Orders
                    </a>
                </div>

                <div
                    id="order-queue-list"
                    class="scroll-thin flex max-h-[720px] flex-col gap-4 overflow-y-auto pr-1"
                >
                    @forelse($orders as $index => $order)
                        @php
                            $isPickup = in_array(
                                $order->status,
                                $pickupStatuses ?? [],
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
                            class="flex-shrink-0 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition hover:shadow-lg"
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
                                        {{ $order->customer?->name ?? 'Customer' }}
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

                                    
                                        <a href="{{ route('kurir.orders.show', $order) }}"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-900 px-5 py-3 text-xs font-black text-white transition hover:bg-blue-600"
                                    >
                                        Open Task

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
                                No active tasks
                            </h4>

                            <p class="mt-2 text-sm text-gray-500">
                                New tasks will appear once assigned by the admin.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <style>
        .scroll-thin {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .scroll-thin::-webkit-scrollbar {
            width: 6px;
        }

        .scroll-thin::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 9999px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const gpsDot = document.getElementById('gps-status-dot');
            const gpsPing = document.getElementById('gps-status-ping');
            const gpsLabel = document.getElementById('gps-status-label');
            const gpsTime = document.getElementById('gps-status-time');

            const locationUrl = @json(route('kurir.location.update'));
            const csrfToken = @json(csrf_token());

            const firstOrder = document.querySelector('[data-order-id]');
            const orderId = firstOrder ? firstOrder.dataset.orderId : null;

            let lastSentAt = 0;
            let hasShownActiveToast = false;
            let hasShownDeniedToast = false;

            function fireToast(type, message) {
                window.dispatchEvent(
                    new CustomEvent('board-toast', {
                        detail: { type, message },
                    })
                );
            }

            function updateGpsStatus(label, timeText, colorKey) {
                const colors = {
                    gray: { dot: 'bg-gray-400', ping: 'bg-gray-300', text: 'text-gray-600' },
                    green: { dot: 'bg-green-500', ping: 'bg-green-400', text: 'text-green-700' },
                    red: { dot: 'bg-red-500', ping: 'bg-red-400', text: 'text-red-700' },
                    yellow: { dot: 'bg-amber-500', ping: 'bg-amber-400', text: 'text-amber-700' },
                };

                const c = colors[colorKey] || colors.gray;

                gpsDot.className = `relative inline-flex h-2.5 w-2.5 rounded-full ${c.dot}`;
                gpsPing.className = `absolute inline-flex h-full w-full animate-ping rounded-full opacity-75 ${c.ping}`;
                gpsLabel.className = `text-[11px] font-black uppercase tracking-widest ${c.text}`;
                gpsLabel.textContent = label;
                gpsTime.textContent = timeText;
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
                            throw new Error('Failed to send location.');
                        }

                        return response.json();
                    })
                    .then(() => {
                        const time = new Date().toLocaleTimeString(
                            'en-US',
                            {
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit',
                            }
                        );

                        updateGpsStatus('GPS Active', `Last update — ${time}`, 'green');

                        if (!hasShownActiveToast) {
                            hasShownActiveToast = true;
                            fireToast('success', 'GPS tracking is now active.');
                        }
                    })
                    .catch(() => {
                        updateGpsStatus('GPS Failed', 'Could not send location', 'red');
                        fireToast('error', 'Failed to send your location. Retrying…');
                    });
            }

            if (!orderId) {
                updateGpsStatus('No Active Tasks', 'GPS tracking is idle', 'gray');
                fireToast('warning', 'You have no active tasks right now.');
            } else if (!navigator.geolocation) {
                updateGpsStatus('GPS Unsupported', 'This browser has no GPS support', 'red');
                fireToast('error', 'Your browser does not support GPS.');
            } else {
                navigator.geolocation.watchPosition(
                    position => {
                        sendLocation(
                            position.coords.latitude,
                            position.coords.longitude
                        );
                    },
                    () => {
                        updateGpsStatus('GPS Denied', 'Location permission was denied', 'yellow');

                        if (!hasShownDeniedToast) {
                            hasShownDeniedToast = true;
                            fireToast('warning', 'GPS permission was denied. Please enable location access.');
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        maximumAge: 5000,
                        timeout: 10000,
                    }
                );
            }
        });
    </script>
</x-app-layout>