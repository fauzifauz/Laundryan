<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-black text-2xl text-gray-900 leading-tight">{{ __('Employee Dashboard') }}</h2>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ showToast: {{ session('success') ? 'true' : 'false' }}, toastMsg: '{{ session('success') }}' }" x-init="
        if(showToast) setTimeout(() => showToast = false, 5000);
        window.addEventListener('karyawan-order-status-success', (e) => {
            toastMsg = e.detail?.message || 'Order status updated successfully.';
            showToast = true;
            setTimeout(() => showToast = false, 5000);
        });
    ">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Toast alert --}}
            <div x-show="showToast" 
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed top-6 right-6 z-[110] max-w-sm w-full bg-emerald-50 border border-emerald-200 rounded-3xl p-5 shadow-2xl text-emerald-800 flex items-center justify-between overflow-hidden" x-cloak>
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-600/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100/50 border border-emerald-200 flex items-center justify-center shadow-inner">
                        <span class="material-symbols-outlined text-emerald-600 text-xl">check_circle</span>
                    </div>
                    <div>
                        <h4 class="font-black text-xs uppercase tracking-wider">Changes Saved</h4>
                        <p class="text-[11px] text-emerald-700 font-medium mt-0.5" x-text="toastMsg"></p>
                    </div>
                </div>
                <button @click="showToast = false" class="text-emerald-600/60 hover:text-emerald-800 transition-colors p-2 rounded-xl hover:bg-emerald-100/50 relative z-10">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            {{-- Greeting Banner --}}
            @php
                $greetingConfig = [
                    'GOOD MORNING'   => ['gradient'=>'from-amber-500 via-orange-500 to-rose-500 shadow-orange-100','icon'=>'wb_sunny','icon_animate'=>'animate-[spin_20s_linear_infinite]','subtitle'=>'Rise and shine! Ready to tackle today\'s laundry operations?'],
                    'GOOD AFTERNOON' => ['gradient'=>'from-sky-500 via-blue-600 to-indigo-700 shadow-blue-100','icon'=>'wb_twilight','icon_animate'=>'animate-[pulse_3s_infinite]','subtitle'=>'Good afternoon! Operations are running in full swing.'],
                    'GOOD EVENING'   => ['gradient'=>'from-rose-500 via-purple-600 to-indigo-800 shadow-purple-100','icon'=>'filter_drama','icon_animate'=>'animate-[bounce_2s_infinite]','subtitle'=>'Good evening! Time to review today\'s completed orders.'],
                    'GOOD NIGHT'     => ['gradient'=>'from-slate-950 via-indigo-950 to-slate-900 shadow-slate-900/50','icon'=>'bedtime','icon_animate'=>'animate-[pulse_2s_infinite]','subtitle'=>'Operational hours are ending. Have a peaceful night!'],
                ];
                $gc = $greetingConfig[(string)$greeting] ?? $greetingConfig['GOOD MORNING'];
            @endphp
            <div class="bg-gradient-to-r {{ $gc['gradient'] }} rounded-3xl py-10 md:py-12 px-6 md:px-10 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-inner">
                        <span class="material-symbols-outlined text-4xl {{ $gc['icon_animate'] }}">{{ $gc['icon'] }}</span>
                    </div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight uppercase">HALLO, {{ $greeting }}!</h1>
                        <p class="text-sm text-blue-50 font-medium tracking-wide mt-1">{{ $gc['subtitle'] }}</p>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/20 px-5 py-2.5 rounded-2xl text-white text-xs font-black uppercase tracking-widest relative z-10 flex items-center gap-2 hover:bg-white/20 transition-all cursor-default">
                    <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                    {{ now()->format('l, d F Y') }}
                </div>
            </div>

            {{-- KPI Row 1 --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <a href="{{ route('karyawan.orders.index') }}" class="bg-white overflow-hidden shadow-sm rounded-xl p-6 flex flex-col justify-center border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="text-xs font-black text-gray-400 uppercase tracking-widest group-hover:text-blue-600 transition-colors">Total Orders</div>
                    <div class="mt-2 text-4xl font-black text-gray-900">{{ $totalOrders }}</div>
                    <div class="mt-2 text-[10px] font-bold text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">All recorded orders →</div>
                </a>
                <a href="{{ route('karyawan.orders.index', ['status' => 'in_progress']) }}" class="bg-white overflow-hidden shadow-sm rounded-xl p-6 flex flex-col justify-center border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="text-xs font-black text-gray-400 uppercase tracking-widest group-hover:text-blue-600 transition-colors">In Progress</div>
                    <div class="mt-2 text-4xl font-black text-blue-600">{{ $inProgressOrders }}</div>
                    <div class="mt-2 text-[10px] font-bold text-blue-400 opacity-0 group-hover:opacity-100 transition-opacity">Currently processing →</div>
                </a>
                <a href="{{ route('karyawan.orders.index', ['status' => 'completed']) }}" class="bg-white overflow-hidden shadow-sm rounded-xl p-6 flex flex-col justify-center border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="text-xs font-black text-gray-400 uppercase tracking-widest group-hover:text-emerald-600 transition-colors">Completed</div>
                    <div class="mt-2 text-4xl font-black text-emerald-600">{{ $completedOrders }}</div>
                    <div class="mt-2 text-[10px] font-bold text-emerald-400 opacity-0 group-hover:opacity-100 transition-opacity">Successfully delivered →</div>
                </a>
                <a href="{{ route('karyawan.orders.index', ['status' => 'ready_for_delivery']) }}" class="bg-white overflow-hidden shadow-sm rounded-xl p-6 flex flex-col justify-center border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="text-xs font-black text-gray-400 uppercase tracking-widest group-hover:text-emerald-600 transition-colors">Ready for Delivery</div>
                    <div class="mt-2 text-4xl font-black text-emerald-600 ready-delivery-stat">{{ $readyCount }}</div>
                    <div class="mt-2 text-[10px] font-bold text-emerald-400 opacity-0 group-hover:opacity-100 transition-opacity">Orders ready to ship →</div>
                </a>
            </div>

            {{-- KPI Row 2 --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-4">
                <a href="{{ route('karyawan.orders.index', ['status' => 'arrived_at_laundry']) }}" class="bg-white overflow-hidden shadow-sm rounded-xl p-5 flex items-center gap-4 border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 flex items-center justify-center flex-shrink-0 group-hover:bg-orange-600 transition-colors duration-300">
                        <span class="material-symbols-outlined text-orange-600 text-2xl group-hover:text-white transition-colors duration-300">store</span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-orange-600 transition-colors">Arrived at Laundry</div>
                        <div class="mt-0.5 text-xl font-black text-orange-600 arrived-laundry-stat">{{ $receivedCount }}</div>
                    </div>
                </a>
                <a href="{{ route('karyawan.orders.index', ['status' => 'washing']) }}" class="bg-white overflow-hidden shadow-sm rounded-xl p-5 flex items-center gap-4 border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-50 flex items-center justify-center flex-shrink-0 group-hover:bg-cyan-600 transition-colors duration-300">
                        <span class="material-symbols-outlined text-cyan-600 text-2xl group-hover:text-white transition-colors duration-300">water_drop</span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-cyan-600 transition-colors">Washing</div>
                        <div class="mt-0.5 text-xl font-black text-cyan-600 pipeline-washing-count">{{ $washingCount }}</div>
                    </div>
                </a>
                <a href="{{ route('karyawan.tracking.index') }}" class="bg-white overflow-hidden shadow-sm rounded-xl p-5 flex items-center gap-4 border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition-colors duration-300">
                        <span class="material-symbols-outlined text-blue-600 text-2xl group-hover:text-white transition-colors duration-300">delivery_dining</span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-blue-600 transition-colors">Active Couriers</div>
                        <div class="mt-0.5 text-xl font-black text-blue-600">{{ $activeCouriersCount }}</div>
                        <div class="text-[9px] font-bold text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">Click to track map →</div>
                    </div>
                </a>
                <a href="{{ route('karyawan.orders.index', ['status' => 'in_queue']) }}" class="bg-white overflow-hidden shadow-sm rounded-xl p-5 flex items-center gap-4 border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-violet-50 flex items-center justify-center flex-shrink-0 group-hover:bg-violet-600 transition-colors duration-300">
                        <span class="material-symbols-outlined text-violet-600 text-2xl group-hover:text-white transition-colors duration-300">local_laundry_service</span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-violet-600 transition-colors">In Queue</div>
                        <div class="mt-0.5 text-xl font-black text-violet-600">{{ $orders->count() }}</div>
                    </div>
                </a>
            </div>

            {{-- Processing Status Timeline --}}
            <div id="customer-reviews-anchor"></div>
            <div class="space-y-6">
                <div class="bg-white shadow-sm rounded-lg p-6 md:p-8 overflow-hidden">
                    <h3 class="text-lg font-black text-gray-900 mb-10">Laundry Processing Status</h3>
                    @php
                        $styles=['Arrived at Laundry'=>['icon'=>'inventory_2','border'=>'border-blue-100','text'=>'text-blue-500','bg'=>'bg-blue-500','en'=>'Received'],'Washing'=>['icon'=>'water_drop','border'=>'border-cyan-100','text'=>'text-cyan-500','bg'=>'bg-cyan-500','en'=>'Washing'],'Drying & Ironing'=>['icon'=>'iron','border'=>'border-orange-100','text'=>'text-orange-500','bg'=>'bg-orange-500','en'=>'Ironing'],'Packing'=>['icon'=>'package','border'=>'border-amber-100','text'=>'text-amber-500','bg'=>'bg-amber-500','en'=>'Packing'],'Completed'=>['icon'=>'check_circle','border'=>'border-emerald-100','text'=>'text-emerald-500','bg'=>'bg-emerald-500','en'=>'Completed']];
                    @endphp
                    <style>@keyframes flow-line{0%{background-position:200% 0}100%{background-position:-200% 0}}.animate-flow-line{background-size:200% 100%;animation:flow-line 3s linear infinite}</style>
                    <div class="relative flex items-start justify-between w-full mx-auto px-4 sm:px-12">
                        <div class="absolute left-16 right-16 top-[2rem] -translate-y-1/2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-400 via-blue-500 to-emerald-400 w-full opacity-70 animate-flow-line"></div>
                        </div>
                        @php
                            $statusDbMap = [
                                'Arrived at Laundry' => 'arrived_at_laundry',
                                'Washing' => 'washing',
                                'Drying & Ironing' => 'drying_ironing',
                                'Packing' => 'packing',
                                'Completed' => 'completed',
                            ];
                        @endphp
                        @foreach($statusProses as $status => $count)
                            @php $dbStatus = $statusDbMap[$status] ?? 'all'; @endphp
                            <a href="{{ route('karyawan.orders.index', ['status' => $dbStatus]) }}" class="relative flex flex-col items-center group z-10 w-32 hover:scale-105 transition-transform">
                                <div class="w-16 h-16 rounded-full bg-white border-4 {{ $styles[$status]['border'] }} flex items-center justify-center shadow-lg transition-all duration-300 group-hover:shadow-xl group-hover:-translate-y-1 mb-4 relative z-10 {{ $styles[$status]['text'] }}">
                                    <span class="material-symbols-outlined text-3xl">{{ $styles[$status]['icon'] }}</span>
                                    <div class="absolute -top-2 -right-2 {{ $styles[$status]['bg'] }} text-white text-[11px] font-black px-2 py-0.5 rounded-full min-w-[24px] text-center border-2 border-white shadow-md">{{ $count }}</div>
                                </div>
                                <span class="text-[10px] font-black text-gray-700 uppercase tracking-widest text-center leading-tight group-hover:text-blue-600 transition-colors">{{ $styles[$status]['en'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Charts Row --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white shadow-sm rounded-lg p-6 flex flex-col">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center gap-3">
                                <div>
                                    <h3 class="text-lg font-black text-gray-900">Order Statistics <span class="text-blue-600">({{ $period==='daily'?'This Week by Day':($period==='weekly'?'This Month by Week':($period==='monthly'?'This Year by Month':'All Years')) }})</span></h3>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                                        @if($period==='daily') Mon – Sun of the current week
                                        @elseif($period==='weekly') Week ranges within {{ now()->format('F Y') }}
                                        @elseif($period==='monthly') Jan – Dec of {{ now()->year }}
                                        @else All recorded years @endif
                                    </p>
                                </div>
                                <div class="relative inline-block text-left" x-data="{ open: false }">
                                    <button @click="open = !open" class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-all border border-gray-100 group">
                                        <span class="material-symbols-outlined text-[18px] transition-transform group-hover:rotate-12">tune</span>
                                    </button>
                                    <div x-show="open" @click.away="open=false" x-transition style="display:none" class="absolute left-0 mt-2 w-48 rounded-2xl bg-white shadow-2xl border border-gray-100 z-50 overflow-hidden py-1.5">
                                        <div class="px-3 py-2 border-b border-gray-50 mb-1"><span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Select Period</span></div>
                                        @foreach(['daily'=>['label'=>'Day','icon'=>'calendar_today'],'weekly'=>['label'=>'Week','icon'=>'date_range'],'monthly'=>['label'=>'Month','icon'=>'calendar_month'],'yearly'=>['label'=>'Year','icon'=>'event_note']] as $p => $info)
                                            <a href="{{ route('karyawan.dashboard', array_merge(request()->query(), ['period' => $p])) }}"
                                               class="flex items-center justify-between px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $period==$p ? 'bg-blue-600 text-white':'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                                                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-[14px]">{{ $info['icon'] }}</span>{{ $info['label'] }}</span>
                                                @if($period==$p)<span class="material-symbols-outlined text-sm">check_circle</span>@endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Real-time Data</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                            <a href="{{ route('karyawan.dashboard', array_merge(request()->query(), ['period'=>'daily'])) }}" class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center hover:shadow-sm transition-all {{ $period=='daily'?'border-blue-200 bg-blue-50/30 ring-1 ring-blue-200':'' }}">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">This Week</div>
                                <div class="text-xl font-black text-gray-800">{{ $stats['daily'] }}</div>
                                <div class="text-[9px] font-bold text-gray-400 mt-1">Mon – Sun</div>
                            </a>
                            <a href="{{ route('karyawan.dashboard', array_merge(request()->query(), ['period'=>'weekly'])) }}" class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center hover:shadow-sm transition-all {{ $period=='weekly'?'border-blue-200 bg-blue-50/30 ring-1 ring-blue-200':'' }}">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">This Month</div>
                                <div class="text-xl font-black text-gray-800">{{ $stats['weekly'] }}</div>
                                <div class="text-[9px] font-bold text-gray-400 mt-1">By week range</div>
                            </a>
                            <a href="{{ route('karyawan.dashboard', array_merge(request()->query(), ['period'=>'monthly'])) }}" class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center hover:shadow-sm transition-all {{ $period=='monthly'?'border-blue-200 bg-blue-50/30 ring-1 ring-blue-200':'' }}">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">This Year</div>
                                <div class="text-xl font-black text-gray-800">{{ $stats['monthly'] }}</div>
                                <div class="text-[9px] font-bold text-gray-400 mt-1">Jan – Dec {{ now()->year }}</div>
                            </a>
                            <a href="{{ route('karyawan.dashboard', array_merge(request()->query(), ['period'=>'yearly'])) }}" class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center hover:shadow-sm transition-all {{ $period=='yearly'?'border-blue-200 bg-blue-50/30 ring-1 ring-blue-200':'' }}">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">All Time</div>
                                <div class="text-xl font-black text-gray-800">{{ $totalOrders }}</div>
                                <div class="text-[9px] font-bold text-gray-400 mt-1">All recorded years</div>
                            </a>
                        </div>
                        <div class="flex-1 mt-auto h-[350px]"><canvas id="ordersChart"></canvas></div>
                    </div>

                    {{-- Service Pie --}}
                    <div class="bg-white shadow-sm rounded-lg p-6 flex flex-col">
                        <div class="flex items-center gap-2 mb-6">
                            <span class="material-symbols-outlined text-blue-600">pie_chart</span>
                            <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Most Popular Services</h3>
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button @click="open=!open" class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-50 text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-all border border-gray-100 group">
                                    <span class="material-symbols-outlined text-[16px] transition-transform group-hover:rotate-12">tune</span>
                                </button>
                                <div x-show="open" @click.away="open=false" x-transition style="display:none" class="absolute left-0 mt-2 w-48 rounded-2xl bg-white shadow-2xl border border-gray-100 z-50 overflow-hidden py-1.5">
                                    <div class="px-3 py-2 border-b border-gray-50 mb-1"><span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Filter By Period</span></div>
                                    @foreach(['daily'=>'Day','weekly'=>'Week','monthly'=>'Month','yearly'=>'Year'] as $p => $label)
                                        <a href="{{ route('karyawan.dashboard', array_merge(request()->query(), ['service_period'=>$p])) }}"
                                           class="flex items-center justify-between px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $servicePeriod==$p?'bg-blue-600 text-white':'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                                            {{ $label }}@if($servicePeriod==$p)<span class="material-symbols-outlined text-sm">check_circle</span>@endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="flex-1 flex flex-col items-center justify-center relative">
                            <div class="w-full h-[280px]"><canvas id="servicePieChart"></canvas></div>
                            <div class="mt-6 w-full space-y-2">
                                @foreach($serviceDistribution->take(4) as $index => $item)
                                    <div class="flex justify-between items-center text-[10px] font-black text-gray-400 uppercase tracking-widest hover:bg-gray-50 p-2 rounded-xl transition-all group/service">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full" style="background-color:{{ ['#005bc0','#10B981','#F59E0B','#EF4444'][$index%4] }}"></span>
                                            <span class="group-hover/service:text-blue-600 transition-colors">{{ Str::limit($item['label'],20) }}</span>
                                        </div>
                                        <span class="text-gray-900">{{ $item['count'] }} Orders</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Live Order Feed (Full Width) --}}
                <div class="bg-white shadow-sm rounded-lg overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span></span>
                            <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Live Order Feed</h3>
                            <span class="ml-2 bg-rose-100 text-rose-600 text-[10px] font-black px-2 py-0.5 rounded-full border border-rose-200">{{ $latestOrders->count() }} RECENT</span>
                        </div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Auto Update</span>
                    </div>
                    <div class="flex-1 overflow-y-auto max-h-[600px] divide-y divide-gray-50" style="scrollbar-width:thin;scrollbar-color:#e5e7eb transparent">
                        @php
                            $statusMap=[
                                'pending_payment'=>['label'=>'Pending Payment','icon'=>'payments','bg'=>'bg-amber-50 text-amber-700 border-amber-100'],
                                'waiting_pickup'=>['label'=>'Waiting Pickup','icon'=>'schedule','bg'=>'bg-blue-50 text-blue-700 border-blue-100'],
                                'penjemputan'=>['label'=>'Picking Up','icon'=>'local_shipping','bg'=>'bg-indigo-50 text-indigo-700 border-indigo-100'],
                                'picking_up'=>['label'=>'Picking Up','icon'=>'local_shipping','bg'=>'bg-indigo-50 text-indigo-700 border-indigo-100'],
                                'picked_up'=>['label'=>'Picked Up','icon'=>'hail','bg'=>'bg-sky-50 text-sky-700 border-sky-100'],
                                'in_transit_to_laundry'=>['label'=>'To Laundry','icon'=>'airport_shuttle','bg'=>'bg-cyan-50 text-cyan-700 border-cyan-100'],
                                'arrived_at_laundry'=>['label'=>'Arrived','icon'=>'inventory_2','bg'=>'bg-teal-50 text-teal-700 border-teal-100'],
                                'washing'=>['label'=>'Washing','icon'=>'water_drop','bg'=>'bg-blue-50 text-blue-600 border-blue-100'],
                                'drying_ironing'=>['label'=>'Ironing','icon'=>'iron','bg'=>'bg-orange-50 text-orange-700 border-orange-100'],
                                'packing'=>['label'=>'Packing','icon'=>'inventory','bg'=>'bg-amber-50 text-amber-700 border-amber-100'],
                                'ready_for_delivery'=>['label'=>'Ready for Delivery','icon'=>'check_box','bg'=>'bg-emerald-50 text-emerald-700 border-emerald-100'],
                                'pengantaran'=>['label'=>'Delivering','icon'=>'delivery_dining','bg'=>'bg-purple-50 text-purple-700 border-purple-100'],
                                'delivering'=>['label'=>'On Delivery','icon'=>'delivery_dining','bg'=>'bg-purple-50 text-purple-700 border-purple-100'],
                                'completed'=>['label'=>'Completed','icon'=>'check_circle','bg'=>'bg-emerald-50 text-emerald-700 border-emerald-100'],
                                'cancelled'=>['label'=>'Cancelled','icon'=>'cancel','bg'=>'bg-rose-50 text-rose-700 border-rose-100']
                            ];
                        @endphp
                        @forelse($latestOrders as $order)
                            @php $si=$statusMap[$order->status]??['label'=>str_replace('_',' ',$order->status),'icon'=>'info','bg'=>'bg-gray-50 text-gray-700 border-gray-100']; @endphp
                            <div class="block p-4 hover:bg-blue-50/50 transition-colors group">
                                <div class="flex items-start gap-4">
                                    <img src="{{ $order->customer->photo ? asset('storage/'.$order->customer->photo) : 'https://ui-avatars.com/api/?name='.urlencode($order->customer->name??'P').'&background=005bc0&color=fff' }}" class="w-12 h-12 rounded-2xl object-cover border-2 border-white shadow-md flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start mb-1">
                                            <div class="min-w-0">
                                                <div class="text-sm font-black text-gray-900 truncate group-hover:text-blue-600 transition-colors">{{ $order->customer->name??'Customer' }}</div>
                                                <div class="text-[9px] font-bold text-gray-400 truncate uppercase tracking-tight">{{ $order->customer->phone??'Phone -' }} • {{ Str::limit($order->customer->address??'Address -',40) }}</div>
                                            </div>
                                            <div class="text-right flex-shrink-0 flex items-start gap-3">
                                                <div>
                                                    <div class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-all">{{ $order->order_code }}</div>
                                                    <div class="text-[8px] font-bold text-gray-400 mt-1 uppercase">{{ $order->created_at->format('d M Y, H:i') }}</div>
                                                    <div class="text-[8px] font-bold text-gray-300 mt-0.5 uppercase">{{ $order->created_at->diffForHumans() }}</div>
                                                </div>
                                                <div onclick="openQr('{{ $order->order_code }}','https://quickchart.io/qr?text={{ urlencode(route('orders.scan',$order->id)) }}&size=300&margin=1')"
                                                    class="bg-white p-1.5 rounded-xl border border-gray-100 shadow-sm group-hover:border-blue-200 hover:scale-105 transition-all cursor-zoom-in" title="Preview QR Code">
                                                    <img src="https://quickchart.io/qr?text={{ urlencode(route('orders.scan',$order->id)) }}&size=120&margin=1" width="48" height="48" alt="QR" class="rounded-lg">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-1.5 mb-3 mt-2">
                                            <span class="px-2 py-0.5 rounded-md bg-slate-50 text-slate-600 text-[8px] font-black uppercase tracking-tighter border border-slate-100 shadow-sm">{{ $order->service->name??'Service' }}</span>
                                            <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 text-[8px] font-black uppercase tracking-tighter border border-blue-100 shadow-sm">{{ $order->itemType->name??'Type' }}</span>
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[8px] font-black uppercase tracking-tighter border border-emerald-100 shadow-sm">🫧 {{ $order->soap??'Standard Soap' }}</span>
                                            <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 text-[8px] font-black uppercase tracking-tighter border border-rose-100 shadow-sm">🌸 {{ $order->fragrance??'No Fragrance' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center bg-gray-50/50 p-2 rounded-xl border border-gray-100/50">
                                            <span class="inline-flex items-center gap-1 text-[9px] font-black px-2.5 py-1 rounded-full border shadow-sm {{ $si['bg'] }} uppercase tracking-tighter">
                                                <span class="material-symbols-outlined text-[12px] font-bold">{{ $si['icon'] }}</span>{{ $si['label'] }}
                                            </span>
                                            <div class="text-xs font-black text-gray-900 font-sans">Rp {{ number_format($order->total_price,0,',','.') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center"><p class="text-gray-400 font-bold text-sm">No incoming orders yet.</p></div>
                        @endforelse
                    </div>
                </div>

                {{-- Mini Tracking Map (Full Width) --}}
                <div class="bg-white shadow-sm rounded-lg overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-500">map</span>
                            <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Courier Tracker (Mini)</h3>
                        </div>
                        <a href="{{ route('karyawan.tracking.index') }}" class="text-[10px] font-black text-blue-600 uppercase tracking-widest hover:underline">Open Full Map</a>
                    </div>
                    <div id="miniMap" class="h-[420px] w-full z-0"></div>
                </div>

            </div>{{-- end space-y-6 --}}

            {{-- Order Pipeline (Employee-specific) --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden" x-data="{
                activeStatus: null,
                orders: @js($orders->map(fn($o) => [
                    'id'          => $o->id,
                    'order_code'  => $o->order_code,
                    'status'      => $o->status,
                    'customer'    => $o->customer?->name ?? 'Customer',
                    'photo'       => $o->customer?->photo ? asset('storage/'.$o->customer->photo) : 'https://ui-avatars.com/api/?name='.urlencode($o->customer?->name ?? 'P').'&background=EBF4FF&color=005bc0',
                    'service'     => $o->service?->name ?? '-',
                    'itemType'    => $o->itemType?->name ?? '-',
                    'soap'        => $o->soap ?? null,
                    'fragrance'   => $o->fragrance ?? null,
                    'total_price' => $o->total_price,
                ])),
                statusCounts: {
                    arrived_at_laundry: {{ $receivedCount }},
                    washing: {{ $washingCount }},
                    drying_ironing: {{ $ironingCount }},
                    packing: {{ $packingCount }},
                    ready_for_delivery: {{ $readyCount }},
                },
                get filteredOrders() {
                    if (!this.activeStatus) return [];
                    return this.orders.filter(o => o.status === this.activeStatus);
                },
                toggle(status) {
                    this.activeStatus = this.activeStatus === status ? null : status;
                },
                handleStatusSync(detail) {
                    if (!detail) return;
                    const order = this.orders.find(o => o.id === detail.order_id);
                    if (order) order.status = detail.status;
                    if (detail.counts) this.statusCounts = detail.counts;
                },
                init() {
                    window.addEventListener('karyawan-order-status-updated', (e) => {
                        this.handleStatusSync(e.detail);
                    });
                }
            }">
                <div class="p-6 border-b border-gray-100 flex items-center gap-3 bg-gray-50/50">
                    <span class="material-symbols-outlined text-blue-600">local_laundry_service</span>
                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Orders In Process</h3>
                    <span class="ml-auto text-[10px] font-bold text-gray-400 uppercase tracking-widest" x-show="activeStatus" x-text="'Showing: ' + filteredOrders.length + ' order(s)'" x-cloak></span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <!-- Laundry Received -->
                        <button @click="toggle('arrived_at_laundry')"
                            :class="activeStatus === 'arrived_at_laundry' ? 'ring-2 ring-blue-500 border-blue-300 shadow-xl scale-[1.03]' : 'border-gray-100 hover:shadow-lg hover:scale-[1.02]'"
                            class="bg-white shadow-sm rounded-xl p-4 flex items-center gap-3 border transition-all group w-full text-left">
                            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition-colors" :class="activeStatus === 'arrived_at_laundry' ? 'bg-blue-600' : ''">
                                <span class="material-symbols-outlined text-blue-600 text-xl group-hover:text-white transition-colors" :class="activeStatus === 'arrived_at_laundry' ? 'text-white' : ''">inventory_2</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap group-hover:text-blue-600 transition-colors" :class="activeStatus === 'arrived_at_laundry' ? 'text-blue-600' : ''">Laundry Received</div>
                                <div class="mt-0.5 text-xl font-black text-blue-600 pipeline-arrived-count" x-text="statusCounts.arrived_at_laundry">{{ $receivedCount }}</div>
                            </div>
                        </button>
                        <!-- Washing -->
                        <button @click="toggle('washing')"
                            :class="activeStatus === 'washing' ? 'ring-2 ring-cyan-500 border-cyan-300 shadow-xl scale-[1.03]' : 'border-gray-100 hover:shadow-lg hover:scale-[1.02]'"
                            class="bg-white shadow-sm rounded-xl p-4 flex items-center gap-3 border transition-all group w-full text-left">
                            <div class="w-11 h-11 rounded-xl bg-cyan-50 flex items-center justify-center flex-shrink-0 group-hover:bg-cyan-600 transition-colors" :class="activeStatus === 'washing' ? 'bg-cyan-600' : ''">
                                <span class="material-symbols-outlined text-cyan-600 text-xl group-hover:text-white transition-colors" :class="activeStatus === 'washing' ? 'text-white' : ''">water_drop</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap group-hover:text-cyan-600 transition-colors" :class="activeStatus === 'washing' ? 'text-cyan-600' : ''">Washing</div>
                                <div class="mt-0.5 text-xl font-black text-cyan-600 pipeline-washing-count" x-text="statusCounts.washing">{{ $washingCount }}</div>
                            </div>
                        </button>
                        <!-- Ironing -->
                        <button @click="toggle('drying_ironing')"
                            :class="activeStatus === 'drying_ironing' ? 'ring-2 ring-orange-500 border-orange-300 shadow-xl scale-[1.03]' : 'border-gray-100 hover:shadow-lg hover:scale-[1.02]'"
                            class="bg-white shadow-sm rounded-xl p-4 flex items-center gap-3 border transition-all group w-full text-left">
                            <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0 group-hover:bg-orange-600 transition-colors" :class="activeStatus === 'drying_ironing' ? 'bg-orange-600' : ''">
                                <span class="material-symbols-outlined text-orange-600 text-xl group-hover:text-white transition-colors" :class="activeStatus === 'drying_ironing' ? 'text-white' : ''">iron</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap group-hover:text-orange-600 transition-colors" :class="activeStatus === 'drying_ironing' ? 'text-orange-600' : ''">Ironing</div>
                                <div class="mt-0.5 text-xl font-black text-orange-600 pipeline-ironing-count" x-text="statusCounts.drying_ironing">{{ $ironingCount }}</div>
                            </div>
                        </button>
                        <!-- Packing -->
                        <button @click="toggle('packing')"
                            :class="activeStatus === 'packing' ? 'ring-2 ring-amber-500 border-amber-300 shadow-xl scale-[1.03]' : 'border-gray-100 hover:shadow-lg hover:scale-[1.02]'"
                            class="bg-white shadow-sm rounded-xl p-4 flex items-center gap-3 border transition-all group w-full text-left">
                            <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-600 transition-colors" :class="activeStatus === 'packing' ? 'bg-amber-600' : ''">
                                <span class="material-symbols-outlined text-amber-600 text-xl group-hover:text-white transition-colors" :class="activeStatus === 'packing' ? 'text-white' : ''">package</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap group-hover:text-amber-600 transition-colors" :class="activeStatus === 'packing' ? 'text-amber-600' : ''">Packing</div>
                                <div class="mt-0.5 text-xl font-black text-amber-600 pipeline-packing-count" x-text="statusCounts.packing">{{ $packingCount }}</div>
                            </div>
                        </button>
                        <!-- Ready for Delivery -->
                        <button @click="toggle('ready_for_delivery')"
                            :class="activeStatus === 'ready_for_delivery' ? 'ring-2 ring-emerald-500 border-emerald-300 shadow-xl scale-[1.03]' : 'border-gray-100 hover:shadow-lg hover:scale-[1.02]'"
                            class="bg-white shadow-sm rounded-xl p-4 flex items-center gap-3 border transition-all group w-full text-left">
                            <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-600 transition-colors" :class="activeStatus === 'ready_for_delivery' ? 'bg-emerald-600' : ''">
                                <span class="material-symbols-outlined text-emerald-600 text-xl group-hover:text-white transition-colors" :class="activeStatus === 'ready_for_delivery' ? 'text-white' : ''">check_box</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap group-hover:text-emerald-600 transition-colors" :class="activeStatus === 'ready_for_delivery' ? 'text-emerald-600' : ''">Ready for Delivery</div>
                                <div class="mt-0.5 text-xl font-black text-emerald-600 pipeline-ready-count" x-text="statusCounts.ready_for_delivery">{{ $readyCount }}</div>
                            </div>
                        </button>
                    </div>

                    {{-- Inline filtered order list --}}
                    <div x-show="activeStatus !== null" x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="mt-6 border-t border-gray-100 pt-6">

                        <div x-show="filteredOrders.length === 0" class="py-10 text-center text-gray-400 font-bold text-sm">
                            No orders with this status.
                        </div>

                        <div class="divide-y divide-gray-50">
                            <template x-for="order in filteredOrders" :key="order.id">
                                <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <img :src="order.photo" class="w-11 h-11 rounded-xl object-cover border-2 border-white shadow-md flex-shrink-0"
                                             :onerror="`this.src='https://ui-avatars.com/api/?name='+encodeURIComponent(order.customer)+'&background=EBF4FF&color=005bc0'`">
                                        <div>
                                            <div class="font-black text-gray-900 text-sm" x-text="order.customer"></div>
                                            <div class="text-[10px] font-bold text-blue-600 font-mono uppercase" x-text="order.order_code"></div>
                                            <div class="text-[9px] text-gray-400 font-bold uppercase tracking-wider" x-text="order.service + ' • ' + order.itemType"></div>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                <span class="text-[9px] font-bold text-blue-700 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-full" x-show="order.soap" x-text="'\uD83E\uDEB7 ' + order.soap"></span>
                                                <span class="text-[9px] font-bold text-pink-700 bg-pink-50 border border-pink-100 px-2 py-0.5 rounded-full" x-show="order.fragrance" x-text="'\uD83C\uDF38 ' + order.fragrance"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-black text-gray-900">Rp <span x-text="new Intl.NumberFormat('id-ID').format(order.total_price)"></span></span>
                                        @php
                                            $nextSteps = [
                                                'arrived_at_laundry' => ['status'=>'washing','label'=>'Start Washing','icon'=>'water_drop','color'=>'bg-cyan-600 hover:bg-cyan-700'],
                                                'washing'            => ['status'=>'drying_ironing','label'=>'Start Ironing','icon'=>'iron','color'=>'bg-orange-500 hover:bg-orange-600'],
                                                'drying_ironing'     => ['status'=>'packing','label'=>'Start Packing','icon'=>'package','color'=>'bg-amber-500 hover:bg-amber-600'],
                                                'packing'            => ['status'=>'ready_for_delivery','label'=>'Mark Ready','icon'=>'check_box','color'=>'bg-emerald-600 hover:bg-emerald-700'],
                                            ];
                                        @endphp
                                        @foreach($nextSteps as $fromStatus => $next)
                                        <form x-show="order.status === '{{ $fromStatus }}'" x-bind:action="'{{ url('karyawan/orders') }}/' + order.id + '/status'" method="POST" enctype="multipart/form-data" data-karyawan-status-form
                                            x-data="{
                                                previewSrc: null,
                                                hasFile: false,
                                                setFile(file) {
                                                    if (!file) return;
                                                    this.hasFile = true;
                                                    const reader = new FileReader();
                                                    reader.onload = e => this.previewSrc = e.target.result;
                                                    reader.readAsDataURL(file);
                                                    // Copy to the main photo input for submission
                                                    const dt = new DataTransfer();
                                                    dt.items.add(file);
                                                    this.$refs.photoInput.files = dt.files;
                                                }
                                            }"
                                            class="flex flex-col gap-2">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $next['status'] }}">
                                            {{-- Hidden master input that gets submitted --}}
                                            <input type="file" name="photo" accept="image/*" required x-ref="photoInput" class="hidden">
                                            {{-- Hidden gallery input --}}
                                            <input type="file" accept="image/*" class="hidden" x-ref="galleryInput"
                                                @change="setFile($event.target.files[0])">
                                            {{-- Preview --}}
                                            <div x-show="previewSrc" class="flex items-center gap-2">
                                                <img :src="previewSrc" class="w-10 h-10 rounded-lg object-cover border border-gray-200 shadow-sm">
                                                <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-wide">Photo Ready ✓</span>
                                            </div>

                                            {{-- Picker Buttons --}}
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" @click="$refs.galleryInput.click()"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 text-[9px] font-black rounded-lg transition-all">
                                                    <span class="material-symbols-outlined text-[13px]">upload</span>Upload
                                                </button>
                                                <button type="button" @click="window.openCamera(file => setFile(file))"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-600 text-[9px] font-black rounded-lg transition-all">
                                                    <span class="material-symbols-outlined text-[13px]">photo_camera</span>Camera
                                                </button>
                                                <button type="submit" :disabled="!hasFile" :class="hasFile ? 'opacity-100 cursor-pointer' : 'opacity-40 cursor-not-allowed'"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $next['color'] }} text-white text-[10px] font-black rounded-lg shadow active:scale-95 transition-all uppercase tracking-wide">
                                                    <span class="material-symbols-outlined text-[14px]">{{ $next['icon'] }}</span>{{ $next['label'] }}
                                                </button>
                                            </div>
                                        </form>
                                        @endforeach
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @include('karyawan.partials.order-status-sync')

    {{-- QR Modal --}}
    <div id="qrModal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-xs w-full relative flex flex-col items-center gap-4">
            <button onclick="closeQr()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition-colors"><span class="material-symbols-outlined">close</span></button>
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest" id="qrModalTitle">QR Code</h3>
            <img id="qrModalImg" src="" class="w-48 h-48 rounded-xl border border-gray-100 shadow-inner">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Scan to track this order</p>
        </div>
    </div>

    {{-- Camera Modal (getUserMedia) --}}
    <div id="cameraModal" class="fixed inset-0 z-[300] hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-gray-900 rounded-3xl shadow-2xl overflow-hidden w-full max-w-lg flex flex-col gap-0 relative">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-400">photo_camera</span>
                    <span class="text-sm font-black text-white uppercase tracking-widest">Take Photo</span>
                </div>
                <button onclick="window.closeCamera()" class="text-white/50 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="relative bg-black">
                <video id="cameraStream" autoplay playsinline muted class="w-full max-h-[60vh] object-cover"></video>
                <div id="cameraError" class="hidden absolute inset-0 flex flex-col items-center justify-center gap-3 bg-gray-900 text-white/60">
                    <span class="material-symbols-outlined text-4xl">no_photography</span>
                    <p class="text-sm font-bold text-center px-6" id="cameraErrorMsg">Unable to access camera.</p>
                </div>
            </div>
            <canvas id="cameraCanvas" class="hidden"></canvas>
            <div class="flex items-center justify-center gap-4 px-5 py-5 border-t border-white/10">
                <button onclick="window.capturePhoto()"
                    class="flex items-center justify-center w-16 h-16 rounded-full bg-white hover:bg-blue-50 shadow-xl active:scale-95 transition-all border-4 border-blue-400">
                    <span class="material-symbols-outlined text-blue-600 text-3xl">camera</span>
                </button>
            </div>
            <p class="text-center text-[10px] text-white/30 font-bold pb-4 uppercase tracking-widest">Click the button to capture</p>
        </div>
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    function openQr(code, src) {
        document.getElementById('qrModalTitle').textContent = code;
        document.getElementById('qrModalImg').src = src;
        const m = document.getElementById('qrModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeQr() {
        const m = document.getElementById('qrModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
    document.getElementById('qrModal').addEventListener('click', function(e){ if(e.target===this) closeQr(); });

    // ── Camera via getUserMedia ──────────────────────────────────────────
    let _cameraStream = null;
    let _cameraCallback = null;

    window.openCamera = function(callback) {
        _cameraCallback = callback;
        const modal = document.getElementById('cameraModal');
        const errBox = document.getElementById('cameraError');
        const video  = document.getElementById('cameraStream');
        errBox.classList.add('hidden');
        video.classList.remove('hidden');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Prefer rear camera on mobile, fall back to any camera (webcam on desktop)
        const constraints = { video: { facingMode: { ideal: 'environment' } }, audio: false };
        navigator.mediaDevices.getUserMedia(constraints)
            .then(stream => {
                _cameraStream = stream;
                video.srcObject = stream;
            })
            .catch(err => {
                video.classList.add('hidden');
                errBox.classList.remove('hidden');
                document.getElementById('cameraErrorMsg').textContent =
                    'Cannot access camera: ' + (err.message || err.name);
            });
    };

    window.capturePhoto = function() {
        const video  = document.getElementById('cameraStream');
        const canvas = document.getElementById('cameraCanvas');
        if (!video.videoWidth) return;
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        canvas.toBlob(blob => {
            if (!blob) return;
            const file = new File([blob], 'camera-capture.jpg', { type: 'image/jpeg' });
            if (_cameraCallback) _cameraCallback(file);
            window.closeCamera();
        }, 'image/jpeg', 0.88);
    };

    window.closeCamera = function() {
        if (_cameraStream) {
            _cameraStream.getTracks().forEach(t => t.stop());
            _cameraStream = null;
        }
        const modal = document.getElementById('cameraModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        _cameraCallback = null;
    };

    document.getElementById('cameraModal').addEventListener('click', function(e) {
        if (e.target === this) window.closeCamera();
    });
    // ────────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function() {
        // Orders Chart
        const chartData = @json($chartData);
        new Chart(document.getElementById('ordersChart').getContext('2d'), {
            type:'bar',
            data:{ labels:chartData.labels, datasets:[{ label:'Incoming Orders', data:chartData.data, backgroundColor:'#005bc0', borderRadius:6, barPercentage:0.6 }] },
            options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{ backgroundColor:'#111827', titleFont:{family:"'Plus Jakarta Sans',sans-serif",size:13}, bodyFont:{family:"'Plus Jakarta Sans',sans-serif",size:14,weight:'bold'}, padding:12, cornerRadius:8, displayColors:false } }, scales:{ y:{ beginAtZero:true, ticks:{precision:0,font:{family:"'Plus Jakarta Sans',sans-serif",size:11}}, grid:{color:'#F3F4F6'} }, x:{ ticks:{font:{family:"'Plus Jakarta Sans',sans-serif",size:11,weight:'bold'}}, grid:{display:false} } } }
        });

        // Service Pie
        const serviceData = @json($serviceDistribution);
        new Chart(document.getElementById('servicePieChart'), {
            type:'doughnut',
            data:{ labels:serviceData.map(d=>d.label), datasets:[{ data:serviceData.map(d=>d.count), backgroundColor:['#005bc0','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899'], borderWidth:0, hoverOffset:15 }] },
            options:{ responsive:true, maintainAspectRatio:false, cutout:'70%', plugins:{ legend:{display:false}, tooltip:{ backgroundColor:'#111827', titleFont:{family:"'Plus Jakarta Sans',sans-serif",size:12}, bodyFont:{family:"'Plus Jakarta Sans',sans-serif",size:13,weight:'bold'}, padding:12, cornerRadius:8 } } }
        });

        // Mini Map with admin-identical markers
        const miniMap = L.map('miniMap', { zoomControl:false, attributionControl:false }).setView([-6.1664983,106.5602886], 13);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { subdomains:'abcd', maxZoom:20 }).addTo(miniMap);
        L.control.zoom({ position:'bottomleft' }).addTo(miniMap);

        const hqIcon = L.divIcon({ html:`<div class="bg-blue-900 text-white h-12 w-12 rounded-full flex items-center justify-center shadow-2xl border-4 border-white animate-pulse"><span class="material-symbols-outlined text-2xl">local_laundry_service</span></div>`, className:'', iconSize:[48,48], iconAnchor:[24,24], popupAnchor:[0,-24] });
        L.marker([-6.1664983,106.5602886],{icon:hqIcon}).bindPopup('<div class="p-2 font-black text-center text-blue-900">LAUNDRYAN HQ<br><span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Base Operations</span></div>').addTo(miniMap);

        fetch("{{ route('karyawan.tracking.data') }}")
            .then(r=>r.json())
            .then(data=>{
                const marks=[];
                data.tracking.forEach(item=>{
                    if(!item.location) return;
                    const ll=[item.location.lat, item.location.lng];
                    const orders=item.orders||[];

                    // Determine status color matching Admin
                    let statusColor = 'blue'; // Idle
                    if (orders.length > 0) {
                        statusColor = orders.some(o => o.type === 'pickup') ? 'amber' : 'emerald';
                    }

                    // Custom DivIcon consistent with Admin
                    const iconHtml = `
                        <div class="relative">
                            <div class="w-10 h-10 rounded-full border-4 border-${statusColor}-500 bg-white shadow-lg overflow-hidden flex items-center justify-center transition-all hover:scale-110">
                                <img src="${item.courier.photo}" class="w-full h-full object-cover" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(item.courier.name)}&color=005bc0&background=EBF4FF'">
                            </div>
                            ${orders.length > 0 ? `<div class="absolute -top-1 -right-1 bg-white text-blue-600 rounded-full h-4 w-4 flex items-center justify-center shadow-md border border-blue-100"><span class="material-symbols-outlined text-[10px] font-black">inventory_2</span></div>` : ''}
                        </div>`;

                    const icon = L.divIcon({
                        html: iconHtml,
                        className: '',
                        iconSize: [40, 40],
                        iconAnchor: [20, 20]
                    });

                    const m = L.marker(ll, { icon })
                        .on('click', () => {
                            window.location.href = `{{ route('karyawan.tracking.index') }}?focus_courier=${item.courier.id}`;
                        })
                        .addTo(miniMap);
                    marks.push(m);
                });
                if(marks.length>0){
                    const g=new L.featureGroup(marks);
                    const b=g.getBounds();
                    b.extend([-6.1664983,106.5602886]);
                    miniMap.fitBounds(b.pad(0.3));
                }
            });
    });
    </script>
    @endpush
</x-app-layout>
