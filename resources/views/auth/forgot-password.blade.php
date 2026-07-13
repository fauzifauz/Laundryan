<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - {{ config('app.name', 'LAUNDRYAN') }}</title>
    
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
                <h1 class="text-4xl xl:text-6xl font-extrabold mb-8 leading-tight tracking-tight">{{ $settings['forgot_password']['left_title'] ?? 'Reset Your Password.' }}</h1>
                <p class="text-white/80 text-lg xl:text-xl leading-relaxed mb-12">{{ $settings['forgot_password']['left_subtitle'] ?? 'No worries — enter your email and we\'ll send a secure link to get you back in.' }}</p>
                
                <div class="flex items-center gap-6 pt-10 border-t border-white/20">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                        <span class="material-symbols-outlined text-white text-3xl">lock_reset</span>
                    </div>
                    <div>
                        <p class="text-white font-bold text-xl tracking-wide">Secure Recovery</p>
                        <p class="text-white/60 text-sm uppercase tracking-widest font-semibold">Encrypted Process</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Forgot Password Form -->
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12 md:p-16 lg:p-20 bg-white animate-slide-right">
            <div class="w-full max-w-[440px] animate-fade-up">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex justify-center mb-10">
                    <a href="/" class="inline-block">
                        <x-application-logo class="h-12 w-auto fill-current text-primary" />
                    </a>
                </div>

                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">{{ $settings['forgot_password']['right_title'] ?? 'Forgot Password?' }}</h2>
                    <p class="text-gray-500 text-base sm:text-lg leading-relaxed">
                        {{ $settings['forgot_password']['right_subtitle'] ?? 'We\'ll email you a link to reset your password.' }}
                    </p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-6 p-4 bg-green-50 text-green-700 rounded-2xl text-sm font-medium border border-green-100" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <!-- Email Address -->
                    <div class="space-y-1">
                        <label for="email" class="text-sm font-semibold text-gray-700 ml-1">Email Address</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 group-focus-within:text-primary transition-colors">mail</span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                                class="w-full pl-12 pr-4 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none"
                                placeholder="name@example.com">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-4 bg-primary text-white font-bold rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <span>Send Reset Link</span>
                        <span class="material-symbols-outlined text-xl">send</span>
                    </button>

                    <!-- Back to Login -->
                    <div class="text-center mt-8">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-primary font-bold hover:underline">
                            <span class="material-symbols-outlined text-base">arrow_back</span>
                            <span>Back to Sign In</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
