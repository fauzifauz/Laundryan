<!-- Sidebar -->
@php
    $siteSettings = \App\Models\LandingPageSetting::where(
        'key',
        'site'
    )->first()?->content ?? [
        'name' => 'LAUNDRYAN',
        'logo_url' => '',
    ];

    $dashboardRoute = Auth::user()->role === 'kurir'
        ? route('kurir.dashboard')
        : route('dashboard');
@endphp

<aside
    x-show="true"
    :class="sidebarOpen
        ? 'translate-x-0'
        : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-[#005bc0] via-[#005bc0] to-[#004899] text-white transition-all duration-500 ease-in-out lg:static lg:inset-0 shadow-[10px_0_40px_rgba(0,0,0,0.1)] border-r border-white/5"
>
    <div class="flex h-full flex-col">
        <!-- Logo Section -->
        <div class="relative flex h-28 items-center overflow-hidden px-6">
            <div class="absolute -left-10 -top-10 h-32 w-32 rounded-full bg-white/10 blur-3xl"></div>

            <div class="flex w-full justify-center">
                <a
                    href="{{ $dashboardRoute }}"
                    class="group relative block"
                >
                    @if(!empty($siteSettings['logo_url']))
                        <img
                            src="{{ $siteSettings['logo_url'] }}"
                            alt="Logo"
                            class="h-24 w-24 object-contain transition-all duration-500 group-hover:rotate-3 group-hover:scale-110 filter drop-shadow-[0_0_20px_rgba(255,255,255,0.3)]"
                        >
                    @else
                        <div class="transition-all duration-500 group-hover:rotate-12 group-hover:scale-110">
                            <span class="material-symbols-outlined text-7xl font-bold text-white drop-shadow-[0_0_20px_rgba(255,255,255,0.5)]">
                                local_laundry_service
                            </span>
                        </div>
                    @endif
                </a>
            </div>

            <!-- Mobile Close Button -->
            <button
                type="button"
                @click="sidebarOpen = false"
                class="ml-auto rounded-xl p-2 transition-colors hover:bg-white/10 lg:hidden"
            >
                <span class="material-symbols-outlined">
                    close
                </span>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="custom-scrollbar flex-1 space-y-1.5 overflow-y-auto px-4 py-4">
            <div class="mb-6 px-4">
                <div class="h-px w-full bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
            </div>

            <div class="space-y-1.5">
                <p class="mb-4 px-4 text-[10px] font-bold uppercase tracking-[0.25em] text-white/40">
                    Core
                </p>

                {{-- Dashboard umum tidak ditampilkan untuk kurir --}}
                @if(Auth::user()->role !== 'kurir')
                    <x-sidebar-link
                        :href="route('dashboard')"
                        :active="request()->routeIs('dashboard')"
                        icon="grid_view"
                    >
                        Dashboard
                    </x-sidebar-link>
                @endif

                {{-- Admin Navigation --}}
                @if(Auth::user()->role === 'admin')
                    <x-sidebar-link
                        :href="route('admin.tracking.index')"
                        :active="request()->routeIs('admin.tracking.*')"
                        icon="location_on"
                    >
                        Tracking
                    </x-sidebar-link>

                    <!-- Financial Dropdown -->
                    <div
                        x-data="{
                            open: {{ request()->routeIs('admin.finance.*')
                                ? 'true'
                                : 'false' }}
                        }"
                        class="space-y-1"
                    >
                        <button
                            type="button"
                            @click="open = !open"
                            class="group flex w-full items-center justify-between rounded-2xl px-4 py-3 transition-all duration-300 {{ request()->routeIs('admin.finance.*')
                                ? 'bg-white/10 text-white shadow-lg'
                                : 'text-white/50 hover:bg-white/5 hover:text-white' }}"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-xl transition-all duration-300 {{ request()->routeIs('admin.finance.*')
                                    ? 'bg-white text-gray-900 shadow-[0_0_15px_rgba(255,255,255,0.3)]'
                                    : 'bg-white/5 text-white group-hover:bg-white/10' }}"
                                >
                                    <span class="material-symbols-outlined text-[18px]">
                                        payments
                                    </span>
                                </div>

                                <span class="text-xs font-black uppercase tracking-widest">
                                    Financial
                                </span>
                            </div>

                            <span
                                class="material-symbols-outlined text-[18px] transition-transform duration-300"
                                :class="{ 'rotate-180': open }"
                            >
                                expand_more
                            </span>
                        </button>

                        <div
                            x-show="open"
                            x-collapse
                            class="relative ml-8 space-y-1 border-l border-white/10 py-1 pl-4"
                        >
                            <div class="absolute bottom-0 left-[-1px] top-0 w-[1px] bg-gradient-to-b from-white/20 via-white/10 to-transparent"></div>

                            <a
                                href="{{ route('admin.finance.index') }}"
                                class="group/item relative flex items-center gap-3 rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ request()->routeIs('admin.finance.index')
                                    ? 'bg-white/5 text-white'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white' }}"
                            >
                                <span class="h-1.5 w-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.finance.index')
                                    ? 'scale-125 bg-white shadow-[0_0_8px_white]'
                                    : 'bg-white/20 group-hover/item:bg-white/50' }}"
                                ></span>

                                Overview
                            </a>

                            <a
                                href="{{ route('admin.finance.income') }}"
                                class="group/item relative flex items-center gap-3 rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ request()->routeIs('admin.finance.income')
                                    ? 'bg-white/5 text-white'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white' }}"
                            >
                                <span class="h-1.5 w-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.finance.income')
                                    ? 'scale-125 bg-white shadow-[0_0_8px_white]'
                                    : 'bg-white/20 group-hover/item:bg-white/50' }}"
                                ></span>

                                Income Records
                            </a>

                            <a
                                href="{{ route('admin.finance.expense') }}"
                                class="group/item relative flex items-center gap-3 rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ request()->routeIs('admin.finance.expense')
                                    ? 'bg-white/5 text-white'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white' }}"
                            >
                                <span class="h-1.5 w-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.finance.expense')
                                    ? 'scale-125 bg-white shadow-[0_0_8px_white]'
                                    : 'bg-white/20 group-hover/item:bg-white/50' }}"
                                ></span>

                                Expense Records
                            </a>
                        </div>
                    </div>

                    <x-sidebar-link
                        :href="route('admin.payroll.index')"
                        :active="request()->routeIs('admin.payroll.*')"
                        icon="wallet"
                    >
                        Payroll Ops
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('admin.attendance.index')"
                        :active="request()->routeIs('admin.attendance.*')"
                        icon="how_to_reg"
                    >
                        Attendance
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('admin.users.index')"
                        :active="request()->routeIs('admin.users.*')"
                        icon="group"
                    >
                        User
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('admin.orders.index')"
                        :active="request()->routeIs('admin.orders.*')"
                        icon="shopping_basket"
                    >
                        Order
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('admin.payments.index')"
                        :active="request()->routeIs('admin.payments.*')"
                        icon="payments"
                    >
                        Payment
                    </x-sidebar-link>

                    <!-- Pricing Dropdown -->
                    <div
                        x-data="{
                            open: {{ request()->routeIs('admin.pricing.*')
                                ? 'true'
                                : 'false' }}
                        }"
                        class="space-y-1"
                    >
                        <button
                            type="button"
                            @click="open = !open"
                            class="group flex w-full items-center justify-between rounded-2xl px-4 py-3 transition-all duration-300 {{ request()->routeIs('admin.pricing.*')
                                ? 'bg-white/10 text-white shadow-lg'
                                : 'text-white/50 hover:bg-white/5 hover:text-white' }}"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-xl transition-all duration-300 {{ request()->routeIs('admin.pricing.*')
                                    ? 'bg-white text-gray-900 shadow-[0_0_15px_rgba(255,255,255,0.3)]'
                                    : 'bg-white/5 text-white group-hover:bg-white/10' }}"
                                >
                                    <span class="material-symbols-outlined text-[18px]">
                                        price_change
                                    </span>
                                </div>

                                <span class="text-xs font-black uppercase tracking-widest">
                                    Pricing & Services
                                </span>
                            </div>

                            <span
                                class="material-symbols-outlined text-[18px] transition-transform duration-300"
                                :class="{ 'rotate-180': open }"
                            >
                                expand_more
                            </span>
                        </button>

                        <div
                            x-show="open"
                            x-collapse
                            class="relative ml-8 space-y-1 border-l border-white/10 py-1 pl-4"
                        >
                            <div class="absolute bottom-0 left-[-1px] top-0 w-[1px] bg-gradient-to-b from-white/20 via-white/10 to-transparent"></div>

                            <a
                                href="{{ route('admin.pricing.services') }}"
                                class="group/item relative flex items-center gap-3 rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ request()->routeIs('admin.pricing.services')
                                    ? 'bg-white/5 text-white'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white' }}"
                            >
                                <span class="h-1.5 w-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.pricing.services')
                                    ? 'scale-125 bg-white shadow-[0_0_8px_white]'
                                    : 'bg-white/20 group-hover/item:bg-white/50' }}"
                                ></span>

                                Services
                            </a>

                            <a
                                href="{{ route('admin.pricing.item-types') }}"
                                class="group/item relative flex items-center gap-3 rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ request()->routeIs('admin.pricing.item-types')
                                    ? 'bg-white/5 text-white'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white' }}"
                            >
                                <span class="h-1.5 w-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.pricing.item-types')
                                    ? 'scale-125 bg-white shadow-[0_0_8px_white]'
                                    : 'bg-white/20 group-hover/item:bg-white/50' }}"
                                ></span>

                                Item Types
                            </a>

                            <a
                                href="{{ route('admin.pricing.delivery-fees') }}"
                                class="group/item relative flex items-center gap-3 rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ request()->routeIs('admin.pricing.delivery-fees')
                                    ? 'bg-white/5 text-white'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white' }}"
                            >
                                <span class="h-1.5 w-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.pricing.delivery-fees')
                                    ? 'scale-125 bg-white shadow-[0_0_8px_white]'
                                    : 'bg-white/20 group-hover/item:bg-white/50' }}"
                                ></span>

                                Delivery Fees
                            </a>

                            <a
                                href="{{ route('admin.pricing.taxes') }}"
                                class="group/item relative flex items-center gap-3 rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ request()->routeIs('admin.pricing.taxes')
                                    ? 'bg-white/5 text-white'
                                    : 'text-white/40 hover:bg-white/5 hover:text-white' }}"
                            >
                                <span class="h-1.5 w-1.5 rounded-full transition-all duration-300 {{ request()->routeIs('admin.pricing.taxes')
                                    ? 'scale-125 bg-white shadow-[0_0_8px_white]'
                                    : 'bg-white/20 group-hover/item:bg-white/50' }}"
                                ></span>

                                Taxes
                            </a>
                        </div>
                    </div>

                    <x-sidebar-link
                        :href="route('admin.activity-logs.index')"
                        :active="request()->routeIs('admin.activity-logs.*')"
                        icon="receipt_long"
                    >
                        Activity Logs
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('admin.landing-page.index')"
                        :active="request()->routeIs('admin.landing-page.*')"
                        icon="tactic"
                    >
                        CMS Editor
                    </x-sidebar-link>
                @endif

                {{-- Employee Navigation --}}
                @if(Auth::user()->role === 'karyawan')
                    <x-sidebar-link
                        :href="route('karyawan.tracking.index')"
                        :active="request()->routeIs('karyawan.tracking.*')"
                        icon="location_on"
                    >
                        Tracking
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('karyawan.attendance.index')"
                        :active="request()->routeIs('karyawan.attendance.*')"
                        icon="timer"
                    >
                        Check In/Out
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('karyawan.salary.index')"
                        :active="request()->routeIs('karyawan.salary.*')"
                        icon="wallet"
                    >
                        Salary
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('karyawan.orders.index')"
                        :active="request()->routeIs('karyawan.orders.*')"
                        icon="shopping_basket"
                    >
                        Order
                    </x-sidebar-link>
                @endif

                {{-- Courier Navigation --}}
                @if(Auth::user()->role === 'kurir')
                    <x-sidebar-link
                        :href="route('kurir.dashboard')"
                        :active="request()->routeIs('kurir.dashboard')"
                        icon="dashboard"
                    >
                        Dashboard
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('kurir.delivery-board')"
                        :active="request()->routeIs('kurir.delivery-board')"
                        icon="local_shipping"
                    >
                        Delivery Board
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('kurir.orders.index')"
                        :active="request()->routeIs('kurir.orders.*')"
                        icon="shopping_basket"
                    >
                        Order & Riwayat
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('kurir.salary.index')"
                        :active="request()->routeIs('kurir.salary.*')"
                        icon="payments"
                    >
                        Gaji
                    </x-sidebar-link>

                    <x-sidebar-link
                        :href="route('kurir.attendance.index')"
                        :active="request()->routeIs('kurir.attendance.*')"
                        icon="timer"
                    >
                        Check In/Out
                    </x-sidebar-link>
                @endif

                {{-- Customer Navigation --}}
                @if(Auth::user()->role === 'pelanggan')
                    <x-sidebar-link
                        id="tour-sidebar-my-laundry"
                        :href="route('customer.orders.index')"
                        :active="request()->routeIs('customer.orders.*')"
                        icon="shopping_bag"
                    >
                        My Laundry
                    </x-sidebar-link>

                    <x-sidebar-link
                        id="tour-sidebar-payments"
                        :href="route('customer.payments.index')"
                        :active="request()->routeIs('customer.payments.*')"
                        icon="payments"
                    >
                        Payments
                    </x-sidebar-link>
                @endif
            </div>
        </nav>

        <!-- User Identity & Account Actions -->
        <div
            class="mt-auto p-6"
            x-data="{ showActions: false }"
            @mouseenter="showActions = true"
            @mouseleave="showActions = false"
        >
            <div class="relative">
                <!-- Dropup Menu -->
                <div
                    x-show="showActions"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                    class="absolute bottom-full left-0 mb-4 w-full space-y-1 overflow-hidden rounded-3xl border border-white/10 bg-white/10 p-2 shadow-2xl backdrop-blur-2xl"
                    x-cloak
                >
                    <a
                        href="{{ route('profile.edit') }}"
                        class="group/item flex items-center gap-3 rounded-2xl px-4 py-3 transition-all hover:bg-white/10"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10 transition-all group-hover/item:bg-white group-hover/item:text-[#005bc0]">
                            <span class="material-symbols-outlined text-sm">
                                person_outline
                            </span>
                        </div>

                        <span class="text-[10px] font-black uppercase tracking-widest">
                            My Account
                        </span>
                    </a>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        class="w-full"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="group/logout flex w-full items-center gap-3 rounded-2xl px-4 py-3 transition-all hover:bg-red-500/20"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-500/20 text-red-300 transition-all group-hover/logout:bg-red-500 group-hover/logout:text-white">
                                <span class="material-symbols-outlined text-sm">
                                    power_settings_new
                                </span>
                            </div>

                            <span class="text-[10px] font-black uppercase tracking-widest text-red-200">
                                Sign Out
                            </span>
                        </button>
                    </form>
                </div>

                <!-- Identity Card -->
                <div
                    class="group/trigger cursor-pointer rounded-2xl px-4 py-3 transition-all duration-500"
                    :class="showActions
                        ? 'bg-white/10'
                        : 'hover:bg-white/5'"
                >
                    <div class="flex items-center gap-3">
                        <div class="relative flex-shrink-0">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-lg font-bold text-white transition-all group-hover/trigger:border-white/30">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>

                            <div class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-[#005bc0] bg-green-500"></div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-xs font-bold text-white transition-colors">
                                {{ Auth::user()->name }}
                            </h4>

                            <p class="truncate text-[9px] font-medium tracking-tight text-white/30">
                                {{ Auth::user()->email }}
                            </p>
                        </div>

                        <span
                            class="material-symbols-outlined text-xs text-white/20 transition-transform duration-500"
                            :class="showActions
                                ? 'rotate-180 opacity-100'
                                : 'opacity-40 group-hover/trigger:opacity-100'"
                        >
                            expand_less
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Overlay -->
<div
    x-show="sidebarOpen"
    @click="sidebarOpen = false"
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
></div>