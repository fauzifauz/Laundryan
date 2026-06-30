<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-gray-900 tracking-tight">
            {{ __('Payment History') }}
        </h2>
        <p class="text-xs text-gray-500 mt-1">Manage transaction logs of your laundry payments.</p>
    </x-slot>

    <div class="py-2 space-y-6">
        <!-- Filter and Summary Overview card -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Period filter pill container -->
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between space-y-4">
                <div>
                    <span class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-2">Filter by Period</span>
                    <form method="GET" action="{{ route('customer.payments.index') }}" class="flex flex-wrap gap-2">
                        @foreach([
                            'all' => 'All Time',
                            'harian' => 'Daily',
                            'bulanan' => 'Monthly',
                            'tahunan' => 'Yearly'
                        ] as $key => $label)
                            <button type="submit" name="period" value="{{ $key }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all border
                                {{ $period === $key 
                                    ? 'bg-brand border-brand text-white shadow-sm' 
                                    : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </form>
                </div>
            </div>

            <!-- Simple Payment totals statistics -->
            @php
                $successfulPaymentsCount = $payments->where('status', 'success')->count();
            @endphp
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Payments Completed</p>
                    <h4 class="text-2xl font-black text-emerald-600 mt-1">{{ $successfulPaymentsCount }} Success</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Successful transactions recorded</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">check_circle</span>
                </div>
            </div>
        </div>

        <!-- Payments Log List Card -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 text-left">
                <h3 class="text-base font-bold text-gray-900">Transaction Listing</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-400 font-black uppercase tracking-wider border-b border-gray-100">
                            <th class="p-4">Payment Code</th>
                            <th class="p-4">Order Code</th>
                            <th class="p-4">Service</th>
                            <th class="p-4">Payment Date</th>
                            <th class="p-4">Amount</th>
                            <th class="p-4">Method</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="p-4 font-mono font-bold text-gray-900">{{ $payment->payment_code }}</td>
                                <td class="p-4">
                                    <a href="{{ route('customer.orders.show', $payment->order_id) }}" class="font-bold text-brand hover:underline">
                                        {{ $payment->order->order_code }}
                                    </a>
                                </td>
                                <td class="p-4 font-semibold text-gray-700">
                                    {{ $payment->order->service->name }} ({{ $payment->order->itemType->name }})
                                </td>
                                <td class="p-4 text-gray-500 font-medium">
                                    {{ $payment->payment_date ? $payment->payment_date->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                                </td>
                                <td class="p-4 font-extrabold text-gray-950">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider bg-gray-100 text-gray-700 border border-gray-200 rounded-full">
                                        {{ $payment->payment_method }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    @if($payment->status === 'success')
                                        <span class="px-2.5 py-0.5 text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full uppercase">
                                            SUCCESS
                                        </span>
                                    @elseif($payment->status === 'pending')
                                        <span class="px-2.5 py-0.5 text-[10px] font-black bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full uppercase">
                                            PENDING
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 text-[10px] font-black bg-red-50 text-red-700 border border-red-200 rounded-full uppercase">
                                            {{ $payment->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <div class="inline-flex gap-2">
                                        <a href="{{ route('customer.orders.invoice', $payment->order_id) }}" class="bg-brand/5 hover:bg-brand/10 text-brand font-bold py-1.5 px-3 rounded-lg flex items-center gap-1 transition-all">
                                            <span class="material-symbols-outlined text-sm">download</span> Invoice
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-12 text-center text-gray-400 italic font-medium text-xs bg-white">
                                    No payment transactions recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="p-4 bg-gray-50 border-t border-gray-100">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
