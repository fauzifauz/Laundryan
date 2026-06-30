<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Task Detail: {{ $order->order_code }}
            </h2>
            <span class="bg-blue-600 text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest">
                {{ str_replace('_', ' ', strtoupper($order->status)) }}
            </span>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Order Context Card -->
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden mb-8">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-8 border-b border-gray-50 pb-8">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Customer</p>
                            <h3 class="text-xl font-bold text-gray-900">{{ $order->customer->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $order->customer->phone }}</p>
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

            <!-- Leaflet CSS -->
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

            <!-- Live Route Navigation Map -->
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden mb-8 z-0">
                <div class="p-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">map</span>
                        Live Route Navigation Map
                    </h3>
                    <div id="tracking-map" class="w-full h-80 rounded-2xl border border-gray-100 shadow-inner z-0"></div>
                </div>
            </div>

            <!-- Action Card -->
            <div class="bg-blue-900 rounded-3xl shadow-xl p-8 text-white">
                <h3 class="text-lg font-bold mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Update Progress
                </h3>

                <form action="{{ route('kurir.orders.status', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest opacity-70 mb-3">Next Status</label>
                        <select name="status" class="w-full bg-white/10 border-white/20 rounded-xl text-white py-3 focus:ring-white focus:border-white transition-all appearance-none" required>
                            @php
                                $pickupStatuses = ['penjemputan', 'dijemput', 'diantar', 'sampai'];
                                $deliveryStatuses = ['pengantaran', 'diantarkan', 'selesai'];
                                
                                // Simple logic to determine which flow we are in
                                $isPickupFlow = in_array($order->status, ['waiting_pickup', 'penjemputan', 'dijemput', 'diantar', 'sampai']) || !$order->status;
                            @endphp

                            @if($isPickupFlow)
                                <option class="text-black" value="penjemputan" {{ $order->status === 'penjemputan' ? 'selected' : '' }}>Pickup (Heading to Customer)</option>
                                <option class="text-black" value="dijemput" {{ $order->status === 'dijemput' ? 'selected' : '' }}>Picked Up (Items collected)</option>
                                <option class="text-black" value="diantar" {{ $order->status === 'diantar' ? 'selected' : '' }}>In Transit (Heading to Laundry)</option>
                                <option class="text-black" value="sampai" {{ $order->status === 'sampai' ? 'selected' : '' }}>Arrived (At Laundry Facility)</option>
                            @else
                                <option class="text-black" value="pengantaran" {{ $order->status === 'pengantaran' ? 'selected' : '' }}>Delivery (Heading to Customer)</option>
                                <option class="text-black" value="diantarkan" {{ $order->status === 'diantarkan' ? 'selected' : '' }}>Delivered (Arrived at destination)</option>
                                <option class="text-black" value="selesai" {{ $order->status === 'selesai' ? 'selected' : '' }}>Completed (Task Finished)</option>
                            @endif
                            
                            @if(in_array($order->status, ['ready_for_delivery']))
                                <option class="text-black" value="pengantaran">Start Delivery</option>
                            @endif
                        </select>
                    </div>

                    <!-- Photo Upload -->
                    <div id="photo-upload-section">
                        <label class="block text-xs font-black uppercase tracking-widest opacity-70 mb-3">Upload Proof Photo (Optional/Required at handover)</label>
                        <div class="relative group">
                            <input type="file" name="photo" id="photo" class="hidden" accept="image/*" onchange="previewFile()">
                            <label for="photo" class="w-full flex flex-col items-center justify-center p-8 border-2 border-dashed border-white/30 rounded-2xl cursor-pointer hover:border-white/60 transition-all bg-white/5 group-hover:bg-white/10">
                                <svg class="w-10 h-10 mb-2 opacity-50 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                                <span class="text-sm font-bold opacity-70 group-hover:opacity-100" id="file-label">Tap to take/select photo</span>
                            </label>
                            <img id="preview" class="hidden mt-4 w-full h-auto rounded-xl border border-white/20">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-white text-blue-900 font-black py-4 rounded-xl text-lg hover:bg-blue-50 transition-all shadow-xl active:scale-[0.98]">
                            Submit Update
                        </button>
                    </div>
                </form>
            </div>

            <!-- Chat Section (Cross-role) -->
            <div class="mt-8 bg-white p-8 rounded-3xl shadow-xl border border-gray-100 flex flex-col h-[500px]">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                   <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    Live Chat
                </h3>
                <div class="flex-1 overflow-y-auto space-y-4 mb-4 pr-2 custom-scrollbar">
                    @forelse($order->messages as $msg)
                        <div class="flex flex-col {{ $msg->sender_id === auth()->id() ? 'items-end' : 'items-start' }}">
                            <div class="max-w-[85%] rounded-2xl p-4 {{ $msg->sender_id === auth()->id() ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-gray-100 text-gray-800 rounded-tl-none' }}">
                                <p class="text-xs opacity-70 mb-1 font-bold">{{ $msg->sender->name }} ({{ ucfirst($msg->sender->role) }})</p>
                                <p class="text-sm font-medium">{{ $msg->message }}</p>
                            </div>
                            <span class="text-[9px] text-gray-400 mt-1 uppercase font-bold">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="text-center text-gray-400 py-8 italic text-sm">No messages yet. Ask anything about this order!</p>
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderId = {{ $order->id }};
            const currentUserId = {{ auth()->id() }};

            // Scroll chat to bottom
            const chatContainer = document.querySelector('.flex-1.overflow-y-auto');
            if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;

            // Map Setup
            const isPickupFlow = {{ in_array($order->status, ['waiting_pickup', 'penjemputan', 'dijemput', 'diantar', 'sampai']) ? 'true' : 'false' }};
            const destLat = isPickupFlow ? {{ $order->pickup_lat ?? -6.2000 }} : {{ $order->delivery_lat ?? -6.2000 }};
            const destLng = isPickupFlow ? {{ $order->pickup_lng ?? 106.8166 }} : {{ $order->delivery_lng ?? 106.8166 }};

            var map = L.map('tracking-map').setView([destLat, destLng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const customerIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            const courierIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            var customerMarker = L.marker([destLat, destLng], { icon: customerIcon })
                .addTo(map)
                .bindPopup(isPickupFlow ? 'Customer Pickup Location' : 'Customer Delivery Location')
                .openPopup();

            var courierMarker = null;
            var routeLine = null;

            function updateRoute() {
                if (customerMarker && courierMarker) {
                    const latlngs = [
                        customerMarker.getLatLng(),
                        courierMarker.getLatLng()
                    ];
                    if (!routeLine) {
                        routeLine = L.polyline(latlngs, { color: '#2563eb', weight: 4, dashArray: '8, 8', opacity: 0.8 }).addTo(map);
                    } else {
                        routeLine.setLatLngs(latlngs);
                    }
                }
            }

            function setCourierPosition(lat, lng) {
                const newLatLng = new L.LatLng(lat, lng);
                if (!courierMarker) {
                    courierMarker = L.marker(newLatLng, { icon: courierIcon })
                        .addTo(map)
                        .bindPopup('Your Position')
                        .openPopup();
                } else {
                    courierMarker.setLatLng(newLatLng);
                }
                updateRoute();
            }

            function setCustomerPosition(lat, lng) {
                const newLatLng = new L.LatLng(lat, lng);
                customerMarker.setLatLng(newLatLng);
                updateRoute();
            }

            // Real-time Chat and Location Listener
            if (window.Echo) {
                window.Echo.private(`order.${orderId}`)
                    .listen('MessageSent', (e) => {
                        console.log('New message received:', e.message);
                        appendMessage(e.message);
                    })
                    .listen('LocationUpdated', (e) => {
                        const sender = e.location.user;
                        if (sender && sender.role === 'pelanggan') {
                            setCustomerPosition(e.location.latitude, e.location.longitude);
                        }
                    });
            }

            // Watch courier's own location to update locally & send to server
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        setCourierPosition(lat, lng);

                        // Broadcast to server
                        fetch('{{ route("kurir.location.update") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                latitude: lat,
                                longitude: lng,
                                order_id: orderId
                            })
                        });
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
                
                const msgHtml = `
                    <div class="flex flex-col ${isMine ? 'items-end' : 'items-start'} animate-fade-in">
                        <div class="max-w-[85%] rounded-2xl p-4 ${isMine ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-gray-100 text-gray-800 rounded-tl-none'}">
                            <p class="text-xs opacity-70 mb-1 font-bold">${msg.sender.name} (${msg.sender.role})</p>
                            <p class="text-sm font-medium">${msg.message}</p>
                        </div>
                        <span class="text-[9px] text-gray-400 mt-1 uppercase font-bold">Just now</span>
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
</x-app-layout>
