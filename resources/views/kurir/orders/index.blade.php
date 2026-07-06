<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-[#005bc0]">
                    Courier Operations
                </p>

                <h2 class="mt-1 text-2xl font-black text-gray-900">
                    Order & Riwayat
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Daftar tugas pickup dan delivery yang ditugaskan kepada kurir.
                </p>
            </div>

            <a
                href="{{ route('kurir.dashboard') }}"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#005bc0] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-[#004899]"
            >
                <span class="material-symbols-outlined text-lg">
                    local_shipping
                </span>

                Delivery Board
            </a>
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

        $completedStatuses = [
            'completed',
            'selesai',
        ];

        $cancelledStatuses = [
            'cancelled',
        ];
    @endphp

    <div class="space-y-6">
        {{-- Statistik --}}
        <section class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            <a
                href="{{ route('kurir.orders.index', ['scope' => 'all']) }}"
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
            >
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                        <span class="material-symbols-outlined">
                            inventory_2
                        </span>
                    </div>

                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                        Total
                    </span>
                </div>

                <p class="mt-4 text-3xl font-black text-gray-900">
                    {{ number_format($statistics['total']) }}
                </p>

                <p class="mt-1 text-xs font-semibold text-gray-500">
                    Semua order
                </p>
            </a>

            <a
                href="{{ route('kurir.orders.index', ['scope' => 'active']) }}"
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
            >
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                        <span class="material-symbols-outlined">
                            pending_actions
                        </span>
                    </div>

                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                        Aktif
                    </span>
                </div>

                <p class="mt-4 text-3xl font-black text-gray-900">
                    {{ number_format($statistics['active']) }}
                </p>

                <p class="mt-1 text-xs font-semibold text-gray-500">
                    Belum selesai
                </p>
            </a>

            <a
                href="{{ route('kurir.orders.index', ['scope' => 'completed']) }}"
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
            >
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <span class="material-symbols-outlined">
                            task_alt
                        </span>
                    </div>

                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                        Selesai
                    </span>
                </div>

                <p class="mt-4 text-3xl font-black text-gray-900">
                    {{ number_format($statistics['completed']) }}
                </p>

                <p class="mt-1 text-xs font-semibold text-gray-500">
                    Order selesai
                </p>
            </a>

            <a
                href="{{ route('kurir.orders.index', [
                    'scope' => 'all',
                    'type' => 'pickup',
                ]) }}"
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
            >
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-50 text-orange-600">
                        <span class="material-symbols-outlined">
                            package_2
                        </span>
                    </div>

                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                        Pickup
                    </span>
                </div>

                <p class="mt-4 text-3xl font-black text-gray-900">
                    {{ number_format($statistics['pickup']) }}
                </p>

                <p class="mt-1 text-xs font-semibold text-gray-500">
                    Penjemputan
                </p>
            </a>

            <a
                href="{{ route('kurir.orders.index', [
                    'scope' => 'all',
                    'type' => 'delivery',
                ]) }}"
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
            >
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                        <span class="material-symbols-outlined">
                            local_shipping
                        </span>
                    </div>

                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                        Delivery
                    </span>
                </div>

                <p class="mt-4 text-3xl font-black text-gray-900">
                    {{ number_format($statistics['delivery']) }}
                </p>

                <p class="mt-1 text-xs font-semibold text-gray-500">
                    Pengantaran
                </p>
            </a>
        </section>

        {{-- Filter --}}
        <form
            method="GET"
            action="{{ route('kurir.orders.index') }}"
            class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm"
        >
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <label
                        for="search"
                        class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-400"
                    >
                        Cari Order
                    </label>

                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            search
                        </span>

                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ $search }}"
                            placeholder="Kode, pelanggan, nomor HP..."
                            class="w-full rounded-2xl border-gray-200 py-3 pl-12 pr-4 text-sm font-semibold focus:border-[#005bc0] focus:ring-[#005bc0]"
                        >
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <label
                        for="scope"
                        class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-400"
                    >
                        Riwayat
                    </label>

                    <select
                        name="scope"
                        id="scope"
                        class="w-full rounded-2xl border-gray-200 py-3 text-sm font-semibold focus:border-[#005bc0] focus:ring-[#005bc0]"
                    >
                        <option value="active" @selected($scope === 'active')>
                            Order Aktif
                        </option>

                        <option value="completed" @selected($scope === 'completed')>
                            Selesai
                        </option>

                        <option value="all" @selected($scope === 'all')>
                            Semua
                        </option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label
                        for="type"
                        class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-400"
                    >
                        Jenis Tugas
                    </label>

                    <select
                        name="type"
                        id="type"
                        class="w-full rounded-2xl border-gray-200 py-3 text-sm font-semibold focus:border-[#005bc0] focus:ring-[#005bc0]"
                    >
                        <option value="all" @selected($type === 'all')>
                            Semua
                        </option>

                        <option value="pickup" @selected($type === 'pickup')>
                            Pickup
                        </option>

                        <option value="delivery" @selected($type === 'delivery')>
                            Delivery
                        </option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label
                        for="status"
                        class="mb-2 block text-xs font-black uppercase tracking-widest text-gray-400"
                    >
                        Status
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="w-full rounded-2xl border-gray-200 py-3 text-sm font-semibold focus:border-[#005bc0] focus:ring-[#005bc0]"
                    >
                        <option value="">
                            Semua Status
                        </option>

                        @foreach($statusOptions as $statusValue => $optionLabel)
                            <option
                                value="{{ $statusValue }}"
                                @selected($status === $statusValue)
                            >
                                {{ $optionLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2 lg:col-span-2">
                    <button
                        type="submit"
                        class="flex-1 rounded-2xl bg-[#005bc0] px-4 py-3 text-sm font-black text-white transition hover:bg-[#004899]"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route('kurir.orders.index') }}"
                        title="Reset filter"
                        class="flex h-12 w-12 items-center justify-center rounded-2xl border border-gray-200 text-gray-500 transition hover:bg-gray-100"
                    >
                        <span class="material-symbols-outlined">
                            restart_alt
                        </span>
                    </a>
                </div>
            </div>
        </form>

        {{-- Daftar Order --}}
        <section class="space-y-4">
            @forelse($orders as $order)
                @php
                    $isPickupAssigned =
                        (int) $order->pickup_courier_id === $courierId;

                    $isDeliveryAssigned =
                        (int) $order->delivery_courier_id === $courierId;

                    $isLegacy =
                        $order->pickup_courier_id === null
                        && $order->delivery_courier_id === null
                        && (int) $order->courier_id === $courierId;

                    if ($isPickupAssigned && $isDeliveryAssigned) {
                        $taskLabel = 'Pickup & Delivery';
                        $taskClass = 'bg-blue-50 text-blue-700';
                        $taskIcon = 'sync_alt';
                    } elseif ($isPickupAssigned) {
                        $taskLabel = 'Pickup';
                        $taskClass = 'bg-orange-50 text-orange-700';
                        $taskIcon = 'package_2';
                    } elseif ($isDeliveryAssigned) {
                        $taskLabel = 'Delivery';
                        $taskClass = 'bg-violet-50 text-violet-700';
                        $taskIcon = 'local_shipping';
                    } elseif (
                        $isLegacy
                        && in_array(
                            $order->status,
                            $pickupStatuses,
                            true
                        )
                    ) {
                        $taskLabel = 'Pickup';
                        $taskClass = 'bg-orange-50 text-orange-700';
                        $taskIcon = 'package_2';
                    } else {
                        $taskLabel = 'Delivery';
                        $taskClass = 'bg-violet-50 text-violet-700';
                        $taskIcon = 'local_shipping';
                    }

                    if (
                        in_array(
                            $order->status,
                            $completedStatuses,
                            true
                        )
                    ) {
                        $statusClass = 'bg-emerald-50 text-emerald-700';
                        $statusIcon = 'task_alt';
                    } elseif (
                        in_array(
                            $order->status,
                            $cancelledStatuses,
                            true
                        )
                    ) {
                        $statusClass = 'bg-red-50 text-red-700';
                        $statusIcon = 'cancel';
                    } elseif (
                        in_array(
                            $order->status,
                            $pickupStatuses,
                            true
                        )
                    ) {
                        $statusClass = 'bg-amber-50 text-amber-700';
                        $statusIcon = 'move_to_inbox';
                    } else {
                        $statusClass = 'bg-blue-50 text-blue-700';
                        $statusIcon = 'progress_activity';
                    }

                    $currentStatusLabel =
                        $statusOptions[$order->status]
                        ?? ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $order->status
                            )
                        );

                    $customerName =
                        $order->customer?->name
                        ?? 'Pelanggan';

                    $customerPhoto =
                        $order->customer?->photo
                            ? asset(
                                'storage/'
                                .$order->customer->photo
                            )
                            : 'https://ui-avatars.com/api/?name='
                                .urlencode($customerName)
                                .'&background=EBF4FF&color=005bc0';

                    $serviceName =
                        $order->service?->name
                        ?? '-';

                    $itemTypeName =
                        $order->itemType?->name
                        ?? '-';
                @endphp

                <article class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition hover:shadow-lg">
                    <div class="flex flex-col gap-5 p-6 lg:flex-row lg:items-center">
                        <div class="flex min-w-0 flex-1 items-start gap-4">
                            <img
                                src="{{ $customerPhoto }}"
                                alt="{{ $customerName }}"
                                class="h-14 w-14 flex-shrink-0 rounded-2xl object-cover ring-4 ring-gray-50"
                            >

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#005bc0]">
                                        {{ $order->order_code }}
                                    </p>

                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[10px] font-black uppercase {{ $taskClass }}">
                                        <span class="material-symbols-outlined text-sm">
                                            {{ $taskIcon }}
                                        </span>

                                        {{ $taskLabel }}
                                    </span>

                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[10px] font-black uppercase {{ $statusClass }}">
                                        <span class="material-symbols-outlined text-sm">
                                            {{ $statusIcon }}
                                        </span>

                                        {{ $currentStatusLabel }}
                                    </span>
                                </div>

                                <h3 class="mt-2 truncate text-lg font-black text-gray-900">
                                    {{ $customerName }}
                                </h3>

                                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs font-semibold text-gray-500">
                                    <span>
                                        {{ $order->customer?->phone ?? '-' }}
                                    </span>

                                    <span>
                                        {{ $serviceName }}
                                    </span>

                                    <span>
                                        {{ $itemTypeName }}
                                    </span>
                                </div>

                                @if($order->notes)
                                    <div class="mt-3 rounded-xl bg-yellow-50 px-3 py-2 text-xs font-semibold text-yellow-800">
                                        Catatan: {{ $order->notes }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="grid min-w-0 flex-1 grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-orange-50/70 p-4">
                                <p class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-orange-600">
                                    <span class="material-symbols-outlined text-base">
                                        location_on
                                    </span>

                                    Alamat Pickup
                                </p>

                                <p class="mt-2 text-xs font-semibold leading-relaxed text-gray-700">
                                    {{ $order->pickup_address ?: '-' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-violet-50/70 p-4">
                                <p class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-violet-600">
                                    <span class="material-symbols-outlined text-base">
                                        flag
                                    </span>

                                    Alamat Delivery
                                </p>

                                <p class="mt-2 text-xs font-semibold leading-relaxed text-gray-700">
                                    {{ $order->delivery_address ?: '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-shrink-0 flex-col gap-3 lg:items-end">
                            <div class="text-left lg:text-right">
                                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    Terakhir Diperbarui
                                </p>

                                <p class="mt-1 text-xs font-bold text-gray-700">
                                    {{ $order->updated_at->format('d M Y, H:i') }}
                                </p>
                            </div>

                            <a
                                href="{{ route('kurir.orders.show', $order) }}"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gray-900 px-5 py-3 text-xs font-black text-white transition hover:bg-[#005bc0]"
                            >
                                Lihat Detail

                                <span class="material-symbols-outlined text-base">
                                    arrow_forward
                                </span>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-20 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-gray-100 text-gray-400">
                        <span class="material-symbols-outlined text-4xl">
                            search_off
                        </span>
                    </div>

                    <h3 class="mt-5 text-lg font-black text-gray-800">
                        Order tidak ditemukan
                    </h3>

                    <p class="mt-2 text-sm text-gray-500">
                        Belum ada order yang sesuai dengan filter tersebut.
                    </p>

                    <a
                        href="{{ route('kurir.orders.index') }}"
                        class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-[#005bc0] px-5 py-3 text-sm font-bold text-white"
                    >
                        Reset Filter
                    </a>
                </div>
            @endforelse
        </section>

        @if($orders->hasPages())
            <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>