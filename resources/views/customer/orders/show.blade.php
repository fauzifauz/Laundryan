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

    <div class="py-2 space-y-8">
        <!-- Review Block (If completed and no review yet) -->
        @if($order->status === 'completed' && !$order->review)
            <div class="bg-gradient-to-r from-brand via-blue-600 to-blue-700 rounded-3xl p-8 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h3 class="text-2xl font-black mb-1 leading-tight font-jakarta">Laundry Delivered!</h3>
                    <p class="opacity-80 text-sm">How was your experience using Laundryan?</p>
                </div>
                <form action="{{ route('customer.reviews.store', $order->id) }}" method="POST" class="w-full md:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    @csrf
                    <select name="rating" class="rounded-xl border-none text-blue-900 font-extrabold px-4 py-3 text-xs bg-white focus:ring-2 focus:ring-brand" required>
                        <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                        <option value="4">⭐⭐⭐⭐ Good</option>
                        <option value="3">⭐⭐⭐ Neutral</option>
                        <option value="2">⭐⭐ Bad</option>
                        <option value="1">⭐ Terrible</option>
                    </select>
                    <input type="text" name="comment" placeholder="Write your feedback..." class="rounded-xl border-none text-blue-900 px-4 py-3 text-xs bg-white focus:ring-2 focus:ring-brand min-w-[200px]" required>
                    <button type="submit" class="bg-white text-brand font-black text-xs px-6 py-3 rounded-xl hover:bg-blue-50 transition-all shadow-lg active:scale-95 uppercase tracking-wider">
                        Submit Review
                    </button>
                </form>
            </div>
        @endif

        @if($order->review)
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-left">
                <div>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Your Review</span>
                    <div class="flex items-center text-yellow-400 mb-1">
                        @for($i=0; $i<$order->review->rating; $i++)
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endfor
                    </div>
                    <p class="text-gray-700 font-bold text-xs italic">"{{ $order->review->comment }}"</p>
                </div>
                <div>
                    <a href="{{ route('customer.orders.invoice', $order->id) }}" class="inline-flex items-center gap-1 bg-brand/5 hover:bg-brand/10 text-brand font-black text-xs px-4 py-2.5 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        Download Invoice PDF
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

                <!-- Service Progress Photos (Uploaded by Courier/Employee) -->
                <div class="bg-white p-8 rounded-3xl shadow-md border border-gray-100 text-left">
                    <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand">photo_library</span>
                        Laundry Progress Photo Documentation
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @forelse($order->photos as $photo)
                            <div class="group relative rounded-2xl overflow-hidden shadow-sm aspect-square bg-gray-50 border border-gray-100">
                                <img src="{{ asset('storage/' . $photo->photo_path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-[9px] font-black tracking-wider p-2.5 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                    {{ str_replace('_', ' ', strtoupper($photo->context)) }}
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center text-gray-400 py-10 italic text-xs">
                                No progress photo documentation uploaded by staff yet.
                            </div>
                        @endforelse
                    </div>
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
                    </div>
                </div>

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

                <!-- Vertical tracking logs timeline -->
                <div class="bg-white p-8 rounded-3xl shadow-md border border-gray-100 space-y-6">
                    <h3 class="text-base font-black text-gray-900 border-b border-gray-100 pb-4">Status Logs</h3>
                    <div class="relative pl-6 border-l-2 border-gray-100 space-y-6">
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

                <!-- Live Chat Area -->
                <div class="bg-white p-6 rounded-3xl shadow-md border border-gray-100 flex flex-col h-[400px]">
                    <h3 class="text-base font-black text-gray-900 pb-4 border-b border-gray-50 flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand">forum</span>
                        Staff Live Chat
                    </h3>
                    <div class="flex-1 overflow-y-auto space-y-3 my-4 pr-2 custom-scrollbar" id="chat-scroller">
                        @forelse($order->messages as $msg)
                            <div class="flex flex-col {{ $msg->sender_id === auth()->id() ? 'items-end' : 'items-start' }}">
                                <div class="max-w-[85%] rounded-2xl p-3.5 {{ $msg->sender_id === auth()->id() ? 'bg-brand text-white rounded-tr-none' : 'bg-gray-100 text-gray-800 rounded-tl-none' }}">
                                    <p class="text-[9px] opacity-70 mb-0.5 font-bold">{{ $msg->sender->name }} ({{ ucfirst($msg->sender->role) }})</p>
                                    <p class="text-xs font-bold leading-normal">{{ $msg->message }}</p>
                                </div>
                                <span class="text-[8px] text-gray-400 mt-1 uppercase font-bold">{{ $msg->created_at->diffForHumans() }}</span>
                            </div>
                        @empty
                            <p class="text-center text-gray-400 py-8 italic text-xs">No messages yet. Ask anything about your order!</p>
                        @endforelse
                    </div>
                    <form action="{{ route('messages.store', $order->id) }}" method="POST" class="mt-auto relative">
                        @csrf
                        <input type="text" name="message" class="w-full rounded-2xl border-gray-200 pr-12 focus:border-brand focus:ring-brand py-3 text-xs" placeholder="Type a message..." required autocomplete="off">
                        <button type="submit" class="absolute right-2 top-2 p-1.5 text-brand hover:text-blue-800 transition-colors">
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll chat to bottom
            const scroller = document.getElementById('chat-scroller');
            if (scroller) scroller.scrollTop = scroller.scrollHeight;

            const orderId = {{ $order->id }};
            const currentUserId = {{ auth()->id() }};

            // Initialize map coordinates (fallback to Surabaya/Jakarta center)
            var lat = {{ $latestLocation->latitude ?? -6.2000 }};
            var lng = {{ $latestLocation->longitude ?? 106.8166 }};
            
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

            // Real-time Echo Listeners (Pusher wrapper)
            if (window.Echo) {
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
                        window.location.reload();
                    })
                    .listen('MessageSent', (e) => {
                        appendMessage(e.message);
                    });
            }

            function appendMessage(msg) {
                const chatContainer = document.getElementById('chat-scroller');
                const isMine = msg.sender_id === currentUserId;
                
                const msgHtml = `
                    <div class="flex flex-col ${isMine ? 'items-end' : 'items-start'} animate-fade-in">
                        <div class="max-w-[85%] rounded-2xl p-3.5 ${isMine ? 'bg-brand text-white rounded-tr-none' : 'bg-gray-100 text-gray-800 rounded-tl-none'}">
                            <p class="text-[9px] opacity-70 mb-0.5 font-bold">${msg.sender.name} (${msg.sender.role})</p>
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
    </style>
</x-app-layout>
