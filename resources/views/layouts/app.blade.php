<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta name="description" content="Laundryan - Premium laundry solution, fast, and trusted with real-time tracking system.">
        <meta name="keywords" content="laundry, laundryan, dry cleaning, laundry tracking">
        
        <title>{{ config('app.name', 'Laundryan') }} - Smart Laundry Solution</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            [x-cloak] { display: none !important; }
            body { font-family: 'Plus Jakarta Sans', sans-serif !important; }
            * { transition: all 0.2s ease-out; }
            
            /* FORCE VISIBLE SCROLLBAR */
            .custom-scrollbar {
                scrollbar-width: thin;
                scrollbar-color: rgba(255, 255, 255, 0.8) transparent;
            }

            /* Webkit (Chrome, Safari, Edge) */
            .custom-scrollbar::-webkit-scrollbar {
                width: 10px !important;
                display: block !important;
            }
            
            .custom-scrollbar::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.1) !important;
                border-radius: 10px;
            }
            
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #ffffff !important; 
                border-radius: 20px !important;
                /* Trik untuk memendekkan garis: Tambahkan border transparan yang tebal di atas & bawah */
                border-top: 30px solid transparent !important;
                border-bottom: 30px solid transparent !important;
                border-left: 3px solid transparent !important;
                border-right: 3px solid transparent !important;
                background-clip: content-box !important;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #f8fafc !important;
            }

            /* Untuk Area Konten (Kanan) */
            .overflow-y-auto::-webkit-scrollbar {
                width: 10px !important;
            }
            .overflow-y-auto::-webkit-scrollbar-thumb {
                background: #005bc0 !important; 
                border-radius: 20px !important;
                border-top: 40px solid transparent !important;
                border-bottom: 40px solid transparent !important;
                border-left: 3px solid transparent !important;
                border-right: 3px solid transparent !important;
                background-clip: content-box !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-900 overflow-hidden">
        <div class="flex h-screen bg-[#F8FAFC] overflow-hidden" x-data="{ sidebarOpen: false }">
            <!-- Sidebar Navigation (Scrollable) -->
            @include('layouts.navigation')

            <!-- Main Content Area (Independent Scroll) -->
            <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
                <!-- Mobile Header -->
                <header class="lg:hidden bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 sticky top-0 z-40 shrink-0">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = true" class="p-2 rounded-xl hover:bg-gray-100 transition-colors">
                            <span class="material-symbols-outlined text-gray-600">menu</span>
                        </button>
                        <span class="font-bold text-primary tracking-tighter uppercase">LAUNDRYAN</span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </div>
                </header>

                <!-- Page Content Area with its own scroll -->
                <div class="flex-1 overflow-y-auto custom-scrollbar flex flex-col">
                    <!-- Desktop Header / Breadcrumbs -->
                    @isset($header)
                        <div class="bg-white border-b border-gray-100 py-6 px-4 sm:px-6 lg:px-10 shrink-0">
                            <div class="max-w-7xl mx-auto">
                                {{ $header }}
                            </div>
                        </div>
                    @endisset

                    <!-- Main Slot -->
                    <main class="flex-1 p-4 sm:p-6 lg:p-10">
                        <div class="max-w-7xl mx-auto">
                            {{ $slot }}
                        </div>
                    </main>


                </div>
            </div>
        </div>

        <!-- Material Symbols -->
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
        
        <!-- Global Toast Notifications -->
        <div id="global-toast"
            x-data="{ 
                show: false, 
                type: 'success', 
                message: '', 
                init() {
                    @if(session('success') || session('payment_success_popup'))
                        this.trigger('success', '{{ session('success') ?: session('payment_success_popup') }}');
                    @elseif(session('error'))
                        this.trigger('error', '{{ session('error') }}');
                    @elseif(session('warning'))
                        this.trigger('warning', '{{ session('warning') }}');
                    @elseif(session('info'))
                        this.trigger('info', '{{ session('info') }}');
                    @elseif($errors->any())
                        this.trigger('error', '{{ $errors->first() }}');
                    @endif
                },
                trigger(type, msg) {
                    this.type = type;
                    this.message = msg;
                    this.show = true;
                    setTimeout(() => { this.show = false; }, 5000);
                }
            }"
            x-show="show"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed top-6 right-6 z-[99999] max-w-sm w-full rounded-3xl p-5 shadow-2xl flex items-center justify-between overflow-hidden border"
            :class="{
                'bg-emerald-50 border-emerald-200 text-emerald-800': type === 'success',
                'bg-rose-50 border-rose-200 text-rose-800': type === 'error',
                'bg-amber-50 border-amber-200 text-amber-800': type === 'warning',
                'bg-blue-50 border-blue-200 text-blue-800': type === 'info'
            }"
            x-cloak>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center shadow-inner"
                    :class="{
                        'bg-emerald-100/50 border border-emerald-200 text-emerald-600': type === 'success',
                        'bg-rose-100/50 border border-rose-200 text-rose-600': type === 'error',
                        'bg-amber-100/50 border border-amber-200 text-amber-600': type === 'warning',
                        'bg-blue-100/50 border border-blue-200 text-blue-600': type === 'info'
                    }">
                    <span class="material-symbols-outlined text-xl" x-text="type === 'success' ? 'check_circle' : (type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'info'))"></span>
                </div>
                <div class="text-left">
                    <h4 class="font-black text-xs uppercase tracking-wider" x-text="type === 'success' ? 'Success' : (type === 'error' ? 'Error' : (type === 'warning' ? 'Warning' : 'Info'))"></h4>
                    <p class="text-[11px] font-medium mt-0.5" x-text="message"></p>
                </div>
            </div>
            <button @click="show = false" class="transition-colors p-2 rounded-xl relative z-10"
                :class="{
                    'text-emerald-600/60 hover:text-emerald-800 hover:bg-emerald-100/50': type === 'success',
                    'text-rose-600/60 hover:text-rose-800 hover:bg-rose-100/50': type === 'error',
                    'text-amber-600/60 hover:text-amber-800 hover:bg-amber-100/50': type === 'warning',
                    'text-blue-600/60 hover:text-blue-800 hover:bg-blue-100/50': type === 'info'
                }">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        @stack('scripts')
    </body>
</html>
