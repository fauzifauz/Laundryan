@php
    $employeeSteps = [
        'arrived_at_laundry' => [
            'label'  => 'Arrived',
            'icon'   => 'store',
            'bg'     => 'bg-orange-50',
            'border' => 'border-orange-100',
            'badge'  => 'bg-orange-100 text-orange-700 border-orange-200',
            'dot'    => 'bg-orange-400',
            'ring'   => 'ring-orange-200',
            'text'   => 'text-orange-600',
        ],
        'washing' => [
            'label'  => 'Washing',
            'icon'   => 'local_laundry_service',
            'bg'     => 'bg-cyan-50',
            'border' => 'border-cyan-100',
            'badge'  => 'bg-cyan-100 text-cyan-700 border-cyan-200',
            'dot'    => 'bg-cyan-400',
            'ring'   => 'ring-cyan-200',
            'text'   => 'text-cyan-600',
        ],
        'drying_ironing' => [
            'label'  => 'Drying & Ironing',
            'icon'   => 'iron',
            'bg'     => 'bg-teal-50',
            'border' => 'border-teal-100',
            'badge'  => 'bg-teal-100 text-teal-700 border-teal-200',
            'dot'    => 'bg-teal-400',
            'ring'   => 'ring-teal-200',
            'text'   => 'text-teal-600',
        ],
        'packing' => [
            'label'  => 'Packing',
            'icon'   => 'inventory_2',
            'bg'     => 'bg-amber-50',
            'border' => 'border-amber-100',
            'badge'  => 'bg-amber-100 text-amber-700 border-amber-200',
            'dot'    => 'bg-amber-400',
            'ring'   => 'ring-amber-200',
            'text'   => 'text-amber-600',
        ],
        'ready_for_delivery' => [
            'label'  => 'Ready',
            'icon'   => 'outbox',
            'bg'     => 'bg-emerald-50',
            'border' => 'border-emerald-100',
            'badge'  => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'dot'    => 'bg-emerald-400',
            'ring'   => 'ring-emerald-200',
            'text'   => 'text-emerald-600',
        ],
    ];

    $pickupContexts   = ['picking_up', 'picked_up', 'in_transit_to_laundry', 'penjemputan', 'dijemput', 'diantar', 'sampai'];
    $deliveryContexts = ['delivering', 'ready_for_delivery_photo', 'pengantaran', 'diantarkan', 'selesai', 'completed'];

    $photosByContext = $order->photos->groupBy('context');

    $employeePhotos = [];
    foreach (array_keys($employeeSteps) as $ctx) {
        $employeePhotos[$ctx] = $photosByContext->get($ctx, collect());
    }

    $courierPickupPhotos   = collect();
    foreach ($pickupContexts as $ctx) {
        $courierPickupPhotos = $courierPickupPhotos->merge($photosByContext->get($ctx, collect()));
    }

    $courierDeliveryPhotos = collect();
    foreach ($deliveryContexts as $ctx) {
        $courierDeliveryPhotos = $courierDeliveryPhotos->merge($photosByContext->get($ctx, collect()));
    }

    $knownContexts = array_merge(array_keys($employeeSteps), $pickupContexts, $deliveryContexts);
    $otherPhotos   = $order->photos->filter(fn($p) => !in_array($p->context, $knownContexts));
    $totalPhotos   = $order->photos->count();
@endphp

