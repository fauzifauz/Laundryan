<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Book a Laundry') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    <form action="{{ route('customer.orders.store') }}" method="POST" id="order-form">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Left Column: Selection -->
                            <div class="space-y-6">
                                <div>
                                    <label for="service_id" class="block text-sm font-medium text-gray-700 mb-2">Service</label>
                                    <select name="service_id" id="service_id" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all" required>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" data-price="{{ $service->base_price }}">{{ $service->name }} (Rp {{ number_format($service->base_price, 0, ',', '.') }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="item_type_id" class="block text-sm font-medium text-gray-700 mb-2">Item Type</label>
                                    <select name="item_type_id" id="item_type_id" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all" required>
                                        @foreach($itemTypes as $type)
                                            <option value="{{ $type->id }}" data-price="{{ $type->base_price }}">{{ $type->name }} (Rp {{ number_format($type->base_price, 0, ',', '.') }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="pickup_time" class="block text-sm font-medium text-gray-700 mb-2">Pickup Schedule</label>
                                    <input type="datetime-local" name="pickup_time" id="pickup_time" min="{{ now()->addHour()->format('Y-m-d\TH:i') }}" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all" required>
                                </div>
                            </div>

                            <!-- Right Column: Addresses -->
                            <div class="space-y-6">
                                <div>
                                    <label for="pickup_address" class="block text-sm font-medium text-gray-700 mb-2">Pickup Address</label>
                                    <textarea name="pickup_address" id="pickup_address" rows="3" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all" placeholder="Enter full address..." required>{{ old('pickup_address', auth()->user()->address) }}</textarea>
                                </div>

                                <div>
                                    <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-2">Delivery Address</label>
                                    <textarea name="delivery_address" id="delivery_address" rows="3" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all" placeholder="Enter full address..." required>{{ old('delivery_address', auth()->user()->address) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Special Notes -->
                        <div class="mt-8">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Special Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="2" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all" placeholder="Fragile items, specific laundry detergent, etc."></textarea>
                        </div>

                        <!-- Price Breakdown Card -->
                        <div class="mt-10 p-6 bg-blue-50 rounded-2xl border border-blue-100">
                            <h3 class="text-lg font-semibold text-blue-900 mb-4">Cost Summary</h3>
                            <div class="space-y-2 text-sm text-blue-800">
                                <div class="flex justify-between">
                                    <span>Service:</span>
                                    <span id="display-service-price">Rp 0</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Item Type:</span>
                                    <span id="display-item-price">Rp 0</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Shipping:</span>
                                    <span id="display-shipping-price">Rp 0</span>
                                </div>
                                <div class="flex justify-between">
                                    <span id="display-tax-label">Tax:</span>
                                    <span id="display-tax-price">Rp 0</span>
                                </div>
                                <div class="flex justify-between mt-4 pt-4 border-t border-blue-200 font-bold text-lg">
                                    <span>Total:</span>
                                    <span id="display-total-price">Rp 0</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-blue-600 border border-transparent rounded-xl font-semibold text-lg text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all shadow-lg hover:shadow-blue-200">
                                Proceed to Checkout
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                debounceTimeout = setTimeout(recalculate, 500);
            }

            serviceSelect.addEventListener('change', recalculate);
            itemSelect.addEventListener('change', recalculate);
            pickupAddress.addEventListener('input', debounceRecalculate);
            deliveryAddress.addEventListener('input', debounceRecalculate);
            
            // Initial calculation
            recalculate();
        });
    </script>
</x-app-layout>
