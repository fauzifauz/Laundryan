<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
             x-data="{ exportMonth: 'all', exportYear: 'all', exportPdfLoading: false, exportCsvLoading: false }">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">User Management</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Manage Administrator, Staff, Courier, and Customer accounts</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                {{-- Month Filter (Export Only) --}}
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">calendar_month</span>
                    <select x-model="exportMonth" class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="all">All Months</option>
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[14px] pointer-events-none">expand_more</span>
                </div>
                {{-- Year Filter (Export Only) --}}
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">event</span>
                    <select x-model="exportYear" class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="all">All Years</option>
                        @foreach($years as $yr)
                            <option value="{{ $yr }}">{{ $yr }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[14px] pointer-events-none">expand_more</span>
                </div>
                {{-- Export PDF --}}
                <a :href="'{{ route('admin.users.export.pdf') }}?month='+exportMonth+'&year='+exportYear"
                    @click="exportPdfLoading = true; setTimeout(() => exportPdfLoading = false, 4000)"
                    :class="exportPdfLoading ? 'pointer-events-none opacity-70' : ''"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-rose-100 text-rose-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-rose-50 hover:shadow-md transition-all group shadow-sm whitespace-nowrap">
                    <template x-if="exportPdfLoading">
                        <svg class="animate-spin h-4.5 w-4.5 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!exportPdfLoading">
                        <span class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">picture_as_pdf</span>
                    </template>
                    <span x-text="exportPdfLoading ? 'Exporting...' : 'Export PDF'"></span>
                </a>
                {{-- Export CSV --}}
                <a :href="'{{ route('admin.users.export.csv') }}?month='+exportMonth+'&year='+exportYear"
                    @click="exportCsvLoading = true; setTimeout(() => exportCsvLoading = false, 4000)"
                    :class="exportCsvLoading ? 'pointer-events-none opacity-70' : ''"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-emerald-100 text-emerald-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-emerald-50 hover:shadow-md transition-all group shadow-sm whitespace-nowrap">
                    <template x-if="exportCsvLoading">
                        <svg class="animate-spin h-4.5 w-4.5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!exportCsvLoading">
                        <span class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">table_view</span>
                    </template>
                    <span x-text="exportCsvLoading ? 'Exporting...' : 'Export CSV'"></span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- AlpineJS State for Tabs, Modal, and AJAX Details -->
    @php
        $activeTab = 'admin';
        if (request()->get('role') === 'karyawan' || request()->has('karyawan_page')) {
            $activeTab = 'karyawan';
        } elseif (request()->get('role') === 'kurir' || request()->has('kurir_page')) {
            $activeTab = 'kurir';
        } elseif (request()->get('role') === 'pelanggan' || request()->has('pelanggan_page') || request()->has('returning') || request()->has('start_date')) {
            $activeTab = 'pelanggan';
        }
    @endphp

    <div class="py-4 space-y-6 relative" 
         x-data="{ 
            gridLoading: false,
            showToast: {{ session('success') ? 'true' : 'false' }},
            toastMessage: '{{ session('success', '') }}',
            triggerToast(msg) {
                this.toastMessage = msg;
                this.showToast = true;
                setTimeout(() => { this.showToast = false; }, 5000);
            },
            showLockModal: false,
            lockUserId: '',
            lockUserName: '',
            lockUserEmail: '',
            lockUserRole: '',
            lockUserAction: '',
            lockUserNewStatus: '',
            lockUserPhoto: '',
            showDeleteModal: false,
            deleteUserId: '',
            deleteUserName: '',
            deleteUserEmail: '',
            deleteUserRole: '',
            deleteUserAction: '',
            deleteUserPhoto: '',
            activeTab: '{{ $activeTab }}', 
            showDetailModal: false, 
            selectedUser: null, 
            loadingDetail: false, 
            recentOrders: [], 
            recentAttendances: [],
            showEditModal: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }},
            showCreateModal: {{ $errors->any() && old('_method') !== 'PUT' ? 'true' : 'false' }},
            createPhotoPreview: null,
            editUser: { 
                id: '{{ old('user_id', '') }}', 
                name: '{{ old('name', '') }}', 
                email: '{{ old('email', '') }}', 
                phone: '{{ old('phone', '') }}', 
                address: '{{ old('address', '') }}', 
                role: '{{ old('role', '') }}', 
                status: '{{ old('status', '') }}', 
                photo: null 
            },
            init() {
                this.gridLoading = false;
                @if(request()->filled('show_user_id'))
                    this.$nextTick(() => {
                        this.fetchDetail({{ request('show_user_id') }});
                    });
                @endif
                if (this.showToast) {
                    setTimeout(() => { this.showToast = false; }, 5000);
                }
            },
            fetchDetail(userId) {
                this.showDetailModal = true;
                this.loadingDetail = true;
                this.selectedUser = null;
                this.recentOrders = [];
                this.recentAttendances = [];
                
                fetch(`/admin/users/${userId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    this.selectedUser = data.user;
                    this.recentOrders = data.recent_orders;
                    this.recentAttendances = data.recent_attendances;
                    this.loadingDetail = false;
                })
                .catch(err => {
                    console.error(err);
                    this.loadingDetail = false;
                    this.showDetailModal = false;
                    alert('Failed to load user details.');
                });
            },
            openEditModal(user) {
                this.editUser = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    phone: user.phone || '',
                    address: user.address || '',
                    role: user.role,
                    status: user.status,
                    photo: user.photo ? '/storage/' + user.photo : null
                };
                this.showEditModal = true;
            }
         }"
         @submit.window="gridLoading = true"
         @click.document="
            const link = $event.target.closest('a');
            if (link) {
                const href = link.getAttribute('href') || link.getAttribute(':href') || '';
                if (href.includes('export')) return;
                if (href.includes('users') || link.closest('.pagination') || link.closest('.page-link')) {
                    gridLoading = true;
                }
            }
         ">
        <!-- Toast Alert Notification -->
        <div x-show="showToast" 
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed top-6 right-6 z-50 max-w-sm w-full bg-emerald-50 border border-emerald-200 rounded-3xl p-5 shadow-2xl text-emerald-800 flex items-center justify-between overflow-hidden" 
            x-cloak>
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-600/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100/50 border border-emerald-200 flex items-center justify-center shadow-inner">
                    <span class="material-symbols-outlined text-emerald-600 text-xl">check_circle</span>
                </div>
                <div>
                    <h4 class="font-black text-xs uppercase tracking-wider">Changes Saved</h4>
                    <p class="text-[11px] text-emerald-700 font-medium mt-0.5" x-text="toastMessage"></p>
                </div>
            </div>
            <button @click="showToast = false" class="text-emerald-600/60 hover:text-emerald-800 transition-colors p-2 rounded-xl hover:bg-emerald-100/50 relative z-10">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center gap-3" role="alert">
                <span class="material-symbols-outlined text-rose-600">error</span>
                <span class="text-sm font-bold">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Validation Errors Display -->
        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl space-y-2" role="alert">
                <div class="flex items-center gap-3 font-bold text-sm">
                    <span class="material-symbols-outlined text-rose-650">error</span>
                    <span>Validation failed. Please check the fields:</span>
                </div>
                <ul class="list-disc list-inside text-xs pl-8 font-semibold text-rose-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <!-- Redesigned Statistics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Card 1: Total Users -->
            <a href="{{ route('admin.users.index', request()->except(['status', 'start_date', 'new_registrations'])) }}"
               class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-gray-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest group-hover:text-gray-600 transition-colors">Total Users</span>
                    <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[16px] text-gray-400">group</span>
                    </div>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $totalUsersCount }}</div>
                <div class="mt-2 flex items-center gap-1 text-[9px] font-bold text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md self-start border border-gray-100 w-fit">
                    <span class="material-symbols-outlined text-[11px]">people</span>All system roles
                </div>
            </a>

            <!-- Card 2: Active Accounts -->
            <a href="{{ route('admin.users.index', array_merge(request()->except(['start_date', 'new_registrations']), ['status' => 'active'])) }}"
               class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-emerald-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Active Accounts</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[16px] text-emerald-500">verified</span>
                    </div>
                </div>
                <div class="text-2xl font-black text-emerald-600">{{ $activeUsersCount }}</div>
                <div class="mt-2 flex items-center gap-1 text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md self-start border border-emerald-100 w-fit">
                    <span class="material-symbols-outlined text-[11px]">check_circle</span>Active status
                </div>
            </a>

            <!-- Card 3: Suspended Accounts -->
            <a href="{{ route('admin.users.index', array_merge(request()->except(['start_date', 'new_registrations']), ['status' => 'inactive'])) }}"
               class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-rose-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[9px] font-black text-rose-600 uppercase tracking-widest">Suspended Accounts</span>
                    <div class="w-7 h-7 rounded-lg bg-rose-50 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[16px] text-rose-500">block</span>
                    </div>
                </div>
                <div class="text-2xl font-black text-rose-600">{{ $suspendedUsersCount }}</div>
                <div class="mt-2 flex items-center gap-1 text-[9px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md self-start border border-rose-100 w-fit">
                    <span class="material-symbols-outlined text-[11px]">cancel</span>Inactive status
                </div>
            </a>

            <!-- Card 4: New Registrations -->
            <a href="{{ route('admin.users.index', array_merge(request()->except(['status', 'start_date']), ['new_registrations' => '1'])) }}"
               class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-indigo-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest">New Registrations</span>
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[16px] text-indigo-500">person_add</span>
                    </div>
                </div>
                <div class="text-2xl font-black text-indigo-600">{{ $newRegistrationsCount }}</div>
                <div class="mt-2 flex items-center gap-1 text-[9px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md self-start border border-indigo-100 w-fit">
                    <span class="material-symbols-outlined text-[11px]">calendar_month</span>Registered this month
                </div>
            </a>
        </div>

        <!-- Filter Form Card -->
        <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
            <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Search Bar -->
                <div class="col-span-12 md:col-span-7">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Search Query</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <span class="material-symbols-outlined text-sm">search</span>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="pl-10 w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3" 
                               placeholder="Search by name, email, phone, address...">
                    </div>
                </div>
                
                <!-- Filter Status -->
                <div class="col-span-12 sm:col-span-6 md:col-span-3">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Account Status</label>
                    <select name="status" class="w-full bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-blue-500 focus:border-blue-500 py-3">
                        <option value="all" {{ request('status') === 'all' || !request()->has('status') ? 'selected' : '' }}>All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                
                <!-- Action Buttons -->
                <div class="col-span-12 sm:col-span-6 md:col-span-2 flex items-end gap-2">
                    <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black shadow-lg shadow-blue-200 uppercase tracking-widest flex items-center justify-center gap-1.5 transition-all">
                        <span class="material-symbols-outlined text-[16px]">filter_alt</span> Filter
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center transition-all" title="Reset Filters">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Role Tabs & Tables Wrapper -->
        @php
            $currentSortBy = request('sort_by', 'created_at');
            $currentSortDir = request('sort_direction', 'desc');
            $nextSortDir = $currentSortDir === 'asc' ? 'desc' : 'asc';
        @endphp

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden relative">
            <!-- Grid Loading state overlay -->
            <div x-show="gridLoading" 
                 class="absolute inset-0 bg-white/70 backdrop-blur-xs z-30 flex flex-col items-center justify-center min-h-[300px]"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="display:none;" x-cloak>
                <div class="flex flex-col items-center gap-3">
                    <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-xs font-black text-blue-600 uppercase tracking-widest animate-pulse">Load User</p>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 px-6 bg-gray-50/50">
                <div class="flex flex-wrap gap-2 pt-2 md:pt-0">
                    <button @click="activeTab = 'admin'" :class="activeTab === 'admin' ? 'border-blue-600 text-blue-600 font-black' : 'border-transparent text-gray-400 hover:text-gray-600 font-bold'" class="py-4 px-6 border-b-2 text-xs uppercase tracking-wider transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span> Administrator
                        <span class="px-2.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'admin' ? 'bg-blue-50 text-blue-600 font-black' : 'bg-gray-100 text-gray-500'">{{ $admins->total() }}</span>
                    </button>
                    <button @click="activeTab = 'karyawan'" :class="activeTab === 'karyawan' ? 'border-blue-600 text-blue-600 font-black' : 'border-transparent text-gray-400 hover:text-gray-600 font-bold'" class="py-4 px-6 border-b-2 text-xs uppercase tracking-wider transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">badge</span> Staff
                        <span class="px-2.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'karyawan' ? 'bg-blue-50 text-blue-600 font-black' : 'bg-gray-100 text-gray-500'">{{ $karyawans->total() }}</span>
                    </button>
                    <button @click="activeTab = 'kurir'" :class="activeTab === 'kurir' ? 'border-blue-600 text-blue-600 font-black' : 'border-transparent text-gray-400 hover:text-gray-600 font-bold'" class="py-4 px-6 border-b-2 text-xs uppercase tracking-wider transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">local_shipping</span> Courier
                        <span class="px-2.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'kurir' ? 'bg-blue-50 text-blue-600 font-black' : 'bg-gray-100 text-gray-500'">{{ $kurirs->total() }}</span>
                    </button>
                    <button @click="activeTab = 'pelanggan'" :class="activeTab === 'pelanggan' ? 'border-blue-600 text-blue-600 font-black' : 'border-transparent text-gray-400 hover:text-gray-600 font-bold'" class="py-4 px-6 border-b-2 text-xs uppercase tracking-wider transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">group</span> Customer
                        <span class="px-2.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'pelanggan' ? 'bg-blue-50 text-blue-600 font-black' : 'bg-gray-100 text-gray-500'">{{ $pelanggans->total() }}</span>
                    </button>
                </div>
                <div class="pb-4 md:pb-0">
                    <button type="button" @click="showCreateModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all group shadow-sm whitespace-nowrap cursor-pointer">
                        <span class="material-symbols-outlined text-[18px] group-hover:rotate-90 transition-transform">add</span>Add New User
                    </button>
                </div>
            </div>

            <!-- Table Contents -->
            <div class="p-6">
                <!-- 1. Administrator Tab -->
                <div x-show="activeTab === 'admin'" class="space-y-4" x-cloak>
                    @include('admin.users.partials.table', ['users' => $admins, 'roleName' => 'admin'])
                </div>

                <!-- 2. Staff Tab -->
                <div x-show="activeTab === 'karyawan'" class="space-y-4" x-cloak>
                    @include('admin.users.partials.table', ['users' => $karyawans, 'roleName' => 'karyawan'])
                </div>

                <!-- 3. Courier Tab -->
                <div x-show="activeTab === 'kurir'" class="space-y-4" x-cloak>
                    @include('admin.users.partials.table', ['users' => $kurirs, 'roleName' => 'kurir'])
                </div>

                <!-- 4. Customer Tab -->
                <div x-show="activeTab === 'pelanggan'" class="space-y-4" x-cloak>
                    @include('admin.users.partials.table', ['users' => $pelanggans, 'roleName' => 'pelanggan'])
                </div>
            </div>
        </div>

        <div x-show="showDetailModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             x-cloak>
            
            <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col border border-gray-105"
                 @click.away="showDetailModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                
                <!-- Header: Premium Indigo/Blue Gradient with glow -->
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white flex items-center justify-between relative">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.15),transparent_60%)]"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20">
                            <span class="material-symbols-outlined text-white text-[22px]">badge</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black tracking-wide">User Profile Directory</h3>
                            <p class="text-[10px] text-blue-100 font-bold uppercase tracking-widest">Full System Profile Details</p>
                        </div>
                    </div>
                    <button @click="showDetailModal = false" class="relative z-10 p-2 text-white/80 hover:text-white rounded-xl hover:bg-white/10 transition-all border border-transparent hover:border-white/10">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Content (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/50">
                    <!-- Loading State -->
                    <div x-show="loadingDetail" class="py-20 flex flex-col items-center justify-center gap-4">
                        <div class="relative w-12 h-12">
                            <div class="absolute inset-0 rounded-full border-4 border-blue-100"></div>
                            <div class="absolute inset-0 rounded-full border-4 border-blue-600 border-t-transparent animate-spin"></div>
                        </div>
                        <p class="text-xs text-gray-400 font-black uppercase tracking-widest animate-pulse">Loading Directory Data...</p>
                    </div>

                    <!-- User Detail Data -->
                    <template x-if="!loadingDetail && selectedUser">
                        <div class="space-y-6">
                            <!-- Premium Upper Card: Profile Summary -->
                            <div class="flex flex-col sm:flex-row items-center gap-6 p-6 bg-white rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/30 rounded-full blur-2xl -mr-10 -mt-10 transition-all group-hover:bg-blue-50/50 duration-500"></div>
                                
                                <!-- Photo Container with Status Indicator -->
                                <div class="relative">
                                    <template x-if="selectedUser.photo">
                                        <img :src="'/storage/' + selectedUser.photo" 
                                             class="w-24 h-24 rounded-2xl object-cover border-4 border-white shadow-md relative z-10">
                                    </template>
                                    <template x-if="!selectedUser.photo">
                                        <div class="w-24 h-24 rounded-2xl bg-gradient-to-tr from-blue-50 to-indigo-100 border-4 border-white shadow-md flex items-center justify-center text-blue-600 font-black text-4xl relative z-10">
                                            <span x-text="selectedUser.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                    </template>
                                    <!-- Status indicator dot -->
                                    <span class="absolute bottom-0 right-0 block w-5 h-5 rounded-full border-4 border-white shadow-sm z-20"
                                          :class="selectedUser.status === 'active' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                </div>

                                <!-- Base Info -->
                                <div class="flex-1 text-center sm:text-left space-y-2 relative z-10">
                                    <h4 class="text-xl font-black text-gray-900 leading-tight" x-text="selectedUser.name"></h4>
                                    <div class="flex flex-wrap justify-center sm:justify-start gap-2 items-center">
                                        <span class="px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full bg-blue-50 text-blue-600 border border-blue-100" 
                                              x-text="selectedUser.role === 'karyawan' ? 'STAFF' : selectedUser.role.toUpperCase()"></span>
                                        
                                        <template x-if="selectedUser.status === 'active'">
                                            <span class="px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>ACTIVE
                                            </span>
                                        </template>
                                        <template x-if="selectedUser.status === 'inactive'">
                                            <span class="px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full bg-rose-50 text-rose-600 border border-rose-100 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>SUSPENDED
                                            </span>
                                        </template>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-bold flex items-center justify-center sm:justify-start gap-1">
                                        <span class="material-symbols-outlined text-[12px]">calendar_month</span>
                                        <span x-text="'Registered at: ' + selectedUser.registered_at"></span>
                                    </p>
                                </div>
                            </div>

                            <!-- Details Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 bg-white rounded-2xl border border-gray-100 shadow-xs hover:border-gray-200 transition-all space-y-1">
                                    <div class="flex items-center gap-1.5 text-gray-400">
                                        <span class="material-symbols-outlined text-[14px]">mail</span>
                                        <span class="text-[9px] font-black uppercase tracking-widest">Email Address</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-800 block break-all" x-text="selectedUser.email"></span>
                                </div>
                                <div class="p-4 bg-white rounded-2xl border border-gray-100 shadow-xs hover:border-gray-200 transition-all space-y-1">
                                    <div class="flex items-center gap-1.5 text-gray-400">
                                        <span class="material-symbols-outlined text-[14px]">phone_iphone</span>
                                        <span class="text-[9px] font-black uppercase tracking-widest">Phone Number</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-800 block" x-text="selectedUser.phone || '-'"></span>
                                </div>
                                <div class="p-4 bg-white rounded-2xl border border-gray-100 shadow-xs hover:border-gray-200 transition-all space-y-1 md:col-span-2">
                                    <div class="flex items-center gap-1.5 text-gray-400">
                                        <span class="material-symbols-outlined text-[14px]">home_pin</span>
                                        <span class="text-[9px] font-black uppercase tracking-widest">Home Address</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-800 block leading-relaxed" x-text="selectedUser.address || '-'"></span>
                                </div>
                            </div>

                            <!-- Dynamic Role-Specific Layouts (Staff, Customer, Courier) -->
                            <div class="border-t border-gray-100 pt-6">
                                <!-- 1. STAFF (KARYAWAN) LAYOUT -->
                                <template x-if="selectedUser.role === 'karyawan'">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Left Side: Work Attendance Stat Card -->
                                        <div class="p-6 rounded-3xl bg-gradient-to-br from-emerald-50/30 to-teal-50/20 border border-emerald-100 flex flex-col justify-center items-center text-center shadow-xs">
                                            <div class="w-14 h-14 rounded-2xl bg-emerald-100/80 flex items-center justify-center text-emerald-600 mb-4 shadow-xs">
                                                <span class="material-symbols-outlined text-[32px]">how_to_reg</span>
                                            </div>
                                            <span class="block text-4xl font-black text-emerald-700" x-text="selectedUser.attendances_count || 0"></span>
                                            <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mt-2">Work Attendance Days</span>
                                        </div>
                                        
                                        <!-- Right Side: Recent Attendances History List -->
                                        <div class="space-y-3">
                                            <h5 class="text-[10px] font-black text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[18px] text-emerald-600">event_note</span> Recent Attendances
                                            </h5>
                                            <div class="max-h-[180px] overflow-y-auto border border-gray-150 rounded-2xl divide-y divide-gray-100 bg-white shadow-xs">
                                                <template x-for="att in recentAttendances" :key="att.id">
                                                    <div class="p-3 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                                                        <div>
                                                            <span class="text-xs font-bold text-gray-800 block" x-text="att.date"></span>
                                                            <span class="text-[9px] text-gray-400 block" x-text="'Clock: ' + att.check_in + ' - ' + att.check_out"></span>
                                                        </div>
                                                        <span class="px-2 py-0.5 text-[8px] font-black uppercase rounded-full"
                                                              :class="{
                                                                  'bg-emerald-50 text-emerald-600 border border-emerald-100': att.status === 'present',
                                                                  'bg-amber-50 text-amber-600 border border-amber-100': att.status === 'late',
                                                                  'bg-rose-50 text-rose-600 border border-rose-100': att.status === 'absent'
                                                              }"
                                                              x-text="att.status"></span>
                                                    </div>
                                                </template>
                                                <template x-if="recentAttendances.length === 0">
                                                    <div class="p-6 text-center text-xs text-gray-300 italic">No attendance records available.</div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- 2. CUSTOMER (PELANGGAN) LAYOUT -->
                                <template x-if="selectedUser.role === 'pelanggan'">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Left Side: Total Orders Stat Card -->
                                        <div class="p-6 rounded-3xl bg-gradient-to-br from-blue-50/30 to-indigo-50/20 border border-blue-100 flex flex-col justify-center items-center text-center shadow-xs">
                                            <div class="w-14 h-14 rounded-2xl bg-blue-100/80 flex items-center justify-center text-blue-600 mb-4 shadow-xs">
                                                <span class="material-symbols-outlined text-[32px]">assignment</span>
                                            </div>
                                            <span class="block text-4xl font-black text-blue-700" x-text="selectedUser.customer_orders_count || 0"></span>
                                            <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest mt-2">TOTAL ORDERS</span>
                                        </div>
                                        
                                        <!-- Right Side: Recent Orders List -->
                                        <div class="space-y-3">
                                            <h5 class="text-[10px] font-black text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[18px] text-blue-600">history</span> Recent Orders
                                            </h5>
                                            <div class="max-h-[180px] overflow-y-auto border border-gray-150 rounded-2xl divide-y divide-gray-100 bg-white shadow-xs">
                                                <template x-for="order in recentOrders" :key="order.id">
                                                    <div class="p-3 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                                                        <div>
                                                            <span class="text-xs font-bold text-gray-800 block" x-text="'Order #' + order.order_code"></span>
                                                            <span class="text-[9px] text-gray-400 block" x-text="order.created_at"></span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="px-2 py-0.5 text-[8px] font-black uppercase rounded-full" 
                                                                  :class="{
                                                                      'bg-blue-50 text-blue-600 border border-blue-100': order.status === 'processing',
                                                                      'bg-emerald-50 text-emerald-600 border border-emerald-100': order.status === 'completed' || order.status === 'selesai',
                                                                      'bg-amber-50 text-amber-600 border border-amber-100': order.status === 'pending'
                                                                  }"
                                                                  x-text="order.status"></span>
                                                            <a :href="order.url" class="p-1 hover:bg-gray-100 rounded-lg text-gray-500 hover:text-blue-600 transition-all">
                                                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="recentOrders.length === 0">
                                                    <div class="p-6 text-center text-xs text-gray-300 italic">No order history available.</div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- 3. COURIER (KURIR) LAYOUT -->
                                <template x-if="selectedUser.role === 'kurir'">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Left Side: Double Stats Column -->
                                        <div class="space-y-4">
                                            <!-- Total Orders Handled -->
                                            <div class="p-5 rounded-2xl bg-gradient-to-br from-blue-50/30 to-indigo-50/10 border border-blue-100 flex items-center gap-4 shadow-xs">
                                                <div class="w-12 h-12 rounded-xl bg-blue-100/80 flex items-center justify-center text-blue-600 shadow-xs">
                                                    <span class="material-symbols-outlined text-2xl">local_shipping</span>
                                                </div>
                                                <div>
                                                    <span class="block text-2xl font-black text-blue-700" x-text="selectedUser.courier_orders_count || 0"></span>
                                                    <span class="text-[9px] font-black text-blue-500 uppercase tracking-widest">Total Orders Handled</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Work Attendance -->
                                            <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-50/30 to-teal-50/10 border border-emerald-100 flex items-center gap-4 shadow-xs">
                                                <div class="w-12 h-12 rounded-xl bg-emerald-100/80 flex items-center justify-center text-emerald-600 shadow-xs">
                                                    <span class="material-symbols-outlined text-2xl">how_to_reg</span>
                                                </div>
                                                <div>
                                                    <span class="block text-2xl font-black text-emerald-700" x-text="selectedUser.attendances_count || 0"></span>
                                                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Work Attendance</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Right Side: History Lists Stack -->
                                        <div class="space-y-4">
                                            <!-- Recent Handled Orders -->
                                            <div class="space-y-2">
                                                <h5 class="text-[9px] font-black text-gray-900 uppercase tracking-wider flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[16px] text-blue-600">assignment</span> Recent Handled Orders
                                                </h5>
                                                <div class="max-h-[100px] overflow-y-auto border border-gray-150 rounded-2xl divide-y divide-gray-100 bg-white shadow-xs">
                                                    <template x-for="order in recentOrders" :key="order.id">
                                                        <div class="p-2.5 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                                                            <div>
                                                                <span class="text-xs font-bold text-gray-800 block" x-text="'Order #' + order.order_code"></span>
                                                                <span class="text-[8px] text-gray-400 block" x-text="order.created_at"></span>
                                                            </div>
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="px-1.5 py-0.5 text-[7px] font-black uppercase rounded-full" 
                                                                      :class="{
                                                                          'bg-blue-50 text-blue-600 border border-blue-100': order.status === 'processing',
                                                                          'bg-emerald-50 text-emerald-600 border border-emerald-100': order.status === 'completed' || order.status === 'selesai',
                                                                          'bg-amber-50 text-amber-600 border border-amber-100': order.status === 'pending'
                                                                      }"
                                                                      x-text="order.status"></span>
                                                                <a :href="order.url" class="p-1 hover:bg-gray-100 rounded-lg text-gray-500 hover:text-blue-600 transition-all">
                                                                    <span class="material-symbols-outlined text-[12px]">visibility</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="recentOrders.length === 0">
                                                        <div class="p-4 text-center text-xs text-gray-300 italic">No handled orders.</div>
                                                    </template>
                                                </div>
                                            </div>
                                            
                                            <!-- Recent Attendances -->
                                            <div class="space-y-2">
                                                <h5 class="text-[9px] font-black text-gray-900 uppercase tracking-wider flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[16px] text-emerald-600">event_note</span> Recent Attendances
                                                </h5>
                                                <div class="max-h-[100px] overflow-y-auto border border-gray-150 rounded-2xl divide-y divide-gray-100 bg-white shadow-xs">
                                                    <template x-for="att in recentAttendances" :key="att.id">
                                                        <div class="p-2.5 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                                                            <div>
                                                                <span class="text-xs font-bold text-gray-800 block" x-text="att.date"></span>
                                                                <span class="text-[8px] text-gray-400 block" x-text="'Clock: ' + att.check_in + ' - ' + att.check_out"></span>
                                                            </div>
                                                            <span class="px-1.5 py-0.5 text-[7px] font-black uppercase rounded-full"
                                                                  :class="{
                                                                      'bg-emerald-50 text-emerald-600 border border-emerald-100': att.status === 'present',
                                                                      'bg-amber-50 text-amber-600 border border-amber-100': att.status === 'late',
                                                                      'bg-rose-50 text-rose-600 border border-rose-100': att.status === 'absent'
                                                                  }"
                                                                  x-text="att.status"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="recentAttendances.length === 0">
                                                        <div class="p-4 text-center text-xs text-gray-300 italic">No attendance records.</div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Footer Buttons -->
                <div class="p-6 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/50">
                    <button @click="showDetailModal = false" class="py-2.5 px-6 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-black rounded-xl uppercase tracking-widest transition-all">
                        Close Profile Directory
                    </button>
                </div>
            </div>
        </div>

        <!-- -------------------- EDIT USER MODAL (NIK Removed completely) -------------------- -->
        <div x-show="showEditModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             x-cloak>
             
            <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden max-h-[90vh] flex flex-col"
                 @click.away="showEditModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                 
                <!-- Header -->
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-xs">
                            <span class="material-symbols-outlined text-[22px]">edit_note</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Edit User Profile</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Modify credentials and status</p>
                        </div>
                    </div>
                    <button @click="showEditModal = false" class="p-2 text-gray-400 hover:text-gray-650 rounded-xl hover:bg-gray-100/80 transition-all">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <!-- Form -->
                <form :action="'/admin/users/' + editUser.id" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" :value="editUser.id">
                    
                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-5">
                        <!-- Large Profile Photo Update Preview -->
                        <div class="flex flex-col items-center sm:flex-row gap-6 p-5 bg-gradient-to-br from-gray-50/50 to-white rounded-3xl border border-gray-150 shadow-xs">
                            <div class="relative flex-shrink-0 group">
                                <template x-if="editUser.photo">
                                    <img :src="editUser.photo" class="w-36 h-36 rounded-3xl object-cover border-4 border-white shadow-md transition-all hover:scale-105 duration-200">
                                </template>
                                <template x-if="!editUser.photo">
                                    <div class="w-36 h-36 rounded-3xl bg-gradient-to-tr from-blue-50 to-indigo-50 border-4 border-white shadow-md flex items-center justify-center text-blue-600 font-black text-4xl">
                                        <span x-text="editUser.name ? editUser.name.charAt(0).toUpperCase() : 'U'"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="flex-1 space-y-2.5 text-center sm:text-left">
                                <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Update Photo Profile</span>
                                <input type="file" name="photo" accept="image/*"
                                       @change="const file = $event.target.files[0]; if (file) { editUser.photo = URL.createObjectURL(file) }"
                                       class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer file:transition-all shadow-xs file:uppercase file:tracking-wider">
                                <p class="text-[9px] text-gray-400 font-bold">Square image recommended, max 2MB (JPEG, PNG, JPG)</p>
                            </div>
                        </div>

                        <!-- Full Name -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">person</span> Full Name
                            </label>
                            <input type="text" name="name" x-model="editUser.name" required
                                   class="w-full bg-white border border-gray-150 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all"
                                   placeholder="User's Full Name">
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">mail</span> Email Address
                            </label>
                            <input type="email" name="email" x-model="editUser.email" required
                                   class="w-full bg-white border border-gray-150 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all"
                                   placeholder="user@example.com">
                        </div>

                        <!-- Phone -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">phone_iphone</span> Phone Number
                            </label>
                            <input type="text" name="phone" x-model="editUser.phone"
                                   class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all"
                                   placeholder="e.g. +62812345678">
                        </div>

                        <!-- Home Address -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">home_pin</span> Home Address
                            </label>
                            <textarea name="address" x-model="editUser.address" rows="3"
                                      class="w-full bg-white border border-gray-150 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 resize-none transition-all"
                                      placeholder="Full street name, house number, area..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Role -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">shield_person</span> System Role
                                </label>
                                <select name="role" x-model="editUser.role" required
                                        class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                                    <option value="admin">Admin</option>
                                    <option value="karyawan">Staff</option>
                                    <option value="kurir">Courier</option>
                                    <option value="pelanggan">Customer</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">verified_user</span> Account Status
                                </label>
                                <select name="status" x-model="editUser.status" required
                                        class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                                    <option value="active">Active</option>
                                    <option value="inactive">Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-2.5 bg-gray-50/50">
                        <button type="button" @click="showEditModal = false"
                                class="py-2.5 px-6 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-black rounded-xl uppercase tracking-widest transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl uppercase tracking-widest shadow-md shadow-blue-200 hover:shadow-lg transition-all">
                            Update Details
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- -------------------- CREATE USER MODAL -------------------- -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             x-cloak>
             
            <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden max-h-[90vh] flex flex-col"
                 @click.away="showCreateModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                 
                <!-- Header -->
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-xs">
                            <span class="material-symbols-outlined text-[22px]">person_add</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Add New User</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Register a new profile in the system</p>
                        </div>
                    </div>
                    <button @click="showCreateModal = false" class="p-2 text-gray-400 hover:text-gray-650 rounded-xl hover:bg-gray-100/80 transition-all">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <!-- Form -->
                <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    
                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-5">
                        <!-- Large Profile Photo Upload Preview -->
                        <div class="flex flex-col items-center sm:flex-row gap-6 p-5 bg-gradient-to-br from-gray-50/50 to-white rounded-3xl border border-gray-150 shadow-xs">
                            <div class="relative flex-shrink-0 group">
                                <template x-if="createPhotoPreview">
                                    <img :src="createPhotoPreview" class="w-36 h-36 rounded-3xl object-cover border-4 border-white shadow-md transition-all hover:scale-105 duration-200">
                                </template>
                                <template x-if="!createPhotoPreview">
                                    <div class="w-36 h-36 rounded-3xl bg-gradient-to-tr from-blue-50 to-indigo-50 border-4 border-white shadow-md flex items-center justify-center text-blue-650 font-black text-4xl">
                                        <span>?</span>
                                    </div>
                                </template>
                            </div>
                            <div class="flex-1 space-y-2.5 text-center sm:text-left">
                                <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Profile Photo</span>
                                <input type="file" name="photo" accept="image/*"
                                       @change="const file = $event.target.files[0]; createPhotoPreview = file ? URL.createObjectURL(file) : null"
                                       class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer file:transition-all shadow-xs file:uppercase file:tracking-wider">
                                <p class="text-[9px] text-gray-400 font-bold">Square image recommended, max 2MB (JPEG, PNG, JPG)</p>
                            </div>
                        </div>

                        <!-- Full Name -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">person</span> Full Name
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. John Doe"
                                   class="w-full bg-white border border-gray-150 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">mail</span> Email Address
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. johndoe@example.com"
                                   class="w-full bg-white border border-gray-150 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                        </div>

                        <!-- Password -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">lock</span> Password
                                </label>
                                <input type="password" name="password" required placeholder="Min. 8 characters"
                                       class="w-full bg-white border border-gray-150 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">key</span> Confirm Password
                                </label>
                                <input type="password" name="password_confirmation" required placeholder="Repeat password"
                                       class="w-full bg-white border border-gray-150 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">phone_iphone</span> Phone Number
                            </label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all"
                                   placeholder="e.g. +62812345678">
                        </div>

                        <!-- Home Address -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">home_pin</span> Home Address
                            </label>
                            <textarea name="address" rows="3" placeholder="Enter full address details"
                                      class="w-full bg-white border border-gray-150 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 resize-none transition-all">{{ old('address') }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Role -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">shield_person</span> System Role
                                </label>
                                <select name="role" required
                                        class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="karyawan" {{ old('role') === 'karyawan' ? 'selected' : '' }}>Staff</option>
                                    <option value="kurir" {{ old('role') === 'kurir' ? 'selected' : '' }}>Courier</option>
                                    <option value="pelanggan" {{ old('role') === 'pelanggan' ? 'selected' : '' }}>Customer</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">verified_user</span> Account Status
                                </label>
                                <select name="status" required
                                        class="w-full bg-white border border-gray-155 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 py-3 px-4 transition-all">
                                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-2.5 bg-gray-50/50">
                        <button type="button" @click="showCreateModal = false"
                                class="py-2.5 px-6 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-black rounded-xl uppercase tracking-widest transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl uppercase tracking-widest shadow-md shadow-blue-200 hover:shadow-lg transition-all">
                            Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <!-- Custom Status Change Modal (Suspend / Activate) -->
    <div x-show="showLockModal" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;" 
         x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" 
             @click="showLockModal = false"
             x-show="showLockModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Modal Wrapper -->
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100"
                 x-show="showLockModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <form :action="lockUserAction" method="POST" @submit="showLockModal = false; gridLoading = true;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" :value="lockUserNewStatus === 'Active' ? 'active' : 'inactive'">
                    
                    <!-- Header -->
                    <div class="p-6 bg-gray-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center"
                                 :class="lockUserNewStatus === 'Active' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-orange-50 text-orange-600 border border-orange-100'">
                                <span class="material-symbols-outlined text-[20px]" x-text="lockUserNewStatus === 'Active' ? 'lock_open' : 'lock'"></span>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Change Account Status</h3>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Verify status update</p>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-4">
                        <!-- Profile Photo (centered) -->
                        <div class="flex justify-center">
                            <template x-if="lockUserPhoto">
                                <img :src="lockUserPhoto" class="w-16 h-16 rounded-full object-cover border-2 border-gray-100 shadow-md">
                            </template>
                            <template x-if="!lockUserPhoto">
                                <div class="w-16 h-16 rounded-full bg-blue-50 border-2 border-blue-100 flex items-center justify-center text-blue-600 font-black text-xl shadow-sm">
                                    <span x-text="lockUserName ? lockUserName.charAt(0).toUpperCase() : ''"></span>
                                </div>
                            </template>
                        </div>

                        <p class="text-xs text-gray-600 font-semibold leading-relaxed text-center">
                            Are you sure you want to change the status of this user account?
                        </p>
                        
                        <!-- User Card -->
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-400">User Name:</span>
                                <span class="font-black text-gray-900" x-text="lockUserName"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-400">Email:</span>
                                <span class="font-semibold text-gray-600" x-text="lockUserEmail"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-400">Role:</span>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100" x-text="lockUserRole"></span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                <span class="font-bold text-gray-400">Target Status:</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider" 
                                      :class="lockUserNewStatus === 'Active' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'"
                                      x-text="lockUserNewStatus"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2.5">
                        <button type="button" @click="showLockModal = false"
                                class="py-2 px-4 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-[10px] font-black rounded-xl uppercase tracking-widest transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="py-2 px-4 text-white text-[10px] font-black rounded-xl uppercase tracking-widest shadow-md transition-all"
                                :class="lockUserNewStatus === 'Active' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-250' : 'bg-orange-600 hover:bg-orange-700 shadow-orange-250'">
                            Yes, Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div x-show="showDeleteModal" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;" 
         x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" 
             @click="showDeleteModal = false"
             x-show="showDeleteModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Modal Wrapper -->
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100"
                 x-show="showDeleteModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <form :action="deleteUserAction" method="POST" @submit="showDeleteModal = false; gridLoading = true;">
                    @csrf
                    @method('DELETE')
                    
                    <!-- Header -->
                    <div class="p-6 bg-gray-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[20px]">warning</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Delete User Account</h3>
                                <p class="text-[10px] text-rose-600 font-black uppercase tracking-widest mt-0.5">Danger zone</p>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-4">
                        <!-- Profile Photo (centered) -->
                        <div class="flex justify-center">
                            <template x-if="deleteUserPhoto">
                                <img :src="deleteUserPhoto" class="w-16 h-16 rounded-full object-cover border-2 border-gray-100 shadow-md">
                            </template>
                            <template x-if="!deleteUserPhoto">
                                <div class="w-16 h-16 rounded-full bg-blue-50 border-2 border-blue-100 flex items-center justify-center text-blue-600 font-black text-xl shadow-sm">
                                    <span x-text="deleteUserName ? deleteUserName.charAt(0).toUpperCase() : ''"></span>
                                </div>
                            </template>
                        </div>

                        <p class="text-xs text-gray-600 font-semibold leading-relaxed text-center">
                            Are you sure you want to permanently delete this user? This action cannot be undone and all associated account records will be permanently removed.
                        </p>
                        
                        <!-- User Card -->
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-400">User Name:</span>
                                <span class="font-black text-gray-900" x-text="deleteUserName"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-400">Email:</span>
                                <span class="font-semibold text-gray-600" x-text="deleteUserEmail"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-400">Role:</span>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100" x-text="deleteUserRole"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2.5">
                        <button type="button" @click="showDeleteModal = false"
                                class="py-2 px-4 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-[10px] font-black rounded-xl uppercase tracking-widest transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="py-2 px-4 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-black rounded-xl uppercase tracking-widest shadow-md shadow-rose-250 transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px]">delete</span> Delete User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
