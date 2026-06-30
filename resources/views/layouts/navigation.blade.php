<!-- Sidebar Sidebar -->
@php
    $siteSettings = \App\Models\LandingPageSetting::where('key', 'site')->first()?->content ?? [
        'name' => 'LAUNDRYAN',
        'logo_url' => ''
    ];
@endphp

<aside x-show="true" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-[#005bc0] via-[#005bc0] to-[#004899] text-white transition-all duration-500 ease-in-out lg:static lg:inset-0 shadow-[10px_0_40px_rgba(0,0,0,0.1)] border-r border-white/5">
    <div class="flex flex-col h-full">
        <!-- Logo Section with Glassmorphism -->
        <div class="h-28 flex items-center px-6 relative overflow-hidden">
            <div class="absolute -top-10 -left-10 w-32 h-32 bg-white/10 rounded-full blur-3xl"></div>

            <div class="flex justify-center w-full">
                <a href="{{ route('dashboard') }}" class="group relative block">
                    @if(!empty($siteSettings['logo_url']))
                        <img src="{{ $siteSettings['logo_url'] }}" alt="Logo"
                            class="w-24 h-24 object-contain group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 filter drop-shadow-[0_0_20px_rgba(255,255,255,0.3)]">
                    @else
                        <div class="group-hover:scale-110 group-hover:rotate-12 transition-all duration-500">
                            <span
                                class="material-symbols-outlined text-white font-bold text-7xl drop-shadow-[0_0_20px_rgba(255,255,255,0.5)]">local_laundry_service</span>
                        </div>
                    @endif
                </a>
            </div>

            <!-- Mobile Close Button -->
            <button @click="sidebarOpen = false"
                class="lg:hidden ml-auto p-2 hover:bg-white/10 rounded-xl transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto custom-scrollbar">
            <div class="px-4 mb-6">
                <div class="h-px w-full bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
            </div>

            <!-- Staggered Animation Wrapper -->
            <div class="space-y-1.5">
                <p class="px-4 text-[10px] font-bold text-white/40 uppercase tracking-[0.25em] mb-4">Core</p>

                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="grid_view">
                    Dashboard
                </x-sidebar-link>

                @if(Auth::user()->role === 'admin')
                    <x-sidebar-link :href="route('admin.tracking.index')" :active="request()->routeIs('admin.tracking.*')" icon="location_on">
                        Tracking
                    </x-sidebar-link>

                    <!-- Financial Dropdown -->
                    <div x-data="{ open: {{ request()->routeIs('admin.finance.*') ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all duration-300 group {{ request()->routeIs('admin.finance.*') ? 'bg-white/10 text-white shadow-lg' : 'text-white/50 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center transition-all duration-300 {{ request()->routeIs('admin.finance.*') ? 'bg-white text-gray-900 shadow-[0_0_15px_rgba(255,255,255,0.3)]' : 'bg-white/5 text-white group-hover:bg-white/10' }}">
                                    <span class="material-symbols-outlined text-[18px]">payments</span>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest">Financial</span>
                            </div>
                            <span class="material-symbols-outlined text-[18px] transition-transform duration-300" :class="{ 'rotate-180': open }">expand_more</span>
                        </button>

                        <div x-show="open" 
                            x-collapse
                            class="relative ml-8 pl-4 border-l border-white/10 space-y-1 py-1">
                            
                            <!-- Vertical Line Gradient -->
                            <div class="absolute left-[-1px] top-0 bottom-0 w-[1px] bg-gradient-to-b from-white/20 via-white/10 to-transparent"></div>

                            <a href="{{ route('admin.finance.index') }}" 
                                class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all group/item {{ request()->routeIs('admin.finance.index') ? 'text-white bg-white/5' : 'text-white/40 hover:text-white hover:bg-white/5' }}">
                                <span class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.finance.index') ? 'bg-white shadow-[0_0_8px_white] scale-125' : 'bg-white/20 group-hover/item:bg-white/50' }}"></span>
                                Overview
                            </a>

                            <a href="{{ route('admin.finance.income') }}" 
                                class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all group/item {{ request()->routeIs('admin.finance.income') ? 'text-white bg-white/5' : 'text-white/40 hover:text-white hover:bg-white/5' }}">
                                <span class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.finance.income') ? 'bg-white shadow-[0_0_8px_white] scale-125' : 'bg-white/20 group-hover/item:bg-white/50' }}"></span>
                                Income Records
                            </a>

                            <a href="{{ route('admin.finance.expense') }}" 
                                class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all group/item {{ request()->routeIs('admin.finance.expense') ? 'text-white bg-white/5' : 'text-white/40 hover:text-white hover:bg-white/5' }}">
                                <span class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.finance.expense') ? 'bg-white shadow-[0_0_8px_white] scale-125' : 'bg-white/20 group-hover/item:bg-white/50' }}"></span>
                                Expense Records
                            </a>
                        </div>
                    </div>

                    <x-sidebar-link :href="route('admin.payroll.index')" :active="request()->routeIs('admin.payroll.*')"
                        icon="wallet">
                        Payroll Ops
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('admin.attendance.index')" :active="request()->routeIs('admin.attendance.*')"
                        icon="how_to_reg">
                        Attendance
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')"
                        icon="group">
                        User
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')"
                        icon="shopping_basket">
                        Order
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('admin.payments.index')" :active="request()->routeIs('admin.payments.*')"
                        icon="payments">
                        Payment
                    </x-sidebar-link>

                    <!-- Pricing Dropdown -->
                    <div x-data="{ open: {{ request()->routeIs('admin.pricing.*') ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all duration-300 group {{ request()->routeIs('admin.pricing.*') ? 'bg-white/10 text-white shadow-lg' : 'text-white/50 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center transition-all duration-300 {{ request()->routeIs('admin.pricing.*') ? 'bg-white text-gray-900 shadow-[0_0_15px_rgba(255,255,255,0.3)]' : 'bg-white/5 text-white group-hover:bg-white/10' }}">
                                    <span class="material-symbols-outlined text-[18px]">price_change</span>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest">Pricing & Services</span>
                            </div>
                            <span class="material-symbols-outlined text-[18px] transition-transform duration-300" :class="{ 'rotate-180': open }">expand_more</span>
                        </button>

                        <div x-show="open" 
                            x-collapse
                            class="relative ml-8 pl-4 border-l border-white/10 space-y-1 py-1">
                            
                            <!-- Vertical Line Gradient -->
                            <div class="absolute left-[-1px] top-0 bottom-0 w-[1px] bg-gradient-to-b from-white/20 via-white/10 to-transparent"></div>

                            <a href="{{ route('admin.pricing.services') }}" 
                                class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all group/item {{ request()->routeIs('admin.pricing.services') ? 'text-white bg-white/5' : 'text-white/40 hover:text-white hover:bg-white/5' }}">
                                <span class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.pricing.services') ? 'bg-white shadow-[0_0_8px_white] scale-125' : 'bg-white/20 group-hover/item:bg-white/50' }}"></span>
                                Services
                            </a>

                            <a href="{{ route('admin.pricing.item-types') }}" 
                                class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all group/item {{ request()->routeIs('admin.pricing.item-types') ? 'text-white bg-white/5' : 'text-white/40 hover:text-white hover:bg-white/5' }}">
                                <span class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.pricing.item-types') ? 'bg-white shadow-[0_0_8px_white] scale-125' : 'bg-white/20 group-hover/item:bg-white/50' }}"></span>
                                Item Types
                            </a>

                            <a href="{{ route('admin.pricing.delivery-fees') }}" 
                                class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all group/item {{ request()->routeIs('admin.pricing.delivery-fees') ? 'text-white bg-white/5' : 'text-white/40 hover:text-white hover:bg-white/5' }}">
                                <span class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.pricing.delivery-fees') ? 'bg-white shadow-[0_0_8px_white] scale-125' : 'bg-white/20 group-hover/item:bg-white/50' }}"></span>
                                Delivery Fees
                            </a>

                            <a href="{{ route('admin.pricing.taxes') }}" 
                                class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all group/item {{ request()->routeIs('admin.pricing.taxes') ? 'text-white bg-white/5' : 'text-white/40 hover:text-white hover:bg-white/5' }}">
                                <span class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.pricing.taxes') ? 'bg-white shadow-[0_0_8px_white] scale-125' : 'bg-white/20 group-hover/item:bg-white/50' }}"></span>
                                Taxes
                            </a>
                        </div>
                    </div>

                    <!-- Activity Logs -->
                    <x-sidebar-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs.*')"
                        icon="receipt_long">
                        Activity Logs
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('admin.landing-page.index')"
                        :active="request()->routeIs('admin.landing-page.*')" icon="tactic">
                        CMS Editor
                    </x-sidebar-link>

                @endif

                @if(Auth::user()->role === 'karyawan')
                    <x-sidebar-link :href="route('karyawan.tracking.index')"
                        :active="request()->routeIs('karyawan.tracking.*')" icon="location_on">
                        Tracking
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('karyawan.attendance.index')"
                        :active="request()->routeIs('karyawan.attendance.*')" icon="timer">
                        Check In/Out
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('karyawan.salary.index')"
                        :active="request()->routeIs('karyawan.salary.*')" icon="wallet">
                        Salary
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('karyawan.orders.index')"
                        :active="request()->routeIs('karyawan.orders.*')" icon="shopping_basket">
                        Order
                    </x-sidebar-link>
                @endif

                @if(Auth::user()->role === 'kurir')
                    <x-sidebar-link :href="route('kurir.dashboard')" :active="request()->routeIs('kurir.dashboard')"
                        icon="local_shipping">
                        Delivery Board
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('kurir.attendance.index')" :active="request()->routeIs('kurir.attendance.*')"
                        icon="timer">
                        Check In/Out
                    </x-sidebar-link>
                @endif

                @if(Auth::user()->role === 'pelanggan')
                    <x-sidebar-link :href="route('customer.orders.index')" :active="request()->routeIs('customer.orders.*')"
                        icon="shopping_bag">
                        My Laundry
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('customer.payments.index')" :active="request()->routeIs('customer.payments.*')"
                        icon="payments">
                        Payments
                    </x-sidebar-link>
                @endif
            </div>
        </nav>

        <!-- User Identity & Account Actions Section (Dropup) -->
        <div class="p-6 mt-auto" x-data="{ showActions: false }" @mouseenter="showActions = true"
            @mouseleave="showActions = false">
            <div class="relative">
                <!-- Dropup Menu Content -->
                <div x-show="showActions" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                    class="absolute bottom-full left-0 w-full mb-4 bg-white/10 backdrop-blur-2xl rounded-3xl border border-white/10 shadow-2xl overflow-hidden p-2 space-y-1"
                    x-cloak>
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-white/10 rounded-2xl transition-all group/item">
                        <div
                            class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover/item:bg-white group-hover/item:text-[#005bc0] transition-all">
                            <span class="material-symbols-outlined text-sm">person_outline</span>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest">My Account</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-red-500/20 rounded-2xl transition-all group/logout">
                            <div
                                class="w-8 h-8 rounded-xl bg-red-500/20 flex items-center justify-center text-red-300 group-hover/logout:bg-red-500 group-hover/logout:text-white transition-all">
                                <span class="material-symbols-outlined text-sm">power_settings_new</span>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-red-200">Sign Out</span>
                        </button>
                    </form>
                </div>

                <!-- Identity Card (Minimalist Trigger) -->
                <div class="px-4 py-3 rounded-2xl cursor-pointer transition-all duration-500 group/trigger"
                    :class="showActions ? 'bg-white/10' : 'hover:bg-white/5'">
                    <div class="flex items-center gap-3">
                        <!-- Minimalist Avatar -->
                        <div class="relative flex-shrink-0">
                            <div
                                class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white font-bold text-lg border border-white/10 group-hover/trigger:border-white/30 transition-all">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div
                                class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-[#005bc0] rounded-full">
                            </div>
                        </div>

                        <!-- Name & Email Minimalist -->
                        <div class="flex-1 min-w-0">
                            <h4
                                class="text-xs font-bold text-white truncate group-hover/trigger:text-white transition-colors">
                                {{ Auth::user()->name }}</h4>
                            <p class="text-[9px] text-white/30 font-medium truncate tracking-tight">
                                {{ Auth::user()->email }}</p>
                        </div>

                        <span class="material-symbols-outlined text-white/20 text-xs transition-transform duration-500"
                            :class="showActions ? 'rotate-180 opacity-100' : 'group-hover/trigger:opacity-100 opacity-40'">expand_less</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-black/60 lg:hidden backdrop-blur-sm"></div>