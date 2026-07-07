<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-blue-600">
                    Courier Operations
                </p>

                <h2 class="mt-1 text-2xl font-black text-gray-900">
                    Dashboard Kurir
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Ringkasan tugas pickup, delivery, aktivitas, dan performa.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span
                    id="gps-status"
                    class="rounded-full bg-gray-100 px-4 py-2 text-xs font-black text-gray-500"
                >
                    📍 GPS menunggu...
                </span>

                <a
                    href="{{ route('kurir.orders.index') }}"
                    class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-blue-700"
                >
                    <span class="material-symbols-outlined text-lg">
                        shopping_basket
                    </span>

                    Order & Riwayat
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $pickupStatuses = [
            'waiting_pickup',
            'picking_up',
            'picked_up',
            'in_transit_to_laundry',
            'arrived_at_laundry',
            'penjemputan',
            'dijemput',
            'diantar',
            'sampai',
        ];

        $deliveryStatuses = [
            'ready_for_delivery',
            'delivering',
            'pengantaran',
            'diantarkan',
        ];

        $ratingAverage = (float) ($ratingSummary['average'] ?? 0);
        $roundedRating = (int) round($ratingAverage);
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div
                    id="flash-message"
                    class="flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800"
                >
                    <span class="material-symbols-outlined text-green-600">
                        check_circle
                    </span>

                    {{ session('success') }}
                </div>
            @endif

            {{-- Welcome Banner --}}
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 p-7 text-white shadow-xl">
                <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-white/10"></div>

                <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/20 bg-white/10 backdrop-blur">
                            <span class="material-symbols-outlined text-4xl">
                                local_shipping
                            </span>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.25em] text-blue-100">
                                Selamat datang
                            </p>

                            <h1 class="mt-1 text-2xl font-black md:text-3xl">
                                {{ auth()->user()->name }}
                            </h1>

                            <p class="mt-1 text-sm font-medium text-blue-100">
                                Pastikan lokasi aktif selama menangani tugas.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/20 bg-white/10 px-5 py-3 backdrop-blur">
                        <p class="text-xs font-black uppercase tracking-widest text-blue-100">
                            Hari ini
                        </p>

                        <p class="mt-1 font-bold">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                </div>
            </section>

            {{-- Statistics --}}
            <section class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                <a
                    href="{{ route('kurir.orders.index', [
                        'scope' => 'active',
                        'type' => 'pickup',
                    ]) }}"
                    class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-50 text-orange-600">
                            <span class="material-symbols-outlined">
                                package_2
                            </span>
                        </div>

                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                            Pickup
                        </span>
                    </div>

                    <p class="mt-4 text-3xl font-black text-gray-900">
                        {{ number_format($statistics['pickup_active'] ?? 0) }}
                    </p>

                    <p class="mt-1 text-xs font-semibold text-gray-500">
                        Tugas pickup aktif
                    </p>
                </a>

                <a
                    href="{{ route('kurir.orders.index', [
                        'scope' => 'active',
                        'type' => 'delivery',
                    ]) }}"
                    class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                            <span class="material-symbols-outlined">
                                local_shipping
                            </span>
                        </div>

                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                            Delivery
                        </span>
                    </div>

                    <p class="mt-4 text-3xl font-black text-gray-900">
                        {{ number_format($statistics['delivery_active'] ?? 0) }}
                    </p>

                    <p class="mt-1 text-xs font-semibold text-gray-500">
                        Tugas delivery aktif
                    </p>
                </a>

                <a
                    href="{{ route('kurir.orders.index', [
                        'scope' => 'completed',
                    ]) }}"
                    class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <span class="material-symbols-outlined">
                                task_alt
                            </span>
                        </div>

                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                            Selesai
                        </span>
                    </div>

                    <p class="mt-4 text-3xl font-black text-gray-900">
                        {{ number_format($statistics['completed'] ?? 0) }}
                    </p>

                    <p class="mt-1 text-xs font-semibold text-gray-500">
                        Order diselesaikan
                    </p>
                </a>

                <a
                    href="{{ route('kurir.orders.index', [
                        'scope' => 'all',
                    ]) }}"
                    class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <span class="material-symbols-outlined">
                                inventory_2
                            </span>
                        </div>

                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                            Total
                        </span>
                    </div>

                    <p class="mt-4 text-3xl font-black text-gray-900">
                        {{ number_format($statistics['total'] ?? 0) }}
                    </p>

                    <p class="mt-1 text-xs font-semibold text-gray-500">
                        Seluruh penugasan
                    </p>
                </a>

                <div class="col-span-2 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm lg:col-span-1">
                    <div class="flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                            <span class="material-symbols-outlined">
                                star
                            </span>
                        </div>

                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                            Performa
                        </span>
                    </div>

                    <div class="mt-4 flex items-end gap-2">
                        <p class="text-3xl font-black text-gray-900">
                            {{ number_format($ratingAverage, 1) }}
                        </p>

                        <p class="pb-1 text-xs font-bold text-gray-400">
                            / 5
                        </p>
                    </div>

                    <div class="mt-2 flex items-center gap-1">
                        @for($star = 1; $star <= 5; $star++)
                            <span
                                class="material-symbols-outlined text-lg {{ $star <= $roundedRating ? 'text-amber-400' : 'text-gray-200' }}"
                            >
                                star
                            </span>
                        @endfor
                    </div>

                    <p class="mt-2 text-xs font-semibold text-gray-500">
                        {{ number_format($ratingSummary['count'] ?? 0) }}
                        penilaian pelanggan
                    </p>
                </div>
            </section>

            <div class="grid grid-cols-1 items-stretch gap-6 xl:grid-cols-3">
                {{-- Active Tasks --}}
                <section class="flex h-full flex-col xl:col-span-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-black text-gray-900">
                                Tugas Aktif
                            </h3>

                            <p class="text-sm text-gray-500">
                                Urutan tugas berdasarkan lokasi terdekat.
                            </p>
                        </div>

                        <a
                            href="{{ route('kurir.orders.index', ['scope' => 'active']) }}"
                            class="text-xs font-black uppercase tracking-widest text-blue-600 hover:text-blue-800"
                        >
                            Lihat semua
                        </a>
                    </div>

                    <div id="order-list" class="mt-4 flex flex-1 flex-col gap-4">
                        @forelse($orders->take(3) as $order)
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

                                $currentStatusLabel =
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

                                $customerName =
                                    $order->customer?->name
                                    ?? 'Pelanggan';
                            @endphp

                            <article
                                class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition hover:shadow-lg"
                                data-order-id="{{ $order->id }}"
                            >
                                <div class="flex flex-col gap-5 p-6 sm:flex-row sm:items-center">
                                    <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl {{ $typeClass }}">
                                        <span class="material-symbols-outlined text-2xl">
                                            {{ $typeIcon }}
                                        </span>
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
                                            {{ $customerName }}
                                        </h4>

                                        <p class="mt-1 text-xs font-semibold text-gray-500">
                                            {{ $order->service?->name ?? '-' }}
                                            •
                                            {{ $order->itemType?->name ?? '-' }}
                                        </p>

                                        <div class="mt-3 flex items-start gap-2 text-xs font-medium text-gray-600">
                                            <span class="material-symbols-outlined text-base text-gray-400">
                                                location_on
                                            </span>

                                            <p>
                                                {{ $address ?: '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-shrink-0 flex-col gap-3 sm:items-end">
                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                                            {{ $currentStatusLabel }}
                                        </span>

                                        @if($order->pickup_time)
                                            <p class="text-xs font-semibold text-gray-500">
                                                {{ $order->pickup_time->format('d M Y, H:i') }}
                                            </p>
                                        @endif

                                        <a
                                            href="{{ route('kurir.orders.show', $order) }}"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-900 px-5 py-3 text-xs font-black text-white transition hover:bg-blue-600"
                                        >
                                            Buka Detail

                                            <span class="material-symbols-outlined text-base">
                                                arrow_forward
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="flex flex-1 flex-col items-center justify-center rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-green-50 text-green-600">
                                    <span class="material-symbols-outlined text-4xl">
                                        task_alt
                                    </span>
                                </div>

                                <h4 class="mt-5 text-lg font-black text-gray-800">
                                    Tidak ada tugas aktif
                                </h4>

                                <p class="mt-2 text-sm text-gray-500">
                                    Tugas baru akan muncul setelah ditugaskan oleh admin.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>

                {{-- Performance --}}
                <aside class="flex h-full flex-col gap-6">
                    <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                                <span class="material-symbols-outlined">
                                    workspace_premium
                                </span>
                            </div>

                            <div>
                                <h3 class="font-black text-gray-900">
                                    Performa Kurir
                                </h3>

                                <p class="text-xs text-gray-500">
                                    Berdasarkan rating pelanggan
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-5">
                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">
                                        Rating Pickup
                                    </p>

                                    <p class="font-black text-gray-900">
                                        {{ number_format($ratingSummary['pickup_average'] ?? 0, 1) }}
                                    </p>
                                </div>

                                <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div
                                        class="h-full rounded-full bg-orange-500"
                                        style="width: {{ min(100, (($ratingSummary['pickup_average'] ?? 0) / 5) * 100) }}%"
                                    ></div>
                                </div>
                            </div>

                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">
                                        Rating Delivery
                                    </p>

                                    <p class="font-black text-gray-900">
                                        {{ number_format($ratingSummary['delivery_average'] ?? 0, 1) }}
                                    </p>
                                </div>

                                <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div
                                        class="h-full rounded-full bg-violet-500"
                                        style="width: {{ min(100, (($ratingSummary['delivery_average'] ?? 0) / 5) * 100) }}%"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="flex flex-1 flex-col rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-black text-gray-900">
                                    Aktivitas Hari Ini
                                </h3>

                                <p class="text-xs text-gray-500">
                                    Perubahan status terbaru
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($recentActivities->count() > 4)
                                    <button
                                        type="button"
                                        id="activity-scroll-left"
                                        aria-label="Geser aktivitas ke kiri"
                                        class="flex h-8 w-8 items-center justify-center rounded-xl border border-gray-200 text-gray-400 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600"
                                    >
                                        <span class="material-symbols-outlined text-lg">
                                            chevron_left
                                        </span>
                                    </button>

                                    <button
                                        type="button"
                                        id="activity-scroll-right"
                                        aria-label="Geser aktivitas ke kanan"
                                        class="flex h-8 w-8 items-center justify-center rounded-xl border border-gray-200 text-gray-400 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600"
                                    >
                                        <span class="material-symbols-outlined text-lg">
                                            chevron_right
                                        </span>
                                    </button>
                                @endif

                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                    <span class="material-symbols-outlined">
                                        history
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            id="activity-list"
                            class="activity-scroll-x mt-5 flex flex-1 snap-x snap-mandatory overflow-x-auto scroll-smooth"
                        >
                            @forelse($recentActivities->chunk(4) as $activityPage)
                                <div class="w-full flex-shrink-0 snap-start space-y-4 pr-1">
                                    @foreach($activityPage as $activity)
                                        @php
                                            $activityStatusLabel =
                                                $statusLabels[$activity->status]
                                                ?? ucfirst(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $activity->status
                                                    )
                                                );
                                        @endphp

                                        <div class="flex gap-3">
                                            <div class="mt-1 h-3 w-3 flex-shrink-0 rounded-full bg-blue-500 ring-4 ring-blue-50"></div>

                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-xs font-black text-gray-800">
                                                    {{ $activity->order?->order_code ?? '-' }}
                                                    —
                                                    {{ $activityStatusLabel }}
                                                </p>

                                                <p class="mt-1 truncate text-xs text-gray-500">
                                                    {{ $activity->order?->customer?->name ?? 'Pelanggan' }}
                                                </p>

                                                <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                                    {{ $activity->created_at->format('H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @empty
                                <div class="flex w-full flex-shrink-0 flex-1 flex-col items-center justify-center rounded-2xl bg-gray-50 px-4 py-8 text-center">
                                    <span class="material-symbols-outlined text-3xl text-gray-300">
                                        history_toggle_off
                                    </span>

                                    <p class="mt-2 text-xs font-semibold text-gray-500">
                                        Belum ada aktivitas hari ini.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </div>

            {{-- Recent Orders --}}
            <section class="rounded-3xl border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">
                            Riwayat Order Terbaru
                        </h3>

                        <p class="text-sm text-gray-500">
                            Lima order terakhir yang ditugaskan.
                        </p>
                    </div>

                    <a
                        href="{{ route('kurir.orders.index', ['scope' => 'all']) }}"
                        class="text-xs font-black uppercase tracking-widest text-blue-600 hover:text-blue-800"
                    >
                        Semua riwayat
                    </a>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($recentOrders as $recentOrder)
                        @php
                            $recentStatusLabel =
                                $statusLabels[$recentOrder->status]
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $recentOrder->status
                                    )
                                );

                            $recentIsPickup = in_array(
                                $recentOrder->status,
                                $pickupStatuses,
                                true
                            );
                        @endphp

                        <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center">
                            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl {{ $recentIsPickup ? 'bg-orange-50 text-orange-600' : 'bg-violet-50 text-violet-600' }}">
                                <span class="material-symbols-outlined">
                                    {{ $recentIsPickup ? 'package_2' : 'local_shipping' }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-black uppercase tracking-widest text-blue-600">
                                    {{ $recentOrder->order_code }}
                                </p>

                                <h4 class="mt-1 truncate font-black text-gray-900">
                                    {{ $recentOrder->customer?->name ?? 'Pelanggan' }}
                                </h4>

                                <p class="mt-1 text-xs font-semibold text-gray-500">
                                    {{ $recentOrder->service?->name ?? '-' }}
                                    •
                                    {{ $recentOrder->itemType?->name ?? '-' }}
                                </p>
                            </div>

                            <div class="sm:text-right">
                                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">
                                    {{ $recentStatusLabel }}
                                </span>

                                <p class="mt-2 text-xs font-semibold text-gray-400">
                                    {{ $recentOrder->updated_at->format('d M Y, H:i') }}
                                </p>
                            </div>

                            <a
                                href="{{ route('kurir.orders.show', $recentOrder) }}"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-600 transition hover:bg-blue-600 hover:text-white"
                            >
                                <span class="material-symbols-outlined">
                                    arrow_forward
                                </span>
                            </a>
                        </div>
                    @empty
                        <div class="px-6 py-14 text-center">
                            <p class="text-sm font-semibold text-gray-500">
                                Belum ada riwayat order.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <style>
        .activity-scroll-x {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .activity-scroll-x::-webkit-scrollbar {
            display: none;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const gpsStatus = document.getElementById('gps-status');
            const locationUrl = @json(route('kurir.location.update'));
            const csrfToken = @json(csrf_token());

            const firstOrderElement = document.querySelector(
                '[data-order-id]'
            );

            const orderId = firstOrderElement
                ? firstOrderElement.dataset.orderId
                : null;

            let lastSentAt = 0;

            function updateGpsStatus(message, className) {
                gpsStatus.textContent = message;
                gpsStatus.className = className;
            }

            function sendLocation(latitude, longitude) {
                const now = Date.now();

                if (now - lastSentAt < 10000) {
                    return;
                }

                lastSentAt = now;

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
                            throw new Error('Gagal mengirim lokasi.');
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

                        updateGpsStatus(
                            `📍 GPS aktif — ${time}`,
                            'rounded-full bg-green-100 px-4 py-2 text-xs font-black text-green-700'
                        );
                    })
                    .catch(() => {
                        updateGpsStatus(
                            '⚠️ GPS gagal dikirim',
                            'rounded-full bg-red-100 px-4 py-2 text-xs font-black text-red-700'
                        );
                    });
            }

            function startTracking() {
                if (!navigator.geolocation) {
                    updateGpsStatus(
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
                        updateGpsStatus(
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
            }

            @if($orders->isNotEmpty())
                startTracking();
            @else
                updateGpsStatus(
                    '📍 Tidak ada tugas aktif',
                    'rounded-full bg-gray-100 px-4 py-2 text-xs font-black text-gray-500'
                );
            @endif

            const activityList = document.getElementById('activity-list');
            const activityScrollLeft = document.getElementById('activity-scroll-left');
            const activityScrollRight = document.getElementById('activity-scroll-right');

            if (activityList && activityScrollLeft) {
                activityScrollLeft.addEventListener('click', () => {
                    activityList.scrollBy({
                        left: -activityList.clientWidth,
                        behavior: 'smooth',
                    });
                });
            }

            if (activityList && activityScrollRight) {
                activityScrollRight.addEventListener('click', () => {
                    activityList.scrollBy({
                        left: activityList.clientWidth,
                        behavior: 'smooth',
                    });
                });
            }

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