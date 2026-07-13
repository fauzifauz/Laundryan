<!DOCTYPE html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'LAUNDRYAN') }} - Premium Laundry Service</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .glass-nav { backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
    </style>
</head>
<body class="antialiased bg-white text-on-surface selection:bg-primary-container selection:text-on-primary" x-data="{ mobileMenu: false }">
    <!-- Global Toast Notification for Guest/Welcome Page -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed top-24 right-6 z-50 max-w-sm w-full bg-gradient-to-r from-emerald-600 to-teal-600 rounded-3xl p-5 shadow-2xl text-white flex items-center justify-between overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-10 h-10 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center shadow-inner">
                    <span class="material-symbols-outlined text-white text-xl">check_circle</span>
                </div>
                <div>
                    <h4 class="font-black text-xs uppercase tracking-wider font-sans">Action Completed</h4>
                    <p class="text-[11px] text-emerald-50 font-medium font-sans mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            <button @click="show = false" class="text-white/60 hover:text-white transition-colors p-2 rounded-xl hover:bg-white/10 relative z-10">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    <!-- TopAppBar -->
    <header class="bg-white/80 backdrop-blur-2xl fixed top-0 w-full z-50 transition-all duration-300 shadow-[0_12px_40px_rgba(25,28,30,0.06)]">
        <nav class="flex justify-between items-center px-4 sm:px-6 md:px-12 h-20 w-full max-w-7xl mx-auto">
            <a href="/" class="flex items-center">
                @if(!empty($settings['site']['logo_url']))
                    <img src="{{ $settings['site']['logo_url'] }}" alt="{{ $settings['site']['name'] ?? 'LAUNDRYAN' }}" class="h-10 sm:h-12 w-auto object-contain">
                @else
                    <div class="text-xl sm:text-2xl font-black tracking-tighter text-primary uppercase">{{ $settings['site']['name'] ?? 'LAUNDRYAN' }}</div>
                @endif
            </a>
            
            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-8">
                <a class="text-on-surface-variant font-medium hover:text-primary transition-all text-sm" href="#services">Services</a>
                <a class="text-on-surface-variant font-medium hover:text-primary transition-all text-sm" href="#process">Process</a>
                <a class="text-on-surface-variant font-medium hover:text-primary transition-all text-sm" href="#pricing">Pricing</a>
                <a class="text-on-surface-variant font-medium hover:text-primary transition-all text-sm" href="#benefits">Benefits</a>
                <a class="text-on-surface-variant font-medium hover:text-primary transition-all text-sm" href="#faq">FAQ</a>
                <a class="text-on-surface-variant font-medium hover:text-primary transition-all text-sm" href="#reviews">Reviews</a>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <div class="hidden sm:flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-primary text-on-primary px-6 py-2.5 rounded-full font-bold transition-all shadow-lg hover:scale-105 active:scale-95 text-xs">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-on-surface font-semibold hover:text-primary transition-colors text-xs">Log in</a>
                        <a href="{{ route('register') }}" class="bg-primary text-on-primary px-6 py-2.5 rounded-full font-bold transition-all shadow-lg hover:scale-105 active:scale-95 text-xs">
                            Sign Up
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Toggle -->
                <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 text-on-surface hover:bg-surface-container rounded-xl transition-colors">
                    <span class="material-symbols-outlined" x-text="mobileMenu ? 'close' : 'menu'">menu</span>
                </button>
            </div>
        </nav>

        <!-- Mobile Menu Overlay -->
        <div x-show="mobileMenu" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="lg:hidden absolute top-20 left-0 w-full bg-white border-t border-gray-100 shadow-2xl p-6 space-y-6 z-40"
             @click.away="mobileMenu = false">
            <div class="flex flex-col gap-4">
                <a @click="mobileMenu = false" class="text-on-surface-variant font-bold hover:text-primary transition-all text-lg" href="#services">Services</a>
                <a @click="mobileMenu = false" class="text-on-surface-variant font-bold hover:text-primary transition-all text-lg" href="#process">Process</a>
                <a @click="mobileMenu = false" class="text-on-surface-variant font-bold hover:text-primary transition-all text-lg" href="#pricing">Pricing</a>
                <a @click="mobileMenu = false" class="text-on-surface-variant font-bold hover:text-primary transition-all text-lg" href="#benefits">Benefits</a>
                <a @click="mobileMenu = false" class="text-on-surface-variant font-bold hover:text-primary transition-all text-lg" href="#faq">FAQ</a>
                <a @click="mobileMenu = false" class="text-on-surface-variant font-bold hover:text-primary transition-all text-lg" href="#reviews">Reviews</a>
            </div>
            <div class="pt-6 border-t border-gray-100 flex flex-col gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}" class="w-full bg-primary text-on-primary py-4 rounded-xl font-bold text-center shadow-lg">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="w-full border-2 border-gray-100 text-on-surface py-4 rounded-xl font-bold text-center">Log in</a>
                    <a href="{{ route('register') }}" class="w-full bg-primary text-on-primary py-4 rounded-xl font-bold text-center shadow-lg">Join Now</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="pt-20">
        <!-- Hero Section -->
        <section class="relative min-h-[70vh] lg:min-h-[85vh] flex items-center px-6 md:px-12 lg:px-24 overflow-hidden bg-white py-12 lg:py-0">
            <div class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Image Div (Order 1 on Mobile/Tablet) -->
                <div class="order-1 lg:order-2 lg:col-span-5 lg:col-start-8 relative h-[300px] sm:h-[400px] lg:h-[480px] w-full max-w-lg mx-auto lg:mx-0">
                    <div class="absolute inset-0 bg-outline-variant rounded-3xl -rotate-3 translate-x-3 translate-y-3 lg:translate-x-4 lg:translate-y-4"></div>
                    <img src="{{ $settings['hero']['image_url'] ?? '' }}" alt="Freshly folded laundry" class="absolute inset-0 w-full h-full object-cover rounded-3xl shadow-2xl z-10">
                    <div class="absolute bottom-6 -left-6 sm:bottom-8 sm:-left-8 z-20 bg-white p-4 sm:p-6 rounded-2xl shadow-xl hidden sm:flex border border-gray-100 max-w-xs animate-fade-in-up">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                <span class="material-symbols-outlined text-xl sm:text-2xl">verified</span>
                            </div>
                            <div>
                                <p class="font-bold text-on-surface text-sm sm:text-base">Premium Care</p>
                                <p class="text-[10px] sm:text-xs text-on-surface-variant">100% Satisfaction Guaranteed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Text Div (Order 2 on Mobile/Tablet) -->
                <div class="order-2 lg:order-1 lg:col-span-6 z-10 text-center lg:text-left">
                    <h1 class="text-on-surface text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold tracking-tighter leading-tight mb-6 mt-8 lg:mt-0">
                        {{ $settings['hero']['title_line1'] ?? 'Laundry Day,' }} <br/>
                        <span class="text-primary italic">{{ $settings['hero']['title_accent'] ?? 'Made Easy' }}</span>
                    </h1>
                    <p class="text-on-surface-variant text-base sm:text-lg md:text-xl max-w-lg mx-auto lg:mx-0 mb-10 leading-relaxed">
                        {{ $settings['hero']['subtitle'] ?? 'Experience the gold standard of garment care. We pick up, clean with eco-conscious precision, and deliver fresh luxury straight to your door.' }}
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                        <a href="{{ route('login') }}" class="bg-primary text-on-primary px-8 sm:px-10 py-4 sm:py-5 rounded-full text-base sm:text-lg text-center font-bold shadow-[0_12px_40px_rgba(0,89,187,0.2)] hover:scale-105 transition-transform">
                            {{ $settings['hero']['cta_text'] ?? 'Schedule Your Pickup' }}
                        </a>
                        <a href="#pricing" class="bg-gray-50 text-on-surface-variant px-8 sm:px-10 py-4 sm:py-5 rounded-full text-base sm:text-lg text-center font-semibold hover:bg-gray-100 transition-colors">
                            View Pricing
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="py-16 sm:py-24 px-6 md:px-12 lg:px-24 bg-white" id="services">
            <div class="max-w-7xl mx-auto">
                <div class="mb-12 sm:mb-16 text-center lg:text-left">
                    <span class="text-primary font-bold tracking-widest uppercase text-[10px] sm:text-xs">{{ $settings['services']['subtitle'] ?? 'Our Expertise' }}</span>
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-on-surface mt-4">{{ $settings['services']['heading'] ?? 'Curated Care Services' }}</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($settings['services']['items'] ?? [] as $service)
                        <div class="bg-white p-8 sm:p-10 rounded-3xl group hover:bg-primary transition-all duration-500 shadow-sm border border-gray-50">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gray-50 flex items-center justify-center mb-6 sm:mb-8 group-hover:bg-white/20 transition-all">
                                <span class="material-symbols-outlined text-primary group-hover:text-white text-2xl sm:text-3xl">{{ $service['icon'] }}</span>
                            </div>
                            <h3 class="text-2xl font-bold text-on-surface group-hover:text-white mb-4">{{ $service['title'] }}</h3>
                            <p class="text-on-surface-variant group-hover:text-white/80 leading-relaxed text-sm sm:text-base">
                                {{ $service['desc'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-16 sm:py-24 px-6 md:px-12 lg:px-24 bg-white" id="process">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16 sm:mb-20">
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-on-surface">The Cycle of Freshness</h2>
                    <p class="text-on-surface-variant mt-4 text-base sm:text-lg">Four simple steps to reclaim your weekend.</p>
                </div>
                <div class="relative">
                    <!-- Desktop Connecting Line -->
                    <div class="hidden lg:block absolute top-10 left-0 w-full h-0.5 z-0">
                        <svg class="w-full h-full" overflow="visible">
                            <line x1="12.5%" y1="0" x2="87.5%" y2="0" 
                                  stroke="currentColor" 
                                  stroke-width="2" 
                                  class="text-primary/20" 
                                  stroke-dasharray="8 8">
                                <animate attributeName="stroke-dashoffset" from="100" to="0" dur="10s" repeatCount="indefinite" />
                            </line>
                        </svg>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-12 relative z-10">
                        @foreach($settings['process']['steps'] ?? [] as $i => $step)
                            <div class="flex flex-col items-center text-center group">
                                <div class="relative">
                                    @if($i < 3)
                                        <!-- Mobile Vertical Line -->
                                        <div class="lg:hidden absolute top-20 left-1/2 -translate-x-1/2 w-0.5 h-12 bg-primary/20 z-0"></div>
                                    @endif
                                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-white border-4 border-primary flex items-center justify-center mb-6 shadow-lg group-hover:scale-110 transition-all duration-500 z-10 relative">
                                        <span class="material-symbols-outlined text-primary text-2xl sm:text-3xl">{{ $step['icon'] }}</span>
                                    </div>
                                </div>
                                <h4 class="text-lg sm:text-xl font-bold mb-2">{{ $step['title'] }}</h4>
                                <p class="text-on-surface-variant text-xs sm:text-sm">{{ $step['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        <!-- Features/Benefits Bento -->
        <section class="pt-24 pb-16 sm:pt-32 sm:pb-24 px-6 md:px-12 lg:px-24 bg-white" id="benefits">
            <div class="max-w-7xl mx-auto">
                <div class="mb-12 sm:mb-16 text-center lg:text-left">
                    <span class="text-primary font-bold tracking-widest uppercase text-[10px] sm:text-xs">{{ $settings['benefits']['subtitle'] ?? 'Why Choose Us' }}</span>
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-on-surface mt-4">{{ $settings['benefits']['heading'] ?? 'The Laundryan Advantage' }}</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-12 md:grid-rows-2 gap-6 h-auto md:h-[600px]">
                    @foreach($settings['benefits']['items'] ?? [] as $i => $item)
                        @php
                            $isLarge = in_array($i, [0, 3]);
                            $baseClass = $isLarge ? "md:col-span-8" : "md:col-span-4";
                            
                            // Custom styles based on key or index
                            $cardStyle = match($item['key'] ?? $i) {
                                'eco', 0 => "bg-white text-on-surface border border-gray-50",
                                'bolt', 1 => "bg-primary text-on-primary",
                                'payments', 2 => "bg-secondary-container text-on-secondary-container",
                                'fact_check', 3 => "bg-surface-container-highest text-on-surface border border-gray-100",
                                default => "bg-white text-on-surface"
                            };
                            
                            $iconBg = match($item['key'] ?? $i) {
                                'eco', 0 => "bg-green-50 text-green-600",
                                'bolt', 1 => "bg-white/20 text-white",
                                'payments', 2 => "bg-white/30 text-white",
                                'fact_check', 3 => "bg-primary/10 text-primary",
                                default => "bg-gray-100 text-gray-600"
                            };
                        @endphp

                        <div class="{{ $baseClass }} {{ $cardStyle }} rounded-3xl p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden group shadow-sm transition-all duration-500">
                            @if(($item['key'] ?? $i) == 'eco' || $i == 0)
                                <div class="absolute -right-20 -top-20 w-80 h-80 bg-primary/5 rounded-full blur-3xl group-hover:scale-110 transition-transform"></div>
                            @endif
                            
                            <div class="relative z-10 h-full flex flex-col {{ $isLarge && $i == 3 ? 'md:flex-row md:items-center md:justify-between' : 'justify-between' }}">
                                <div class="{{ $isLarge && $i == 3 ? 'md:grow' : '' }}">
                                    <div class="w-12 h-12 rounded-xl {{ $iconBg }} flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                                    </div>
                                    <h3 class="text-2xl sm:text-3xl font-bold mb-4">{{ $item['title'] }}</h3>
                                    <p class="{{ $cardStyle === 'bg-primary text-on-primary' ? 'text-white/80' : 'text-on-surface-variant' }} {{ $isLarge ? 'max-w-md' : '' }} text-sm sm:text-base leading-relaxed">
                                        {{ $item['desc'] }}
                                    </p>
                                </div>
                                
                                @if(($item['key'] ?? $i) == 'fact_check' || $i == 3)
                                    <div class="hidden lg:block absolute right-0 top-0 h-full w-1/3 bg-white/20 backdrop-blur-md transform skew-x-12 translate-x-12"></div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="py-16 sm:py-24 px-6 md:px-12 lg:px-24 bg-white" id="pricing">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12 sm:mb-16">
                    <span class="text-primary font-bold tracking-widest uppercase text-[10px] sm:text-xs">{{ $settings['pricing']['subtitle'] ?? 'Investment in Quality' }}</span>
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-on-surface mt-4">{{ $settings['pricing']['heading'] ?? 'Transparent Pricing' }}</h2>
                    <p class="text-on-surface-variant mt-4 text-base sm:text-lg">{{ $settings['pricing']['desc'] ?? 'Choose the perfect care plan for your wardrobe.' }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    @foreach($settings['pricing']['plans'] ?? [] as $plan)
                        @php $isPopular = $plan['popular'] ?? false; @endphp
                        <div class="bg-white p-8 sm:p-10 rounded-3xl flex flex-col {{ $isPopular ? 'border-2 border-primary relative shadow-2xl md:scale-105 z-10' : 'border border-outline-variant/30 hover:shadow-2xl' }} transition-all duration-500">
                            @if($isPopular)
                                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary text-on-primary px-4 sm:px-6 py-1 rounded-full text-[9px] sm:text-[10px] font-bold tracking-wide uppercase">
                                    MOST POPULAR
                                </div>
                            @endif
                            <div class="mb-6 sm:mb-8">
                                <h3 class="text-xl sm:text-2xl font-bold text-on-surface mb-2">{{ $plan['name'] }}</h3>
                                <p class="text-on-surface-variant text-xs sm:text-sm">{{ $plan['subtitle'] }}</p>
                            </div>
                            <div class="mb-6 sm:mb-8">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-3xl sm:text-4xl font-extrabold text-primary">{{ $plan['price'] }}</span>
                                    <span class="text-on-surface-variant font-medium text-xs sm:text-sm">/kg</span>
                                </div>
                            </div>
                            <ul class="space-y-3 sm:space-y-4 mb-8 sm:mb-10 flex-grow">
                                @foreach($plan['features'] ?? [] as $feature)
                                    <li class="flex items-start gap-3">
                                        <span class="material-symbols-outlined text-primary text-lg sm:text-xl">check_circle</span>
                                        <span class="text-on-surface-variant text-xs sm:text-sm @if($loop->first && $plan['name'] !== 'Essential') font-medium @endif">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('login') }}" class="w-full py-3 sm:py-4 text-center rounded-xl sm:rounded-full {{ $isPopular ? 'bg-primary text-on-primary shadow-lg hover:scale-[1.02]' : 'border-2 border-primary text-primary hover:bg-primary hover:text-on-primary' }} font-bold active:scale-[0.98] transition-all text-sm">
                                Select Plan
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-16 sm:py-24 px-6 md:px-12 lg:px-24 bg-gray-50/50" id="faq">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12 sm:mb-16">
                    <span class="text-primary font-bold tracking-widest uppercase text-[10px] sm:text-xs">{{ $settings['faqs']['subtitle'] ?? 'Common Questions' }}</span>
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-on-surface mt-4">{{ $settings['faqs']['heading'] ?? 'Frequently Asked Questions' }}</h2>
                </div>

                <div class="space-y-4" x-data="{ activeFaq: null }">
                    @php
                        $defaultFaqs = [
                            [
                                'question' => 'How do I schedule a pickup?',
                                'answer' => 'You can schedule a pickup by clicking the "Order Now" button, choosing your service, and selecting a time slot that works for you.'
                            ],
                            [
                                'question' => 'What is your turnaround time?',
                                'answer' => 'Our standard turnaround time is 24-48 hours depending on the plan you choose. Our Premium and Executive plans guarantee 24h delivery.'
                            ],
                            [
                                'question' => 'Do you use eco-friendly detergents?',
                                'answer' => 'Yes, we use dermatologically tested, plant-based solutions that are gentle on your skin and the environment.'
                            ],
                            [
                                'question' => 'How do I pay for the service?',
                                'answer' => 'We accept various payment methods including credit/debit cards, bank transfers, and digital wallets through our secure payment gateway.'
                            ],
                            [
                                'question' => 'What if I am not home during pickup/delivery?',
                                'answer' => 'You can leave instructions for our valet to collect or drop off your laundry at a safe location, like with a concierge or at your doorstep.'
                            ],
                        ];
                        $faqs = $settings['faqs']['items'] ?? $defaultFaqs;
                    @endphp

                    @foreach($faqs as $i => $faq)
                        @if(!empty($faq['question']) && !empty($faq['answer']))
                            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden transition-all duration-300" 
                                 :class="activeFaq === {{ $i }} ? 'shadow-lg ring-1 ring-primary/10' : 'hover:shadow-md'">
                                <button 
                                    @click="activeFaq = (activeFaq === {{ $i }} ? null : {{ $i }})"
                                    class="w-full px-6 py-5 sm:px-8 sm:py-6 text-left flex justify-between items-center gap-4"
                                >
                                    <span class="font-bold text-on-surface text-base sm:text-lg leading-tight">{{ $faq['question'] }}</span>
                                    <span class="material-symbols-outlined text-primary transition-transform duration-300"
                                          :class="activeFaq === {{ $i }} ? 'rotate-180' : ''">
                                        expand_more
                                    </span>
                                </button>
                                <div 
                                    x-show="activeFaq === {{ $i }}"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-cloak
                                >
                                    <div class="px-6 pb-6 sm:px-8 sm:pb-8 text-on-surface-variant text-sm sm:text-base leading-relaxed border-t border-gray-50 pt-4">
                                        {{ $faq['answer'] }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-16 sm:py-24 px-6 md:px-12 lg:px-24 bg-white" id="reviews">
            <div class="max-w-7xl mx-auto" x-data="{
                currentSlide: 0,
                totalSlides: 2,
                isAnimating: false,
                direction: 'next',
                goNext() {
                    if (this.isAnimating) return;
                    this.isAnimating = true;
                    this.direction = 'next';
                    this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                    setTimeout(() => this.isAnimating = false, 500);
                },
                goPrev() {
                    if (this.isAnimating) return;
                    this.isAnimating = true;
                    this.direction = 'prev';
                    this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                    setTimeout(() => this.isAnimating = false, 500);
                }
            }">
                <div class="flex flex-col md:flex-row justify-between items-center md:items-end mb-12 sm:mb-16 text-center md:text-left gap-6">
                    <div class="max-w-xl">
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-on-surface">{{ $settings['reviews']['heading'] ?? 'Trusted by Thousands' }}</h2>
                        <p class="text-on-surface-variant mt-4 text-sm sm:text-base italic">{{ $settings['reviews']['subtitle'] ?? 'See why LAUNDRYAN is the highest-rated garment care service in the city.' }}</p>
                    </div>
                    <div class="flex gap-4">
                        <button @click="goPrev()"
                            class="w-12 h-12 sm:w-14 sm:h-14 rounded-full border border-outline-variant flex items-center justify-center hover:bg-surface-container-low transition-all shadow-sm hover:scale-105 active:scale-95">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </button>
                        <button @click="goNext()"
                            class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-lg hover:scale-105 active:scale-95 transition-transform">
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    </div>
                </div>

                <!-- Dot indicators -->
                <div class="flex justify-center gap-2 mb-8">
                    <template x-for="i in totalSlides" :key="i">
                        <button @click="currentSlide = i - 1"
                            :class="currentSlide === i - 1 ? 'bg-primary w-6' : 'bg-gray-200 w-2'"
                            class="h-2 rounded-full transition-all duration-300">
                        </button>
                    </template>
                </div>

                <div class="overflow-hidden relative">
                    @php
                        $allReviews = $settings['reviews']['items'] ?? [];
                        // Tambah 3 review baru
                        $extraReviews = [
                            ['name' => 'Budi Santoso', 'role' => 'Software Engineer', 'text' => 'Sangat puas dengan layanan LAUNDRYAN! Pakaian saya dikembalikan dalam kondisi bersih, rapi, dan harum. Pengiriman juga tepat waktu. Highly recommended!', 'avatar' => 'https://ui-avatars.com/api/?name=Budi+Santoso&background=005bc0&color=fff'],
                            ['name' => 'Dewi Rahayu', 'role' => 'Dokter Umum', 'text' => 'Layanan laundry terbaik yang pernah saya gunakan. Jas dokter saya selalu dikembalikan dengan setrika yang sempurna. Tidak perlu khawatir soal kebersihan!', 'avatar' => 'https://ui-avatars.com/api/?name=Dewi+Rahayu&background=10B981&color=fff'],
                            ['name' => 'Ahmad Fauzi', 'role' => 'Pengusaha Muda', 'text' => 'Harga terjangkau dengan kualitas premium. Fitur pickup dan delivery sangat membantu rutinitas saya yang padat. LAUNDRYAN adalah solusi laundry terbaik!', 'avatar' => 'https://ui-avatars.com/api/?name=Ahmad+Fauzi&background=F59E0B&color=fff'],
                        ];
                        $allReviews = array_merge($allReviews, $extraReviews);
                        $slides = array_chunk($allReviews, 3);
                    @endphp

                    @foreach($slides as $slideIndex => $slideReviews)
                        <div x-show="currentSlide === {{ $slideIndex }}"
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 translate-x-full"
                             x-transition:enter-end="opacity-100 translate-x-0"
                             x-transition:leave="transition ease-in duration-400"
                             x-transition:leave-start="opacity-100 translate-x-0"
                             x-transition:leave-end="opacity-0 -translate-x-full"
                             class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8"
                             style="display: none;">
                            @foreach($slideReviews as $review)
                                <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-50 flex flex-col hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                    <div class="flex gap-1 text-primary mb-6">
                                        @foreach(range(1, 5) as $star)
                                            <span class="material-symbols-outlined text-sm sm:text-base" style="font-variation-settings: 'FILL' 1;">star</span>
                                        @endforeach
                                    </div>
                                    <p class="text-on-surface mb-8 italic text-sm sm:text-base leading-relaxed grow">
                                        "{{ $review['text'] }}"
                                    </p>
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $review['avatar'] }}" alt="{{ $review['name'] }}" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover shadow-sm ring-2 ring-gray-50">
                                        <div>
                                            <p class="font-bold text-sm sm:text-base">{{ $review['name'] }}</p>
                                            <p class="text-[10px] sm:text-xs text-on-surface-variant font-medium">{{ $review['role'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Location Section -->
        @php
            $mapIframe = $settings['location']['map_iframe'] ?? '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126748.77506686!2d106.4774561!3d-6.1632146!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69fb5f1f282bf1%3A0xa3ca0c14b3e07736!2sKabupaten%20Tangerang%2C%20Banten!5e0!3m2!1sid!2sid!4v1700000000002!5m2!1sid!2sid" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';

            // Extract src URL from iframe tag
            $mapUrl = '';
            if (preg_match('/src=["\']([^"\']+)["\']/', $mapIframe, $matches)) {
                $mapUrl = $matches[1];
            }

            // Extract coordinates from Google Maps embed pb token (supports negative values)
            // !2d = longitude, !3d = latitude
            $latitude  = null;
            $longitude = null;
            if (preg_match('/[!,]2d(-?[0-9]+\.?[0-9]*)/', $mapUrl, $lonMatch) &&
                preg_match('/[!,]3d(-?[0-9]+\.?[0-9]*)/', $mapUrl, $latMatch)) {
                $longitude = $lonMatch[1];
                $latitude  = $latMatch[1];
            }

            $address = $settings['footer']['address'] ?? 'Kabupaten Tangerang, Banten';

            if ($latitude !== null && $longitude !== null) {
                // Use coordinates for precise pin
                $googleMapsLink = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($latitude . ',' . $longitude);
            } else {
                // Fallback to address text search
                $googleMapsLink = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address);
            }
        @endphp
        <section class="py-16 sm:py-24 px-6 md:px-12 lg:px-24 bg-white" id="location">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12 sm:mb-16">
                    <span class="text-primary font-bold tracking-widest uppercase text-[10px] sm:text-xs">{{ $settings['location']['subtitle'] ?? 'Our Outlet' }}</span>
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-on-surface mt-4 mb-4">{{ $settings['location']['heading'] ?? 'Visit Our Location' }}</h2>
                    <p class="text-on-surface-variant max-w-xl mx-auto mb-6 text-sm sm:text-base flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">location_on</span>
                        <span>{{ $address }}</span>
                    </p>
                    <div class="flex justify-center mb-8">
                        <a href="{{ $googleMapsLink }}" target="_blank" class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-full font-bold shadow-lg hover:scale-105 active:scale-95 transition-all text-xs uppercase tracking-wider font-sans">
                            <span class="material-symbols-outlined text-sm">map</span>
                            Visit Our Location
                        </a>
                    </div>
                </div>

                <div class="rounded-[2rem] overflow-hidden shadow-2xl border-8 border-white ring-1 ring-gray-100 h-[400px] sm:h-[500px] relative">
                    <div class="w-full h-full [&>iframe]:w-full [&>iframe]:h-full">
                        {!! $mapIframe !!}
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="pt-12 pb-6 sm:pt-16 sm:pb-8 px-6 md:px-12 lg:px-24">
            <div class="max-w-5xl mx-auto bg-primary rounded-3xl p-10 sm:p-12 md:p-16 relative overflow-hidden text-center shadow-xl">
                <div class="absolute top-0 right-0 w-full h-full opacity-10 pointer-events-none -scale-x-100 lg:scale-x-100">
                    <svg class="w-full h-full" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                        <path d="M47.7,-62.4C60.2,-52.1,67.6,-36,71.2,-19.1C74.9,-2.1,74.7,15.6,67.7,30.3C60.7,45.1,46.9,56.8,31.2,64.2C15.6,71.5,-1.9,74.5,-18.9,70.5C-35.8,66.6,-52.2,55.8,-63.3,41C-74.4,26.2,-80.2,7.4,-77.4,-10.4C-74.6,-28.1,-63.2,-44.8,-48.6,-54.6C-34,-64.4,-17,-67.2,0.4,-67.8C17.8,-68.3,35.2,-72.7,47.7,-62.4Z" fill="white" transform="translate(200 200)"></path>
                    </svg>
                </div>
                <div class="relative z-10 max-w-2xl mx-auto">
                    <h2 class="text-white text-3xl sm:text-4xl md:text-5xl font-extrabold mb-6 leading-tight">{{ $settings['footer']['cta_title'] ?? 'Ready for a spotless week?' }}</h2>
                    <p class="text-white/80 text-base sm:text-lg mb-10">{{ $settings['footer']['cta_subtitle'] ?? 'Join 10,000+ happy customers and schedule your first pickup today. Get 20% off your first order.' }}</p>
                    <a href="{{ route('login') }}" class="inline-block bg-white text-primary px-8 sm:px-12 py-4 sm:py-5 rounded-full text-lg font-bold hover:scale-105 transition-transform shadow-2xl">
                        {{ $settings['footer']['cta_button'] ?? 'Schedule Your Pickup' }}
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white pt-24 pb-12 px-6 md:px-12 lg:px-24 border-t border-gray-100 font-['Plus_Jakarta_Sans'] relative z-10">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-20">
                <!-- Brand & Mission -->
                <div class="space-y-6">
                    <a href="/" class="flex items-center">
                        @if(!empty($settings['site']['logo_url']))
                            <img src="{{ $settings['site']['logo_url'] }}" alt="{{ $settings['site']['name'] ?? 'LAUNDRYAN' }}" class="h-10 w-auto object-contain">
                        @else
                            <div class="text-2xl font-black tracking-tighter text-primary uppercase">{{ $settings['site']['name'] ?? 'LAUNDRYAN' }}</div>
                        @endif
                    </a>
                    <p class="text-on-surface-variant text-sm leading-relaxed max-w-xs">
                        {{ $settings['footer']['mission'] ?? 'Providing premium garment care since 2024. We combine eco-friendly technology with artisan precision to give your clothes the love they deserve.' }}
                    </p>
                    <div class="flex gap-4">
                        <!-- Facebook -->
                        <a href="{{ $settings['footer']['facebook_url'] ?? '#' }}" target="_blank" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-white transition-all duration-300 group">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.03 1.764-5.908 5.43-5.908 1.489 0 2.242.1 2.614.157v2.999h-1.706c-1.519 0-2.041.59-2.041 2.177v2.075h3.35l-.56 3.667h-2.79v7.98H9.101z"/>
                            </svg>
                        </a>
                        <!-- Instagram -->
                        <a href="{{ $settings['footer']['instagram_url'] ?? '#' }}" target="_blank" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-white transition-all duration-300">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <!-- X (Twitter) -->
                        <a href="{{ $settings['footer']['x_url'] ?? '#' }}" target="_blank" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-white transition-all duration-300">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932L18.901 1.153zM17.61 20.644h2.039L6.486 3.24H4.298l13.312 17.404z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-on-surface font-bold mb-6">Explore</h4>
                    <ul class="space-y-4">
                        <li><a href="#services" class="text-on-surface-variant hover:text-primary text-sm transition-colors">Services</a></li>
                        <li><a href="#process" class="text-on-surface-variant hover:text-primary text-sm transition-colors">How it Works</a></li>
                        <li><a href="#pricing" class="text-on-surface-variant hover:text-primary text-sm transition-colors">Pricing Plans</a></li>
                        <li><a href="#faq" class="text-on-surface-variant hover:text-primary text-sm transition-colors">FAQ</a></li>
                        <li><a href="#reviews" class="text-on-surface-variant hover:text-primary text-sm transition-colors">Testimonials</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div>
                    <h4 class="text-on-surface font-bold mb-6">Our Services</h4>
                    <ul class="space-y-4">
                        <li><a href="#services" class="text-on-surface-variant hover:text-primary text-sm transition-colors">Wash & Fold</a></li>
                        <li><a href="#services" class="text-on-surface-variant hover:text-primary text-sm transition-colors">Dry Cleaning</a></li>
                        <li><a href="#services" class="text-on-surface-variant hover:text-primary text-sm transition-colors">Steam Ironing</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-on-surface font-bold mb-6">Get in Touch</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary text-xl">location_on</span>
                            <span class="text-on-surface-variant text-sm">{{ $settings['footer']['address'] ?? '123 Fresh Lane, Spotless District, Jakarta 12345' }}</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary text-xl">call</span>
                            <span class="text-on-surface-variant text-sm">{{ $settings['footer']['phone'] ?? '+62 21 555 0123' }}</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary text-xl">mail</span>
                            <span class="text-on-surface-variant text-sm">{{ $settings['footer']['email'] ?? 'hello@laundryan.com' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="pt-8 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-on-surface-variant text-xs">
                    {{ $settings['footer']['copyright'] ?? '© 2024 LAUNDRYAN. All rights reserved.' }}
                </p>
                <div class="flex gap-8">
                    <a href="#" class="text-on-surface-variant hover:text-primary text-xs transition-colors">Privacy Policy</a>
                    <a href="#" class="text-on-surface-variant hover:text-primary text-xs transition-colors">Terms of Service</a>
                    <a href="#" class="text-on-surface-variant hover:text-primary text-xs transition-colors">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
