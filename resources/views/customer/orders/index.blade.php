<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-900 tracking-tight">
                    {{ __('Laundry Order History') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Manage and track all your laundry transactions.</p>
            </div>
            <a href="{{ route('customer.orders.create') }}" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-brand to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-6 rounded-2xl shadow-[0_10px_20px_rgba(0,91,192,0.15)] transition-all">
                <span class="material-symbols-outlined text-[20px]">add</span>
                New Order
            </a>
        </div>
    </x-slot>

    <div class="py-2 space-y-6">
        <!-- Interactive Top Filters -->
        <form method="GET" action="{{ route('customer.orders.index') }}" class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Status Filter Links/Radio -->
            <div>
                <span class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-3">Filter Status</span>
                <div class="flex flex-wrap gap-2">
                    @foreach([
                        'all' => 'All',
                        'proses_pencucian' => 'Washing Process',
                        'setrika' => 'Ironing',
                        'packing' => 'Packing',
                        'selesai' => 'Completed'
                    ] as $key => $label)
                        <button type="submit" name="status" value="{{ $key }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all border
                            {{ request('status', 'all') === $key 
                                ? 'bg-brand border-brand text-white shadow-sm' 
                                : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Period Filter Links/Radio -->
            <div>
                <span class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-3">Filter Period</span>
                <div class="flex flex-wrap gap-2">
                    @foreach([
                        'all' => 'All Time',
                        'harian' => 'Today',
                        'mingguan' => 'This Week',
                        'bulanan' => 'This Month',
                        'tahunan' => 'This Year'
                    ] as $key => $label)
                        <button type="submit" name="period" value="{{ $key }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all border
                            {{ request('period', 'all') === $key 
                                ? 'bg-brand border-brand text-white shadow-sm' 
                                : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
            
            <!-- Hidden inputs to preserve the other filter when clicking one -->
            @if(request()->has('period') && !request()->has('status'))
                <input type="hidden" name="period" value="{{ request('period') }}">
            @endif
            @if(request()->has('status') && !request()->has('period'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
        </form>

        <!-- Orders Grid Listing -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse ($orders as $order)
                <div class="bg-white rounded-3xl p-6 shadow-md border border-gray-100 hover:shadow-lg transition-shadow duration-300 flex flex-col justify-between space-y-6">
                    <!-- Top header of the card -->
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider font-jakarta">Order Code</span>
                            <h4 class="text-sm font-black text-brand tracking-tight mt-0.5">{{ $order->order_code }}</h4>
                            <p class="text-[10px] text-gray-500 font-medium mt-1">
                                {{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                            </p>
                        </div>
                        
                        <!-- Badges -->
                        <div class="flex flex-col items-end gap-1.5">
                            @php
                                $statusMapping = [
                                    'pending_payment' => ['label' => 'Awaiting Payment', 'color' => 'bg-gray-100 text-gray-800 border-gray-200'],
                                    'waiting_pickup' => ['label' => 'Awaiting Courier', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                    'picking_up' => ['label' => 'Courier Picking Up', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                    'picked_up' => ['label' => 'Picked Up', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                    'in_transit_to_laundry' => ['label' => 'Transit to Laundry', 'color' => 'bg-yellow-50 text-yellow-700 border-yellow-200'],
                                    'arrived_at_laundry' => ['label' => 'Arrived at Laundry', 'color' => 'bg-orange-50 text-orange-700 border-orange-200'],
                                    'washing' => ['label' => 'Washing', 'color' => 'bg-cyan-50 text-cyan-700 border-cyan-200'],
                                    'drying_ironing' => ['label' => 'Drying & Ironing', 'color' => 'bg-teal-50 text-teal-700 border-teal-200'],
                                    'packing' => ['label' => 'Packing Clothes', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                    'ready_for_delivery' => ['label' => 'Ready for Delivery', 'color' => 'bg-lime-50 text-lime-700 border-lime-200'],
                                    'delivering' => ['label' => 'Out for Delivery', 'color' => 'bg-sky-50 text-sky-700 border-sky-200'],
                                    'completed' => ['label' => 'Completed', 'color' => 'bg-green-50 text-green-700 border-green-200'],
                                ];
                                $mapped = $statusMapping[$order->status] ?? ['label' => ucfirst($order->status), 'color' => 'bg-gray-100 text-gray-700 border-gray-200'];
                            @endphp
                            <span class="px-2.5 py-0.5 text-[10px] font-black rounded-full border {{ $mapped['color'] }}">
                                {{ $mapped['label'] }}
                            </span>
                            
                            @if($order->payment_status === 'paid')
                                <span class="px-2.5 py-0.5 text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                                    PAID
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 text-[10px] font-black bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full">
                                    UNPAID
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Middle: Service detail -->
                    <div class="grid grid-cols-2 gap-4 py-3 border-y border-gray-50 text-left">
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider font-jakarta">Service</span>
                            <p class="text-xs font-extrabold text-gray-800 mt-0.5">{{ $order->service->name }}</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider font-jakarta">Item Category</span>
                            <p class="text-xs font-extrabold text-gray-800 mt-0.5">{{ $order->itemType->name }}</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider font-jakarta">Soap / Fragrance</span>
                            <p class="text-xs font-medium text-gray-600 mt-0.5">{{ $order->soap ?? '-' }} / {{ $order->fragrance ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider font-jakarta">Total Price</span>
                            <p class="text-xs font-black text-brand mt-0.5">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <!-- Bottom: Actions -->
                    <div class="flex items-center justify-between gap-4">
                        <div class="text-left">
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider font-jakarta">Pickup Scheduled</span>
                            <p class="text-xs font-semibold text-gray-700 mt-0.5">
                                {{ $order->pickup_time->format('d M Y, H:i') }}
                            </p>
                        </div>

                        <div class="flex gap-2">
                            @if($order->payment_status !== 'paid' && $order->payment_method === 'bank_transfer')
                                <a href="{{ route('customer.orders.show', $order->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-black px-4 py-2 rounded-xl transition-colors shadow-sm">
                                    Upload Receipt
                                </a>
                            @endif
                            <a href="{{ route('customer.orders.show', $order->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-black px-4 py-2 rounded-xl transition-colors">
                                Details
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-3xl p-12 shadow-sm border border-gray-100 text-center flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-4xl">receipt_long</span>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900">No Transactions Yet</h4>
                    <p class="text-sm text-gray-500 mt-1 max-w-sm">You haven't placed any laundry orders yet, or no matches found for the filters.</p>
                    <a href="{{ route('customer.orders.create') }}" class="mt-6 inline-flex items-center gap-2 bg-brand hover:bg-blue-700 text-white text-xs font-black px-6 py-3 rounded-xl shadow transition-all">
                        Book Your First Order
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
