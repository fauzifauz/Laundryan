<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-gray-900">
                    Performance
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Your rating breakdown from pickup and delivery activities.
                </p>
            </div>

            
                <a href="{{ route('kurir.dashboard') }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-gray-900 px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-blue-600"
            >
                <span class="material-symbols-outlined text-lg">
                    arrow_back
                </span>

                Back to Dashboard
            </a>
        </div>
    </x-slot>

    @php
        $ratingAverage = (float) ($ratingSummary['average'] ?? 0);
        $roundedRating = (int) round($ratingAverage);
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Overall Summary --}}
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 p-7 text-white shadow-xl">
                <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-white/10"></div>

                <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/20 bg-white/10 backdrop-blur">
                            <span class="material-symbols-outlined text-4xl">
                                workspace_premium
                            </span>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.25em] text-orange-100">
                                Overall Rating
                            </p>

                            <div class="mt-1 flex items-end gap-2">
                                <h1 class="text-4xl font-black">
                                    {{ number_format($ratingAverage, 1) }}
                                </h1>

                                <span class="pb-1 text-sm font-bold text-orange-100">/ 5</span>
                            </div>

                            <div class="mt-1 flex items-center gap-1">
                                @for($star = 1; $star <= 5; $star++)
                                    <span class="material-symbols-outlined text-lg {{ $star <= $roundedRating ? 'text-white' : 'text-white/30' }}">
                                        star
                                    </span>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/20 bg-white/10 px-5 py-3 backdrop-blur">
                        <p class="text-xs font-black uppercase tracking-widest text-orange-100">
                            Total Reviews
                        </p>

                        <p class="mt-1 text-2xl font-black">
                            {{ number_format($ratingSummary['count'] ?? 0) }}
                        </p>
                    </div>
                </div>
            </section>

            {{-- Pickup vs Delivery Rating --}}
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-50 text-orange-600">
                            <span class="material-symbols-outlined">package_2</span>
                        </div>

                        <div>
                            <h3 class="font-black text-gray-900">Pickup Rating</h3>
                            <p class="text-xs text-gray-500">Average from pickup tasks</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-end gap-2">
                        <p class="text-3xl font-black text-gray-900">
                            {{ number_format($ratingSummary['pickup_average'] ?? 0, 1) }}
                        </p>
                        <p class="pb-1 text-xs font-bold text-gray-400">/ 5</p>
                    </div>

                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div
                            class="h-full rounded-full bg-orange-500"
                            style="width: {{ min(100, (($ratingSummary['pickup_average'] ?? 0) / 5) * 100) }}%"
                        ></div>
                    </div>

                    <p class="mt-2 text-xs font-semibold text-gray-500">
                        {{ $pickupReviews->count() }} pickup reviews
                    </p>
                </div>

                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                            <span class="material-symbols-outlined">local_shipping</span>
                        </div>

                        <div>
                            <h3 class="font-black text-gray-900">Delivery Rating</h3>
                            <p class="text-xs text-gray-500">Average from delivery tasks</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-end gap-2">
                        <p class="text-3xl font-black text-gray-900">
                            {{ number_format($ratingSummary['delivery_average'] ?? 0, 1) }}
                        </p>
                        <p class="pb-1 text-xs font-bold text-gray-400">/ 5</p>
                    </div>

                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div
                            class="h-full rounded-full bg-violet-500"
                            style="width: {{ min(100, (($ratingSummary['delivery_average'] ?? 0) / 5) * 100) }}%"
                        ></div>
                    </div>

                    <p class="mt-2 text-xs font-semibold text-gray-500">
                        {{ $deliveryReviews->count() }} delivery reviews
                    </p>
                </div>
            </section>

            {{-- Review Lists --}}
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                {{-- Pickup Reviews --}}
                <section class="rounded-3xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-5">
                        <h3 class="text-lg font-black text-gray-900">Pickup Reviews</h3>
                        <p class="text-sm text-gray-500">Customer feedback on your pickups.</p>
                    </div>

                    <div class="max-h-[520px] divide-y divide-gray-100 overflow-y-auto">
                        @forelse($pickupReviews as $review)
                            <div class="px-6 py-5">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-black uppercase tracking-widest text-blue-600">
                                        {{ $review->order?->order_code ?? '-' }}
                                    </p>

                                    <div class="flex items-center gap-0.5">
                                        @for($star = 1; $star <= 5; $star++)
                                            <span class="material-symbols-outlined text-sm {{ $star <= (int) round($review->rating_pickup_courier) ? 'text-amber-400' : 'text-gray-200' }}">
                                                star
                                            </span>
                                        @endfor
                                    </div>
                                </div>

                                <h4 class="mt-2 font-bold text-gray-900">
                                    {{ $review->order?->customer?->name ?? 'Customer' }}
                                </h4>

                                @if($review->comment)
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ $review->comment }}
                                    </p>
                                @endif

                                <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                    {{ $review->created_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        @empty
                            <div class="px-6 py-14 text-center">
                                <p class="text-sm font-semibold text-gray-500">
                                    No pickup reviews yet.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>

                {{-- Delivery Reviews --}}
                <section class="rounded-3xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-5">
                        <h3 class="text-lg font-black text-gray-900">Delivery Reviews</h3>
                        <p class="text-sm text-gray-500">Customer feedback on your deliveries.</p>
                    </div>

                    <div class="max-h-[520px] divide-y divide-gray-100 overflow-y-auto">
                        @forelse($deliveryReviews as $review)
                            <div class="px-6 py-5">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-black uppercase tracking-widest text-blue-600">
                                        {{ $review->order?->order_code ?? '-' }}
                                    </p>

                                    <div class="flex items-center gap-0.5">
                                        @for($star = 1; $star <= 5; $star++)
                                            <span class="material-symbols-outlined text-sm {{ $star <= (int) round($review->rating_delivery_courier) ? 'text-amber-400' : 'text-gray-200' }}">
                                                star
                                            </span>
                                        @endfor
                                    </div>
                                </div>

                                <h4 class="mt-2 font-bold text-gray-900">
                                    {{ $review->order?->customer?->name ?? 'Customer' }}
                                </h4>

                                @if($review->comment)
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ $review->comment }}
                                    </p>
                                @endif

                                <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                    {{ $review->created_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        @empty
                            <div class="px-6 py-14 text-center">
                                <p class="text-sm font-semibold text-gray-500">
                                    No delivery reviews yet.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>