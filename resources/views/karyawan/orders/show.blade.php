<x-app-layout>
    <div x-data="{
        showToast: {{ session('success') ? 'true' : 'false' }},
        toastMessage: '{{ session('success', '') }}',
        toastTitle: '{{ session('action_type') === 'message_sent' ? 'Message Sent' : (session('action_type') === 'courier_assigned' ? 'Courier Assigned' : (session('action_type') === 'status_updated' ? 'Status Updated' : 'Success')) }}',
        triggerToast(title, msg) {
            this.toastTitle = title;
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 5000);
        }
    }" x-init="if (showToast) { setTimeout(() => { showToast = false; }, 5000); }" class="relative">

        <!-- Toast alert -->
        <div x-show="showToast" 
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed top-6 right-6 z-50 max-w-sm w-full bg-emerald-50 border border-emerald-200 rounded-3xl p-5 shadow-2xl text-emerald-800 flex items-center justify-between overflow-hidden" x-cloak>
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-600/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100/50 border border-emerald-200 flex items-center justify-center shadow-inner">
                    <span class="material-symbols-outlined text-emerald-600 text-xl">check_circle</span>
                </div>
                <div>
                    <h4 class="font-black text-xs uppercase tracking-wider" x-text="toastTitle"></h4>
                    <p class="text-[11px] text-emerald-700 font-medium mt-0.5" x-text="toastMessage"></p>
                </div>
            </div>
            <button @click="showToast = false" class="text-emerald-600/60 hover:text-emerald-800 transition-colors p-2 rounded-xl hover:bg-emerald-100/50 relative z-10">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @php
        $statusConfig = [
            'pending_payment' => [
                'label' => 'Pending Payment',
                'bg' => 'bg-slate-50 text-slate-700 border-slate-200',
                'dot' => 'bg-slate-400',
                'icon' => 'hourglass_empty'
            ],
            'waiting_pickup' => [
                'label' => 'Waiting Pickup',
                'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                'dot' => 'bg-blue-500',
                'icon' => 'hail'
            ],
            'picking_up' => [
                'label' => 'Picking Up',
                'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                'dot' => 'bg-blue-500',
                'icon' => 'local_shipping'
            ],
            'picked_up' => [
                'label' => 'Picked Up',
                'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                'dot' => 'bg-blue-500',
                'icon' => 'shopping_bag'
            ],
            'in_transit_to_laundry' => [
                'label' => 'In Transit',
                'bg' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                'dot' => 'bg-yellow-500',
                'icon' => 'local_shipping'
            ],
            'arrived_at_laundry' => [
                'label' => 'Arrived at Laundry',
                'bg' => 'bg-orange-50 text-orange-700 border-orange-200',
                'dot' => 'bg-orange-500',
                'icon' => 'store'
            ],
            'washing' => [
                'label' => 'Washing',
                'bg' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                'dot' => 'bg-cyan-500',
                'icon' => 'local_laundry_service'
            ],
            'drying_ironing' => [
                'label' => 'Drying & Ironing',
                'bg' => 'bg-teal-50 text-teal-700 border-teal-200',
                'dot' => 'bg-teal-500',
                'icon' => 'iron'
            ],
            'packing' => [
                'label' => 'Packing',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'icon' => 'inventory_2'
            ],
            'ready_for_delivery' => [
                'label' => 'Ready for Delivery',
                'bg' => 'bg-lime-50 text-lime-700 border-lime-200',
                'dot' => 'bg-lime-500',
                'icon' => 'outbox'
            ],
            'delivering' => [
                'label' => 'Delivering',
                'bg' => 'bg-sky-50 text-sky-700 border-sky-200',
                'dot' => 'bg-sky-500',
                'icon' => 'delivery_dining'
            ],
            'completed' => [
                'label' => 'Completed',
                'bg' => 'bg-green-50 text-green-700 border-green-200',
                'dot' => 'bg-green-500',
                'icon' => 'check_circle'
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                'dot' => 'bg-rose-500',
                'icon' => 'cancel'
            ],
        ];

        $paymentStatusConfig = [
            'unpaid' => [
                'label' => 'Unpaid',
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                'dot' => 'bg-rose-500',
                'icon' => 'pending'
            ],
            'paid' => [
                'label' => 'Paid',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'icon' => 'check_circle'
            ],
        ];

        $sConfig = $statusConfig[$order->status] ?? [
            'label' => str_replace('_', ' ', $order->status),
            'bg' => 'bg-gray-50 text-gray-700 border-gray-200',
            'dot' => 'bg-gray-500',
            'icon' => 'help'
        ];

        $psConfig = $paymentStatusConfig[$order->payment_status] ?? [
            'label' => $order->payment_status,
            'bg' => 'bg-gray-50 text-gray-700 border-gray-200',
            'dot' => 'bg-gray-500',
            'icon' => 'help'
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('karyawan.orders.index') }}"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-outlined text-[24px]">arrow_back</span>
                    </a>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                        Order Details
                        @if(session('success') && session('action_type') === 'order_updated')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-full">
                                ✓ Successfully Updated
                            </span>
                        @endif
                    </h2>
                </div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Order Code:
                    #{{ $order->order_code }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-[92rem] mx-auto sm:px-6 lg:px-8">

            @if(session('success') && !in_array(session('action_type'), ['message_sent', 'courier_assigned', 'status_updated']))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm mb-6"
                    role="alert">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- LEFT COLUMN: Order Details, Timelines, Photos, Chat -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- Card 1: Order Information -->
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-start flex-wrap gap-2">
                            <div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">General
                                    Information</span>
                                <h3 class="text-lg font-black text-gray-800 tracking-tight mt-0.5">Order Overview</h3>
                            </div>
                            <div class="flex gap-2">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase {{ $sConfig['bg'] }}">
                                    <span class="material-symbols-outlined text-[14px]">{{ $sConfig['icon'] }}</span>
                                    {{ $sConfig['label'] }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase {{ $psConfig['bg'] }}">
                                    <span class="material-symbols-outlined text-[14px]">{{ $psConfig['icon'] }}</span>
                                    {{ $psConfig['label'] }}
                                </span>
                            </div>
                        </div>

                        @if(!empty($qrCode))
                            <div class="flex justify-center">
                                <button onclick="document.getElementById('qrModal').classList.replace('hidden','flex')"
                                    class="bg-gray-50 border border-gray-200 rounded-3xl p-4 hover:border-blue-400 hover:shadow-md transition-all group flex flex-col items-center gap-1.5 shadow-sm">
                                    <img src="{{ $qrCode }}" class="w-24 h-24 object-contain" alt="QR Code">
                                    <span
                                        class="text-[8px] text-gray-400 font-black uppercase tracking-widest group-hover:text-blue-500">Tap
                                        to Zoom QR</span>
                                </button>
                            </div>
                        @endif

                        <div class="space-y-4">
                            <!-- Service Details Grid -->
                            <div
                                class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-gray-50/50 rounded-2xl border border-gray-100 p-4">
                                <div>
                                    <span
                                        class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Service
                                        Type</span>
                                    <span
                                        class="text-xs font-black text-gray-800 block mt-1">{{ $order->service->name }}</span>
                                </div>
                                <div>
                                    <span
                                        class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Item
                                        Category</span>
                                    <span
                                        class="text-xs font-black text-gray-800 block mt-1">{{ $order->itemType->name }}</span>
                                </div>
                                <div>
                                    <span
                                        class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Soap
                                        Pref</span>
                                    <span class="text-xs font-bold text-blue-600 block mt-1">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">bubble_chart</span>
                                            {{ $order->soap ?: 'Default Soap' }}
                                        </span>
                                    </span>
                                </div>
                                <div>
                                    <span
                                        class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Fragrance
                                        Pref</span>
                                    <span class="text-xs font-bold text-purple-600 block mt-1">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">opacity</span>
                                            {{ $order->fragrance ?: 'Default Fragrance' }}
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <!-- Notes -->
                            @if($order->notes)
                                <div
                                    class="bg-amber-50/50 border border-amber-100/80 rounded-2xl p-4 flex items-start gap-3">
                                    <span
                                        class="material-symbols-outlined text-amber-500 text-[20px] shrink-0 mt-0.5">sticky_note_2</span>
                                    <div>
                                        <span
                                            class="text-[8px] font-black text-amber-600 uppercase tracking-widest block">Customer
                                            Notes</span>
                                        <p class="text-xs font-bold text-amber-800 mt-1 italic">"{{ $order->notes }}"</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Pickup & Delivery Addresses -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-blue-50/30 border border-blue-100/60 rounded-2xl p-4 space-y-2">
                                <div
                                    class="flex items-center gap-1.5 text-blue-700 font-extrabold text-[10px] uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-[16px]">hail</span>
                                    <span>Pickup Information</span>
                                </div>
                                <p class="text-xs font-bold text-gray-700 leading-relaxed">{{ $order->pickup_address }}
                                </p>
                                @if($order->pickup_lat)
                                    @php
                                        $isFinished = in_array($order->status, ['completed', 'cancelled']);
                                        $pickupGpsUrl = $isFinished
                                            ? "https://www.google.com/maps/search/?api=1&query={$order->pickup_lat},{$order->pickup_lng}"
                                            : route('karyawan.tracking.index') . "?focus_order={$order->id}&lat={$order->pickup_lat}&lng={$order->pickup_lng}&label=Pickup+Target+ORD-" . urlencode($order->order_code);
                                    @endphp
                                    <a href="{{ $pickupGpsUrl }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-[9px] font-black text-blue-600 bg-white border border-blue-100 px-2 py-0.5 rounded-lg shadow-sm hover:bg-blue-50 transition-colors uppercase tracking-wider">
                                        <span class="material-symbols-outlined text-[12px]">map</span> Open GPS
                                    </a>
                                @endif
                            </div>

                            <div class="bg-emerald-50/30 border border-emerald-100/60 rounded-2xl p-4 space-y-2">
                                <div
                                    class="flex items-center gap-1.5 text-emerald-700 font-extrabold text-[10px] uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-[16px]">local_shipping</span>
                                    <span>Delivery Information</span>
                                </div>
                                <p class="text-xs font-bold text-gray-700 leading-relaxed">
                                    {{ $order->delivery_address }}</p>
                                @if($order->delivery_lat)
                                    @php
                                        $deliveryGpsUrl = $isFinished
                                            ? "https://www.google.com/maps/search/?api=1&query={$order->delivery_lat},{$order->delivery_lng}"
                                            : route('karyawan.tracking.index') . "?focus_order={$order->id}&lat={$order->delivery_lat}&lng={$order->delivery_lng}&label=Delivery+Target+ORD-" . urlencode($order->order_code);
                                    @endphp
                                    <a href="{{ $deliveryGpsUrl }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-[9px] font-black text-emerald-600 bg-white border border-emerald-100 px-2 py-0.5 rounded-lg shadow-sm hover:bg-emerald-50 transition-colors uppercase tracking-wider">
                                        <span class="material-symbols-outlined text-[12px]">map</span> Open GPS
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Status Timeline -->
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-purple-500 flex items-center justify-center text-white shadow-sm">
                                <span class="material-symbols-outlined text-lg">timeline</span>
                            </div>
                            <div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Audit
                                    Trail</span>
                                <h3 class="text-lg font-black text-gray-800 tracking-tight mt-0.5">Order Status History
                                </h3>
                            </div>
                        </div>

                        @if($order->statusLogs->count())
                            <div class="relative pl-6 border-l border-gray-100 space-y-6 ml-4">
                                @foreach($order->statusLogs as $log)
                                    @php
                                        $currConfig = $statusConfig[$log->status] ?? [
                                            'label' => str_replace('_', ' ', $log->status),
                                            'bg' => 'bg-gray-50 text-gray-600',
                                            'dot' => 'bg-gray-400',
                                            'icon' => 'help'
                                        ];
                                    @endphp
                                    <div class="relative">
                                        <!-- Timeline Dot -->
                                        <div
                                            class="absolute -left-[30px] top-1 w-3.5 h-3.5 rounded-full {{ $currConfig['dot'] }} border-2 border-white shadow-sm">
                                        </div>

                                        <div
                                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gray-50/50 hover:bg-gray-50 border border-gray-100/50 rounded-2xl p-3.5 transition-colors">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase {{ $currConfig['bg'] }}">
                                                    <span
                                                        class="material-symbols-outlined text-[12px]">{{ $currConfig['icon'] }}</span>
                                                    {{ $currConfig['label'] }}
                                                </span>
                                            </div>
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                                                {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB ·
                                                {{ $log->user->name ?? 'System' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div
                                class="bg-gray-50 rounded-2xl p-8 border border-dashed border-gray-200 flex flex-col items-center justify-center gap-2 text-gray-400">
                                <span class="material-symbols-outlined text-3xl">route</span>
                                <p class="text-xs font-bold">No history recorded yet</p>
                            </div>
                        @endif
                    </div>


                    <!-- Notification for Chat -->
                    @if(session('success') && session('action_type') === 'message_sent')
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm mb-4"
                            role="alert">
                            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                            <span class="text-sm font-bold">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Card 4: Chat -->
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col"
                        style="height:480px">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3 bg-gray-50/50 shrink-0">
                            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white">
                                <span class="material-symbols-outlined text-lg">forum</span>
                            </div>
                            <div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Internal
                                    Thread</span>
                                <h3 class="text-sm font-black text-gray-800 tracking-tight mt-0.5">Team Coordination
                                    Chat</h3>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-5 space-y-4 custom-scrollbar bg-gray-50/30">
                            @php
                                $roleBubbleConfig = [
                                    'admin' => [
                                        'bg' => 'bg-blue-50 text-blue-900 border border-blue-200/65',
                                        'badge' => 'text-blue-600 bg-blue-100/50',
                                        'role_name' => 'Admin'
                                    ],
                                    'karyawan' => [
                                        'bg' => 'bg-amber-50 text-amber-900 border border-amber-200/65',
                                        'badge' => 'text-amber-600 bg-amber-100/50',
                                        'role_name' => 'Staff'
                                    ],
                                    'kurir' => [
                                        'bg' => 'bg-purple-50 text-purple-900 border border-purple-200/65',
                                        'badge' => 'text-purple-600 bg-purple-100/50',
                                        'role_name' => 'Courier'
                                    ],
                                    'pelanggan' => [
                                        'bg' => 'bg-emerald-50 text-emerald-900 border border-emerald-200/65',
                                        'badge' => 'text-emerald-600 bg-emerald-100/50',
                                        'role_name' => 'Customer'
                                    ]
                                ];
                            @endphp
                            @forelse($order->messages as $msg)
                                @php
                                    $mine = $msg->sender_id === auth()->id();
                                    $role = strtolower($msg->sender->role ?? 'pelanggan');
                                    $rCfg = $roleBubbleConfig[$role] ?? $roleBubbleConfig['pelanggan'];
                                @endphp
                                <div class="flex flex-col {{ $mine ? 'items-end' : 'items-start' }} space-y-1">
                                    <div
                                        class="max-w-[75%] px-4 py-3 rounded-2xl text-xs font-bold shadow-sm {{ $rCfg['bg'] }} {{ $mine ? 'rounded-tr-none' : 'rounded-tl-none' }}">
                                        <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                                            <span
                                                class="text-[10px] font-black uppercase text-gray-800">{{ $msg->sender->name }}</span>
                                            <span
                                                class="text-[8px] font-black uppercase px-1.5 py-0.5 rounded {{ $rCfg['badge'] }} tracking-wider">
                                                {{ $rCfg['role_name'] }}
                                            </span>
                                        </div>
                                        <p class="leading-relaxed text-[11px] font-semibold">{{ $msg->message }}</p>
                                    </div>
                                    <span
                                        class="text-[9px] text-gray-400 font-bold uppercase tracking-wider px-1">{{ $msg->created_at->diffForHumans() }}</span>
                                </div>
                            @empty
                                <div class="h-full flex flex-col items-center justify-center text-gray-300 gap-2">
                                    <span class="material-symbols-outlined text-4xl">chat_bubble_outline</span>
                                    <p class="text-xs font-bold uppercase tracking-wider">No coordination logs yet</p>
                                </div>
                            @endforelse
                        </div>

                        <form action="{{ route('messages.store', $order->id) }}" method="POST"
                            class="p-4 border-t border-gray-100 relative bg-white shrink-0">
                            @csrf
                            <input type="text" name="message" required autocomplete="off"
                                placeholder="Type an internal note or message…"
                                class="w-full bg-gray-50 border border-gray-200 rounded-2xl pr-12 pl-4 py-3.5 text-xs font-bold text-gray-800 focus:bg-white focus:border-blue-500 focus:ring-0 transition-all shadow-inner">
                            <button type="submit"
                                class="absolute right-7 top-1/2 -translate-y-1/2 bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-xl shadow-lg shadow-blue-100 active:scale-95 transition-all">
                                <span class="material-symbols-outlined text-[16px] block">send</span>
                            </button>
                        </form>
                    </div>

                    <!-- Card 5: Review -->
                    @if($order->review)
                        <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-yellow-400 flex items-center justify-center text-white shadow-sm">
                                    <span class="material-symbols-outlined text-lg">star</span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Customer
                                        Feedback</span>
                                    <h3 class="text-lg font-black text-gray-800 tracking-tight mt-0.5">Review & Rating</h3>
                                </div>
                            </div>

                            <div
                                class="flex flex-col sm:flex-row gap-4 sm:items-center bg-yellow-50/30 border border-yellow-100/50 p-4 rounded-2xl">
                                <div class="flex items-center gap-0.5 text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span
                                            class="material-symbols-outlined text-[22px] {{ $i <= ($order->review->rating ?? 0) ? 'fill-current' : 'text-gray-200' }}">star</span>
                                    @endfor
                                </div>
                                <p class="text-xs text-gray-700 italic font-bold">
                                    "{{ $order->review->comment ?: 'No comments provided.' }}"</p>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- RIGHT COLUMN: Billing summary, Customer contact, Courier assignment, Controls -->
                <div class="lg:col-span-5 space-y-6">

                    <!-- Card 6: Price Breakdown (Aligned with Payment details) -->
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-6">
                        <div>
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Billing
                                Summary</span>
                            <h3 class="text-lg font-black text-gray-800 tracking-tight mt-0.5">Price Breakdown</h3>
                        </div>

                        <!-- Item details -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <div>
                                    <p class="font-extrabold text-gray-800">{{ $order->service->name }}</p>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-0.5">Base
                                        Service Cost</p>
                                </div>
                                <span class="font-bold text-gray-900">Rp
                                    {{ number_format($order->service_price, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between items-center text-sm">
                                <div>
                                    <p class="font-extrabold text-gray-800">{{ $order->itemType->name }}</p>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-0.5">Item
                                        Weight/Category Cost</p>
                                </div>
                                <span class="font-bold text-gray-900">Rp
                                    {{ number_format($order->item_price, 0, ',', '.') }}</span>
                            </div>

                            <div class="border-t border-gray-100 my-2"></div>

                            <div class="flex justify-between items-center text-xs text-gray-600">
                                <span>Shipping Cost</span>
                                <span class="font-bold text-gray-800">Rp
                                    {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs text-gray-600">
                                <span>Service Tax (10%)</span>
                                <span class="font-bold text-gray-800">Rp
                                    {{ number_format($order->tax, 0, ',', '.') }}</span>
                            </div>

                            <div class="border-t border-dashed border-gray-200 pt-3 flex justify-between items-center">
                                <span class="text-sm font-black text-gray-900 tracking-wide uppercase">TOTAL BILL</span>
                                <span class="text-xl font-black text-emerald-600">Rp
                                    {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-gray-50/50 rounded-2xl border border-gray-100 p-4 grid grid-cols-2 gap-4">
                            <div>
                                <span
                                    class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Payment
                                    Status</span>
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 mt-1 rounded-full text-[10px] font-black uppercase {{ $psConfig['bg'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $psConfig['dot'] }}"></span>
                                    {{ $psConfig['label'] }}
                                </span>
                            </div>
                            <div>
                                <span
                                    class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Payment
                                    Method</span>
                                @php
                                    $method = $order->payment_method ?: ($order->latestPayment?->payment_method);
                                    if ($method === 'qris') {
                                        $methodLabel = 'QRIS';
                                    } elseif ($method === 'stripe') {
                                        $methodLabel = 'Card / Online (Stripe)';
                                    } elseif (in_array($method, ['transfer', 'bank_transfer'])) {
                                        $methodLabel = 'Bank Transfer';
                                    } else {
                                        $methodLabel = $method ? ucwords(str_replace('_', ' ', $method)) : 'Not Specified';
                                    }
                                @endphp
                                <span class="text-xs font-black text-gray-800 block mt-1 uppercase">
                                    {{ $methodLabel }}
                                </span>
                            </div>
                            @if($order->payment_status === 'paid')
                                <div class="col-span-2 border-t border-gray-100 pt-2 mt-1">
                                    <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Payment Completion Time</span>
                                    @php
                                        $paymentDate = $order->latestPayment && $order->latestPayment->status === 'success'
                                            ? $order->latestPayment->payment_date
                                            : null;
                                        if (!$paymentDate && $order->updated_at) {
                                            $paymentDate = $order->updated_at;
                                        }
                                    @endphp
                                    <span class="text-xs font-bold text-gray-800 block mt-0.5">
                                        {{ $paymentDate ? $paymentDate->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if($order->payment_method === 'bank_transfer')
                            @php
                                $latestPayment = $order->payments->sortByDesc('created_at')->first();
                            @endphp
                            @if($latestPayment && $latestPayment->proof_path)
                                <div class="bg-gray-50/50 border border-gray-100 rounded-2xl p-4 space-y-3">
                                    <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Payment Proof Receipt</span>
                                    <a href="{{ asset('storage/' . $latestPayment->proof_path) }}" target="_blank" class="block group relative w-full h-32 rounded-xl overflow-hidden border border-gray-200 bg-white hover:border-blue-400 transition-colors shadow-sm">
                                        <img src="{{ asset('storage/' . $latestPayment->proof_path) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-black gap-1">
                                            <span class="material-symbols-outlined text-sm">zoom_in</span> View Receipt
                                        </div>
                                    </a>
                                </div>
                            @else
                                <div class="bg-gray-50/50 border border-gray-100 rounded-2xl p-4 text-center py-6 text-gray-400 italic text-xs">
                                    No payment proof uploaded yet
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Card 7: Customer Profile (Aligned with Payment details) -->
                    @if($order->customer)
                        <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-4">
                            <div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Customer
                                    Profile</span>
                                <h3 class="text-lg font-black text-gray-800 tracking-tight mt-0.5">Contact Information</h3>
                            </div>

                            <div class="flex items-center gap-4">
                                @if($order->customer->photo)
                                    <img src="{{ Storage::url($order->customer->photo) }}"
                                        class="w-16 h-16 rounded-2xl object-cover border border-gray-100 shadow-sm"
                                        alt="Profile Photo">
                                @else
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-black text-2xl flex items-center justify-center shadow-md">
                                        {{ strtoupper(substr($order->customer->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <h4 class="font-black text-gray-900 tracking-tight">{{ $order->customer->name }}</h4>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-0.5">Customer</p>
                                    <span
                                        class="inline-flex items-center gap-1 mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 uppercase">
                                        <span class="material-symbols-outlined text-[12px]">smartphone</span>
                                        {{ $order->customer->phone ?: '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-4 space-y-3 text-xs">
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-[16px] mt-0.5">mail</span>
                                    <div>
                                        <span
                                            class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Email
                                            Address</span>
                                        <a href="mailto:{{ $order->customer->email }}"
                                            class="font-bold text-gray-700 hover:text-blue-600 hover:underline">{{ $order->customer->email }}</a>
                                    </div>
                                </div>
                                @if($order->pickup_address)
                                    <div class="flex items-start gap-2">
                                        <span class="material-symbols-outlined text-gray-400 text-[16px] mt-0.5">home_pin</span>
                                        <div>
                                            <span
                                                class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Home
                                                Pickup Address</span>
                                            <p class="font-bold text-gray-700 leading-relaxed">{{ $order->pickup_address }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Notification for Courier Assignment -->
                    @if(session('success') && session('action_type') === 'courier_assigned')
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm mb-4"
                            role="alert">
                            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                            <span class="text-sm font-bold">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Card 8: Courier Assignment -->
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-blue-500 flex items-center justify-center text-white shadow-sm">
                                <span class="material-symbols-outlined text-lg">local_shipping</span>
                            </div>
                            <div>
                                <span
                                    class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Logistics</span>
                                <h3 class="text-lg font-black text-gray-800 tracking-tight mt-0.5">Courier Assignment
                                </h3>
                            </div>
                        </div>

                        <div class="space-y-4 pt-2">
                            <div>
                                <label
                                    class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Pickup
                                    Courier</label>
                                <div
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl py-2.5 px-3 text-[12px] font-semibold text-gray-800">
                                    {{ $order->pickupCourier?->name ?? 'Unassigned' }}
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Delivery
                                    Courier</label>
                                <div
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl py-2.5 px-3 text-[12px] font-semibold text-gray-800">
                                    {{ $order->deliveryCourier?->name ?? 'Unassigned' }}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 pt-2">
                            @foreach([['label' => 'Pickup Courier', 'model' => $order->pickupCourier, 'color' => 'blue'], ['label' => 'Delivery Courier', 'model' => $order->deliveryCourier, 'color' => 'emerald']] as $item)
                                @if($item['model'])
                                    <div
                                        class="bg-{{ $item['color'] }}-50/50 border border-{{ $item['color'] }}-100/60 rounded-2xl p-4 space-y-2">
                                        <span
                                            class="text-[9px] font-black uppercase text-{{ $item['color'] }}-600 block">{{ $item['label'] }}</span>
                                        <p class="text-xs font-black text-gray-800">{{ $item['model']->name }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold">{{ $item['model']->phone }}</p>
                                        <div class="flex gap-2 pt-1">
                                            <a href="tel:{{ $item['model']->phone }}"
                                                class="flex-1 text-center py-2 bg-white border border-gray-200 rounded-xl text-[9px] font-black uppercase hover:bg-gray-50 tracking-wider shadow-sm transition-all">Call</a>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $item['model']->phone) }}"
                                                target="_blank"
                                                class="flex-1 text-center py-2 bg-green-50 border border-green-100 text-green-700 rounded-xl text-[9px] font-black uppercase hover:bg-green-100 tracking-wider shadow-sm transition-all">WhatsApp</a>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Notification for Force Status Update -->
                    @if(session('success') && session('action_type') === 'status_updated')
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm mb-4"
                            role="alert">
                            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                            <span class="text-sm font-bold">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Card 9: Status Control -->
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-gray-600 flex items-center justify-center text-white shadow-sm">
                                <span class="material-symbols-outlined text-lg">settings</span>
                            </div>
                            <div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Management
                                    Controls</span>
                                <h3 class="text-lg font-black text-gray-800 tracking-tight mt-0.5">Force Status Update
                                </h3>
                            </div>
                        </div>

                        @php
                            $employeeAllowedStatuses = [
                                'arrived_at_laundry' => 'Arrived at Laundry',
                                'washing' => 'Washing',
                                'drying_ironing' => 'Drying & Ironing',
                                'packing' => 'Packing',
                                'ready_for_delivery' => 'Ready for Delivery',
                            ];
                        @endphp
                        <form action="{{ route('karyawan.orders.status', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4" data-karyawan-status-form>
                            @csrf
                            <div class="relative">
                                <select name="status"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl py-2.5 pl-3 pr-8 text-[12px] font-semibold text-gray-800 appearance-none focus:border-blue-500 focus:ring-0 transition-all">
                                    @if(!array_key_exists($order->status, $employeeAllowedStatuses))
                                        <option value="{{ $order->status }}" selected disabled>
                                            {{ str_replace('_', ' ', ucfirst($order->status)) }} (current)
                                        </option>
                                    @endif
                                    @foreach($employeeAllowedStatuses as $v => $l)
                                        <option value="{{ $v }}" {{ $order->status === $v ? 'selected' : '' }}>{{ $l }}
                                        </option>
                                    @endforeach
                                </select>
                                <span
                                    class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[16px] pointer-events-none">expand_more</span>
                            </div>

                            <div class="space-y-1.5 text-left">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Upload Proof Photo (Optional)</label>
                                <input type="file" name="photo" accept="image/*" class="w-full text-xs font-bold bg-gray-50 border border-gray-200 rounded-xl py-2 px-3 text-gray-800 focus:border-blue-500 focus:ring-0 transition-all">
                            </div>

                            <button type="submit"
                                class="w-full py-3 bg-gray-900 hover:bg-gray-800 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all">
                                Update Status
                            </button>
                        </form>
                    </div>

                </div>
            </div>

            {{-- Full-width: Photo Documentation --}}
            @include('partials.order-photo-documentation')

        </div>
    </div>

    <!-- Zoom QR Modal -->
    @if(!empty($qrCode))
        <div id="qrModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm">
            <div class="bg-white rounded-3xl p-6 w-80 shadow-2xl relative">
                <button onclick="document.getElementById('qrModal').classList.replace('flex','hidden')"
                    class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
                <p class="text-sm font-black text-gray-900 text-center mb-4 uppercase tracking-widest">Order
                    #{{ $order->order_code }}</p>
                <div
                    class="bg-gray-50 rounded-2xl p-4 flex items-center justify-center border border-gray-100 shadow-inner">
                    <img src="{{ $qrCode }}" class="w-64 h-64 object-contain" alt="QR Code">
                </div>
                <p class="text-center text-[9px] text-gray-400 mt-3 font-bold uppercase tracking-wider">Scan code for quick
                    courier / status tracking</p>
            </div>
        </div>
    @endif

    <!-- Zoom Photo Modal -->
    <div id="photoModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm"
        onclick="closePhotoZoom()">
        <div class="max-w-4xl w-full p-4 relative mx-4 flex flex-col items-center justify-center h-full">
            <button onclick="closePhotoZoom()"
                class="absolute top-6 right-6 w-10 h-10 bg-white/20 text-white hover:bg-white/40 rounded-full flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
            <img id="photoZoomImg" src="" class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl"
                alt="Process Proof Zoom">
        </div>
    </div>

    <script>
        function openPhotoZoom(src) {
            const m = document.getElementById('photoModal');
            const img = document.getElementById('photoZoomImg');
            if (m && img) {
                img.src = src;
                m.classList.remove('hidden');
                m.classList.add('flex');
            }
        }
        function closePhotoZoom() {
            const m = document.getElementById('photoModal');
            if (m) {
                m.classList.add('hidden');
                m.classList.remove('flex');
            }
        }
    </script>
    @include('karyawan.partials.order-status-sync')
</div>
</x-app-layout>