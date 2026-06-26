<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-gray-900 tracking-tight mb-1">Create New Order</h2>
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-widest">Manual entry for walk-ins, phone bookings, and custom logistics</p>
            </div>
            <a href="{{ route('admin.orders.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-blue-600 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-200 px-4 py-2 rounded-xl transition-all">
                <span class="material-symbols-outlined text-[15px]">arrow_back</span> Back to Orders
            </a>
        </div>
    </x-slot>

    {{-- Page uses Plus Jakarta Sans already loaded in app layout --}}
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

            <form action="{{ route('admin.orders.store') }}" method="POST" x-data="orderForm()" class="space-y-6">
                @csrf

                {{-- ══════════════════════════════════════════════════════
                     SECTION 1 — CUSTOMER & SERVICE
                ══════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    {{-- Section header --}}
                    <div class="flex items-center gap-3 px-7 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50/60 to-white">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">person</span>
                        </div>
                        <div>
                            <h3 class="text-[13px] font-extrabold text-gray-800 tracking-tight">Customer & Service</h3>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Select or manually enter customer details, then choose service rates</p>
                        </div>
                    </div>

                    <div class="p-7 space-y-6">
                        {{-- Customer input mode toggle --}}
                        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <input type="text" name="customer_mode" x-model="customerMode" class="hidden">
                            <span class="text-[10px] font-extrabold text-gray-500 uppercase tracking-widest shrink-0">Customer Mode:</span>
                            <div class="flex gap-2">
                                <button type="button" @click="customerMode = 'select'"
                                    :class="customerMode === 'select' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200'"
                                    class="px-4 py-1.5 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[13px]">manage_search</span> Select Existing
                                </button>
                                <button type="button" @click="customerMode = 'manual'"
                                    :class="customerMode === 'manual' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200'"
                                    class="px-4 py-1.5 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[13px]">edit_note</span> Enter Manually
                                </button>
                            </div>
                        </div>

                        {{-- Mode A: Select from dropdown --}}
                        <div x-show="customerMode === 'select'" x-transition>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Select Customer Account</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">person_search</span>
                                <select name="customer_id" :required="customerMode === 'select'"
                                    class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl pl-10 pr-9 py-3 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 appearance-none transition-all cursor-pointer">
                                    <option value="">— Select a customer account —</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}  ·  {{ $customer->phone ?? 'no phone' }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        {{-- Mode B: Manual entry --}}
                        <div x-show="customerMode === 'manual'" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Customer Full Name <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">badge</span>
                                    <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                                        :required="customerMode === 'manual'"
                                        placeholder="e.g. John Doe"
                                        class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl pl-10 pr-4 py-3 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Phone Number</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">phone</span>
                                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                                        placeholder="e.g. 0812-3456-7890"
                                        class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl pl-10 pr-4 py-3 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                </div>
                            </div>
                        </div>

                        {{-- Service & Item Type --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                            <div>
                                <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Service Type <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">local_laundry_service</span>
                                    <select name="service_id" required
                                        class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl pl-10 pr-9 py-3 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 appearance-none transition-all cursor-pointer">
                                        <option value="" disabled selected>Select service type</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                                {{ $service->name }}  ·  Rp {{ number_format($service->base_price, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Item Category <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">checkroom</span>
                                    <select name="item_type_id" required
                                        class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl pl-10 pr-9 py-3 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 appearance-none transition-all cursor-pointer">
                                        <option value="" disabled selected>Select item category</option>
                                        @foreach($itemTypes as $item)
                                            <option value="{{ $item->id }}" {{ old('item_type_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}  ·  Rp {{ number_format($item->base_price, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════
                     SECTION 2 — LOGISTICS & PREFERENCES
                ══════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-7 py-5 border-b border-gray-100 bg-gradient-to-r from-purple-50/50 to-white">
                        <div class="w-9 h-9 rounded-xl bg-purple-600 flex items-center justify-center shadow-sm shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">local_shipping</span>
                        </div>
                        <div>
                            <h3 class="text-[13px] font-extrabold text-gray-800 tracking-tight">Logistics & Preferences</h3>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Pickup & delivery addresses, schedule, cleaning preferences</p>
                        </div>
                    </div>

                    <div class="p-7 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Pickup Address <span class="text-rose-500" x-show="customerMode === 'select'">*</span></label>
                            <textarea name="pickup_address" rows="3" :required="customerMode === 'select'"
                                placeholder="Full pickup address — street, district, city…"
                                class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all resize-none">{{ old('pickup_address') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Delivery Address <span class="text-rose-500" x-show="customerMode === 'select'">*</span></label>
                            <textarea name="delivery_address" rows="3" :required="customerMode === 'select'"
                                placeholder="Full delivery address — street, district, city…"
                                class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all resize-none">{{ old('delivery_address') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Pickup Schedule <span class="text-rose-500" x-show="customerMode === 'select'">*</span></label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">event</span>
                                <input type="datetime-local" name="pickup_time" :required="customerMode === 'select'"
                                    value="{{ old('pickup_time', now()->addHours(2)->format('Y-m-d\TH:i')) }}"
                                    class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl pl-10 pr-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Special Instructions</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">note_alt</span>
                                <input type="text" name="notes" value="{{ old('notes') }}"
                                    placeholder="e.g. delicate items, hang shirts, no dryer…"
                                    class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl pl-10 pr-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Soap / Detergent</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">soap</span>
                                <input type="text" name="soap" value="{{ old('soap', 'Rinso Matic') }}"
                                    placeholder="e.g. Rinso, Downy, Skip…"
                                    class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl pl-10 pr-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Fragrance</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-3 text-gray-400 text-[17px] pointer-events-none">air_freshener</span>
                                <input type="text" name="fragrance" value="{{ old('fragrance', 'Downy Passion') }}"
                                    placeholder="e.g. Downy Passion, Molto Blue…"
                                    class="w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl pl-10 pr-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════
                     SECTION 3 — ASSIGNMENTS & PAYMENT
                ══════════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-7 py-5 border-b border-gray-100 bg-gradient-to-r from-emerald-50/50 to-white">
                        <div class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center shadow-sm shrink-0">
                            <span class="material-symbols-outlined text-white text-[18px]">assignment_ind</span>
                        </div>
                        <div>
                            <h3 class="text-[13px] font-extrabold text-gray-800 tracking-tight">Assignments & Payment</h3>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Assign couriers, set initial status, and configure payment details</p>
                        </div>
                    </div>

                    <div class="p-7 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @php
                        $selectCls = 'w-full bg-gray-50 text-[13px] font-semibold text-gray-800 border border-gray-200 rounded-2xl py-3 pl-4 pr-9 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 appearance-none transition-all cursor-pointer';
                        @endphp

                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Pickup Courier</label>
                            <div class="relative">
                                <select name="pickup_courier_id" class="{{ $selectCls }}">
                                    <option value="">Unassigned</option>
                                    @foreach($couriers as $c)
                                        <option value="{{ $c->id }}" {{ old('pickup_courier_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Delivery Courier</label>
                            <div class="relative">
                                <select name="delivery_courier_id" class="{{ $selectCls }}">
                                    <option value="">Unassigned</option>
                                    @foreach($couriers as $c)
                                        <option value="{{ $c->id }}" {{ old('delivery_courier_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Order Status <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="status" required class="{{ $selectCls }}">
                                    <option value="pending_payment" {{ old('status') === 'pending_payment' ? 'selected' : '' }}>Pending Payment</option>
                                    <option value="waiting_pickup" {{ old('status', 'waiting_pickup') === 'waiting_pickup' ? 'selected' : '' }}>Waiting Pickup</option>
                                    <option value="picking_up" {{ old('status') === 'picking_up' ? 'selected' : '' }}>Picking Up</option>
                                    <option value="picked_up" {{ old('status') === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                                    <option value="in_transit_to_laundry" {{ old('status') === 'in_transit_to_laundry' ? 'selected' : '' }}>In Transit to Laundry</option>
                                    <option value="arrived_at_laundry" {{ old('status') === 'arrived_at_laundry' ? 'selected' : '' }}>Arrived at Laundry</option>
                                    <option value="washing" {{ old('status') === 'washing' ? 'selected' : '' }}>Washing</option>
                                    <option value="drying_ironing" {{ old('status') === 'drying_ironing' ? 'selected' : '' }}>Drying & Ironing</option>
                                    <option value="packing" {{ old('status') === 'packing' ? 'selected' : '' }}>Packing</option>
                                    <option value="ready_for_delivery" {{ old('status') === 'ready_for_delivery' ? 'selected' : '' }}>Ready for Delivery</option>
                                    <option value="delivering" {{ old('status') === 'delivering' ? 'selected' : '' }}>Delivering</option>
                                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Payment Status <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="payment_status" required class="{{ $selectCls }}">
                                    <option value="pending" {{ old('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ old('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Payment Method <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="payment_method" required class="{{ $selectCls }}">
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash on Delivery</option>
                                    <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="e-wallet" {{ old('payment_method') === 'e-wallet' ? 'selected' : '' }}>E-Wallet</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Action Buttons ── --}}
                <div class="flex items-center justify-end gap-3 pb-2">
                    <a href="{{ route('admin.orders.index') }}"
                       class="px-6 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-[12px] font-bold transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-[12px] font-bold shadow-lg shadow-blue-200 hover:shadow-xl transition-all hover:-translate-y-0.5">
                        <span class="material-symbols-outlined text-[16px]">add_circle</span>
                        Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function orderForm() {
            return {
                customerMode: '{{ old('customer_name') ? 'manual' : 'select' }}'
            }
        }
    </script>
</x-app-layout>
