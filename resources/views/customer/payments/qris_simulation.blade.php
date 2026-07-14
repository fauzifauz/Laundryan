<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-gray-900 tracking-tight text-center">
            {{ __('QRIS Payment Simulation') }}
        </h2>
    </x-slot>

    <div class="py-12 flex justify-center items-center">
        <div class="max-w-md w-full bg-white rounded-3xl p-8 shadow-2xl border border-gray-100 text-center space-y-8 relative overflow-hidden">
            <!-- Decorative brand background bar -->
            <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-brand via-blue-500 to-emerald-500"></div>

            <!-- QRIS & Stripe logos -->
            <div class="flex items-center justify-center gap-4 pt-2">
                <!-- QRIS mock logo -->
                <div class="bg-gray-50 border border-gray-100 rounded-xl px-4 py-2 flex items-center justify-center font-black text-xs text-red-600 tracking-tighter shadow-sm">
                    QRIS
                </div>
                <span class="text-gray-300 font-light">✕</span>
                <!-- Stripe mock logo -->
                <div class="bg-brand text-white rounded-xl px-4 py-2 flex items-center justify-center font-black text-xs tracking-wider shadow-sm uppercase">
                    Stripe
                </div>
            </div>

            <!-- Order Details -->
            <div class="space-y-4">
                <span class="bg-yellow-50 text-yellow-800 text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full border border-yellow-200">
                    Testing & Simulation Mode
                </span>
                
                <div class="pt-4 space-y-1">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Total Amount</p>
                    <h3 class="text-3xl font-black text-gray-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</h3>
                </div>
            </div>

            <!-- Bill Table Card -->
            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 text-left text-xs space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-400 font-medium">Order Number</span>
                    <span class="font-extrabold text-gray-800">{{ $order->order_code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400 font-medium">Service</span>
                    <span class="font-bold text-gray-800">{{ $order->service->name }} ({{ $order->itemType->name }})</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400 font-medium">Customer</span>
                    <span class="font-bold text-gray-800">{{ $order->customer->name }}</span>
                </div>
            </div>

            <!-- QRIS Barcode Container -->
            <div class="bg-gray-50 border border-gray-100 rounded-3xl p-6 space-y-4">
                <p class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Scan QRIS to Simulate Payment</p>
                <div class="bg-white p-4 rounded-2xl border border-gray-150 inline-block shadow-sm">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode(route('customer.payment.qris-simulation', $order->id)) }}" 
                         alt="QRIS Barcode" 
                         class="w-48 h-48 mx-auto object-contain">
                </div>
                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">NMID: ID102030405060</p>
            </div>

            <!-- Simulation Action Block -->
            <div class="space-y-4">
                <p class="text-xs text-gray-500 leading-relaxed max-w-xs mx-auto">
                    Clicking the button below will simulate a successful webhook callback from the **Stripe QRIS API** to verify and settle this transaction instantly.
                </p>

                <form action="{{ route('customer.payment.qris-simulation.pay', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-extrabold text-sm py-4 px-6 rounded-2xl shadow-[0_10px_20px_rgba(16,185,129,0.2)] hover:shadow-[0_10px_20px_rgba(16,185,129,0.3)] transition-all duration-300 transform active:scale-95 uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[18px]">credit_score</span>
                        Complete Payment (Simulation)
                    </button>
                </form>

                <form action="{{ route('customer.payment.qris-simulation.fail', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-rose-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-extrabold text-sm py-4 px-6 rounded-2xl shadow-[0_10px_20px_rgba(244,63,94,0.2)] hover:shadow-[0_10px_20px_rgba(244,63,94,0.3)] transition-all duration-300 transform active:scale-95 uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[18px]">block</span>
                        Fail Payment (Simulation)
                    </button>
                </form>

                <a href="{{ route('customer.orders.show', $order->id) }}" class="text-xs font-bold text-gray-400 hover:text-gray-600 block transition-colors">
                    Go Back to Order Details
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
