<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'LAUNDRYAN') }}</title>
    
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
                <h1 class="text-4xl xl:text-6xl font-extrabold mb-8 leading-tight tracking-tight">Welcome Back to Laundryan.</h1>
                <p class="text-white/80 text-lg xl:text-xl leading-relaxed mb-12">Reclaim your time while we handle your garments with the gold standard of cleaning technology.</p>
                
                <div class="grid grid-cols-2 gap-8 pt-10 border-t border-white/20">
                    <div>
                        <p class="text-3xl font-bold tracking-tighter">10k+</p>
                        <p class="text-white/60 text-sm uppercase tracking-widest font-semibold">Customers</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold tracking-tighter">24h</p>
                        <p class="text-white/60 text-sm uppercase tracking-widest font-semibold">Turnaround</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12 md:p-16 lg:p-20 bg-white animate-slide-right">
            <div class="w-full max-w-[440px] animate-fade-up">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex justify-center mb-10">
                    <a href="/" class="inline-block">
                        <x-application-logo class="h-12 w-auto fill-current text-primary" />
                    </a>
                </div>

                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-3 tracking-tight">Sign In</h2>
                    <p class="text-gray-500 text-base sm:text-lg">Access your premium laundry dashboard.</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Global Error Alert -->
                @if (session('error') || $errors->has('error'))
                    <div class="mb-8 overflow-hidden rounded-2xl bg-white shadow-xl shadow-red-100/50 border border-red-100 animate-fade-up">
                        <div class="flex">
                            <div class="flex-shrink-0 w-1.5 bg-red-500"></div>
                            <div class="p-4 flex items-center gap-4">
                                <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center">
                                    <span class="material-symbols-outlined text-red-500 text-2xl">warning</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-red-900 mb-0.5">Security Alert</p>
                                    <p class="text-xs text-red-600 font-medium leading-relaxed">
                                        {{ session('error') ?? $errors->first('error') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
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

                    <!-- Password -->
                    <div class="space-y-1">
                        <div class="flex justify-between items-center px-1">
                            <label for="password" class="text-sm font-semibold text-gray-700">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-bold text-primary hover:underline">Forgot?</a>
                            @endif
                        </div>
                        <div class="relative group" x-data="{ show: false }">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 group-focus-within:text-primary transition-colors">lock</span>
                            <input id="password" :type="show ? 'text' : 'password'" name="password" required 
                                class="w-full pl-12 pr-12 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined" x-text="show ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Math Captcha -->
                    <div class="space-y-1">
                        <label for="captcha" class="text-sm font-semibold text-gray-700 ml-1">Verification: {{ $num1 }} + {{ $num2 }} = ?</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 group-focus-within:text-primary transition-colors">calculate</span>
                            <input id="captcha" type="number" name="captcha" required 
                                class="w-full pl-12 pr-4 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none"
                                placeholder="Answer">
                        </div>
                        <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center ml-1">
                        <input id="remember_me" type="checkbox" name="remember" class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary/20 transition-all cursor-pointer">
                        <label for="remember_me" class="ml-3 text-sm text-gray-600 cursor-pointer select-none">Remember this device</label>
                    </div>

                    <button type="submit" :disabled="loading" 
                        class="w-full py-4 bg-primary text-white font-bold rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-wait">
                        <span x-show="!loading">Log In</span>
                        <span x-show="!loading" class="material-symbols-outlined text-xl">arrow_forward</span>
                        
                        <!-- Spinner -->
                        <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="loading">Authenticating...</span>
                    </button>

                    <div class="relative my-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500 font-medium uppercase tracking-wider">Or continue with</span>
                        </div>
                    </div>

                    <a href="{{ route('auth.google') }}" class="w-full py-4 bg-white border-2 border-gray-100 text-gray-700 font-bold rounded-2xl hover:bg-gray-50 hover:border-gray-200 transition-all flex items-center justify-center gap-3">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span>Sign in with Google</span>
                    </a>

                    <!-- Footer Links -->
                    <p class="text-center text-gray-500 text-sm mt-8">
                        Don't have an account? 
                        <a href="{{ route('register') }}" class="text-primary font-bold hover:underline">Sign up for free</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Clear onboarding flag to force the tour to start again upon next login/session
        sessionStorage.removeItem('onboarding_tour_played');
    </script>
</body>
</html>
