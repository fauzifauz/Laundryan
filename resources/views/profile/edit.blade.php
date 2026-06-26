<x-app-layout>
    <!-- Global Toast Notification -->
    @if(session('success') || session('info'))
        @php
            $toastTitle = session('success') ? 'Changes Saved' : 'No Changes';
            $toastMsg = session('success') ?: session('info');
            $toastBg = session('success') ? 'from-emerald-600 to-teal-600 shadow-emerald-100' : 'from-blue-600 to-indigo-600 shadow-blue-100';
            $toastIcon = session('success') ? 'check_circle' : 'info';
        @endphp
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed top-6 right-6 z-50 max-w-sm w-full bg-gradient-to-r {{ $toastBg }} rounded-3xl p-5 shadow-2xl text-white flex items-center justify-between overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-10 h-10 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center shadow-inner">
                    <span class="material-symbols-outlined text-white text-xl">{{ $toastIcon }}</span>
                </div>
                <div>
                    <h4 class="font-black text-xs uppercase tracking-wider">{{ $toastTitle }}</h4>
                    <p class="text-[11px] text-white/95 font-medium mt-0.5">{{ $toastMsg }}</p>
                </div>
            </div>
            <button @click="show = false" class="text-white/60 hover:text-white transition-colors p-2 rounded-xl hover:bg-white/10 relative z-10">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Edit Profile</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Manage your account information and personal details.</p>
            </div>
            <div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if($errors->any() && !$errors->hasAny(['photo', 'name', 'email', 'phone', 'address', 'current_password', 'password', 'password_confirmation']))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl space-y-2 shadow-xs" role="alert">
                <div class="flex items-center gap-3 font-bold text-sm">
                    <span class="material-symbols-outlined text-rose-600">error</span>
                    <span>Validation failed. Please check the input fields:</span>
                </div>
                <ul class="list-disc list-inside text-xs pl-8 font-semibold text-rose-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Unified Form wrapping the columns -->
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" 
              x-data="{ 
                  photoPreview: '{{ $user->photo ? asset('storage/' . $user->photo) : '' }}', 
                  removePhoto: false, 
                  password: '', 
                  password_confirmation: '', 
                  current_password: '',
                  showCurrent: false,
                  showNew: false,
                  showConfirm: false
              }" 
              class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            @csrf
            @method('patch')

            <!-- Hidden remove photo flag -->
            <input type="hidden" name="remove_photo" :value="removePhoto ? 1 : 0">

            <!-- COLUMN LEFT: Profile Overview Card -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Validation Errors for Profile Photo -->
                @if($errors->has('photo'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl space-y-2 shadow-xs mb-4" role="alert">
                        <div class="flex items-center gap-3 font-bold text-sm">
                            <span class="material-symbols-outlined text-rose-600">error</span>
                            <span>Validation failed. Please check the input fields:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs pl-8 font-semibold text-rose-700 space-y-1">
                            @foreach($errors->get('photo') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm flex flex-col items-center text-center relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/30 rounded-full blur-2xl -mr-10 -mt-10 transition-all group-hover:bg-blue-50/50 duration-500 pointer-events-none"></div>
                    
                    <div class="w-full flex justify-between items-center mb-6 relative z-10">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Profile Photo</span>
                            @if(session('photo_success'))
                                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                                     x-transition:leave="transition ease-in duration-300"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="flex items-center gap-1 px-2 py-0.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-full text-[9px] font-bold shadow-2xs">
                                    <span class="material-symbols-outlined text-[10px] text-emerald-600 font-black">check_circle</span>
                                    <span>{{ session('photo_success') }}</span>
                                </div>
                            @endif
                        </div>
                        <span class="px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest rounded-full {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }} shadow-2xs">
                            {{ $user->status === 'active' ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <!-- Circular Avatar Frame -->
                    <div class="relative mb-6 z-10">
                        <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg bg-gray-50 flex items-center justify-center transition-all hover:scale-105 duration-300">
                            <!-- Show new preview / existing photo if not marked for removal -->
                            <template x-if="photoPreview && !removePhoto">
                                <img :src="photoPreview" class="w-full h-full object-cover">
                            </template>
                            <!-- Show fallback avatar when no photo exists or photo is removed -->
                            <template x-if="!photoPreview || removePhoto">
                                <div class="w-full h-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-4xl">
                                    <span>{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Account Status Badge Overlay -->
                        <span class="absolute bottom-1 right-1 block w-5 h-5 rounded-full border-4 border-white shadow-md z-20 {{ $user->status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"
                              title="Status: {{ ucfirst($user->status) }}"></span>
                    </div>

                    <!-- Brief User Information -->
                    <div class="space-y-1 relative z-10 mb-6 w-full">
                        <h3 class="text-lg font-black text-gray-900 leading-tight truncate px-2">{{ $user->name }}</h3>
                        <p class="text-xs text-gray-400 font-bold truncate px-2">{{ $user->email }}</p>
                        
                        <div class="flex justify-center items-center pt-3">
                            <span class="px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full bg-blue-50 text-blue-600 border border-blue-100 shadow-2xs">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                    </div>

                    <!-- Photo Upload and Delete Actions -->
                    <div class="w-full space-y-2.5 relative z-10">
                        <input type="file" id="photo" name="photo" accept="image/*" class="hidden"
                               @change="const file = $event.target.files[0]; if (file) { photoPreview = URL.createObjectURL(file); removePhoto = false; }">
                        
                        <button type="button" @click="document.getElementById('photo').click()"
                                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-md shadow-blue-200 hover:shadow-lg transition-all flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">cloud_upload</span> Upload New Photo
                        </button>

                        <button type="button" x-show="photoPreview && !removePhoto" @click="removePhoto = true; photoPreview = ''"
                                class="w-full py-3 bg-white hover:bg-rose-50 text-rose-600 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-rose-100 hover:border-rose-200 transition-all flex items-center justify-center gap-2 hover:shadow-xs cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">delete</span> Remove Photo
                        </button>
                    </div>
                </div>

                <!-- Card 2: Keamanan Sesi & Aktivitas Terbaru -->
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-6">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-gray-50">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <span class="material-symbols-outlined text-[20px]">security</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Activity & Session</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Login details and history of your actions</p>
                        </div>
                    </div>

                    <!-- Current Session Details -->
                    <div class="space-y-3">
                        <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Current Active Session</span>
                        
                        <div class="bg-gray-50/50 border border-gray-100 p-4 rounded-2xl space-y-3 shadow-2xs">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white border border-gray-100 flex items-center justify-center text-gray-400">
                                    <span class="material-symbols-outlined text-[18px]">devices</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Device & Browser</span>
                                    <span class="block text-xs font-bold text-gray-700 truncate">{{ $currentDevice }} / {{ $currentBrowser }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white border border-gray-100 flex items-center justify-center text-gray-400">
                                    <span class="material-symbols-outlined text-[18px]">lan</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">IP Address</span>
                                    <span class="block text-xs font-bold text-gray-700 truncate">{{ $currentIp }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities Logs -->
                    <div class="space-y-3">
                        <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Activity History</span>
                        
                        <div class="space-y-3.5">
                            @forelse($recentActivities as $activity)
                                <div class="flex items-start gap-3">
                                    @php
                                        $icon = 'info';
                                        $colorClass = 'bg-gray-50 text-gray-500 border border-gray-100';
                                        
                                        if (str_contains($activity->activity_type, 'Password')) {
                                            $icon = 'vpn_key';
                                            $colorClass = 'bg-amber-50 text-amber-600 border border-amber-100';
                                        } elseif (str_contains($activity->activity_type, 'Profile') || str_contains($activity->activity_type, 'User')) {
                                            $icon = 'person';
                                            $colorClass = 'bg-blue-50 text-blue-600 border border-blue-100';
                                        } elseif (str_contains(strtolower($activity->activity_type), 'login')) {
                                            $icon = 'login';
                                            $colorClass = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
                                        }
                                    @endphp
                                    
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $colorClass }}">
                                        <span class="material-symbols-outlined text-[16px]">{{ $icon }}</span>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-gray-700 leading-snug break-words">
                                            {{ $activity->description }}
                                        </p>
                                        <span class="block text-[9px] font-semibold text-gray-400 mt-0.5">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 border border-dashed border-gray-200 rounded-2xl">
                                    <span class="material-symbols-outlined text-gray-300 text-[28px] mb-1">history</span>
                                    <p class="text-xs font-bold text-gray-400">No activity recorded yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Card 3: Metrik Akun & Bantuan -->
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-4">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-gray-50">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <span class="material-symbols-outlined text-[20px]">info</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Account Metrics</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Registration details and system support</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Date Joined -->
                        <div class="flex items-center justify-between text-xs py-1">
                            <div class="flex items-center gap-2 text-gray-400 font-bold uppercase tracking-wider text-[9px]">
                                <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                                <span>Joined Since</span>
                            </div>
                            <span class="font-bold text-gray-700">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                        </div>

                        <!-- Last Updated -->
                        <div class="flex items-center justify-between text-xs py-1">
                            <div class="flex items-center gap-2 text-gray-400 font-bold uppercase tracking-wider text-[9px]">
                                <span class="material-symbols-outlined text-[16px]">update</span>
                                <span>Profile Update</span>
                            </div>
                            <span class="font-bold text-gray-700">{{ $lastProfileUpdate ? $lastProfileUpdate->format('d M Y - H:i') : 'Never' }}</span>
                        </div>

                        <!-- Support Desk -->
                        <div class="pt-3 border-t border-gray-50 text-center">
                            <p class="text-[10px] font-bold text-gray-400">Need help regarding your account?</p>
                            <a href="mailto:{{ $supportEmail }}" class="inline-flex items-center gap-1 mt-1.5 text-xs font-black text-blue-600 hover:text-blue-700 transition-colors" title="Contact {{ $supportEmail }}">
                                <span class="material-symbols-outlined text-[14px]">mail</span> Contact IT Helpdesk
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMN RIGHT: Data & Password Form Fields -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Validation Errors for Personal Information -->
                @if($errors->hasAny(['name', 'email', 'phone', 'address']))
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl space-y-2 shadow-xs mb-4" role="alert">
                        <div class="flex items-center gap-3 font-bold text-sm">
                            <span class="material-symbols-outlined text-rose-600">error</span>
                            <span>Validation failed. Please check the input fields:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs pl-8 font-semibold text-rose-700 space-y-1">
                            @foreach(['name', 'email', 'phone', 'address'] as $field)
                                @foreach($errors->get($field) as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- SECTION 2: Data Pribadi -->
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-50">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <span class="material-symbols-outlined text-[20px]">person_edit</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900">Personal Information</h3>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Update your identity details and contact information</p>
                            </div>
                        </div>
                        @if(session('personal_success'))
                            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                                 x-transition:leave="transition ease-in duration-300"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm">
                                <span class="material-symbols-outlined text-[14px] text-emerald-600 font-black">check_circle</span>
                                <span>{{ session('personal_success') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Full Name -->
                        <div class="space-y-1.5">
                            <label for="name" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">person</span> Full Name
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3.5 px-4 transition-all"
                                   placeholder="Your Full Name">
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label for="email" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">mail</span> Email Address
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3.5 px-4 transition-all"
                                   placeholder="email@example.com">
                        </div>

                        <!-- Phone -->
                        <div class="space-y-1.5 md:col-span-2">
                            <label for="phone" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">phone_iphone</span> Phone Number
                            </label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3.5 px-4 transition-all"
                                   placeholder="Example: +628123456789">
                        </div>

                        <!-- Address -->
                        <div class="space-y-1.5 md:col-span-2">
                            <label for="address" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">home_pin</span> Address
                            </label>
                            <textarea id="address" name="address" rows="3"
                                      class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3.5 px-4 resize-none transition-all leading-relaxed"
                                      placeholder="Write your complete residential address...">{{ old('address', $user->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Validation Errors for Account Security -->
                @if($errors->hasAny(['current_password', 'password', 'password_confirmation']))
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl space-y-2 shadow-xs mb-4" role="alert">
                        <div class="flex items-center gap-3 font-bold text-sm">
                            <span class="material-symbols-outlined text-rose-600">error</span>
                            <span>Validation failed. Please check the input fields:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs pl-8 font-semibold text-rose-700 space-y-1">
                            @foreach(['current_password', 'password', 'password_confirmation'] as $field)
                                @foreach($errors->get($field) as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- SECTION 3: Keamanan Akun -->
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-50">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <span class="material-symbols-outlined text-[20px]">lock</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900">Account Security</h3>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Change your password to maintain account security</p>
                            </div>
                        </div>
                        @if(session('password_success'))
                            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                                 x-transition:leave="transition ease-in duration-300"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm">
                                <span class="material-symbols-outlined text-[14px] text-emerald-600 font-black">check_circle</span>
                                <span>{{ session('password_success') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-5">
                        <!-- Password Lama -->
                        <div class="space-y-1.5">
                            <label for="current_password" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">vpn_key</span> Current Password
                            </label>
                            <div class="relative">
                                <input :type="showCurrent ? 'text' : 'password'" id="current_password" name="current_password" x-model="current_password"
                                       class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3.5 pl-4 pr-12 transition-all"
                                       placeholder="Enter current password">
                                <button type="button" @click="showCurrent = !showCurrent" 
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]" x-text="showCurrent ? 'visibility_off' : 'visibility'">visibility</span>
                                </button>
                            </div>
                        </div>

                        <!-- Password Baru -->
                        <div class="space-y-1.5">
                            <label for="password" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">lock_open</span> New Password
                            </label>
                            <div class="relative">
                                <input :type="showNew ? 'text' : 'password'" id="password" name="password" x-model="password"
                                       class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3.5 pl-4 pr-12 transition-all"
                                       placeholder="Enter new password">
                                <button type="button" @click="showNew = !showNew" 
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]" x-text="showNew ? 'visibility_off' : 'visibility'">visibility</span>
                                </button>
                            </div>
                            
                            <!-- Real-time Password Strength / Validation Info -->
                            <div class="pt-2 flex items-center gap-2" x-show="password.length > 0">
                                <div class="w-4 h-4 rounded-full flex items-center justify-center transition-all text-white"
                                     :class="password.length >= 8 ? 'bg-emerald-500' : 'bg-rose-500'">
                                    <span class="material-symbols-outlined text-[10px] font-black" x-text="password.length >= 8 ? 'check' : 'close'"></span>
                                </div>
                                <span class="text-[10px] font-black transition-colors"
                                      :class="password.length >= 8 ? 'text-emerald-600' : 'text-rose-600'">
                                    Minimum 8 Characters (Currently: <span x-text="password.length"></span> characters)
                                </span>
                            </div>
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="space-y-1.5">
                            <label for="password_confirmation" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span> Confirm New Password
                            </label>
                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" x-model="password_confirmation"
                                       class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3.5 pl-4 pr-12 transition-all"
                                       placeholder="Repeat new password">
                                <button type="button" @click="showConfirm = !showConfirm" 
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]" x-text="showConfirm ? 'visibility_off' : 'visibility'">visibility</span>
                                </button>
                            </div>
                            
                            <!-- Real-time Passwords Match Validation Info -->
                            <div class="pt-2 flex items-center gap-2" x-show="password_confirmation.length > 0">
                                <div class="w-4 h-4 rounded-full flex items-center justify-center transition-all text-white"
                                     :class="(password === password_confirmation && password_confirmation.length >= 8) ? 'bg-emerald-500' : 'bg-rose-500'">
                                    <span class="material-symbols-outlined text-[10px] font-black" x-text="(password === password_confirmation && password_confirmation.length >= 8) ? 'check' : 'close'"></span>
                                </div>
                                <span class="text-[10px] font-black transition-colors"
                                      :class="(password === password_confirmation && password_confirmation.length >= 8) ? 'text-emerald-600' : 'text-rose-600'">
                                    <span x-text="password === password_confirmation ? 'New passwords match' : 'New passwords do not match'"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: Hapus Akun -->
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-50">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                                <span class="material-symbols-outlined text-[20px]">warning</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900">Delete Account</h3>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Permanently delete account along with all data</p>
                            </div>
                        </div>
                        @if(session('delete_success'))
                            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                                 x-transition:leave="transition ease-in duration-300"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-500/20 text-emerald-800 rounded-xl text-[10px] font-bold shadow-sm">
                                <span class="material-symbols-outlined text-[14px] text-emerald-600 font-black">check_circle</span>
                                <span>Action Completed</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="p-4 rounded-2xl bg-rose-50/50 border border-rose-100 text-rose-800 text-xs font-bold leading-relaxed">
                        Once your account is deleted, all of its resources and data will be permanently deleted. Please make sure to save or download your important data before proceeding with this action.
                    </div>

                    <div>
                        <button type="button" @click="$dispatch('open-modal', 'confirm-user-deletion')"
                                class="inline-flex items-center gap-2 px-5 py-3 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-md shadow-rose-200 transition-all hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">delete_forever</span>
                            Delete Your Account
                        </button>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <!-- Tombol Batal -->
                    <a href="{{ route('dashboard') }}" 
                       class="py-3 px-6 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-1.5 transition-all shadow-xs">
                        Cancel
                    </a>
                    
                    <!-- Tombol Simpan Perubahan -->
                    <button type="submit" 
                            class="py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black shadow-lg shadow-blue-200 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 uppercase tracking-widest flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">save</span> Save Changes
                    </button>
                </div>
            </div>
        </form>

        <!-- Modal for Delete Account Confirmation -->
        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
            <form method="post" action="{{ route('profile.destroy') }}" class="p-6 md:p-8 space-y-6">
                @csrf
                @method('delete')
                
                <div>
                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">
                        Are you sure you want to delete your account?
                    </h3>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">
                        Please enter your password to confirm that you want to permanently delete your account.
                    </p>
                </div>

                <!-- Password Input -->
                <div class="relative group" x-data="{ show: false }">
                    <label for="password_delete" class="block text-xs font-black uppercase tracking-wider text-gray-400 mb-2 group-focus-within:text-rose-600 transition-colors">
                        Password
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-rose-500 transition-colors">lock</span>
                        <input :type="show ? 'text' : 'password'" id="password_delete" name="password" placeholder="••••••••" class="w-full pl-12 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 placeholder-gray-400 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all font-semibold text-sm shadow-sm" required />
                        <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                            <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>

                <!-- Modal Action Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
                    <button type="button" x-on:click="$dispatch('close')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 hover:shadow-sm transition-all shadow-sm">
                        Cancel
                    </button>

                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-rose-700 hover:shadow-lg active:scale-95 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">delete_forever</span>
                        Delete Account
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
