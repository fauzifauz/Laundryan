<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Account - {{ config('app.name', 'LAUNDRYAN') }}</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        @keyframes slideInLeft {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-slide-left { animation: slideInLeft 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-slide-right { animation: slideInRight 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-fade-up { animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.3s both; }
    </style>
</head>
<body class="antialiased bg-gray-50 overflow-x-hidden">
    <div class="min-h-screen flex flex-col lg:flex-row overflow-hidden lg:overflow-visible">
        <!-- Left Side: Branding & Image (Hidden on mobile/tablet) -->
        <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 bg-primary relative overflow-hidden items-center justify-center p-12 xl:p-24 animate-slide-left">
            <!-- Decorative Blobs -->
            <div class="absolute top-0 left-0 w-full h-full opacity-20 pointer-events-none">
                <svg class="w-full h-full" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                    <path d="M47.7,-62.4C60.2,-52.1,67.6,-36,71.2,-19.1C74.9,-2.1,74.7,15.6,67.7,30.3C60.7,45.1,46.9,56.8,31.2,64.2C15.6,71.5,-1.9,74.5,-18.9,70.5C-35.8,66.6,-52.2,55.8,-63.3,41C-74.4,26.2,-80.2,7.4,-77.4,-10.4C-74.6,-28.1,-63.2,-44.8,-48.6,-54.6C-34,-64.4,-17,-67.2,0.4,-67.8C17.8,-68.3,35.2,-72.7,47.7,-62.4Z" fill="white" transform="translate(200 200)"></path>
                </svg>
            </div>
            
            <div class="relative z-10 text-white max-w-lg">
                <a href="/" class="inline-block mb-12 hover:opacity-80 transition-opacity">
                    <x-application-logo class="h-16 w-auto fill-current text-white" />
                </a>
                <h1 class="text-4xl xl:text-6xl font-extrabold mb-8 leading-tight tracking-tight">{{ $settings['register']['left_title'] ?? 'Join the Revolution of Clean.' }}</h1>
                <p class="text-white/80 text-lg xl:text-xl leading-relaxed mb-12">{{ $settings['register']['left_subtitle'] ?? 'Create your account today and experience garment care that exceeds expectations, every single time.' }}</p>
                
                <div class="space-y-6 pt-10 border-t border-white/20">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                            <span class="material-symbols-outlined text-white">verified</span>
                        </div>
                        <p class="text-white font-semibold text-lg tracking-wide">Eco-friendly cleaning standards</p>
                    </div>
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                            <span class="material-symbols-outlined text-white">schedule</span>
                        </div>
                        <p class="text-white font-semibold text-lg tracking-wide">Real-time order tracking</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Register Form -->
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12 md:p-16 lg:p-20 bg-white overflow-y-auto animate-slide-right">
            <div class="w-full max-w-[520px] py-8 sm:py-12 animate-fade-up">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex justify-center mb-10">
                    <a href="/" class="inline-block">
                        <x-application-logo class="h-12 w-auto fill-current text-primary" />
                    </a>
                </div>

                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-3 tracking-tight">{{ $settings['register']['right_title'] ?? 'Create Account' }}</h2>
                    <p class="text-gray-500 text-base sm:text-lg">{{ $settings['register']['right_subtitle'] ?? 'Experience premium laundry services with ease.' }}</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <!-- Name -->
                    <div class="space-y-1">
                        <label for="name" class="text-sm font-semibold text-gray-700 ml-1">Full Name</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 group-focus-within:text-primary transition-colors">person</span>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus 
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none"
                                placeholder="Your Name">
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Email -->
                        <div class="space-y-1">
                            <label for="email" class="text-sm font-semibold text-gray-700 ml-1">Email</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 group-focus-within:text-primary transition-colors text-lg">mail</span>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required 
                                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-sm"
                                    placeholder="email@example.com">
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Phone -->
                        <div class="space-y-1" x-data="{ phone: '{{ old('phone') }}' }">
                            <label for="phone" class="text-sm font-semibold text-gray-700 ml-1">Phone</label>
                            <div class="relative group">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-1.5 pointer-events-none">
                                    <span class="material-symbols-outlined text-gray-400 group-focus-within:text-primary transition-colors text-lg">call</span>
                                    <span class="text-sm font-bold text-gray-500 border-r border-gray-200 pr-2">+62</span>
                                </div>
                                <input id="phone" type="text" name="phone" x-model="phone" required 
                                    @input="phone = phone.replace(/[^0-9]/g, '')"
                                    class="w-full pl-20 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-sm"
                                    placeholder="812...">
                            </div>
                            <div x-show="phone.length > 13" class="text-[10px] text-red-500 ml-2 mt-1 font-medium animate-pulse">
                                Warning: Phone number is usually not more than 13 digits.
                            </div>
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>


                    <!-- Password -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" x-data="{ showPass: false, showConfirm: false }">
                        <div class="space-y-1">
                            <label for="password" class="text-sm font-semibold text-gray-700 ml-1">Password</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 group-focus-within:text-primary transition-colors text-lg">lock</span>
                                <input id="password" :type="showPass ? 'text' : 'password'" name="password" required 
                                    class="w-full pl-11 pr-11 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-sm"
                                    placeholder="••••••••">
                                <button type="button" @click="showPass = !showPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-lg" x-text="showPass ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="space-y-1">
                            <label for="password_confirmation" class="text-sm font-semibold text-gray-700 ml-1">Confirm</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 group-focus-within:text-primary transition-colors text-lg">lock_reset</span>
                                <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required 
                                    class="w-full pl-11 pr-11 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-sm"
                                    placeholder="••••••••">
                                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-lg" x-text="showConfirm ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-4 bg-primary text-white font-bold rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 mt-4">
                        <span>Create Account</span>
                        <span class="material-symbols-outlined text-xl">person_add</span>
                    </button>

                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500 font-medium uppercase tracking-wider">Or register with</span>
                        </div>
                    </div>

                    <a href="{{ route('auth.google') }}" class="w-full py-4 bg-white border-2 border-gray-100 text-gray-700 font-bold rounded-2xl hover:bg-gray-50 hover:border-gray-200 transition-all flex items-center justify-center gap-3">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span>Register with Google</span>
                    </a>

                    <!-- Footer Links -->
                    <p class="text-center text-gray-500 text-sm mt-8">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="text-primary font-bold hover:underline">Sign in instead</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
