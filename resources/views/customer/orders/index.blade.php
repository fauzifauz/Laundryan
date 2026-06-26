<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Orders') }}
            </h2>
            <a href="{{ route('customer.orders.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl shadow-lg transition-all transform hover:scale-105">
                + New Order
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-bold text-gray-500 uppercase tracking-widest">
                                    <th class="px-6 py-4">Order Code</th>
                                    <th class="px-6 py-4">Service</th>
                                    <th class="px-6 py-4">Pickup Time</th>
                                    <th class="px-6 py-4">Total</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Payment</th>
                                    <th class="px-6 py-4">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                @forelse ($orders as $order)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 font-bold text-blue-600">{{ $order->order_code }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ $order->service->name }}</div>
                                            <div class="text-gray-500">{{ $order->itemType->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600">{{ $order->pickup_time->format('d M Y, H:i') }}</td>
                                        <td class="px-6 py-4 font-semibold text-gray-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4">
                                            @php
                                                $statusColors = [
                                                    'pending_payment' => 'bg-gray-100 text-gray-800',
                                                    'waiting_pickup' => 'bg-blue-100 text-blue-800',
                                                    'picking_up' => 'bg-blue-100 text-blue-800',
                                                    'picked_up' => 'bg-blue-100 text-blue-800',
                                                    'in_transit_to_laundry' => 'bg-yellow-100 text-yellow-800',
                                                    'arrived_at_laundry' => 'bg-orange-100 text-orange-800',
                                                    'washing' => 'bg-cyan-100 text-cyan-800',
                                                    'drying_ironing' => 'bg-teal-100 text-teal-800',
                                                    'packing' => 'bg-emerald-100 text-emerald-800',
                                                    'ready_for_delivery' => 'bg-lime-100 text-lime-800',
                                                    'delivering' => 'bg-sky-100 text-sky-800',
                                                    'completed' => 'bg-green-100 text-green-800',
                                                ];
                                                $color = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full {{ $color }}">
                                                {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($order->payment_status === 'paid')
                                                <span class="text-green-600 font-bold flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                    Paid
                                                </span>
                                            @else
                                                <span class="text-gray-500 font-medium italic">Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('customer.orders.show', $order->id) }}" class="text-blue-600 hover:text-blue-900 font-bold transition-all underline decoration-dotted">Details</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                                <p class="text-lg font-medium">No orders found yet.</p>
                                                <a href="{{ route('customer.orders.create') }}" class="mt-4 text-blue-600 hover:underline">Place your first order</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
