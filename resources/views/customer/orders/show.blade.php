<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center text-gray-800">
            <h2 class="font-semibold text-xl leading-tight">
                {{ __('Order Details') }}: {{ $order->order_code }}
            </h2>
            <div class="flex space-x-2">
                @if($order->payment_status === 'paid')
                    <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest">PAID</span>
                @else
                   <span class="bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest">UNPAID</span>
                @endif
                <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                    {{ str_replace('_', ' ', strtoupper($order->status)) }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($order->status === 'completed' && !$order->review)
                <div class="bg-blue-600 rounded-3xl p-8 text-white shadow-xl mb-8 flex flex-col md:flex-row items-center justify-between">
                    <div class="mb-6 md:mb-0">
                        <h3 class="text-2xl font-bold mb-1">Your laundry is done!</h3>
                        <p class="opacity-80">How was your experience using Laundryan?</p>
                    </div>
                    <form action="{{ route('reviews.store', $order->id) }}" method="POST" class="w-full md:w-auto flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4">
                        @csrf
                        <select name="rating" class="rounded-xl border-none text-blue-900 font-bold px-6 py-3 focus:ring-2 focus:ring-white" required>
                            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                            <option value="4">⭐⭐⭐⭐ Good</option>
                            <option value="3">⭐⭐⭐ Neutral</option>
                            <option value="2">⭐⭐ Bad</option>
                            <option value="1">⭐ Terrible</option>
                        </select>
                        <input type="text" name="comment" placeholder="Short comment..." class="rounded-xl border-none text-blue-900 px-6 py-3 focus:ring-2 focus:ring-white min-w-[250px]">
                        <button type="submit" class="bg-white text-blue-600 font-bold px-8 py-3 rounded-xl hover:bg-blue-50 transition-all shadow-lg active:scale-95">
                            Submit Review
                        </button>
                    </form>
                </div>
            @endif

            @if($order->review)
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-xl mb-8 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Your Review</p>
                        <div class="flex items-center text-yellow-400 mb-1">
                            @for($i=0; $i<$order->review->rating; $i++)
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            @endfor
                        </div>
                        <p class="text-gray-700 font-medium italic">"{{ $order->review->comment }}"</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Invoice</span>
                        <a href="{{ route('orders.invoice', $order->id) }}" class="text-blue-600 font-bold hover:underline">Download PDF</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Main Content (2/3) -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Tracking Map -->
                    <div class="bg-white p-4 rounded-3xl shadow-xl border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Live Tracking
                        </h3>
                        <div id="tracking-map" class="h-96 rounded-2xl bg-gray-100 border border-gray-200 z-10 transition-shadow hover:shadow-inner">
                            <!-- Map goes here -->
                        </div>
                    </div>

                    <!-- Photo Proofs -->
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Service Progress Photos
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @forelse($order->photos as $photo)
                                <div class="group relative rounded-xl overflow-hidden shadow-sm aspect-square bg-gray-100 border border-gray-200">
                                    <img src="{{ asset('storage/' . $photo->photo_path) }}" class="w-full h-full object-cover transition-transform group-hover:scale-110">
                                    <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-[10px] p-2 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                        {{ str_replace('_', ' ', strtoupper($photo->context)) }}
                                    </div>
                                </div>
                            @empty
                                <p class="col-span-full text-center text-gray-400 py-8 italic">No photos uploaded yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Sidebar (1/3) -->
                <div class="space-y-8">
                    <!-- Order Info -->
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">Summary</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">Service</span>
                                <span class="text-gray-900 font-bold text-right">{{ $order->service->name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">Category</span>
                                <span class="text-gray-900 font-bold text-right">{{ $order->itemType->name }}</span>
                            </div>
                            <hr class="border-gray-50">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">Service Fee</span>
                                <span class="text-gray-900 font-bold">Rp {{ number_format($order->service_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">Item Type</span>
                                <span class="text-gray-900 font-bold">Rp {{ number_format($order->item_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">Shipping</span>
                                <span class="text-gray-900 font-bold">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">Tax (10%)</span>
                                <span class="text-gray-900 font-bold">Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-lg pt-4 border-t border-gray-100">
                                <span class="text-gray-800 font-black">Total Paid</span>
                                <span class="text-blue-600 font-black">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pickup Address -->
                    <div class="bg-blue-600 p-8 rounded-3xl shadow-xl text-white">
                        <h3 class="text-lg font-bold mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Address Details
                        </h3>
                        <div class="space-y-4 text-sm">
                            <div>
                                <p class="opacity-70 uppercase text-[10px] font-black tracking-widest mb-1">Pickup Address</p>
                                <p class="font-medium leading-relaxed">{{ $order->pickup_address }}</p>
                            </div>
                            <div class="pt-4 border-t border-blue-500/50">
                                <p class="opacity-70 uppercase text-[10px] font-black tracking-widest mb-1">Delivery Address</p>
                                <p class="font-medium leading-relaxed">{{ $order->delivery_address }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Section (Cross-role) -->
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 flex flex-col h-[500px]">
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
                                <p class="text-center text-gray-400 py-8 italic text-sm">No messages yet. Ask anything about your order!</p>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderId = {{ $order->id }};
            const currentUserId = {{ auth()->id() }};

            // Initialize map
            var lat = {{ $latestLocation->latitude ?? -7.2504 }};
            var lng = {{ $latestLocation->longitude ?? 112.7688 }};
            
            var map = L.map('tracking-map').setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var courierMarker = null;

            @if($latestLocation)
                courierMarker = L.marker([lat, lng]).addTo(map)
                    .bindPopup('Courier is here')
                    .openPopup();
            @else
                var tempMarker = L.marker([lat, lng]).addTo(map).bindPopup('Awaiting courier location...').openPopup();
            @endif

            // Real-time Echo Listeners
            if (window.Echo) {
                // Tracking & Status
                window.Echo.private(`order.${orderId}`)
                    .listen('LocationUpdated', (e) => {
                        console.log('Location updated:', e.location);
                        var newLatLng = new L.LatLng(e.location.latitude, e.location.longitude);
                        if (!courierMarker) {
                            if (tempMarker) map.removeLayer(tempMarker);
                            courierMarker = L.marker(newLatLng).addTo(map).bindPopup('Courier is here').openPopup();
                        } else {
                            courierMarker.setLatLng(newLatLng);
                        }
                        map.panTo(newLatLng);
                    })
                    .listen('OrderStatusUpdated', (e) => {
                        console.log('Status updated:', e.order.status);
                        // Reload for status change (simplest way to update various UI elements)
                        window.location.reload();
                    })
                    .listen('MessageSent', (e) => {
                        console.log('New message received:', e.message);
                        appendMessage(e.message);
                    });
            }

            function appendMessage(msg) {
                const chatContainer = document.querySelector('.flex-1.overflow-y-auto');
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
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
</x-app-layout>
