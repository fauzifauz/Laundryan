<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="p-2 hover:bg-gray-100 rounded-xl transition-colors text-gray-500">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-gray-900 tracking-tight">
                    {{ __('Book a New Laundry') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Fill in the order form to schedule your laundry pickup.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-2">
        <form action="{{ route('customer.orders.store') }}" method="POST" id="order-form" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf
            
            <!-- Left & Middle: Form inputs (2/3 width) -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Step 1: Layanan & Kategori -->
                <div class="bg-white rounded-3xl p-8 shadow-md border border-gray-100 space-y-6">
                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-4">
                        <span class="material-symbols-outlined text-brand">layers</span>
                        1. Select Service & Category
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="service_id" class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-2">Service</label>
                            <select name="service_id" id="service_id" class="block w-full rounded-2xl border-gray-200 shadow-sm focus:border-brand focus:ring-brand text-sm py-3 px-4 transition-all" required>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" data-price="{{ $service->base_price }}">{{ $service->name }} (Rp {{ number_format($service->base_price, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="item_type_id" class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-2">Item Category</label>
                            <select name="item_type_id" id="item_type_id" class="block w-full rounded-2xl border-gray-200 shadow-sm focus:border-brand focus:ring-brand text-sm py-3 px-4 transition-all" required>
                                @foreach($itemTypes as $type)
                                    <option value="{{ $type->id }}" data-price="{{ $type->base_price }}">{{ $type->name }} (Rp {{ number_format($type->base_price, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Kustomisasi Detergen & Wangi -->
                <div class="bg-white rounded-3xl p-8 shadow-md border border-gray-100 space-y-6">
                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-4">
                        <span class="material-symbols-outlined text-brand">bubble_chart</span>
                        2. Soap & Fragrance Options
                    </h3>

                    <!-- Soap Selection -->
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-3">Soap/Detergent Option</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @foreach(['Attack Hygiene', 'Rinso Matic', 'So Klin Liquid'] as $soapOption)
                                <label class="relative flex flex-col p-4 bg-gray-50 border-2 border-gray-200 rounded-2xl cursor-pointer hover:bg-gray-100 transition-all text-left">
                                    <input type="radio" name="soap" value="{{ $soapOption }}" class="sr-only peer" {{ $loop->first ? 'checked' : '' }} required>
                                    <span class="peer-checked:border-brand absolute inset-0 border-2 border-transparent rounded-2xl pointer-events-none transition-all"></span>
                                    
                                    <span class="text-sm font-extrabold text-gray-800">{{ $soapOption }}</span>
                                    <span class="text-[10px] text-gray-400 mt-1 font-medium font-jakarta">Standard Service</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Fragrance Selection -->
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-3">Fragrance Option</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @foreach(['Downy Mystique', 'Downy Passion', 'Molen Blue'] as $scentOption)
                                <label class="relative flex flex-col p-4 bg-gray-50 border-2 border-gray-200 rounded-2xl cursor-pointer hover:bg-gray-100 transition-all text-left">
                                    <input type="radio" name="fragrance" value="{{ $scentOption }}" class="sr-only peer" {{ $loop->first ? 'checked' : '' }} required>
                                    <span class="peer-checked:border-brand absolute inset-0 border-2 border-transparent rounded-2xl pointer-events-none transition-all"></span>
                                    
                                    <span class="text-sm font-extrabold text-gray-800">{{ $scentOption }}</span>
                                    <span class="text-[10px] text-gray-400 mt-1 font-medium font-jakarta">Standard Service</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Step 3: Jadwal & Alamat -->
                <div class="bg-white rounded-3xl p-8 shadow-md border border-gray-100 space-y-6">
                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-4">
                        <span class="material-symbols-outlined text-brand">schedule</span>
                        3. Pickup Schedule & Addresses
                    </h3>

                    <div>
                        <label for="pickup_time" class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-2">Pickup Schedule</label>
                        <input type="datetime-local" name="pickup_time" id="pickup_time" min="{{ now()->addHour()->format('Y-m-d\TH:i') }}" class="block w-full rounded-2xl border-gray-200 shadow-sm focus:border-brand focus:ring-brand text-sm py-3 px-4 transition-all" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label for="pickup_address" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Pickup Address</label>
                                <button type="button" onclick="getCurrentLocation('pickup_address')" class="text-[10px] font-bold text-brand hover:underline flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-xs">my_location</span> Use Current Location
                                </button>
                            </div>
                            <textarea name="pickup_address" id="pickup_address" rows="3" class="block w-full rounded-2xl border-gray-200 shadow-sm focus:border-brand focus:ring-brand text-sm p-4 transition-all" placeholder="Enter full pickup address..." required>{{ old('pickup_address', auth()->user()->address) }}</textarea>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label for="delivery_address" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Delivery Address</label>
                                <button type="button" onclick="getCurrentLocation('delivery_address')" class="text-[10px] font-bold text-brand hover:underline flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-xs">my_location</span> Use Current Location
                                </button>
                            </div>
                            <textarea name="delivery_address" id="delivery_address" rows="3" class="block w-full rounded-2xl border-gray-200 shadow-sm focus:border-brand focus:ring-brand text-sm p-4 transition-all" placeholder="Enter full delivery address..." required>{{ old('delivery_address', auth()->user()->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Catatan Khusus -->
                <div class="bg-white rounded-3xl p-8 shadow-md border border-gray-100 space-y-6">
                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-4">
                        <span class="material-symbols-outlined text-brand">edit_note</span>
                        4. Special Notes (Optional)
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="notes_admin" class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-2">For Admin</label>
                            <textarea name="notes_admin" id="notes_admin" rows="2" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand focus:ring-brand text-xs p-3 transition-all" placeholder="Invoicing instructions, discount details, etc..."></textarea>
                        </div>
                        <div>
                            <label for="notes_employee" class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-2">For Washer Staff</label>
                            <textarea name="notes_employee" id="notes_employee" rows="2" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand focus:ring-brand text-xs p-3 transition-all" placeholder="Color bleeding clothes, thick stains on collar, do not hot iron..."></textarea>
                        </div>
                        <div>
                            <label for="notes_courier" class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-2">For Courier</label>
                            <textarea name="notes_courier" id="notes_courier" rows="2" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand focus:ring-brand text-xs p-3 transition-all" placeholder="Leave clothes on front porch, leave with neighbor if out..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Cost Summary & Payment Checkout Card (1/3 width) -->
            <div class="space-y-8">
                <!-- Cost Summary Details Card -->
                <div class="bg-white rounded-3xl p-8 shadow-md border border-gray-100 space-y-6 sticky top-6">
                    <h3 class="text-lg font-black text-gray-900 border-b border-gray-100 pb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand">receipt_long</span>
                        Cost Summary
                    </h3>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Service Price:</span>
                            <span id="display-service-price" class="font-bold text-gray-950">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Item Category:</span>
                            <span id="display-item-price" class="font-bold text-gray-950">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Delivery Fee:</span>
                            <span id="display-shipping-price" class="font-bold text-gray-950">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span id="display-tax-label">Tax (VAT):</span>
                            <span id="display-tax-price" class="font-bold text-gray-950">Rp 0</span>
                        </div>
                        <hr class="border-gray-100 my-2">
                        <div class="flex justify-between items-baseline pt-2">
                            <span class="text-base font-black text-gray-900">Total Price:</span>
                            <span id="display-total-price" class="text-2xl font-black text-brand">Rp 0</span>
                        </div>
                    </div>

                    <!-- Payment Method Custom Selection -->
                    <div class="pt-4 border-t border-gray-100 space-y-3">
                        <label class="block text-xs font-black uppercase text-gray-400 tracking-wider">Payment Method</label>
                        
                        <div class="space-y-3 text-left">
                            <label class="relative flex items-center p-4 bg-gray-50 border-2 border-gray-200 rounded-2xl cursor-pointer hover:bg-gray-100 transition-all">
                                <input type="radio" name="payment_method" value="bank_transfer" class="sr-only peer" checked required>
                                <span class="peer-checked:border-brand absolute inset-0 border-2 border-transparent rounded-2xl pointer-events-none transition-all"></span>
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-gray-500">account_balance</span>
                                    <div>
                                        <p class="text-xs font-extrabold text-gray-800">Manual Bank Transfer</p>
                                        <p class="text-[9px] text-gray-400 mt-0.5">Upload your transfer receipt</p>
                                    </div>
                                </div>
                            </label>

                            <label class="relative flex items-center p-4 bg-gray-50 border-2 border-gray-200 rounded-2xl cursor-pointer hover:bg-gray-100 transition-all">
                                <input type="radio" name="payment_method" value="stripe" class="sr-only peer" required>
                                <span class="peer-checked:border-brand absolute inset-0 border-2 border-transparent rounded-2xl pointer-events-none transition-all"></span>
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-gray-500">credit_card</span>
                                    <div>
                                        <p class="text-xs font-extrabold text-gray-800">Stripe (Card/Online)</p>
                                        <p class="text-[9px] text-gray-400 mt-0.5">Instant online checkout</p>
                                    </div>
                                </div>
                            </label>

                            <label class="relative flex items-center p-4 bg-gray-50 border-2 border-gray-200 rounded-2xl cursor-pointer hover:bg-gray-100 transition-all">
                                <input type="radio" name="payment_method" value="qris" class="sr-only peer" required>
                                <span class="peer-checked:border-brand absolute inset-0 border-2 border-transparent rounded-2xl pointer-events-none transition-all"></span>
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-gray-500">qr_code_2</span>
                                    <div>
                                        <p class="text-xs font-extrabold text-gray-800">QRIS (Stripe Simulation)</p>
                                        <p class="text-[9px] text-gray-400 mt-0.5">Scan QR code for instant payment</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-4 bg-brand hover:bg-blue-700 text-white font-extrabold text-sm uppercase tracking-wider rounded-2xl transition-all shadow-[0_10px_20px_rgba(0,91,192,0.15)] hover:shadow-brand-300 transform active:scale-95">
                            <span class="material-symbols-outlined text-[18px]">shopping_cart_checkout</span>
                            Process Booking
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serviceSelect = document.getElementById('service_id');
            const itemSelect = document.getElementById('item_type_id');
            const pickupAddress = document.getElementById('pickup_address');
            const deliveryAddress = document.getElementById('delivery_address');
            
            const displayService = document.getElementById('display-service-price');
            const displayItem = document.getElementById('display-item-price');
            const displayShipping = document.getElementById('display-shipping-price');
            const displayTaxLabel = document.getElementById('display-tax-label');
            const displayTaxPrice = document.getElementById('display-tax-price');
            const displayTotal = document.getElementById('display-total-price');
            const csrfToken = document.querySelector('input[name="_token"]').value;

            let debounceTimeout;

            function recalculate() {
                const service_id = serviceSelect.value;
                const item_type_id = itemSelect.value;
                const pickup_address = pickupAddress.value.trim();
                const delivery_address = deliveryAddress.value.trim();

                if (!service_id || !item_type_id || !pickup_address || !delivery_address) {
                    return;
                }

                // Show visual calculation indicator
                displayTotal.textContent = 'Calculating...';

                fetch('{{ route('customer.orders.calculate-price') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        service_id,
                        item_type_id,
                        pickup_address,
                        delivery_address
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch pricing');
                    return response.json();
                })
                .then(data => {
                    displayService.textContent = 'Rp ' + Math.round(data.service_price).toLocaleString('id-ID');
                    displayItem.textContent = 'Rp ' + Math.round(data.item_price).toLocaleString('id-ID');
                    displayShipping.textContent = 'Rp ' + Math.round(data.shipping_cost).toLocaleString('id-ID') + ' (' + data.distance.toFixed(1) + ' km)';
                    displayTaxLabel.textContent = 'Tax (' + data.tax_name + ' ' + data.tax_percentage + '%):';
                    displayTaxPrice.textContent = 'Rp ' + Math.round(data.tax).toLocaleString('id-ID');
                    displayTotal.textContent = 'Rp ' + Math.round(data.total_price).toLocaleString('id-ID');
                })
                .catch(error => {
                    console.error('Recalculate Error:', error);
                    displayTotal.textContent = 'Calculation Error';
                });
            }

            function debounceRecalculate() {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(recalculate, 600);
            }

            serviceSelect.addEventListener('change', recalculate);
            itemSelect.addEventListener('change', recalculate);
            pickupAddress.addEventListener('input', debounceRecalculate);
            deliveryAddress.addEventListener('input', debounceRecalculate);
            
            // Initial calculation
            recalculate();
        });

        // HTML5 Geolocation to reverse-geocode using OSM Nominatim API
        function getCurrentLocation(elementId) {
            const textarea = document.getElementById(elementId);
            if (!textarea) return;

            textarea.value = 'Locating your GPS position...';

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // Reverse geocoding with OSM Nominatim API
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.display_name) {
                                textarea.value = data.display_name;
                            } else {
                                textarea.value = `GPS Coordinates (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
                            }
                            // Trigger price recalculation
                            textarea.dispatchEvent(new Event('input', { bubbles: true }));
                        })
                        .catch(err => {
                            textarea.value = `GPS Coordinates (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
                            textarea.dispatchEvent(new Event('input', { bubbles: true }));
                        });
                }, function(error) {
                    textarea.value = 'Location access denied or unavailable. Enter address manually.';
                });
            } else {
                textarea.value = 'Your browser does not support GPS geolocation.';
            }
        }
    </script>
    @endpush
</x-app-layout>
