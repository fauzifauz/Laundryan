<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Welcome Card -->
                <div class="col-span-1 lg:col-span-2 bg-blue-600 rounded-3xl p-8 text-white shadow-xl flex flex-col justify-between overflow-hidden relative">
                    <div class="z-10">
                        <h3 class="text-2xl font-bold mb-2">Welcome Back, {{ auth()->user()->name }}!</h3>
                        <p class="opacity-80">Ready to make your day more productive? Let us handle your laundry.</p>
                        <div class="mt-8 flex space-x-4">
                            <a href="{{ route('customer.orders.create') }}" class="bg-white text-blue-600 font-bold px-6 py-3 rounded-xl hover:bg-blue-50 transition-all shadow-lg">
                                Book Now
                            </a>
                        </div>
                    </div>
                    <!-- Decorative Circle -->
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/10 rounded-full"></div>
                </div>

                <!-- Stats Card -->
                <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 flex flex-col justify-between">
                    <div>
                        <h3 class="text-gray-500 font-bold text-xs uppercase tracking-widest mb-4">Total Orders</h3>
                        <p class="text-4xl font-black text-gray-800">{{ auth()->user()->customerOrders()->count() }}</p>
                    </div>
                    <a href="{{ route('customer.orders.index') }}" class="mt-8 text-blue-600 font-bold text-sm flex items-center hover:underline">
                        View Order History
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
