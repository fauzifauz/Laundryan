<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-gray-900 tracking-tight mb-1 flex items-center gap-2">
                    Edit Order <span class="text-amber-600">#{{ $order->order_code }}</span>
                    @if(session('success') && session('action_type') === 'order_updated')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold rounded-full">
                            ✓ Successfully Updated
                        </span>
                    @endif
                </h2>
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-widest">Modify order details, logistics, couriers, status and payment</p>
            </div>
            <a href="{{ route('karyawan.orders.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-amber-600 bg-gray-50 hover:bg-amber-50 border border-gray-200 hover:border-amber-200 px-4 py-2 rounded-xl transition-all">
                <span class="material-symbols-outlined text-[15px]">arrow_back</span> Back to Orders
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-6 flex gap-3 p-4 bg-rose-50 border border-rose-200 rounded-2xl shadow-sm">
                    <span class="material-symbols-outlined text-rose-500 text-[20px] shrink-0 mt-0.5">error</span>
                    <div>
                        <p class="text-xs font-bold text-rose-700 mb-1">Please fix the following errors:</p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li class="text-xs text-rose-600">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('karyawan.orders.update', $order->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @php
                $inputCls = 'w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl pl-10 pr-4 py-3 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all';
                $selectCls = 'w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl py-3 pl-4 pr-9 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 appearance-none transition-all cursor-pointer';
                $labelCls = 'block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2';
                @endphp

                {{-- ══ SECTION 1: CUSTOMER & SERVICE ══ --}}
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-7 py-5 border-b border-gray-100 bg-gradient-to-r from-amber-50/60 to-white">
                        <div class="w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center shadow-sm shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">person</span>
                        </div>
                        <div>
                            <h3 class="text-[13px] font-extrabold text-gray-800 tracking-tight">Customer & Service</h3>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Change customer, service type, or item category</p>
                        </div>
                    </div>
                    <div class="p-7 grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="{{ $labelCls }}">Customer <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="customer_id" required class="{{ $selectCls }}" id="customer_select" onchange="toggleLogisticsRequired(this)">
                                    @foreach($customers as $customer)
                                        @php
                                        $custIsWalkin = str_contains($customer->email ?? '', 'walkin_');
                                        @endphp
                                        <option value="{{ $customer->id }}" data-is-walkin="{{ $custIsWalkin ? 'true' : 'false' }}" {{ old('customer_id', $order->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}  ·  {{ $customer->phone ?? '—' }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Service Type <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="service_id" required class="{{ $selectCls }}">
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" {{ old('service_id', $order->service_id) == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }}  ·  Rp {{ number_format($service->base_price, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Item Category <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="item_type_id" required class="{{ $selectCls }}">
                                    @foreach($itemTypes as $item)
                                        <option value="{{ $item->id }}" {{ old('item_type_id', $order->item_type_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}  ·  Rp {{ number_format($item->base_price, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══ SECTION 2: LOGISTICS & PREFERENCES ══ --}}
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-7 py-5 border-b border-gray-100 bg-gradient-to-r from-orange-50/50 to-white">
                        <div class="w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center shadow-sm shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">local_shipping</span>
                        </div>
                        <div>
                            <h3 class="text-[13px] font-extrabold text-gray-800 tracking-tight">Logistics & Preferences</h3>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Addresses, schedule, soap and fragrance choices</p>
                        </div>
                    </div>
                    <div class="p-7 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="{{ $labelCls }}">Pickup Address <span class="text-rose-500" id="pickup_asterisk">*</span></label>
                            <textarea name="pickup_address" id="pickup_address" rows="3" required
                                class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all resize-none">{{ old('pickup_address', $order->pickup_address) }}</textarea>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Delivery Address <span class="text-rose-500" id="delivery_asterisk">*</span></label>
                            <textarea name="delivery_address" id="delivery_address" rows="3" required
                                class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all resize-none">{{ old('delivery_address', $order->delivery_address) }}</textarea>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Pickup Schedule <span class="text-rose-500" id="time_asterisk">*</span></label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">event</span>
                                <input type="datetime-local" name="pickup_time" id="pickup_time" required
                                    value="{{ old('pickup_time', $order->pickup_time?->format('Y-m-d\TH:i')) }}"
                                    class="{{ $inputCls }}">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Special Instructions</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">note_alt</span>
                                <input type="text" name="notes" value="{{ old('notes', $order->notes) }}"
                                    placeholder="e.g. delicate items, no dryer…"
                                    class="{{ $inputCls }}">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Soap / Detergent</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">soap</span>
                                <input type="text" name="soap" value="{{ old('soap', $order->soap) }}"
                                    placeholder="e.g. Rinso, Downy…"
                                    class="{{ $inputCls }}">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Fragrance</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">air_freshener</span>
                                <input type="text" name="fragrance" value="{{ old('fragrance', $order->fragrance) }}"
                                    placeholder="e.g. Downy Passion, Molto…"
                                    class="{{ $inputCls }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══ SECTION 3: ASSIGNMENTS & PAYMENT ══ --}}
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-7 py-5 border-b border-gray-100 bg-gradient-to-r from-rose-50/50 to-white">
                        <div class="w-9 h-9 rounded-xl bg-rose-500 flex items-center justify-center shadow-sm shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">assignment_ind</span>
                        </div>
                        <div>
                            <h3 class="text-[13px] font-extrabold text-gray-800 tracking-tight">Assignments & Payment</h3>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Courier dispatch, order status, and payment configuration</p>
                        </div>
                    </div>
                    <div class="p-7 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div>
                            <label class="{{ $labelCls }}">Pickup Courier</label>
                            <div class="relative">
                                <select name="pickup_courier_id" class="{{ $selectCls }}">
                                    <option value="">Unassigned</option>
                                    @foreach($couriers as $c)
                                        <option value="{{ $c->id }}" {{ old('pickup_courier_id', $order->pickup_courier_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Delivery Courier</label>
                            <div class="relative">
                                <select name="delivery_courier_id" class="{{ $selectCls }}">
                                    <option value="">Unassigned</option>
                                    @foreach($couriers as $c)
                                        <option value="{{ $c->id }}" {{ old('delivery_courier_id', $order->delivery_courier_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Order Status <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="status" required class="{{ $selectCls }}">
                                    @foreach([
                                        'pending_payment'       => 'Pending Payment',
                                        'waiting_pickup'        => 'Waiting Pickup',
                                        'picking_up'            => 'Picking Up',
                                        'picked_up'             => 'Picked Up',
                                        'in_transit_to_laundry' => 'In Transit to Laundry',
                                        'arrived_at_laundry'    => 'Arrived at Laundry',
                                        'washing'               => 'Washing',
                                        'drying_ironing'        => 'Drying & Ironing',
                                        'packing'               => 'Packing',
                                        'ready_for_delivery'    => 'Ready for Delivery',
                                        'delivering'            => 'Delivering',
                                        'completed'             => 'Completed',
                                        'cancelled'             => 'Cancelled',
                                    ] as $val => $lbl)
                                        <option value="{{ $val }}" {{ old('status', $order->status) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Payment Status <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="payment_status" required class="{{ $selectCls }}">
                                    <option value="pending" {{ old('payment_status', $order->payment_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ old('payment_status', $order->payment_status) === 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Payment Method <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="payment_method" required class="{{ $selectCls }}">
                                    <option value="cash" {{ old('payment_method', $order->payment_method) === 'cash' ? 'selected' : '' }}>Cash on Delivery</option>
                                    <option value="transfer" {{ old('payment_method', $order->payment_method) === 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="e-wallet" {{ old('payment_method', $order->payment_method) === 'e-wallet' ? 'selected' : '' }}>E-Wallet</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pb-2">
                    <a href="{{ route('karyawan.orders.index') }}"
                       class="px-6 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-[12px] font-bold transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white text-[12px] font-bold shadow-lg shadow-amber-200 hover:shadow-xl transition-all hover:-translate-y-0.5">
                        <span class="material-symbols-outlined text-[16px]">save</span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleLogisticsRequired(selectEl) {
            const selectedOption = selectEl.options[selectEl.selectedIndex];
            const isWalkin = selectedOption ? (selectedOption.getAttribute('data-is-walkin') === 'true') : false;
            
            const pickupAddress = document.getElementById('pickup_address');
            const deliveryAddress = document.getElementById('delivery_address');
            const pickupTime = document.getElementById('pickup_time');
            
            const pickupAsterisk = document.getElementById('pickup_asterisk');
            const deliveryAsterisk = document.getElementById('delivery_asterisk');
            const timeAsterisk = document.getElementById('time_asterisk');
            
            if (isWalkin) {
                pickupAddress.removeAttribute('required');
                deliveryAddress.removeAttribute('required');
                pickupTime.removeAttribute('required');
                
                pickupAsterisk.style.display = 'none';
                deliveryAsterisk.style.display = 'none';
                timeAsterisk.style.display = 'none';
            } else {
                pickupAddress.setAttribute('required', 'required');
                deliveryAddress.setAttribute('required', 'required');
                pickupTime.setAttribute('required', 'required');
                
                pickupAsterisk.style.display = 'inline';
                deliveryAsterisk.style.display = 'inline';
                timeAsterisk.style.display = 'inline';
            }
        }

        // Run once on load to set initial state
        document.addEventListener('DOMContentLoaded', function() {
            const selectEl = document.getElementById('customer_select');
            if (selectEl) {
                toggleLogisticsRequired(selectEl);
            }
        });
    </script>
</x-app-layout>
