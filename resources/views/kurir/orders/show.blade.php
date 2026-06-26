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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderId = {{ $order->id }};
            const currentUserId = {{ auth()->id() }};

            // Real-time Chat Listener
            if (window.Echo) {
                window.Echo.private(`order.${orderId}`)
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

        // Real-time Location Tracking update from Courier
        if ("geolocation" in navigator) {
            setInterval(() => {
                navigator.geolocation.getCurrentPosition(function(position) {
                    fetch('{{ route("kurir.location.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            order_id: '{{ $order->id }}'
                        })
                    });
                }, (error) => {
                    console.error("GPS Error:", error);
                }, {
                    enableHighAccuracy: true
                });
            }, 30000); // Every 30 seconds
        }
    </script>
</x-app-layout>
