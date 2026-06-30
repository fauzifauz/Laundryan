<x-app-layout>
    @php
        $statusConfig = [
            'pending' => [
                'label' => 'Pending Verification',
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'dot' => 'bg-amber-500',
                'icon' => 'hourglass_empty'
            ],
            'success' => [
                'label' => 'Success / Verified',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'icon' => 'check_circle'
            ],
            'failed' => [
                'label' => 'Failed / Rejected',
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                'dot' => 'bg-rose-500',
                'icon' => 'cancel'
            ],
            'refunded' => [
                'label' => 'Refunded',
                'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'dot' => 'bg-indigo-500',
                'icon' => 'replay'
            ]
        ];

        $methodConfig = [
            'cash'     => ['label' => 'Cash',         'icon' => 'payments',             'bg' => 'bg-slate-100 text-slate-700'],
            'transfer' => ['label' => 'Bank Transfer', 'icon' => 'account_balance',      'bg' => 'bg-blue-50 text-blue-700'],
            'e-wallet' => ['label' => 'E-Wallet',      'icon' => 'account_balance_wallet','bg' => 'bg-purple-50 text-purple-700'],
            'stripe'   => ['label' => 'Stripe Card',   'icon' => 'credit_card',          'bg' => 'bg-indigo-50 text-indigo-700'],
            'qris'     => ['label' => 'QRIS',         'icon' => 'qr_code_2',             'bg' => 'bg-fuchsia-50 text-fuchsia-700']
        ];

        $pConfig = $statusConfig[$payment->status] ?? $statusConfig['pending'];
        $mConfig = $methodConfig[$payment->payment_method] ?? ['label' => $payment->payment_method, 'icon' => 'help', 'bg' => 'bg-gray-50 text-gray-600'];
        $order = $payment->order;
    @endphp

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.payments.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-outlined text-[24px]">arrow_back</span>
                    </a>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Payment Transaction Detail</h2>
                </div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Invoice ID: {{ $payment->payment_code }} · Order Code: #{{ $order->order_code }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a id="detailDownloadBtn" href="{{ route('admin.payments.invoice', $payment->id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-rose-100 text-rose-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-rose-50 hover:shadow-md transition-all group shadow-sm">
                    <span class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">picture_as_pdf</span> Download Invoice PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-[92rem] mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                    x-transition:enter="transform ease-out duration-300 transition"
                    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed top-6 right-6 z-50 max-w-sm w-full bg-gradient-to-r from-emerald-600 to-teal-600 rounded-3xl p-5 shadow-2xl text-white flex items-center justify-between overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-10 h-10 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center shadow-inner">
                            <span class="material-symbols-outlined text-white text-xl">check_circle</span>
                        </div>
                        <div>
                            <h4 class="font-black text-xs uppercase tracking-wider">Changes Saved</h4>
                            <p class="text-[11px] text-emerald-50 font-medium mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-white/60 hover:text-white transition-colors p-2 rounded-xl hover:bg-white/10 relative z-10">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- LEFT COLUMN: Billing details + Admin verification -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- Card 1: Billing & Invoice Details -->
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Billing Information</span>
                                <h3 class="text-lg font-black text-gray-800 tracking-tight mt-0.5">Price Breakdown</h3>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black uppercase {{ $pConfig['bg'] }}">
                                <span class="w-2 h-2 rounded-full {{ $pConfig['dot'] }}"></span>
                                {{ $pConfig['label'] }}
                            </span>
                        </div>

                        <!-- Item details -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <div>
                                    <p class="font-extrabold text-gray-800">{{ $order->service?->name ?? 'Service' }}</p>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-0.5">{{ $order->itemType?->name ?? 'Item Type' }}</p>
                                </div>
                                <span class="font-bold text-gray-900">Rp {{ number_format($order->service_price + $order->item_price, 0, ',', '.') }}</span>
                            </div>

                            <div class="border-t border-gray-100 my-2"></div>

                            <div class="flex justify-between items-center text-xs text-gray-600">
                                <span>Shipping Cost</span>
                                <span class="font-bold text-gray-800">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs text-gray-600">
                                <span>Service Tax (10%)</span>
                                <span class="font-bold text-gray-800">Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                            </div>

                            <div class="border-t border-dashed border-gray-200 pt-3 flex justify-between items-center">
                                <span class="text-sm font-black text-gray-900 tracking-wide uppercase">TOTAL BILL</span>
                                <span class="text-xl font-black text-emerald-600">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-gray-50/50 rounded-2xl border border-gray-100 p-4 grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Payment Method</span>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold {{ $mConfig['bg'] }}">
                                        <span class="material-symbols-outlined text-[14px]">{{ $mConfig['icon'] }}</span>
                                        {{ $mConfig['label'] }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block">Payment Date</span>
                                <span class="text-xs font-bold text-gray-700 block mt-1">
                                    {{ $payment->payment_date ? $payment->payment_date->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }} WIB
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Admin Verification Actions -->
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Verification Action</span>
                                <h3 class="text-lg font-black text-gray-800 tracking-tight mt-0.5">Admin Payment Verification</h3>
                            </div>
                            @if(session('success') && (session('action_status') === 'failed' || session('action_status') === 'refunded'))
                                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 transform scale-95"
                                     x-transition:enter-end="opacity-100 transform scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold rounded-lg self-start sm:self-auto">
                                    <span class="text-emerald-500 font-extrabold">✓</span>
                                    <span>
                                        @if(session('action_status') === 'failed')
                                            Payment Marked as Failed Successfully
                                        @elseif(session('action_status') === 'refunded')
                                            Refund Processed Successfully
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if($payment->status === 'pending')
                            <form action="{{ route('admin.payments.verify', $payment->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Admin Notes (Optional)</label>
                                    <textarea name="admin_notes" rows="3"
                                              class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3 px-4"
                                              placeholder="Write a reason if rejecting, or any verification notes..."></textarea>
                                </div>

                                <div class="flex gap-4">
                                    <button type="submit" name="status" value="success"
                                            class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-100 hover:shadow-xl transition-all">
                                        <span class="material-symbols-outlined text-[18px]">check_circle</span> Approve
                                    </button>
                                    <button type="submit" name="status" value="failed"
                                            class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-1.5 shadow-lg shadow-rose-100 hover:shadow-xl transition-all">
                                        <span class="material-symbols-outlined text-[18px]">cancel</span> Reject
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 space-y-3">
                                <div class="flex items-center gap-2 text-sm font-bold text-gray-700">
                                    <span class="material-symbols-outlined text-blue-600">info</span>
                                    <span>This transaction has already been processed.</span>
                                </div>
                                @if($payment->admin_notes)
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <p class="font-black text-[9px] text-gray-400 uppercase tracking-widest">Admin Notes:</p>
                                        <p class="italic bg-white p-3 rounded-xl border border-gray-100">"{{ $payment->admin_notes }}"</p>
                                    </div>
                                @endif

                                <div class="border-t border-gray-200/50 my-2 pt-2">
                                    <p class="text-[10px] text-gray-400 font-bold">Want to change the verification status of this payment?</p>

                                    <form action="{{ route('admin.payments.verify', $payment->id) }}" method="POST" class="mt-3 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                        @csrf
                                        <div class="md:col-span-8">
                                            <input type="text" name="admin_notes" value="{{ $payment->admin_notes }}"
                                                   class="w-full bg-white border border-gray-200 rounded-xl text-xs font-bold py-2 px-3 focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="Notes for status update...">
                                        </div>
                                        <div class="md:col-span-4 flex gap-1">
                                            @if($payment->status === 'success')
                                                <button type="submit" name="status" value="failed"
                                                        class="flex-1 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                                    Mark as Failed
                                                </button>
                                                <button type="submit" name="status" value="refunded"
                                                        class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                                    Refund Payment
                                                </button>
                                            @elseif($payment->status === 'refunded')
                                                <span class="w-full py-2 text-center text-indigo-700 text-[10px] font-black uppercase tracking-widest bg-indigo-50 border border-indigo-200 rounded-xl">
                                                    Already Refunded
                                                </span>
                                            @else
                                                <button type="submit" name="status" value="success"
                                                        class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                                    Mark as Success
                                                </button>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- RIGHT COLUMN: Customer profile + Proof of Payment -->
                <div class="lg:col-span-5 space-y-6">

                    <!-- Card 3: Customer Profile Info -->
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-4">
                        <div>
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Customer Profile</span>
                            <h3 class="text-lg font-black text-gray-800 tracking-tight mt-0.5">Contact Information</h3>
                        </div>

                        <div class="flex items-center gap-4">
                            @if($order->customer?->photo)
                                <img src="{{ Storage::url($order->customer->photo) }}"
                                     class="w-16 h-16 rounded-2xl object-cover border border-gray-100 shadow-sm" alt="Profile Photo">
                            @else
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-black text-2xl flex items-center justify-center shadow-md">
                                    {{ strtoupper(substr($order->customer?->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <h4 class="font-black text-gray-900 tracking-tight">{{ $order->customer?->name ?? 'Walk-In Guest' }}</h4>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-0.5">Laundryan Customer</p>
                                <span class="inline-flex items-center gap-1 mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 uppercase">
                                    <span class="material-symbols-outlined text-[12px]">smartphone</span> {{ $order->customer?->phone ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4 space-y-3 text-xs">
                            @if($order->customer?->email)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-[16px] mt-0.5">mail</span>
                                    <div>
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Email Address</span>
                                        <a href="mailto:{{ $order->customer->email }}" class="font-bold text-gray-700 hover:text-blue-600 hover:underline">{{ $order->customer->email }}</a>
                                    </div>
                                </div>
                            @endif

                            @if($order->pickup_address)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-[16px] mt-0.5">home_pin</span>
                                    <div>
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Pickup Address</span>
                                        <p class="font-bold text-gray-700 leading-relaxed">{{ $order->pickup_address }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($order->delivery_address)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-[16px] mt-0.5">local_shipping</span>
                                    <div>
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Delivery Address</span>
                                        <p class="font-bold text-gray-700 leading-relaxed">{{ $order->delivery_address }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card 4: Proof of Payment image preview -->
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Proof of Payment</span>
                                <h3 class="text-lg font-black text-gray-800 tracking-tight mt-0.5">Receipt / Transfer Slip</h3>
                            </div>
                            @if($payment->proof_path)
                                <a href="{{ Storage::url($payment->proof_path) }}" download
                                   class="p-2 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-xl border border-gray-200 shadow-sm transition-all"
                                   title="Download Proof">
                                    <span class="material-symbols-outlined text-[18px] block">download</span>
                                </a>
                            @endif
                        </div>

                        @if($payment->proof_path)
                            <div class="relative group bg-gray-50 rounded-2xl p-2 border border-gray-100 shadow-inner flex items-center justify-center overflow-hidden cursor-zoom-in"
                                 onclick="openReceiptZoom()">
                                <img src="{{ Storage::url($payment->proof_path) }}"
                                     class="w-full max-h-[400px] object-contain rounded-xl shadow-sm transition-transform duration-300"
                                     alt="Transfer Proof">
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white rounded-2xl">
                                    <span class="material-symbols-outlined text-3xl">zoom_in</span>
                                </div>
                            </div>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest text-center">Click on the image to zoom in</p>
                        @else
                            <div class="bg-gray-50 rounded-2xl p-12 border border-dashed border-gray-200 flex flex-col items-center justify-center gap-2 text-gray-400">
                                <span class="material-symbols-outlined text-4xl">no_photography</span>
                                <p class="text-xs font-bold">No Proof of Payment Uploaded</p>
                                <p class="text-[10px] text-center max-w-[200px]">Customer has not uploaded a transfer slip, or used Cash as payment method.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Zoom Receipt Modal -->
    @if($payment->proof_path)
        <div id="zoomModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm">
            <div class="max-w-4xl w-full p-4 relative mx-4 flex flex-col items-center justify-center h-full">
                <button onclick="closeReceiptZoom()" class="absolute top-6 right-6 w-10 h-10 bg-white/20 text-white hover:bg-white/40 rounded-full flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <img src="{{ Storage::url($payment->proof_path) }}" class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl" alt="Transfer Proof Zoom">
                <div class="flex gap-4 mt-6">
                    <a href="{{ Storage::url($payment->proof_path) }}" download
                       class="py-2.5 px-6 bg-white text-gray-900 rounded-xl text-xs font-black uppercase tracking-widest flex items-center gap-1.5 transition-all shadow-lg hover:bg-gray-100">
                        <span class="material-symbols-outlined text-[18px]">download</span> Download Proof
                    </a>
                </div>
            </div>
        </div>
    @endif

    <script>
        function openReceiptZoom() {
            const m = document.getElementById('zoomModal');
            if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
        }
        function closeReceiptZoom() {
            const m = document.getElementById('zoomModal');
            if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
        }
        const zm = document.getElementById('zoomModal');
        if (zm) zm.addEventListener('click', function(e) { if (e.target === this) closeReceiptZoom(); });

        // Download Invoice Button Loading Animation
        const detailBtn = document.getElementById('detailDownloadBtn');
        if (detailBtn) {
            detailBtn.addEventListener('click', function(e) {
                detailBtn.classList.add('pointer-events-none', 'opacity-70');
                detailBtn.innerHTML = `
                    <svg class="animate-spin h-4.5 w-4.5 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Downloading...</span>
                `;
                setTimeout(function() {
                    detailBtn.classList.remove('pointer-events-none', 'opacity-70');
                    detailBtn.innerHTML = `
                        <span class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">picture_as_pdf</span> Download Invoice PDF
                    `;
                }, 4000);
            });
        }
    </script>
</x-app-layout>