{{-- ================================================================
     ORDER PHOTO DOCUMENTATION — full-width, compact horizontal layout
     ================================================================ --}}
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden"
     x-data="{ open: true }">

    {{-- ── Header (always visible, click to collapse) ──────────── --}}
    <button type="button"
            @click="open = !open"
            class="w-full px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/50 to-white
                   flex items-center gap-3 hover:from-indigo-50 transition-all group">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600
                    flex items-center justify-center text-white shadow-md flex-shrink-0">
            <span class="material-symbols-outlined text-[17px]">photo_library</span>
        </div>
        <div class="text-left">
            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">Visual Evidence</span>
            <h3 class="text-sm font-black text-gray-800 tracking-tight mt-0">Order Documentation</h3>
        </div>

        @if($totalPhotos > 0)
            <span class="ml-3 text-[10px] font-black text-indigo-600 bg-indigo-50 border border-indigo-100
                         px-2.5 py-0.5 rounded-full">
                {{ $totalPhotos }} {{ Str::plural('Photo', $totalPhotos) }}
            </span>
        @else
            <span class="ml-3 text-[10px] font-black text-gray-400 bg-gray-50 border border-gray-100
                         px-2.5 py-0.5 rounded-full">No photos yet</span>
        @endif

        <span class="ml-auto text-gray-300 group-hover:text-indigo-400 transition-colors flex-shrink-0">
            <span class="material-symbols-outlined text-[20px] transition-transform duration-200"
                  :class="open ? 'rotate-180' : 'rotate-0'">expand_more</span>
        </span>
    </button>

    {{-- ── Collapsible body ─────────────────────────────────────── --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="p-5 space-y-6">

        {{-- ── EMPLOYEE STEPS (5 columns, compact) ──────────────── --}}
        <div>
            <div class="flex items-center gap-2 mb-3">
                <div class="w-6 h-6 rounded-lg bg-blue-600 flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-[13px]">badge</span>
                </div>
                <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Employee Process Photos</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                @foreach($employeeSteps as $ctx => $step)
                    @php $stepPhotos = $employeePhotos[$ctx]; @endphp
                    <div class="rounded-2xl border {{ $step['border'] }} {{ $step['bg'] }} overflow-hidden flex flex-col">

                        {{-- step label --}}
                        <div class="px-2.5 py-2 flex items-center gap-1.5 border-b {{ $step['border'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $step['dot'] }} flex-shrink-0"></span>
                            <span class="material-symbols-outlined text-[13px] {{ $step['text'] }}">{{ $step['icon'] }}</span>
                            <span class="text-[9px] font-black text-gray-600 uppercase tracking-wide truncate">{{ $step['label'] }}</span>
                            @if($stepPhotos->isNotEmpty())
                                <span class="ml-auto text-[8px] font-black {{ $step['badge'] }} border px-1 py-0 rounded-full flex-shrink-0">
                                    {{ $stepPhotos->count() }}
                                </span>
                            @endif
                        </div>

                        {{-- photo or placeholder --}}
                        @if($stepPhotos->isNotEmpty())
                            @php $photo = $stepPhotos->first(); @endphp
                            <div class="relative group cursor-pointer"
                                 onclick="openOrderPhotoZoom('{{ Storage::url($photo->photo_path) }}', '{{ $step['label'] }}')">
                                <img src="{{ Storage::url($photo->photo_path) }}"
                                     class="w-full aspect-[4/3] object-cover transition-transform duration-300 group-hover:scale-105"
                                     alt="{{ $step['label'] }}"
                                     loading="lazy">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100
                                            transition-opacity flex flex-col items-center justify-center gap-0.5">
                                    <span class="material-symbols-outlined text-white text-lg">zoom_in</span>
                                    @if($stepPhotos->count() > 1)
                                        <span class="text-white text-[9px] font-black">+{{ $stepPhotos->count() - 1 }} more</span>
                                    @endif
                                </div>
                                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/70 to-transparent
                                            px-2 py-1.5 flex items-end justify-between">
                                    <span class="text-[8px] text-white font-bold leading-none truncate max-w-[60%]">
                                        {{ $photo->user->name ?? 'Staff' }}
                                    </span>
                                    <span class="text-[7px] text-white/70 font-bold leading-none">
                                        {{ $photo->created_at->timezone('Asia/Jakarta')->format('H:i') }}
                                    </span>
                                </div>
                            </div>
                            {{-- extra photos (thumbnails row) --}}
                            @if($stepPhotos->count() > 1)
                                <div class="flex gap-1 p-1.5 bg-white/60 border-t {{ $step['border'] }}">
                                    @foreach($stepPhotos->skip(1) as $extra)
                                        <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0 cursor-pointer
                                                    ring-1 ring-white hover:ring-2 {{ $step['ring'] }} transition-all"
                                             onclick="openOrderPhotoZoom('{{ Storage::url($extra->photo_path) }}', '{{ $step['label'] }}')">
                                            <img src="{{ Storage::url($extra->photo_path) }}"
                                                 class="w-full h-full object-cover"
                                                 loading="lazy">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="flex-1 flex flex-col items-center justify-center py-5 gap-1.5 text-gray-300">
                                <span class="material-symbols-outlined text-xl">hide_image</span>
                                <span class="text-[8px] font-black uppercase tracking-wider">No Photo</span>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── DIVIDER ──────────────────────────────────────────── --}}
        <div class="border-t border-dashed border-gray-200"></div>

        {{-- ── COURIER PROOF (2 columns, compact) ──────────────── --}}
        <div>
            <div class="flex items-center gap-2 mb-3">
                <div class="w-6 h-6 rounded-xl bg-purple-600 flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-[13px]">delivery_dining</span>
                </div>
                <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Courier Pickup & Delivery Proof</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                {{-- Pickup --}}
                <div class="rounded-2xl border border-blue-100 bg-blue-50/40 overflow-hidden flex flex-col">
                    <div class="px-3 py-2 flex items-center gap-1.5 border-b border-blue-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 flex-shrink-0"></span>
                        <span class="material-symbols-outlined text-[13px] text-blue-500">hail</span>
                        <span class="text-[9px] font-black text-gray-600 uppercase tracking-wide">Pickup Proof</span>
                        @if($courierPickupPhotos->isNotEmpty())
                            <span class="ml-auto text-[8px] font-black bg-blue-100 text-blue-700 border border-blue-200 px-1.5 py-0 rounded-full">
                                {{ $courierPickupPhotos->count() }}
                            </span>
                        @endif
                    </div>
                    @if($courierPickupPhotos->isNotEmpty())
                        @php $first = $courierPickupPhotos->first(); @endphp
                        <div class="relative group cursor-pointer"
                             onclick="openOrderPhotoZoom('{{ Storage::url($first->photo_path) }}', 'Pickup Proof')">
                            <img src="{{ Storage::url($first->photo_path) }}"
                                 class="w-full aspect-[16/7] object-cover transition-transform duration-300 group-hover:scale-105"
                                 loading="lazy">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity
                                        flex flex-col items-center justify-center gap-0.5">
                                <span class="material-symbols-outlined text-white text-lg">zoom_in</span>
                                @if($courierPickupPhotos->count() > 1)
                                    <span class="text-white text-[9px] font-black">+{{ $courierPickupPhotos->count() - 1 }} more</span>
                                @endif
                            </div>
                            <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/70 to-transparent
                                        px-2 py-1.5 flex items-end justify-between">
                                <span class="text-[8px] text-white font-bold leading-none truncate max-w-[60%]">
                                    {{ $first->user->name ?? 'Courier' }}
                                </span>
                                <span class="text-[7px] text-white/70 font-bold leading-none">
                                    {{ $first->created_at->timezone('Asia/Jakarta')->format('d M, H:i') }}
                                </span>
                            </div>
                        </div>
                        @if($courierPickupPhotos->count() > 1)
                            <div class="flex gap-1 p-1.5 bg-white/60 border-t border-blue-100">
                                @foreach($courierPickupPhotos->skip(1) as $extra)
                                    <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0 cursor-pointer
                                                ring-1 ring-white hover:ring-2 ring-blue-200 transition-all"
                                         onclick="openOrderPhotoZoom('{{ Storage::url($extra->photo_path) }}', 'Pickup Proof')">
                                        <img src="{{ Storage::url($extra->photo_path) }}" class="w-full h-full object-cover" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center py-8 gap-1.5 text-gray-300">
                            <span class="material-symbols-outlined text-xl">hide_image</span>
                            <span class="text-[8px] font-black uppercase tracking-wider">No Pickup Photo</span>
                        </div>
                    @endif
                </div>

                {{-- Delivery --}}
                <div class="rounded-2xl border border-purple-100 bg-purple-50/40 overflow-hidden flex flex-col">
                    <div class="px-3 py-2 flex items-center gap-1.5 border-b border-purple-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-400 flex-shrink-0"></span>
                        <span class="material-symbols-outlined text-[13px] text-purple-500">local_shipping</span>
                        <span class="text-[9px] font-black text-gray-600 uppercase tracking-wide">Delivery Proof</span>
                        @if($courierDeliveryPhotos->isNotEmpty())
                            <span class="ml-auto text-[8px] font-black bg-purple-100 text-purple-700 border border-purple-200 px-1.5 py-0 rounded-full">
                                {{ $courierDeliveryPhotos->count() }}
                            </span>
                        @endif
                    </div>
                    @if($courierDeliveryPhotos->isNotEmpty())
                        @php $first = $courierDeliveryPhotos->first(); @endphp
                        <div class="relative group cursor-pointer"
                             onclick="openOrderPhotoZoom('{{ Storage::url($first->photo_path) }}', 'Delivery Proof')">
                            <img src="{{ Storage::url($first->photo_path) }}"
                                 class="w-full aspect-[16/7] object-cover transition-transform duration-300 group-hover:scale-105"
                                 loading="lazy">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity
                                        flex flex-col items-center justify-center gap-0.5">
                                <span class="material-symbols-outlined text-white text-lg">zoom_in</span>
                                @if($courierDeliveryPhotos->count() > 1)
                                    <span class="text-white text-[9px] font-black">+{{ $courierDeliveryPhotos->count() - 1 }} more</span>
                                @endif
                            </div>
                            <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/70 to-transparent
                                        px-2 py-1.5 flex items-end justify-between">
                                <span class="text-[8px] text-white font-bold leading-none truncate max-w-[60%]">
                                    {{ $first->user->name ?? 'Courier' }}
                                </span>
                                <span class="text-[7px] text-white/70 font-bold leading-none">
                                    {{ $first->created_at->timezone('Asia/Jakarta')->format('d M, H:i') }}
                                </span>
                            </div>
                        </div>
                        @if($courierDeliveryPhotos->count() > 1)
                            <div class="flex gap-1 p-1.5 bg-white/60 border-t border-purple-100">
                                @foreach($courierDeliveryPhotos->skip(1) as $extra)
                                    <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0 cursor-pointer
                                                ring-1 ring-white hover:ring-2 ring-purple-200 transition-all"
                                         onclick="openOrderPhotoZoom('{{ Storage::url($extra->photo_path) }}', 'Delivery Proof')">
                                        <img src="{{ Storage::url($extra->photo_path) }}" class="w-full h-full object-cover" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center py-8 gap-1.5 text-gray-300">
                            <span class="material-symbols-outlined text-xl">hide_image</span>
                            <span class="text-[8px] font-black uppercase tracking-wider">No Delivery Photo</span>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- Other uncategorised photos --}}
        @if($otherPhotos->isNotEmpty())
            <div class="border-t border-dashed border-gray-200 pt-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-400 text-[16px]">photo</span>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Other Photos</span>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2">
                    @foreach($otherPhotos as $photo)
                        <div class="rounded-xl overflow-hidden relative group cursor-pointer bg-gray-50 shadow-sm border border-gray-100"
                             onclick="openOrderPhotoZoom('{{ Storage::url($photo->photo_path) }}', '{{ str_replace('_', ' ', $photo->context) }}')">
                            <img src="{{ Storage::url($photo->photo_path) }}"
                                 class="w-full aspect-square object-cover transition-transform duration-300 group-hover:scale-105"
                                 loading="lazy">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-lg">zoom_in</span>
                            </div>
                            <div class="px-1.5 py-1 bg-white border-t border-gray-100">
                                <span class="text-[7px] font-black uppercase text-gray-400 truncate block">
                                    {{ str_replace('_', ' ', $photo->context) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

{{-- ── Full-screen photo modal (rendered once per page) ─────────── --}}
@once
    <div id="orderPhotoModal"
         class="fixed inset-0 z-[999] hidden bg-black/90 backdrop-blur-sm"
         onclick="closeOrderPhotoZoom(event)">
        <div class="absolute inset-0 flex flex-col items-center justify-center p-4">
            <div class="w-full max-w-5xl flex items-center justify-between mb-4 px-2">
                <span id="orderPhotoModalLabel"
                      class="text-white font-black text-xs uppercase tracking-widest bg-white/10 px-3 py-1
                             rounded-full border border-white/20 truncate max-w-[70%]"></span>
                <button onclick="closeOrderPhotoZoom(null)"
                        class="w-9 h-9 flex items-center justify-center bg-white/20 hover:bg-white/30
                               text-white rounded-full transition-colors flex-shrink-0 ml-2">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <div class="w-full max-w-5xl flex items-center justify-center">
                <img id="orderPhotoModalImg"
                     src=""
                     class="max-h-[82vh] max-w-full object-contain rounded-2xl shadow-2xl ring-1 ring-white/10"
                     alt="Photo">
            </div>
            <p class="mt-4 text-[10px] text-white/30 font-bold uppercase tracking-widest">
                Click outside image or press ESC to close
            </p>
        </div>
    </div>

    <script>
        function openOrderPhotoZoom(src, label) {
            const modal = document.getElementById('orderPhotoModal');
            const img   = document.getElementById('orderPhotoModalImg');
            const lbl   = document.getElementById('orderPhotoModalLabel');
            if (!modal || !img) return;
            img.src = src;
            lbl.textContent = label || '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
        function closeOrderPhotoZoom(e) {
            if (e && e.target && e.target.closest('#orderPhotoModalImg')) return;
            const modal = document.getElementById('orderPhotoModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            const img = document.getElementById('orderPhotoModalImg');
            if (img) img.src = '';
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeOrderPhotoZoom(null);
        });
    </script>
@endonce
