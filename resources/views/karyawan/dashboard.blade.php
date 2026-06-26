<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laundry Processing Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    @if(session('success'))
                        <div
                            class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl font-bold text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-8">
                        @forelse($orders as $order)
                            <div
                                class="bg-white border-2 border-gray-100 rounded-3xl p-6 hover:border-blue-100 transition-all">
                                <div
                                    class="flex flex-col md:flex-row justify-between md:items-center space-y-4 md:space-y-0">
                                    <div>
                                        <div class="flex items-center space-x-2 mb-1">
                                            <span
                                                class="text-xs font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded uppercase">{{ $order->order_code }}</span>
                                            <span
                                                class="text-xs font-black text-gray-400 uppercase">{{ $order->service->name }}</span>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900">{{ $order->customer->name }}</h3>
                                        <p class="text-sm text-gray-500 font-medium">{{ $order->itemType->name }}</p>
                                    </div>

                                    <div class="flex items-center space-x-4">
                                        <div class="text-right">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                                                Current Status</p>
                                            <span
                                                class="px-3 py-1 bg-blue-600 text-white text-[10px] font-black rounded-full uppercase tracking-widest">
                                                {{ str_replace('_', ' ', $order->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8 pt-8 border-t border-gray-50">
                                    <form action="{{ route('karyawan.orders.status', $order->id) }}" method="POST"
                                        enctype="multipart/form-data"
                                        class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                                        @csrf
                                        <div>
                                            <label
                                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Update
                                                Stage</label>
                                            <select name="status"
                                                class="w-full rounded-xl border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500"
                                                required>
                                                @if($order->status === 'picked_up' || $order->status === 'in_transit_to_laundry')
                                                    <option value="arrived_at_laundry">Accept from Courier (Arrived)</option>
                                                @elseif($order->status === 'arrived_at_laundry')
                                                    <option value="washing">Start Washing</option>
                                                @elseif($order->status === 'washing')
                                                    <option value="drying_ironing">Move to Drying/Ironing</option>
                                                @elseif($order->status === 'drying_ironing')
                                                    <option value="packing">Move to Packing</option>
                                                @elseif($order->status === 'packing')
                                                    <option value="ready_for_delivery">Ready for Delivery</option>
                                                @else
                                                    <option value="" disabled>Status managed by Courir</option>
                                                @endif
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Photo
                                                Proof (Mandatory)</label>
                                            <input type="file" name="photo"
                                                class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                                required>
                                        </div>

                                        <button type="submit"
                                            class="bg-gray-900 text-white font-black py-2 px-6 rounded-xl hover:bg-blue-600 transition-all text-sm shadow-lg active:scale-95">
                                            Update Stage
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-20 text-gray-400">
                                <p class="text-lg font-medium italic">No orders in the processing pipeline.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>