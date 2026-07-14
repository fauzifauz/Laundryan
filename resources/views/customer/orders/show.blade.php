<x-app-layout>
    <!-- Animated success popup -->
    @if(session('payment_success_popup'))
        <div x-data="{ show: true }" x-show="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
            <div x-show="show" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl border border-gray-100 text-center space-y-6">
                <!-- Checkmark Circle -->
                <div class="mx-auto w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 animate-bounce">
                    <span class="material-symbols-outlined text-5xl">check_circle</span>
                </div>
                
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-gray-900">Payment Accepted!</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        {{ session('payment_success_popup') }}
                    </p>
                </div>
                
                <div class="bg-gray-50 rounded-2xl p-4 flex justify-between items-center text-left text-xs">
                    <div>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Payment Method</span>
                        <span class="font-extrabold text-gray-700">Bank Transfer</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Order Status</span>
                        <span class="px-2 py-0.5 text-[9px] font-black bg-blue-50 text-blue-700 border border-blue-200 rounded-full">WAITING PICKUP</span>
                    </div>
                </div>

                <div class="pt-2">
                    <button @click="show = false" class="w-full bg-brand hover:bg-blue-700 text-white font-extrabold text-xs py-3 px-6 rounded-xl transition-all shadow-md transform active:scale-95 uppercase tracking-wider">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>
    @endif

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.orders.index') }}" class="p-2 hover:bg-gray-100 rounded-xl transition-colors text-gray-500">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
                <div>
                    <h2 class="font-extrabold text-2xl text-gray-900 tracking-tight">
                        {{ __('Order Details') }}: {{ $order->order_code }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Track courier, manage payment simulation, and message staff directly.</p>
                </div>
            </div>
            <div class="flex gap-2">
                @if($order->payment_status === 'paid')
                    <span class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-black px-4 py-2 rounded-full uppercase tracking-wider">PAID</span>
                @else
                    <span class="bg-yellow-50 border border-yellow-200 text-yellow-700 text-xs font-black px-4 py-2 rounded-full uppercase tracking-wider">PENDING PAYMENT</span>
                @endif
                <span class="bg-brand text-white text-xs font-black px-4 py-2 rounded-full uppercase tracking-wider font-jakarta">
                    {{ str_replace('_', ' ', strtoupper($order->status)) }}
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Map & Photo CDN resources -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

    <div class="py-2 space-y-8">
        <!-- Review Block (If completed and no review yet) -->
        @if($order->status === 'completed' && !$order->review)
            <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-md space-y-6 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">reviews</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 leading-tight">Rate Your Experience</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Please take a moment to rate our service and couriers.</p>
                    </div>
                </div>

                <form action="{{ route('customer.reviews.store', $order->id) }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Laundry Service Rating -->
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-700 block uppercase tracking-wider">Laundry Service</label>
                            <div class="flex items-center gap-1 text-yellow-400" x-data="{ rating: 5 }">
                                <input type="hidden" name="rating_service" :value="rating">
                                <template x-for="i in 5">
                                    <button type="button" @click="rating = i" class="hover:scale-125 transition-transform">
                                        <svg class="w-7 h-7 fill-current" :class="i <= rating ? 'text-yellow-400' : 'text-gray-200'" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Pickup Courier Rating -->
                        @if($order->pickupCourier)
                            <div class="space-y-2">
                                <label class="text-xs font-black text-gray-700 block uppercase tracking-wider">Pickup Courier ({{ $order->pickupCourier->name }})</label>
                                <div class="flex items-center gap-1 text-yellow-400" x-data="{ rating: 5 }">
                                    <input type="hidden" name="rating_pickup_courier" :value="rating">
                                    <template x-for="i in 5">
                                        <button type="button" @click="rating = i" class="hover:scale-125 transition-transform">
                                            <svg class="w-7 h-7 fill-current" :class="i <= rating ? 'text-yellow-400' : 'text-gray-200'" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        @endif

                        <!-- Delivery Courier Rating -->
                        @if($order->deliveryCourier)
                            <div class="space-y-2">
                                <label class="text-xs font-black text-gray-700 block uppercase tracking-wider">Delivery Courier ({{ $order->deliveryCourier->name }})</label>
                                <div class="flex items-center gap-1 text-yellow-400" x-data="{ rating: 5 }">
                                    <input type="hidden" name="rating_delivery_courier" :value="rating">
                                    <template x-for="i in 5">
                                        <button type="button" @click="rating = i" class="hover:scale-125 transition-transform">
                                            <svg class="w-7 h-7 fill-current" :class="i <= rating ? 'text-yellow-400' : 'text-gray-200'" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-700 block uppercase tracking-wider">Write your feedback</label>
                        <textarea name="comment" rows="3" placeholder="Share your experience (e.g. laundry cleanliness, speed, courier friendliness...)" class="w-full rounded-2xl border-gray-200 focus:border-brand focus:ring-brand text-xs p-4" required></textarea>
                    </div>

                    <button type="submit" class="bg-brand hover:bg-blue-700 text-white font-black text-xs px-6 py-3.5 rounded-xl transition-all shadow-md active:scale-95 uppercase tracking-wider inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">send</span> Submit Review
                    </button>
                </form>
            </div>
        @endif

        @if($order->review)
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 text-left">
                <div class="flex-1 space-y-3">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Your Review</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-gray-50 p-3 rounded-xl">
                            <span class="text-[9px] font-black text-gray-400 uppercase block mb-1">Service Rating</span>
                            <div class="flex items-center text-yellow-400">
                                @for($i=0; $i<($order->review->rating_service ?? $order->review->rating); $i++)
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @endfor
                            </div>
                        </div>
                        @if($order->review->rating_pickup_courier)
                            <div class="bg-gray-50 p-3 rounded-xl">
                                <span class="text-[9px] font-black text-gray-400 uppercase block mb-1">Pickup Courier Rating</span>
                                <div class="flex items-center text-yellow-400">
                                    @for($i=0; $i<$order->review->rating_pickup_courier; $i++)
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @endfor
                                </div>
                            </div>
                        @endif
                        @if($order->review->rating_delivery_courier)
                            <div class="bg-gray-50 p-3 rounded-xl">
                                <span class="text-[9px] font-black text-gray-400 uppercase block mb-1">Delivery Courier Rating</span>
                                <div class="flex items-center text-yellow-400">
                                    @for($i=0; $i<$order->review->rating_delivery_courier; $i++)
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @endfor
                                </div>
                            </div>
                        @endif
                    </div>
                    @if($order->review->comment)
                        <p class="text-gray-700 font-bold text-xs italic bg-gray-50 p-4 rounded-2xl border border-gray-100">"{{ $order->review->comment }}"</p>
                    @endif
                </div>
                <div x-data="{ loading: false }">
                    <a href="{{ route('customer.orders.invoice', $order->id) }}"
                       @click.prevent="
                           if (loading) return;
                           loading = true;
                           fetch($el.href)
                               .then(response => {
                                   if (!response.ok) throw new Error('Download failed');
                                   return response.blob();
                               })
                               .then(blob => {
                                   const url = window.URL.createObjectURL(blob);
                                   const a = document.createElement('a');
                                   a.href = url;
                                   a.download = 'Invoice-{{ $order->order_code }}.pdf';
                                   document.body.appendChild(a);
                                   a.click();
                                   a.remove();
                                   window.URL.revokeObjectURL(url);
                               })
                               .catch(err => alert('Failed to download invoice.'))
                               .finally(() => { loading = false; });
                       "
                       class="inline-flex items-center gap-1 bg-brand/5 hover:bg-brand/10 text-brand font-black text-xs px-4 py-2.5 rounded-xl transition-all whitespace-nowrap">
                        <span class="material-symbols-outlined text-[16px]" :class="loading ? 'animate-spin' : ''" x-text="loading ? 'sync' : 'download'">download</span>
                        <span x-text="loading ? 'Downloading...' : 'Download Invoice PDF'">Download Invoice PDF</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- Main Details Grid (2/3 & 1/3 layout) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Column left (2/3 width) -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Status & Payment checkout zone (ONLY show if Unpaid) -->
                @if($order->payment_status !== 'paid')
                    <div class="bg-white p-8 rounded-3xl shadow-md border border-gray-100 space-y-6 text-left">
                        <h3 class="text-lg font-black text-gray-900 border-b border-gray-100 pb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-yellow-500">payments</span>
                            Complete Your Payment
                        </h3>

                        @if($order->payment_method === 'stripe')
                            <!-- Stripe redirect notice -->
                            <div class="p-6 bg-blue-50 border border-blue-100 rounded-2xl space-y-4">
                                <p class="text-sm font-bold text-blue-900">Selected Payment Method: Stripe (Online/Card)</p>
                                <p class="text-xs text-blue-700">Use the button below to redirect to Stripe's secure payment portal.</p>
                                <div class="pt-2">
                                    <a href="{{ $order->stripe_session_id ? 'https://checkout.stripe.com/pay/' . $order->stripe_session_id : route('customer.orders.create') }}" target="_blank" class="inline-flex items-center gap-2 bg-brand hover:bg-blue-700 text-white font-extrabold text-xs px-6 py-3 rounded-xl shadow transition-all">
                                        <span class="material-symbols-outlined text-sm">credit_card</span> Pay via Stripe Now
                                    </a>
                                </div>
                            </div>
                        @elseif($order->payment_method === 'qris')
                            <!-- QRIS Mock Generator -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2">
                                        <div class="bg-gray-100 text-red-600 font-black px-3 py-1.5 rounded-xl text-xs tracking-tighter">
                                            QRIS
                                        </div>
                                        <span class="text-gray-300 font-light">✕</span>
                                        <div class="bg-brand text-white font-black px-3 py-1.5 rounded-xl text-xs uppercase tracking-wide">
                                            Stripe
                                        </div>
                                    </div>
                                    <h4 class="text-sm font-bold text-gray-800">Scan QRIS for Payment Simulation</h4>
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        Scan the QR code with your mobile banking or e-wallet to access Stripe's simulation page, or click the link below if you are currently using a desktop browser.
                                    </p>
                                    <div class="pt-2">
                                        <a href="{{ route('customer.payment.qris-simulation', $order->id) }}" class="inline-flex items-center gap-2 bg-brand hover:bg-blue-700 text-white font-extrabold text-xs px-5 py-3 rounded-xl shadow transition-all transform active:scale-95">
                                            <span class="material-symbols-outlined text-sm">open_in_new</span> Open Simulation Page
                                        </a>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-3xl border border-gray-100">
                                    <!-- Dynamic QR Code pointing to QRIS Simulation URL -->
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(route('customer.payment.qris-simulation', $order->id)) }}" alt="QRIS Code" class="w-48 h-48 object-contain bg-white p-2.5 rounded-2xl border border-gray-200 shadow-sm">
                                    <span class="text-[9px] font-bold text-gray-400 mt-3 tracking-wider uppercase">NMID: ID102030405060</span>
                                </div>
                            </div>
                        @else
                            <!-- Bank transfer instructions & upload receipt form -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Bank Accounts Details -->
                                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 space-y-4">
                                    <p class="text-xs font-black uppercase text-gray-400 tracking-wider font-jakarta">Target Bank Transfer Accounts</p>
                                    
                                    <div class="space-y-3">
                                        <div class="bg-white p-4 rounded-xl border border-gray-100">
                                            <p class="text-xs font-black text-gray-700">BANK BCA</p>
                                            <div class="flex items-center justify-between mt-1">
                                                <p class="text-sm font-mono font-bold text-brand" id="bca-acc">6010998877</p>
                                                <button onclick="navigator.clipboard.writeText('6010998877'); alert('BCA account number copied!');" class="text-[10px] font-bold text-gray-500 hover:text-brand flex items-center gap-0.5">
                                                    <span class="material-symbols-outlined text-xs">content_copy</span> Copy
                                                </button>
                                            </div>
                                            <p class="text-[10px] text-gray-400 mt-1">a/n CV Laundryan Nusantara</p>
                                        </div>

                                        <div class="bg-white p-4 rounded-xl border border-gray-100">
                                            <p class="text-xs font-black text-gray-700">BANK MANDIRI</p>
                                            <div class="flex items-center justify-between mt-1">
                                                <p class="text-sm font-mono font-bold text-brand" id="mandiri-acc">1420011223344</p>
                                                <button onclick="navigator.clipboard.writeText('1420011223344'); alert('Mandiri account number copied!');" class="text-[10px] font-bold text-gray-500 hover:text-brand flex items-center gap-0.5">
                                                    <span class="material-symbols-outlined text-xs">content_copy</span> Copy
                                                </button>
                                            </div>
                                            <p class="text-[10px] text-gray-400 mt-1">a/n CV Laundryan Nusantara</p>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-blue-50/50 p-4 rounded-xl text-[10px] text-blue-800 leading-relaxed">
                                        <strong>Transfer Amount:</strong> Must strictly match the total: <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                                    </div>
                                </div>

                                <!-- Receipt Upload Form -->
                                <div class="space-y-4">
                                    <p class="text-xs font-black uppercase text-gray-400 tracking-wider">Upload Transfer Receipt</p>
                                    
                                    @if($order->latestPayment && $order->latestPayment->proof_path)
                                        <!-- If already uploaded but pending verification -->
                                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 flex flex-col items-center justify-center text-center space-y-4">
                                            <span class="material-symbols-outlined text-yellow-500 text-4xl">pending_actions</span>
                                            <div>
                                                <p class="text-xs font-black text-gray-800">Payment Receipt Submitted</p>
                                                <p class="text-[10px] text-gray-500 mt-0.5">Currently verifying by system/admin. You can re-upload if file is incorrect.</p>
                                            </div>
                                            
                                            <!-- Preview thumbnail -->
                                            <div class="relative w-28 h-28 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                                <img src="{{ asset('storage/' . $order->latestPayment->proof_path) }}" class="w-full h-full object-cover">
                                            </div>
                                        </div>
                                    @endif

                                    <form action="{{ route('customer.payments.upload-proof', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                        @csrf
                                        <div class="border-2 border-dashed border-gray-200 hover:border-brand rounded-2xl p-6 transition-colors flex flex-col items-center justify-center text-center cursor-pointer relative bg-gray-50">
                                            <input type="file" name="proof_payment" id="proof_payment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required onchange="previewImage(this)">
                                            
                                            <div class="space-y-2 pointer-events-none" id="upload-prompt">
                                                <span class="material-symbols-outlined text-gray-400 text-3xl">upload_file</span>
                                                <p class="text-xs font-bold text-gray-800">Click / Drag Transfer Receipt</p>
                                                <p class="text-[9px] text-gray-400">Only image files (JPG, PNG) max 2MB</p>
                                            </div>

                                            <!-- Live preview block -->
                                            <div class="hidden space-y-2 pointer-events-none" id="upload-preview-container">
                                                <img id="upload-preview" src="#" alt="Preview" class="w-24 h-24 object-cover rounded-xl border border-gray-200 shadow-sm mx-auto">
                                                <p class="text-[10px] font-bold text-brand">Selected file</p>
                                            </div>
                                        </div>

                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-brand hover:bg-blue-700 text-white font-extrabold text-xs py-3 px-6 rounded-xl transition-all shadow-md">
                                            <span class="material-symbols-outlined text-sm">cloud_upload</span> Submit Transfer Receipt
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Live Tracking Map Section (Leaflet) -->
                <div class="bg-white p-6 rounded-3xl shadow-md border border-gray-100 text-left">
                    <h3 class="text-lg font-black text-gray-900 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand">distance</span>
                        Courier Live Tracking
                    </h3>
                    
                    <div id="tracking-map" class="h-96 rounded-2xl bg-gray-100 border border-gray-200 z-10 transition-shadow hover:shadow-inner">
                        <!-- Leaflet map -->
                    </div>
                </div>

                {{-- Full-width: Photo Documentation --}}
                @include('partials.order-photo-documentation', ['order' => $order])

                <!-- Staff Live Chat Area (Widen to match Photo Documentation grid width) -->
                <div class="bg-white p-6 rounded-3xl shadow-md border border-gray-100 flex flex-col h-[450px] overflow-hidden text-left">
                    <h3 class="text-base font-black text-gray-900 pb-4 border-b border-gray-50 flex items-center gap-2 shrink-0">
                        <span class="material-symbols-outlined text-brand">forum</span>
                        Staff Live Chat
                    </h3>
                    <div class="flex-1 overflow-y-auto space-y-3 my-4 pr-2 custom-scrollbar" id="chat-scroller">
                        @php
                            $chatRoleBubbleConfig = [
                                'admin'     => ['bg' => 'bg-blue-50 text-blue-900 border border-blue-200/65',   'badge' => 'text-blue-600 bg-blue-100/50',   'role_name' => 'Admin'],
                                'karyawan'  => ['bg' => 'bg-amber-50 text-amber-900 border border-amber-200/65', 'badge' => 'text-amber-600 bg-amber-100/50', 'role_name' => 'Staff'],
                                'kurir'     => ['bg' => 'bg-purple-50 text-purple-900 border border-purple-200/65','badge' => 'text-purple-600 bg-purple-100/50','role_name' => 'Courier'],
                                'pelanggan' => ['bg' => 'bg-emerald-50 text-emerald-900 border border-emerald-200/65','badge' => 'text-emerald-600 bg-emerald-100/50','role_name' => 'Customer'],
                            ];
                        @endphp
                        @forelse($order->messages as $msg)
                            @php
                                $isMine   = $msg->sender_id === auth()->id();
                                $msgRole  = strtolower($msg->sender->role ?? 'pelanggan');
                                $rCfg     = $chatRoleBubbleConfig[$msgRole] ?? $chatRoleBubbleConfig['pelanggan'];
                            @endphp
                            <div class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }} space-y-1">
                                <div class="max-w-[85%] px-4 py-3 rounded-2xl text-xs font-bold shadow-sm {{ $rCfg['bg'] }} {{ $isMine ? 'rounded-tr-none' : 'rounded-tl-none' }}">
                                    <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                                        <span class="text-[10px] font-black uppercase text-gray-800">{{ $msg->sender->name }}</span>
                                        <span class="text-[8px] font-black uppercase px-1.5 py-0.5 rounded {{ $rCfg['badge'] }} tracking-wider">
                                            {{ $rCfg['role_name'] }}
                                        </span>
                                    </div>
                                    <p class="leading-relaxed text-[11px] font-semibold">{{ $msg->message }}</p>
                                </div>
                                <span class="text-[8px] text-gray-400 uppercase font-bold px-1">{{ $msg->created_at->diffForHumans() }}</span>
                            </div>
                        @empty
                            <p class="text-center text-gray-400 py-8 italic text-xs">No messages yet. Ask anything about your order!</p>
                        @endforelse
                    </div>
                    <form action="{{ route('messages.store', $order->id) }}" method="POST" class="mt-auto relative shrink-0">
                        @csrf
                        <input type="text" name="message" class="w-full rounded-2xl border-gray-200 pr-12 focus:border-brand focus:ring-brand py-3 text-xs" placeholder="Type a message..." required autocomplete="off">
                        <button type="submit" class="absolute right-2 top-2 p-1.5 text-brand hover:text-blue-800 transition-colors">
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Column right: Sidebar details (1/3 width) -->
            <div class="space-y-8 text-left">
                <!-- Order Cost breakdown details -->
                <div class="bg-white p-8 rounded-3xl shadow-md border border-gray-100 space-y-6">
                    <h3 class="text-base font-black text-gray-900 border-b border-gray-100 pb-4">Order Summary</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400 font-medium">Service</span>
                            <span class="text-gray-900 font-extrabold text-right">{{ $order->service->name }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400 font-medium">Item Category</span>
                            <span class="text-gray-900 font-extrabold text-right">{{ $order->itemType->name }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400 font-medium">Soap Selection</span>
                            <span class="text-gray-900 font-extrabold text-right">{{ $order->soap ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400 font-medium">Fragrance Selection</span>
                            <span class="text-gray-900 font-extrabold text-right">{{ $order->fragrance ?? '-' }}</span>
                        </div>
                        <hr class="border-gray-50">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400 font-medium">Service Price</span>
                            <span class="text-gray-900 font-extrabold">Rp {{ number_format($order->service_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400 font-medium">Item Category Price</span>
                            <span class="text-gray-900 font-extrabold">Rp {{ number_format($order->item_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400 font-medium">Shipping Fee</span>
                            <span class="text-gray-900 font-extrabold">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400 font-medium">Tax (VAT 10%)</span>
                            <span class="text-gray-900 font-extrabold">Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-baseline pt-4 border-t border-gray-100">
                            <span class="text-sm font-black text-gray-900">Total Amount</span>
                            <span class="text-lg font-black text-brand">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </div>
                        @if($order->payment_status === 'paid')
                            <hr class="border-gray-50">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-400 font-medium">Payment Method</span>
                                <span class="text-gray-900 font-extrabold">
                                    @php
                                        $method = $order->payment_method ?: ($order->latestPayment?->payment_method);
                                        if ($method === 'qris') {
                                            $methodLabel = 'QRIS';
                                        } elseif ($method === 'stripe') {
                                            $methodLabel = 'Card / Online (Stripe)';
                                        } elseif (in_array($method, ['transfer', 'bank_transfer'])) {
                                            $methodLabel = 'Bank Transfer';
                                        } else {
                                            $methodLabel = $method ? ucwords(str_replace('_', ' ', $method)) : '-';
                                        }
                                    @endphp
                                    {{ $methodLabel }}
                                </span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-400 font-medium">Payment Time</span>
                                <span class="text-gray-900 font-extrabold text-right">
                                    @php
                                        $paymentDate = $order->latestPayment && $order->latestPayment->status === 'success'
                                            ? $order->latestPayment->payment_date
                                            : null;
                                        if (!$paymentDate && $order->updated_at) {
                                            $paymentDate = $order->updated_at;
                                        }
                                    @endphp
                                    {{ $paymentDate ? $paymentDate->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($order->payment_method === 'bank_transfer')
                    @php
                        $latestPayment = $order->payments->sortByDesc('created_at')->first();
                    @endphp
                    <div class="bg-white p-8 rounded-3xl shadow-md border border-gray-100 space-y-6">
                        <h3 class="text-base font-black text-gray-900 border-b border-gray-100 pb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand">receipt_long</span>
                            Payment Proof Receipt
                        </h3>
                        @if($latestPayment && $latestPayment->proof_path)
                            <div class="bg-gray-50/50 border border-gray-100 rounded-2xl p-4 space-y-3">
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
                    </div>
                @endif

                <!-- Courier Information -->
                @if($order->pickupCourier || $order->deliveryCourier)
                    <div class="bg-white p-8 rounded-3xl shadow-md border border-gray-100 space-y-6">
                        <h3 class="text-base font-black text-gray-900 border-b border-gray-100 pb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand">sports_motorsports</span>
                            Assigned Courier
                        </h3>
                        <div class="space-y-6">
                            @if($order->pickupCourier)
                                <div class="flex items-center gap-4">
                                    <img src="{{ $order->pickupCourier->photo ? asset('storage/' . $order->pickupCourier->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($order->pickupCourier->name) . '&background=EBF4FF&color=005bc0' }}" class="w-12 h-12 rounded-2xl object-cover border border-gray-100 shadow-sm">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-[9px] font-black text-amber-600 uppercase tracking-wider block">Pickup Courier</span>
                                        <p class="text-sm font-extrabold text-gray-900 truncate">{{ $order->pickupCourier->name }}</p>
                                        <p class="text-xs text-gray-500 font-medium">{{ $order->pickupCourier->phone ?? '-' }}</p>
                                    </div>
                                    <a href="tel:{{ $order->pickupCourier->phone }}" class="p-2.5 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors text-brand flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-sm">phone</span>
                                    </a>
                                </div>
                            @endif

                            @if($order->deliveryCourier)
                                <div class="flex items-center gap-4 pt-4 border-t border-gray-50">
                                    <img src="{{ $order->deliveryCourier->photo ? asset('storage/' . $order->deliveryCourier->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($order->deliveryCourier->name) . '&background=EBF4FF&color=005bc0' }}" class="w-12 h-12 rounded-2xl object-cover border border-gray-100 shadow-sm">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-[9px] font-black text-emerald-600 uppercase tracking-wider block">Delivery Courier</span>
                                        <p class="text-sm font-extrabold text-gray-900 truncate">{{ $order->deliveryCourier->name }}</p>
                                        <p class="text-xs text-gray-500 font-medium">{{ $order->deliveryCourier->phone ?? '-' }}</p>
                                    </div>
                                    <a href="tel:{{ $order->deliveryCourier->phone }}" class="p-2.5 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors text-brand flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-sm">phone</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="bg-white p-8 rounded-3xl shadow-md border border-gray-100 space-y-4">
                        <h3 class="text-base font-black text-gray-900 border-b border-gray-100 pb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-400">sports_motorsports</span>
                            Assigned Courier
                        </h3>
                        <p class="text-xs text-gray-400 italic">Awaiting courier assignment...</p>
                    </div>
                @endif

                <!-- Custom Note split rendering -->
                @php
                    $adminNote = '';
                    $employeeNote = '';
                    $courierNote = '';
                    if ($order->notes) {
                        $lines = explode("\n", $order->notes);
                        foreach($lines as $line) {
                            if (str_starts_with($line, 'Catatan Admin:')) {
                                $adminNote = trim(str_replace('Catatan Admin:', '', $line));
                            } elseif (str_starts_with($line, 'Catatan Karyawan:')) {
                                $employeeNote = trim(str_replace('Catatan Karyawan:', '', $line));
                            } elseif (str_starts_with($line, 'Catatan Kurir:')) {
                                $courierNote = trim(str_replace('Catatan Kurir:', '', $line));
                            }
                        }
                    }
                @endphp
                @if($adminNote || $employeeNote || $courierNote)
                    <div class="bg-white p-8 rounded-3xl shadow-md border border-gray-100 space-y-6">
                        <h3 class="text-base font-black text-gray-900 border-b border-gray-100 pb-4">Special Notes</h3>
                        <div class="space-y-4">
                            @if($adminNote)
                                <div class="bg-gray-50 p-4 rounded-xl">
                                    <span class="text-[9px] font-black text-brand uppercase tracking-wider block mb-1">Admin Notes</span>
                                    <p class="text-xs text-gray-700 leading-relaxed font-semibold">{{ $adminNote }}</p>
                                </div>
                            @endif
                            @if($employeeNote)
                                <div class="bg-gray-50 p-4 rounded-xl">
                                    <span class="text-[9px] font-black text-emerald-600 uppercase tracking-wider block mb-1">Staff Notes</span>
                                    <p class="text-xs text-gray-700 leading-relaxed font-semibold">{{ $employeeNote }}</p>
                                </div>
                            @endif
                            @if($courierNote)
                                <div class="bg-gray-50 p-4 rounded-xl">
                                    <span class="text-[9px] font-black text-yellow-600 uppercase tracking-wider block mb-1">Courier Notes</span>
                                    <p class="text-xs text-gray-700 leading-relaxed font-semibold">{{ $courierNote }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Addresses Details -->
                <div class="bg-white p-8 rounded-3xl shadow-md border border-gray-100 space-y-6">
                    <h3 class="text-base font-black text-gray-900 border-b border-gray-100 pb-4">Address Details</h3>
                    <div class="space-y-4 text-xs">
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block mb-1 font-jakarta">Pickup Address</span>
                            <p class="font-bold text-gray-700 leading-relaxed">{{ $order->pickup_address }}</p>
                        </div>
                        <div class="pt-3 border-t border-gray-50">
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block mb-1 font-jakarta">Delivery Address</span>
                            <p class="font-bold text-gray-700 leading-relaxed">{{ $order->delivery_address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Status Logs (Relocated below Address Details) -->
                <div class="bg-white p-8 rounded-3xl shadow-md border border-gray-100 text-left">
                    <h3 class="text-base font-black text-gray-900 border-b border-gray-100 pb-4">Status Logs</h3>
                    <div class="pl-6 border-l-2 border-gray-100 space-y-6 my-4 pr-2 custom-scrollbar">
                        @forelse($order->statusLogs as $log)
                            <div class="relative">
                                <!-- Dot indicator on line -->
                                <span class="absolute -left-[29px] top-1.5 w-3 h-3 rounded-full bg-brand ring-4 ring-blue-50"></span>
                                <p class="text-xs font-black text-gray-800">
                                    {{ str_replace('_', ' ', ucfirst($log->status)) }}
                                </p>
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                </p>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">No status log updates yet.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll chat to bottom
            const scroller = document.getElementById('chat-scroller');
            if (scroller) scroller.scrollTop = scroller.scrollHeight;

            const orderId = {{ $order->id }};
            const currentUserId = {{ auth()->id() }};

            // Let's define locations
            const laundryLat = -6.1664983;
            const laundryLng = 106.5602886;

            let pickupLat = {{ $order->pickup_lat ?? -6.2000 }};
            let pickupLng = {{ $order->pickup_lng ?? 106.8166 }};
            let deliveryLat = {{ $order->delivery_lat ?? -6.2000 }};
            let deliveryLng = {{ $order->delivery_lng ?? 106.8166 }};

            const orderStatus = '{{ $order->status }}';

            const isPickupFlow = ['waiting_pickup', 'picking_up', 'penjemputan'].includes(orderStatus);
            const isTransitFlow = ['picked_up', 'dijemput', 'in_transit_to_laundry', 'diantar'].includes(orderStatus);
            const isDeliveryFlow = ['delivering', 'pengantaran'].includes(orderStatus);

            // Determine default map center
            let centerLat = isPickupFlow ? pickupLat : (isDeliveryFlow ? deliveryLat : laundryLat);
            let centerLng = isPickupFlow ? pickupLng : (isDeliveryFlow ? deliveryLng : laundryLng);

            var map = L.map('tracking-map', { 
                zoomControl: false, 
                attributionControl: false 
            }).setView([centerLat, centerLng], 14);

            L.control.zoom({ position: 'bottomleft' }).addTo(map);

            const standardMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                subdomains: 'abcd', maxZoom: 20
            });
            const darkMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                subdomains: 'abcd', maxZoom: 20
            });
            const satelliteMap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19
            });

            standardMap.addTo(map);

            L.control.layers({ 
                "Standard": standardMap, 
                "Dark Mode": darkMap, 
                "Satellite": satelliteMap 
            }, null, { position: 'bottomleft' }).addTo(map);

            // Custom markers definitions from Admin Theme
            const customerName = '{{ $order->customer->name ?? "Customer" }}';
            const customerPhoto = '{{ ($order->customer && $order->customer->photo) ? asset("storage/" . $order->customer->photo) : "" }}' || `https://ui-avatars.com/api/?name=${encodeURIComponent(customerName)}&color=005bc0&background=EBF4FF`;
            
            const customerIcon = L.divIcon({
                html: `
                <div class="relative h-12 w-12 flex items-center justify-center">
                    <!-- Customer Photo Frame -->
                    <div class="bg-white p-0.5 h-11 w-11 rounded-full border-4 border-${isPickupFlow ? 'amber' : 'emerald'}-500 flex items-center justify-center shadow-2xl animate-pulse overflow-hidden z-10">
                        <img src="${customerPhoto}" class="w-full h-full rounded-full object-cover">
                    </div>
                    
                    <!-- Label -->
                    <div class="absolute -bottom-2 bg-${isPickupFlow ? 'amber' : 'emerald'}-500 text-white text-[7px] px-2 py-0.5 rounded-full font-black uppercase tracking-tighter shadow-md z-30">
                        TARGET
                    </div>
                </div>`,
                className: '', iconSize: [48, 48], iconAnchor: [24, 48], popupAnchor: [0, -48]
            });

            const courierName = '{{ $order->courier->name ?? "Courier" }}';
            const courierPhoto = '{{ ($order->courier && $order->courier->photo) ? asset("storage/" . $order->courier->photo) : "" }}' || `https://ui-avatars.com/api/?name=${encodeURIComponent(courierName)}&color=005bc0&background=EBF4FF`;
            const activeType = isPickupFlow ? 'pickup' : (isDeliveryFlow ? 'delivery' : 'idle');
            const hasOrder = activeType === 'pickup' || activeType === 'delivery';
            
            const courierIcon = L.divIcon({
                html: `
                <div class="courier-marker ${activeType} overflow-visible">
                    <div class="w-full h-full rounded-full overflow-hidden border-2 border-white shadow-inner bg-white">
                        <img src="${courierPhoto}" class="w-full h-full object-cover" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(courierName)}&color=005bc0&background=EBF4FF'">
                    </div>
                    ${hasOrder ? '<div class="absolute -top-1 -right-1 bg-white text-blue-600 rounded-full h-5 w-5 flex items-center justify-center shadow-lg border border-blue-50 animate-bounce z-10"><span class="material-symbols-outlined text-[12px] font-black">inventory_2</span></div>' : ''}
                </div>`,
                className: '', iconSize: [44, 44], iconAnchor: [22, 44], popupAnchor: [0, -44]
            });

            const laundryIcon = L.divIcon({
                html: `<div class="bg-blue-900 text-white h-12 w-12 rounded-full flex items-center justify-center shadow-2xl border-4 border-white animate-pulse"><span class="material-symbols-outlined text-2xl">local_laundry_service</span></div>`,
                className: '', iconSize: [48, 48], iconAnchor: [24, 24], popupAnchor: [0, -24]
            });

            // Put static markers on map based on status
            var customerMarker = null;
            var laundryMarker = null;
            var courierMarker = null;
            var routingControl = null;

            if (isPickupFlow) {
                // Show customer marker (blue/amber) at pickup location
                customerMarker = L.marker([pickupLat, pickupLng], { icon: customerIcon })
                    .addTo(map)
                    .bindPopup('Pickup Location (Your Home)')
                    .openPopup();
            } else if (isTransitFlow) {
                // Show laundry marker and customer marker
                laundryMarker = L.marker([laundryLat, laundryLng], { icon: laundryIcon })
                    .addTo(map)
                    .bindPopup('<div class="p-2 font-black text-center text-blue-900">LAUNDRYAN HQ<br><span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Base Operations</span></div>')
                    .openPopup();

                customerMarker = L.marker([pickupLat, pickupLng], { icon: customerIcon })
                    .addTo(map)
                    .bindPopup('Pickup Location (Your Home)');
            } else if (isDeliveryFlow) {
                // Show laundry marker and customer marker at delivery location
                laundryMarker = L.marker([laundryLat, laundryLng], { icon: laundryIcon })
                    .addTo(map)
                    .bindPopup('<div class="p-2 font-black text-center text-blue-900">LAUNDRYAN HQ<br><span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Base Operations</span></div>')
                    .openPopup();

                customerMarker = L.marker([deliveryLat, deliveryLng], { icon: customerIcon })
                    .addTo(map)
                    .bindPopup('Delivery Location (Your Home)');
            } else {
                // Fallback: just show customer delivery location
                customerMarker = L.marker([deliveryLat, deliveryLng], { icon: customerIcon })
                    .addTo(map)
                    .bindPopup('Your Home')
                    .openPopup();
            }

            function updateRoute(courierLat, courierLng) {
                let waypoints = [];
                let color = '#005bc0'; // Default brand color

                if (isPickupFlow && courierLat && courierLng) {
                    // Route from courier to customer home
                    waypoints = [
                        L.latLng(courierLat, courierLng),
                        L.latLng(pickupLat, pickupLng)
                    ];
                    color = '#f59e0b'; // Amber for pickup
                } else if (isTransitFlow && courierLat && courierLng) {
                    // Route from courier to laundry HQ
                    waypoints = [
                        L.latLng(courierLat, courierLng),
                        L.latLng(laundryLat, laundryLng)
                    ];
                    color = '#8b5cf6'; // Purple for transit
                } else if (isDeliveryFlow) {
                    // Route from laundry HQ to customer home (always displayed, even without courier coordinates)
                    waypoints = [
                        L.latLng(laundryLat, laundryLng),
                        L.latLng(deliveryLat, deliveryLng)
                    ];
                    color = '#10b981'; // Green for delivery
                }

                if (waypoints.length >= 2) {
                    if (!routingControl) {
                        routingControl = L.Routing.control({
                            waypoints: waypoints,
                            createMarker: function() { return null; }, // We handle markers ourselves
                            routeWhileDragging: false,
                            show: false,
                            fitSelectedRoutes: false,
                            lineOptions: {
                                styles: [{ color: color, opacity: 0.8, weight: 6 }]
                            }
                        }).addTo(map);
                    } else {
                        // Dynamically update waypoints
                        routingControl.setWaypoints(waypoints);
                    }
                }
            }

            function setCourierPosition(lat, lng) {
                const newLatLng = new L.LatLng(lat, lng);
                if (!courierMarker) {
                    courierMarker = L.marker(newLatLng, { icon: courierIcon })
                        .addTo(map)
                        .bindPopup('Courier is here')
                        .openPopup();
                } else {
                    courierMarker.setLatLng(newLatLng);
                }

                // Update the routing path based on current courier position
                updateRoute(lat, lng);

                // Fit bounds to show all active markers
                const activeMarkers = [];
                if (customerMarker) activeMarkers.push(customerMarker);
                if (laundryMarker) activeMarkers.push(laundryMarker);
                if (courierMarker) activeMarkers.push(courierMarker);
                
                if (activeMarkers.length > 0) {
                    const group = new L.featureGroup(activeMarkers);
                    map.fitBounds(group.getBounds().pad(0.15));
                }
            }

            function setCustomerPosition(lat, lng) {
                if (customerMarker) {
                    customerMarker.setLatLng(new L.LatLng(lat, lng));
                }
                if (isPickupFlow) {
                    pickupLat = lat;
                    pickupLng = lng;
                } else if (isDeliveryFlow) {
                    deliveryLat = lat;
                    deliveryLng = lng;
                }
                
                // Refresh route with new customer location
                if (courierMarker) {
                    updateRoute(courierMarker.getLatLng().lat, courierMarker.getLatLng().lng);
                } else if (isDeliveryFlow) {
                    updateRoute();
                }
            }

            // Set initial courier position if loaded
            @if($latestLocation)
                setCourierPosition({{ $latestLocation->latitude }}, {{ $latestLocation->longitude }});
            @else
                if (isDeliveryFlow) {
                    updateRoute();
                }
            @endif

            // Watch customer's real position and report to server
            const activeTrackingStatuses = [
                'penjemputan', 'dijemput', 'diantar', 'sampai',
                'pengantaran', 'diantarkan', 'delivering', 'picking_up'
            ];

            if (activeTrackingStatuses.includes(orderStatus) && navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (pos) => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        setCustomerPosition(lat, lng);

                        // Send to server
                        fetch('{{ route("customer.location.update") }}', {
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
                    (err) => console.log('Customer Geolocation watch error:', err),
                    { enableHighAccuracy: true, maximumAge: 10000 }
                );
            }

            // Echo Channel private order
            if (window.Echo) {
                window.Echo.private(`order.${orderId}`)
                    .listen('LocationUpdated', (e) => {
                        const sender = e.location.user;
                        if (sender && sender.role === 'kurir') {
                            setCourierPosition(e.location.latitude, e.location.longitude);
                        } else if (sender && sender.role === 'pelanggan' && sender.id !== currentUserId) {
                            setCustomerPosition(e.location.latitude, e.location.longitude);
                        }
                    })
                    .listen('OrderStatusUpdated', (e) => {
                        window.location.reload();
                    })
                    .listen('MessageSent', (e) => {
                        appendMessage(e.message);
                    });
            }

            // AJAX Polling Fallback (every 8 seconds)
            function pollLocations() {
                fetch(`/orders/${orderId}/locations`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.courier) {
                            setCourierPosition(data.courier.latitude, data.courier.longitude);
                        }
                        if (data.customer) {
                            setCustomerPosition(data.customer.latitude, data.customer.longitude);
                        }
                    })
                    .catch(err => console.log('Polling error:', err));
            }
            if (activeTrackingStatuses.includes(orderStatus) || '{{ $latestLocation }}') {
                setInterval(pollLocations, 8000);
            }

            function appendMessage(msg) {
                const chatContainer = document.getElementById('chat-scroller');
                const isMine = msg.sender_id === currentUserId;
                
                let roleColors = 'bg-blue-100 text-blue-700 border-blue-200';
                if (msg.sender.role === 'admin') {
                    roleColors = 'bg-rose-100 text-rose-700 border-rose-200';
                } else if (msg.sender.role === 'karyawan') {
                    roleColors = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                } else if (msg.sender.role === 'kurir') {
                    roleColors = 'bg-amber-100 text-amber-700 border-amber-200';
                }

                const msgHtml = `
                    <div class="flex flex-col ${isMine ? 'items-end' : 'items-start'} animate-fade-in">
                        <div class="max-w-[85%] rounded-2xl p-3.5 ${isMine ? 'bg-brand text-white rounded-tr-none' : 'bg-gray-100 text-gray-800 rounded-tl-none'}">
                            <p class="text-[9px] mb-1 font-black flex items-center gap-1">
                                <span class="font-extrabold ${isMine ? 'text-white' : 'text-gray-900'}">${msg.sender.name}</span>
                                <span class="px-1.5 py-0.5 rounded-full border text-[7px] uppercase tracking-wider ${roleColors}">
                                    ${msg.sender.role}
                                </span>
                            </p>
                            <p class="text-xs font-bold leading-normal">${msg.message}</p>
                        </div>
                        <span class="text-[8px] text-gray-400 mt-1 uppercase font-bold">Just now</span>
                    </div>
                `;
                
                chatContainer.insertAdjacentHTML('beforeend', msgHtml);
                chatContainer.scrollTop = chatContainer.scrollHeight;

                const emptyMsg = chatContainer.querySelector('p.italic');
                if (emptyMsg) emptyMsg.remove();
            }
        });

        // Preview local receipt file selection before upload
        // Preview local receipt file selection before upload
        function previewImage(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('upload-prompt').classList.add('hidden');
                    document.getElementById('upload-preview').src = e.target.result;
                    document.getElementById('upload-preview-container').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        .leaflet-routing-container { display: none !important; }

        /* Courier Markers from Admin Theme */
        .courier-marker {
            width: 44px !important;
            height: 44px !important;
            background: #3b82f6;
            border: 3px solid #fff;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 14px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .courier-marker > div {
            transform: rotate(45deg);
            text-transform: uppercase;
        }
        .courier-marker.pickup { background: #F59E0B !important; }
        .courier-marker.delivery { background: #10B981 !important; }
        .courier-marker.idle { background: #3B82F6 !important; }
        .courier-marker.offline { background: #64748B !important; filter: grayscale(0.5); }
        .courier-marker:hover { transform: rotate(-45deg) scale(1.1); z-index: 1001 !important; }
    </style>
</x-app-layout>
