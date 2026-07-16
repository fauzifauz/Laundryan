<x-app-layout>
    <div x-data="{
        showToast: {{ session('success') ? 'true' : 'false' }},
        toastMessage: '{{ session('success', '') }}',
        toastTitle: 'Berhasil',
        triggerToast(title, msg) {
            this.toastTitle = title;
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 5000);
        }
    }" x-init="if (showToast) { setTimeout(() => { showToast = false; }, 5000); }" @toast-notify.window="triggerToast($event.detail.title, $event.detail.message)">

        <div x-show="showToast"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed top-6 right-6 z-50 max-w-sm w-full bg-emerald-50 border border-emerald-200 rounded-3xl p-5 shadow-2xl text-emerald-800 flex items-center justify-between" x-cloak>
            <div class="flex items-center gap-4">
                <span class="material-symbols-outlined text-emerald-600 text-xl">check_circle</span>
                <div>
                    <h4 class="font-black text-xs uppercase tracking-wider" x-text="toastTitle"></h4>
                    <p class="text-[11px] text-emerald-700 font-medium mt-0.5" x-text="toastMessage"></p>
                </div>
            </div>
            <button @click="showToast = false" class="text-emerald-600/60 hover:text-emerald-800 p-2 rounded-xl">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Task Detail: {{ $order->order_code }}
            </h2>
            <span class="bg-blue-600 text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest">
                {{ $statusLabel }}
            </span>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Order Context Card -->
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden mb-8">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-8 border-b border-gray-50 pb-8">
                        <div class="flex items-center gap-4">
                            <img
                                src="{{ $order->customer->photo ? asset('storage/'.$order->customer->photo) : 'https://ui-avatars.com/api/?name='.urlencode($order->customer->name).'&color=fff&background=2563eb' }}"
                                alt="{{ $order->customer->name }}"
                                class="w-14 h-14 rounded-2xl object-cover border border-gray-100 shadow-sm"
                            >
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Customer</p>
                                <h3 class="text-xl font-bold text-gray-900">{{ $order->customer->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $order->customer->phone }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Items</p>
                            <p class="font-bold text-blue-600">{{ $order->service->name }}</p>
                            <p class="text-xs text-gray-500 font-medium">{{ $order->itemType->name }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Pickup Address
                            </p>
                            <p class="text-sm font-medium leading-relaxed bg-gray-50 p-4 rounded-xl border border-gray-100">{{ $order->pickup_address }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Customer Notes
                            </p>
                            <p class="text-sm font-medium leading-relaxed bg-yellow-50 p-4 rounded-xl border border-yellow-100 italic">{{ $order->notes ?? 'No specific notes.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($customerOrderHistory->isNotEmpty())
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden mb-8">
                <div class="p-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">history</span>
                        Riwayat Order Pelanggan (Selesai)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Riwayat Pickup --}}
                        <div>
                            <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-3">Riwayat Pickup</p>
                            <div class="space-y-2 max-h-64 overflow-y-auto custom-scrollbar pr-1">
                                @foreach($customerOrderHistory as $h)
                                    <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-3">
                                        <p class="text-xs font-black text-gray-800">{{ $h->order_code }}</p>
                                        <p class="text-[11px] text-gray-500 truncate">{{ $h->pickup_address }}</p>
                                        <p class="text-[9px] text-gray-400 font-bold mt-1">{{ $h->updated_at->translatedFormat('d M Y') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        {{-- Riwayat Delivery --}}
                        <div>
                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-3">Riwayat Delivery</p>
                            <div class="space-y-2 max-h-64 overflow-y-auto custom-scrollbar pr-1">
                                @foreach($customerOrderHistory as $h)
                                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-3">
                                        <p class="text-xs font-black text-gray-800">{{ $h->order_code }}</p>
                                        <p class="text-[11px] text-gray-500 truncate">{{ $h->delivery_address }}</p>
                                        <p class="text-[9px] text-gray-400 font-bold mt-1">{{ $h->updated_at->translatedFormat('d M Y') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Leaflet CSS -->
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
            <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
            <style>.leaflet-routing-container { display: none !important; }</style>

            <!-- Live Route Navigation Map -->
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden mb-8 z-0">
                <div class="p-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">map</span>
                        Live Route Navigation Map
                        <span id="eta-badge" class="ml-auto text-xs font-black text-blue-700 bg-blue-50 border border-blue-100 px-3 py-1 rounded-full">
                            Menghitung...
                        </span>
                    </h3>
                    <div id="tracking-map" class="w-full h-80 rounded-2xl border border-gray-100 shadow-inner z-0"></div>
                </div>
            </div>

            <!-- Action Card -->
            <div class="bg-blue-900 rounded-3xl shadow-xl p-8 text-white">
                <h3 class="text-lg font-bold mb-6 flex items-center">
                    <svg
                    class="w-6 h-6 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
            >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z"
            ></path>
        </svg>

        Update Progress
    </h3>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-300 bg-green-100 p-4 text-sm font-semibold text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-300 bg-red-100 p-4 text-red-800">
            <ul class="list-disc space-y-1 pl-5 text-sm font-semibold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($canUpdateStatus && $nextStatus)
        <form
            action="{{ route('kurir.orders.status', $order->id) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6"
        >
            @csrf

            <input
                type="hidden"
                name="status"
                value="{{ $nextStatus }}"
            >

            <div>
                <p class="mb-3 text-xs font-black uppercase tracking-widest opacity-70">
                    Status Sekarang
                </p>

                <div class="rounded-xl border border-white/20 bg-white/10 p-4">
                    <p class="font-bold">
                        {{ $statusLabel }}
                    </p>
                </div>
            </div>

            <div>
                <p class="mb-3 text-xs font-black uppercase tracking-widest opacity-70">
                    Status Berikutnya
                </p>

                <div class="rounded-xl border border-white/30 bg-white/15 p-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined">
                            arrow_forward
                        </span>

                        <p class="font-black">
                            {{ $nextStatusLabel }}
                        </p>
                    </div>
                </div>
            </div>

            <div id="photo-upload-section">
                <label
                    for="photo"
                    class="mb-3 block text-xs font-black uppercase tracking-widest opacity-70"
                >
                    Foto Bukti

                    @if($photoRequired)
                        <span class="text-yellow-300">
                            — Wajib
                        </span>
                    @else
                        <span class="opacity-70">
                            — Opsional
                        </span>
                    @endif
                </label>

                @if($photoRequired)
                    <p class="mb-3 text-xs text-blue-100">
                        Foto wajib diunggah sebagai bukti
                        {{ $nextStatus === 'picked_up'
                            ? 'laundry telah diambil dari pelanggan.'
                            : 'laundry telah diterima pelanggan.' }}
                    </p>
                @endif

                <div class="relative group">
                    <input
                        type="file"
                        name="photo"
                        id="photo"
                        class="hidden"
                        accept="image/jpeg,image/png,image/webp"
                        onchange="previewFile()"
                        @required($photoRequired)
                    >

                    <label
                        for="photo"
                        class="flex w-full cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-white/30 bg-white/5 p-8 transition-all hover:border-white/60 hover:bg-white/10"
                    >
                        <svg
                            class="mb-2 h-10 w-10 opacity-60"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                            ></path>
                        </svg>

                        <span
                            id="file-label"
                            class="text-sm font-bold opacity-80"
                        >
                            Pilih atau ambil foto
                        </span>
                    </label>

                    <img
                        id="preview"
                        alt="Preview foto bukti"
                        class="mt-4 hidden max-h-96 w-full rounded-xl border border-white/20 object-contain"
                    >
                </div>
            </div>

            <div class="pt-4">
                <button
                    type="submit"
                    class="w-full rounded-xl bg-white py-4 text-lg font-black text-blue-900 shadow-xl transition-all hover:bg-blue-50 active:scale-[0.98]"
                >
                    Ubah ke {{ $nextStatusLabel }}
                </button>
            </div>
        </form>
    @elseif($nextStatus)
        <div class="rounded-2xl border border-yellow-300/40 bg-yellow-100/10 p-6 text-center">
            <span class="material-symbols-outlined mb-2 text-4xl text-yellow-300">
                assignment_ind
            </span>

            <p class="font-bold">
                Tahap ini ditugaskan kepada kurir lain.
            </p>

            <p class="mt-2 text-sm text-blue-100">
                Lu tetap bisa melihat detail order, tetapi tidak bisa mengubah statusnya.
            </p>
        </div>
    @else
        <div class="rounded-2xl border border-green-300/40 bg-green-100/10 p-6 text-center">
            <span class="material-symbols-outlined mb-2 text-4xl text-green-300">
                task_alt
            </span>

            <p class="font-bold">
                Tidak ada proses kurir berikutnya.
            </p>

            <p class="mt-2 text-sm text-blue-100">
                Order sedang menunggu proses dari admin atau karyawan.
            </p>
        </div>
    @endif
</div>
            

            <!-- Chat Section (Cross-role) -->
            <div class="mt-8 bg-white p-8 rounded-3xl shadow-xl border border-gray-100 flex flex-col h-[500px]">
                <div id="chatNewMsgBanner" class="hidden mb-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-bold text-blue-700 flex items-center gap-2">
                <span class="material-symbols-outlined text-base">mark_chat_unread</span>
                <span id="chatNewMsgText">Pesan baru masuk</span>
            </div>
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                   <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    Live Chat
                </h3>
                <div class="flex-1 overflow-y-auto space-y-4 mb-4 pr-2 custom-scrollbar">
                    @php
                        $chatRoleBubbleConfig = [
                            'admin'     => ['bg' => 'bg-blue-50 text-blue-900 border border-blue-200/65',    'badge' => 'text-blue-600 bg-blue-100/50',    'role_name' => 'Admin'],
                            'karyawan'  => ['bg' => 'bg-amber-50 text-amber-900 border border-amber-200/65', 'badge' => 'text-amber-600 bg-amber-100/50',  'role_name' => 'Staff'],
                            'kurir'     => ['bg' => 'bg-purple-50 text-purple-900 border border-purple-200/65','badge' => 'text-purple-600 bg-purple-100/50','role_name' => 'Courier'],
                            'pelanggan' => ['bg' => 'bg-emerald-50 text-emerald-900 border border-emerald-200/65','badge' => 'text-emerald-600 bg-emerald-100/50','role_name' => 'Customer'],
                        ];
                    @endphp
                    @forelse($order->messages as $msg)
                        @php
                            $isMine = $msg->sender_id === auth()->id();
                            $rCfg = $chatRoleBubbleConfig[strtolower($msg->sender->role ?? 'pelanggan')] ?? $chatRoleBubbleConfig['pelanggan'];
                        @endphp
                        <div class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }} space-y-1">
                            <div class="max-w-[85%] px-4 py-3 rounded-2xl text-xs font-bold shadow-sm {{ $rCfg['bg'] }} {{ $isMine ? 'rounded-tr-none' : 'rounded-tl-none' }}">
                                <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                                    <span class="text-[10px] font-black uppercase text-gray-800">{{ $msg->sender->name }}</span>
                                    <span class="text-[8px] font-black uppercase px-1.5 py-0.5 rounded {{ $rCfg['badge'] }} tracking-wider">{{ $rCfg['role_name'] }}</span>
                                </div>
                                <p class="leading-relaxed text-[11px] font-semibold">{{ $msg->message }}</p>
                            </div>
                            <span class="text-[8px] text-gray-400 uppercase font-bold px-1">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="text-center text-gray-400 py-8 italic text-sm">Belum ada pesan. Tanyakan sesuatu tentang order ini!</p>
                    @endforelse
                </div>
                <form action="{{ route('messages.store', $order->id) }}" method="POST" class="mt-auto relative">
                    @csrf
                    <input type="text" name="message" class="w-full rounded-2xl border-gray-200 pr-12 focus:border-blue-500 focus:ring-blue-500 py-3 text-sm" placeholder="Type a message..." required autocomplete="off">
                    <button type="submit" class="absolute right-2 top-2 p-1.5 text-blue-600 hover:text-blue-800 transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
                    </button>
                </form>
            </div>
            
            <div class="mt-8 text-center text-gray-400">
                <a href="{{ route('kurir.dashboard') }}" class="text-gray-400 font-bold hover:text-blue-600 transition-colors uppercase text-xs tracking-widest">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderId = {{ $order->id }};
            const currentUserId = {{ auth()->id() }};

            // Scroll chat to bottom
            const chatContainer = document.querySelector('.flex-1.overflow-y-auto');
            if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;

            // Map Setup
            var map = L.map('tracking-map').setView([-6.1664983, 106.5602886], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            let routeControl = null;
            let stopMarkers = L.layerGroup().addTo(map);

            function nearestNeighborOrder(startLatLng, stops) {
                let remaining = [...stops];
                let current = startLatLng;
                const ordered = [];
                while (remaining.length) {
                    let bestIdx = 0, bestDist = Infinity;
                    remaining.forEach((s, i) => {
                        const d = Math.hypot(s.lat - current.lat, s.lng - current.lng);
                        if (d < bestDist) { bestDist = d; bestIdx = i; }
                    });
                    const next = remaining.splice(bestIdx, 1)[0];
                    ordered.push(next);
                    current = { lat: next.lat, lng: next.lng };
                }
                return ordered;
            }

            function loadRoute() {
                fetch('{{ route("kurir.orders.route", $order->id) }}')
                    .then(r => r.json())
                    .then(data => {
                        const start = L.latLng(data.courier_location.lat, data.courier_location.lng);
                        const ordered = nearestNeighborOrder(data.courier_location, data.stops);
                        const waypoints = [start, ...ordered.map(s => L.latLng(s.lat, s.lng))];

                        stopMarkers.clearLayers();
                        ordered.forEach((s, idx) => {
                            const icon = L.divIcon({
                                html: `<div class="relative"><div class="absolute -top-2 -left-2 bg-gray-900 text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-black border-2 border-white shadow-lg">${idx + 1}</div><img src="${s.customer_photo}" class="w-10 h-10 rounded-full border-4 ${s.is_current ? 'border-blue-500' : 'border-gray-300'} object-cover shadow-lg"></div>`,
                                className: '', iconSize: [40, 40], iconAnchor: [20, 40]
                            });
                            L.marker([s.lat, s.lng], { icon })
                                .bindPopup(`<b>${s.customer_name}</b><br>${s.address}`)
                                .addTo(stopMarkers);
                        });

                        if (routeControl) map.removeControl(routeControl);
                        routeControl = L.Routing.control({
                            waypoints,
                            createMarker: () => null,
                            routeWhileDragging: false,
                            show: false,
                            fitSelectedRoutes: true,
                            lineOptions: { styles: [{ color: '#2563eb', opacity: 0.8, weight: 6 }] }
                        }).on('routesfound', function (e) {
                            const s = e.routes[0].summary;
                            // Akurasi: waktu tempuh * faktor traffic + buffer serah terima
                            const minutes = Math.round((s.totalTime / 60) * 1.3) + 5;
                            const km = (s.totalDistance / 1000).toFixed(1);
                            const etaEl = document.getElementById('eta-badge');
                            if (etaEl) etaEl.textContent = `± ${minutes} menit (${km} km)`;
                        }).addTo(map);
                    });
            }
            loadRoute();
            setInterval(loadRoute, 15000); // refresh posisi & rute berkala

            // Real-time Chat and Location Listener
            if (window.Echo) {
                window.Echo.private(`order.${orderId}`)
                    .listen('MessageSent', (e) => {
                        appendMessage(e.message);

                        const banner = document.getElementById('chatNewMsgBanner');
                        const text = document.getElementById('chatNewMsgText');
                        if (banner && text) {
                            text.textContent = `Pesan baru dari ${e.message.sender.name} (${e.message.sender.role})`;
                            banner.classList.remove('hidden');
                            setTimeout(() => banner.classList.add('hidden'), 6000);
                        }

                        window.dispatchEvent(new CustomEvent('toast-notify', {
                            detail: { title: 'Pesan Baru', message: `${e.message.sender.name}: ${e.message.message}` }
                        }));
                    })
            }

            // Watch courier's own location to update locally & send to server
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (position) => {
                        fetch('{{ route("kurir.location.update") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                                order_id: orderId
                            })
                        }).then(() => loadRoute());
                    },
                    (error) => console.error("Courier GPS Watch Error:", error),
                    { enableHighAccuracy: true, maximumAge: 10000 }
                );
            }

            // Poll customer & courier positions fallback (every 8 seconds)
            function pollLocations() {
                fetch(`/orders/${orderId}/locations`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.customer) {
                            setCustomerPosition(data.customer.latitude, data.customer.longitude);
                        }
                        if (data.courier) {
                            setCourierPosition(data.courier.latitude, data.courier.longitude);
                        }
                    })
                    .catch(err => console.log('Polling error:', err));
            }
            setInterval(pollLocations, 8000);

            function appendMessage(msg) {
                const isMine = msg.sender_id === currentUserId;
                
                const roleCfg = {
                    admin:     { bg: 'bg-blue-50 text-blue-900 border border-blue-200/65',    badge: 'text-blue-600 bg-blue-100/50',    name: 'Admin' },
                    karyawan:  { bg: 'bg-amber-50 text-amber-900 border border-amber-200/65', badge: 'text-amber-600 bg-amber-100/50',  name: 'Staff' },
                    kurir:     { bg: 'bg-purple-50 text-purple-900 border border-purple-200/65', badge: 'text-purple-600 bg-purple-100/50', name: 'Courier' },
                    pelanggan: { bg: 'bg-emerald-50 text-emerald-900 border border-emerald-200/65', badge: 'text-emerald-600 bg-emerald-100/50', name: 'Customer' },
                };
                const cfg = roleCfg[msg.sender.role] || roleCfg.pelanggan;
                const msgHtml = `
                    <div class="flex flex-col ${isMine ? 'items-end' : 'items-start'} space-y-1 animate-fade-in">
                        <div class="max-w-[85%] px-4 py-3 rounded-2xl text-xs font-bold shadow-sm ${cfg.bg} ${isMine ? 'rounded-tr-none' : 'rounded-tl-none'}">
                            <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                                <span class="text-[10px] font-black uppercase text-gray-800">${msg.sender.name}</span>
                                <span class="text-[8px] font-black uppercase px-1.5 py-0.5 rounded ${cfg.badge} tracking-wider">${cfg.name}</span>
                            </div>
                            <p class="leading-relaxed text-[11px] font-semibold">${msg.message}</p>
                        </div>
                        <span class="text-[8px] text-gray-400 uppercase font-bold px-1">Baru saja</span>
                    </div>
                `;
                
                chatContainer.insertAdjacentHTML('beforeend', msgHtml);
                chatContainer.scrollTop = chatContainer.scrollHeight;

                // Remove "No messages yet" if exists
                const emptyMsg = chatContainer.querySelector('p.italic');
                if (emptyMsg) emptyMsg.remove();
            }
        });

        function previewFile() {
            const preview = document.getElementById('preview');
            const file = document.getElementById('photo').files[0];
            const reader = new FileReader();

            reader.onloadend = function() {
                preview.src = reader.result;
                preview.classList.remove('hidden');
                document.getElementById('file-label').textContent = 'Photo selected: ' + file.name;
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
            }
        }
    </script>
    </div>
</x-app-layout>
