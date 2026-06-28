<x-app-layout>
<x-slot name="header">
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
     x-data="{ exportMonth: 'all', exportYear: 'all', exportRole: '{{ request('role','') }}', exportStatus: '{{ request('status','') }}', exportApproval: '{{ request('approval_status','') }}', exportSearch: '{{ request('search','') }}', exportPdfLoading: false, exportCsvLoading: false }">
    <div>
        <h2 class="font-black text-2xl text-gray-900 leading-tight">Attendance Management</h2>
        <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-wider">Manage, approve, and track staff presence</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        {{-- Month Filter --}}
        <div class="relative">
            <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">calendar_month</span>
            <select x-model="exportMonth" class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                <option value="all">All Months</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create(2026,$m)->format('F') }}</option>
                @endforeach
            </select>
            <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[14px] pointer-events-none">expand_more</span>
        </div>
        {{-- Year Filter --}}
        <div class="relative">
            <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-[18px]">event</span>
            <select x-model="exportYear" class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none appearance-none cursor-pointer shadow-sm">
                <option value="all">All Years</option>
                @foreach(range(now()->year-2, now()->year+1) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
            <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-gray-400 text-[14px] pointer-events-none">expand_more</span>
        </div>
        {{-- Export PDF --}}
        <a :href="'{{ route('admin.attendance.export') }}?month='+exportMonth+'&year='+exportYear+'&role='+exportRole+'&status='+exportStatus+'&approval_status='+exportApproval+'&search='+exportSearch"
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
        <a :href="'{{ route('admin.attendance.export_csv') }}?month='+exportMonth+'&year='+exportYear+'&role='+exportRole+'&status='+exportStatus+'&approval_status='+exportApproval+'&search='+exportSearch"
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

<div class="py-8 bg-gray-50/50" x-data="{
    showToast: {{ session('success') ? 'true' : 'false' }},
    toastMessage: '{{ session('success', '') }}',
    toastTitle: '{{ str_contains(session('success', ''), 'approved') ? 'Request Approved' : (str_contains(session('success', ''), 'rejected') ? 'Request Rejected' : 'Success') }}',
    toastType: '{{ str_contains(session('success', ''), 'rejected') ? 'warning' : 'success' }}',
    showDetail: false, showRejectForm: false, selectedRecord: null,
    openDetail(r) { this.selectedRecord = r; this.showDetail = true; this.showRejectForm = false; this.downloadingProof = false; },
    showProofViewer: false,
    proofViewerUrl: '',
    proofViewerIsPdf: false,
    proofViewerTitle: 'Supporting Proof',
    downloadingProof: false,
    isImagePath(path) { return path ? /\.(jpe?g|png|gif|webp)$/i.test(path) : false; },
    isPdfPath(path) { return path ? /\.pdf$/i.test(path) : false; },
    openProofViewer(url, isPdf, title = 'Supporting Proof') {
        this.proofViewerUrl = url;
        this.proofViewerIsPdf = isPdf;
        this.proofViewerTitle = title;
        this.showProofViewer = true;
    },
    async downloadProof(url, filename) {
        if (this.downloadingProof) return;
        this.downloadingProof = true;
        const name = filename || url.split('/').pop() || 'document.pdf';
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Download failed');
            const blob = await response.blob();
            const blobUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = blobUrl;
            link.download = name;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(blobUrl);
        } catch (error) {
            console.error('Download error:', error);
            const link = document.createElement('a');
            link.href = url;
            link.download = name;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } finally {
            this.downloadingProof = false;
        }
    },
    loading: false,
    ft(t) { return t ? t.substring(0,5) : '--:--'; },
    fd(d) { return d ? new Date(d).toLocaleDateString('en-US',{day:'numeric',month:'short',year:'numeric'}) : ''; },
    
    cardStates: {
        total: '{{ (!request()->hasAny(['month', 'year', 'filter_month', 'filter_year', 'period', 'date', 'week']) || request('month') === 'all' || request('year') === 'all') ? 'all' : 'current' }}',
        present: '{{ (!request()->hasAny(['month', 'year', 'filter_month', 'filter_year', 'period', 'date', 'week']) || request('month') === 'all' || request('year') === 'all') ? 'all' : 'current' }}',
        absent: '{{ (!request()->hasAny(['month', 'year', 'filter_month', 'filter_year', 'period', 'date', 'week']) || request('month') === 'all' || request('year') === 'all') ? 'all' : 'current' }}',
        permit: '{{ (!request()->hasAny(['month', 'year', 'filter_month', 'filter_year', 'period', 'date', 'week']) || request('month') === 'all' || request('year') === 'all') ? 'all' : 'current' }}',
        pending: '{{ (!request()->hasAny(['month', 'year', 'filter_month', 'filter_year', 'period', 'date', 'week']) || request('month') === 'all' || request('year') === 'all') ? 'all' : 'current' }}'
    },
    loadingStates: { total: false, present: false, absent: false, permit: false, pending: false },
    statsData: {
        all: {
            total: {{ $allTimeStats['total'] }},
            present: {{ $allTimeStats['present'] }},
            absent: {{ $allTimeStats['absent'] }},
            permit: {{ $allTimeStats['permit'] + $allTimeStats['leave'] }},
            pending: {{ $allTimeStats['pending_approval'] }}
        },
        current: {
            total: {{ $currentMonthStats['total'] }},
            present: {{ $currentMonthStats['present'] }},
            absent: {{ $currentMonthStats['absent'] }},
            permit: {{ $currentMonthStats['permit'] + $currentMonthStats['leave'] }},
            pending: {{ $currentMonthStats['pending_approval'] }}
        }
    },
    toggleCard(type) {
        if (this.cardStates[type] === 'all') {
            this.cardStates[type] = 'current';
        } else {
            this.cardStates[type] = 'all';
        }
        this.fetchRealtimeStats(type);
    },
    fetchRealtimeStats(type) {
        this.loadingStates[type] = true;
        const searchParams = new URLSearchParams(window.location.search);
        fetch('{{ route('admin.attendance.realtime-stats') }}?' + searchParams.toString())
            .then(res => res.json())
            .then(data => {
                this.statsData.all = data.all;
                this.statsData.current = data.current;
            })
            .catch(err => console.error('Error fetching stats:', err))
            .finally(() => {
                this.loadingStates[type] = false;
            });
    },
    filterCard(type) {
        this.loading = true;
        const state = this.cardStates[type];
        const params = new URLSearchParams(window.location.search);
        
        params.delete('staff_page');
        params.delete('courier_page');
        
        if (type === 'total') {
            params.delete('status');
            params.delete('approval_status');
        } else if (type === 'present') {
            params.set('status', 'present,late');
            params.delete('approval_status');
        } else if (type === 'absent') {
            params.set('status', 'absent');
            params.delete('approval_status');
        } else if (type === 'permit') {
            params.set('status', 'permit,leave');
            params.delete('approval_status');
        } else if (type === 'pending') {
            params.set('status', 'permit,leave');
            params.set('approval_status', 'pending');
        }
        
        if (state === 'current') {
            params.set('period', 'monthly');
            params.set('filter_month', '{{ now()->format('Y-m') }}');
            params.delete('month');
            params.delete('year');
            params.delete('date');
            params.delete('week');
            params.delete('filter_year');
        } else {
            params.set('month', 'all');
            params.set('year', 'all');
            params.delete('period');
            params.delete('filter_month');
            params.delete('filter_year');
            params.delete('date');
            params.delete('week');
        }
        
        window.location.href = '{{ route('admin.attendance.index') }}?' + params.toString();
    }
}"
x-init="loading = false"
@submit.window="loading = true"
@click.document="
    const link = $event.target.closest('a');
    if (link) {
        const href = link.getAttribute('href') || link.getAttribute(':href') || '';
        if (href.includes('export')) return;
        if (href.includes('attendance') || link.closest('.pagination') || link.closest('.page-link')) {
            loading = true;
        }
    }
">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

{{-- Toast Alert --}}
<div x-show="showToast"
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed top-6 right-6 z-50 max-w-sm w-full border rounded-3xl p-5 shadow-2xl flex items-center justify-between overflow-hidden"
     :class="{
         'bg-emerald-50 border-emerald-250 text-emerald-800': toastType === 'success',
         'bg-rose-50 border-rose-250 text-rose-800': toastType === 'warning'
     }"
     x-cloak
     x-init="setTimeout(() => showToast = false, 5000)">
    <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full blur-xl pointer-events-none"
         :class="{
             'bg-emerald-600/10': toastType === 'success',
             'bg-rose-600/10': toastType === 'warning'
         }"></div>
    <div class="flex items-center gap-4 relative z-10">
        <div class="w-10 h-10 rounded-2xl flex items-center justify-center shadow-inner border"
             :class="{
                 'bg-emerald-100/50 border-emerald-200 text-emerald-600': toastType === 'success',
                 'bg-rose-100/50 border-rose-200 text-rose-600': toastType === 'warning'
             }">
            <span class="material-symbols-outlined text-xl" x-text="toastType === 'success' ? 'check_circle' : 'cancel'"></span>
        </div>
        <div>
            <h4 class="font-black text-xs uppercase tracking-wider" x-text="toastTitle"></h4>
            <p class="text-[11px] font-medium mt-0.5"
               :class="{
                   'text-emerald-700': toastType === 'success',
                   'text-rose-700': toastType === 'warning'
               }"
               x-text="toastMessage"></p>
        </div>
    </div>
    <button @click="showToast = false"
            class="transition-colors p-2 rounded-xl relative z-10"
            :class="{
                'text-emerald-600/60 hover:text-emerald-800 hover:bg-emerald-100/50': toastType === 'success',
                'text-rose-600/60 hover:text-rose-800 hover:bg-rose-100/50': toastType === 'warning'
            }">
        <span class="material-symbols-outlined text-[18px]">close</span>
    </button>
</div>
@if(session('error'))
<div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-xl flex items-center gap-3 text-rose-800 font-bold text-sm">
    <span class="material-symbols-outlined text-rose-500">error</span>{{ session('error') }}
</div>
@endif

{{-- KPI Cards (clickable and interactive) --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4">
    <div @click="filterCard('total')"
       class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-gray-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group relative overflow-hidden cursor-pointer">
        <!-- Spinner overlay when loading -->
        <div x-show="loadingStates.total" class="absolute inset-0 bg-white/50 backdrop-blur-xs flex items-center justify-center z-10" x-cloak>
            <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <div class="flex items-center justify-between mb-3">
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest group-hover:text-gray-600 transition-colors">Total Records</span>
            <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center"><span class="material-symbols-outlined text-[16px] text-gray-400">import_contacts</span></div>
        </div>
        <div class="text-2xl font-black text-gray-900" x-text="cardStates.total === 'all' ? statsData.all.total : statsData.current.total"></div>
        
        <!-- Segmented Control for Period Scope -->
        <div class="mt-4 pt-3 border-t border-gray-100/60 flex items-center justify-between gap-1" @click.stop>
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider">Scope:</span>
            <div class="inline-flex rounded-lg bg-gray-100/80 p-0.5 relative z-20 border border-gray-200/20">
                <button type="button" 
                    @click="cardStates.total = 'current'; fetchRealtimeStats('total')"
                    :class="cardStates.total === 'current' ? 'bg-white text-indigo-600 shadow-xs font-black' : 'text-gray-400 hover:text-gray-700'"
                    class="px-2 py-1 rounded-md text-[8px] uppercase tracking-wider font-extrabold transition-all duration-200">
                    Month
                </button>
                <button type="button" 
                    @click="cardStates.total = 'all'; fetchRealtimeStats('total')"
                    :class="cardStates.total === 'all' ? 'bg-white text-indigo-600 shadow-xs font-black' : 'text-gray-400 hover:text-gray-700'"
                    class="px-2 py-1 rounded-md text-[8px] uppercase tracking-wider font-extrabold transition-all duration-200">
                    All
                </button>
            </div>
        </div>
    </div>
    <div @click="filterCard('present')"
       class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-emerald-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group relative overflow-hidden cursor-pointer">
        <!-- Spinner overlay when loading -->
        <div x-show="loadingStates.present" class="absolute inset-0 bg-white/50 backdrop-blur-xs flex items-center justify-center z-10" x-cloak>
            <svg class="animate-spin h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <div class="flex items-center justify-between mb-3">
            <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Present</span>
            <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center"><span class="material-symbols-outlined text-[16px] text-emerald-500">check_circle</span></div>
        </div>
        <div class="text-2xl font-black text-emerald-600" x-text="cardStates.present === 'all' ? statsData.all.present : statsData.current.present"></div>
        
        <!-- Segmented Control for Period Scope -->
        <div class="mt-4 pt-3 border-t border-gray-100/60 flex items-center justify-between gap-1" @click.stop>
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider">Scope:</span>
            <div class="inline-flex rounded-lg bg-gray-100/80 p-0.5 relative z-20 border border-gray-200/20">
                <button type="button" 
                    @click="cardStates.present = 'current'; fetchRealtimeStats('present')"
                    :class="cardStates.present === 'current' ? 'bg-white text-emerald-600 shadow-xs font-black' : 'text-gray-400 hover:text-gray-700'"
                    class="px-2 py-1 rounded-md text-[8px] uppercase tracking-wider font-extrabold transition-all duration-200">
                    Month
                </button>
                <button type="button" 
                    @click="cardStates.present = 'all'; fetchRealtimeStats('present')"
                    :class="cardStates.present === 'all' ? 'bg-white text-emerald-600 shadow-xs font-black' : 'text-gray-400 hover:text-gray-700'"
                    class="px-2 py-1 rounded-md text-[8px] uppercase tracking-wider font-extrabold transition-all duration-200">
                    All
                </button>
            </div>
        </div>
    </div>
    <div @click="filterCard('absent')"
       class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-rose-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group relative overflow-hidden cursor-pointer">
        <!-- Spinner overlay when loading -->
        <div x-show="loadingStates.absent" class="absolute inset-0 bg-white/50 backdrop-blur-xs flex items-center justify-center z-10" x-cloak>
            <svg class="animate-spin h-5 w-5 text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <div class="flex items-center justify-between mb-3">
            <span class="text-[9px] font-black text-rose-600 uppercase tracking-widest">Absent</span>
            <div class="w-7 h-7 rounded-lg bg-rose-50 flex items-center justify-center"><span class="material-symbols-outlined text-[16px] text-rose-500">cancel</span></div>
        </div>
        <div class="text-2xl font-black text-rose-600" x-text="cardStates.absent === 'all' ? statsData.all.absent : statsData.current.absent"></div>
        
        <!-- Segmented Control for Period Scope -->
        <div class="mt-4 pt-3 border-t border-gray-100/60 flex items-center justify-between gap-1" @click.stop>
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider">Scope:</span>
            <div class="inline-flex rounded-lg bg-gray-100/80 p-0.5 relative z-20 border border-gray-200/20">
                <button type="button" 
                    @click="cardStates.absent = 'current'; fetchRealtimeStats('absent')"
                    :class="cardStates.absent === 'current' ? 'bg-white text-rose-600 shadow-xs font-black' : 'text-gray-400 hover:text-gray-700'"
                    class="px-2 py-1 rounded-md text-[8px] uppercase tracking-wider font-extrabold transition-all duration-200">
                    Month
                </button>
                <button type="button" 
                    @click="cardStates.absent = 'all'; fetchRealtimeStats('absent')"
                    :class="cardStates.absent === 'all' ? 'bg-white text-rose-600 shadow-xs font-black' : 'text-gray-400 hover:text-gray-700'"
                    class="px-2 py-1 rounded-md text-[8px] uppercase tracking-wider font-extrabold transition-all duration-200">
                    All
                </button>
            </div>
        </div>
    </div>
    <div @click="filterCard('permit')"
       class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-blue-200 hover:shadow-md hover:-translate-y-0.5 transition-all block group relative overflow-hidden cursor-pointer">
        <!-- Spinner overlay when loading -->
        <div x-show="loadingStates.permit" class="absolute inset-0 bg-white/50 backdrop-blur-xs flex items-center justify-center z-10" x-cloak>
            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <div class="flex items-center justify-between mb-3">
            <span class="text-[9px] font-black text-blue-600 uppercase tracking-widest">Permit / Leave</span>
            <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center"><span class="material-symbols-outlined text-[16px] text-blue-500">event_busy</span></div>
        </div>
        <div class="text-2xl font-black text-blue-600" x-text="cardStates.permit === 'all' ? statsData.all.permit : statsData.current.permit"></div>
        
        <!-- Segmented Control for Period Scope -->
        <div class="mt-4 pt-3 border-t border-gray-100/60 flex items-center justify-between gap-1" @click.stop>
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider">Scope:</span>
            <div class="inline-flex rounded-lg bg-gray-100/80 p-0.5 relative z-20 border border-gray-200/20">
                <button type="button" 
                    @click="cardStates.permit = 'current'; fetchRealtimeStats('permit')"
                    :class="cardStates.permit === 'current' ? 'bg-white text-blue-600 shadow-xs font-black' : 'text-gray-400 hover:text-gray-700'"
                    class="px-2 py-1 rounded-md text-[8px] uppercase tracking-wider font-extrabold transition-all duration-200">
                    Month
                </button>
                <button type="button" 
                    @click="cardStates.permit = 'all'; fetchRealtimeStats('permit')"
                    :class="cardStates.permit === 'all' ? 'bg-white text-blue-600 shadow-xs font-black' : 'text-gray-400 hover:text-gray-700'"
                    class="px-2 py-1 rounded-md text-[8px] uppercase tracking-wider font-extrabold transition-all duration-200">
                    All
                </button>
            </div>
        </div>
    </div>
    <div @click="filterCard('pending')"
       :class="(cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending) > 0 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-orange-100 hover:shadow-lg' : 'bg-white border border-gray-100 text-gray-900 hover:border-amber-250'"
       class="rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block group relative overflow-hidden cursor-pointer">
        <!-- Spinner overlay when loading -->
        <div x-show="loadingStates.pending" class="absolute inset-0 bg-white/50 backdrop-blur-xs flex items-center justify-center z-10" x-cloak>
            <svg class="animate-spin h-5 w-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <div class="flex items-center justify-between mb-3">
            <span class="text-[9px] font-black uppercase tracking-widest"
                  :class="(cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending) > 0 ? 'text-orange-100' : 'text-amber-500'">Pending</span>
            <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                 :class="(cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending) > 0 ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-500'">
                <span class="material-symbols-outlined text-[16px]"
                      :class="(cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending) > 0 ? 'animate-pulse' : ''">pending_actions</span>
            </div>
        </div>
        <div class="text-2xl font-black" x-text="cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending"></div>
        
        <!-- Segmented Control for Period Scope -->
        <div class="mt-4 pt-3 border-t flex items-center justify-between gap-1"
             :class="(cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending) > 0 ? 'border-white/20' : 'border-gray-100/60'"
             @click.stop>
            <span class="text-[9px] font-black uppercase tracking-wider"
                  :class="(cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending) > 0 ? 'text-orange-100' : 'text-gray-400'">Scope:</span>
            <div class="inline-flex rounded-lg p-0.5 relative z-20 border"
                 :class="(cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending) > 0 ? 'bg-white/10 border-white/10' : 'bg-gray-100/80 border-gray-200/20'">
                <button type="button" 
                    @click="cardStates.pending = 'current'; fetchRealtimeStats('pending')"
                    :class="cardStates.pending === 'current' 
                        ? ((cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending) > 0 ? 'bg-white text-orange-600 shadow-xs font-black' : 'bg-white text-amber-600 shadow-xs font-black')
                        : ((cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending) > 0 ? 'text-orange-100 hover:text-white' : 'text-gray-400 hover:text-gray-700')"
                    class="px-2 py-1 rounded-md text-[8px] uppercase tracking-wider font-extrabold transition-all duration-200">
                    Month
                </button>
                <button type="button" 
                    @click="cardStates.pending = 'all'; fetchRealtimeStats('pending')"
                    :class="cardStates.pending === 'all' 
                        ? ((cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending) > 0 ? 'bg-white text-orange-600 shadow-xs font-black' : 'bg-white text-amber-600 shadow-xs font-black')
                        : ((cardStates.pending === 'all' ? statsData.all.pending : statsData.current.pending) > 0 ? 'text-orange-100 hover:text-white' : 'text-gray-400 hover:text-gray-700')"
                    class="px-2 py-1 rounded-md text-[8px] uppercase tracking-wider font-extrabold transition-all duration-200">
                    All
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Filter Panel --}}
@php
    $fp = request('period','monthly');
    $fd = request('date', now()->toDateString());
    $fw = request('week', now()->format('Y-\WW'));
    $fm = request('filter_month', now()->format('Y-m'));
    $fy = request('filter_year', now()->year);
@endphp
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
     x-data="{ period:'{{ $fp }}', dateVal:'{{ $fd }}', weekVal:'{{ $fw }}', monthVal:'{{ $fm }}', yearVal:'{{ $fy }}' }">

    <form method="GET" action="{{ route('admin.attendance.index') }}" class="p-5 space-y-3">

        {{-- Row 1: Search · Status · Approval --}}
        <div class="grid grid-cols-1 sm:grid-cols-[1fr_160px_168px] gap-3 items-center">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px] pointer-events-none">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employee or courier name..."
                    class="w-full h-10 pl-10 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400">
            </div>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[17px] pointer-events-none">checklist</span>
                <select name="status" class="w-full h-10 pl-10 pr-7 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="present,late" {{ request('status')==='present,late' ?'selected':'' }}>Present (All)</option>
                    <option value="present"  {{ request('status')==='present' ?'selected':'' }}>Present Only</option>
                    <option value="late"     {{ request('status')==='late'    ?'selected':'' }}>Late Only</option>
                    <option value="absent"   {{ request('status')==='absent'  ?'selected':'' }}>Absent</option>
                    <option value="permit,leave" {{ request('status')==='permit,leave' ?'selected':'' }}>Permit & Leave</option>
                    <option value="permit"   {{ request('status')==='permit'  ?'selected':'' }}>Permit Only</option>
                    <option value="leave"    {{ request('status')==='leave'   ?'selected':'' }}>Leave Only</option>
                </select>
                <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none">expand_more</span>
            </div>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[17px] pointer-events-none">how_to_reg</span>
                <select name="approval_status" class="w-full h-10 pl-10 pr-7 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer">
                    <option value="">All Approvals</option>
                    <option value="pending"  {{ request('approval_status')==='pending' ?'selected':'' }}>Pending</option>
                    <option value="approved" {{ request('approval_status')==='approved'?'selected':'' }}>Approved</option>
                    <option value="rejected" {{ request('approval_status')==='rejected'?'selected':'' }}>Rejected</option>
                </select>
                <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none">expand_more</span>
            </div>
        </div>

        <div class="border-t border-gray-100"></div>

        {{-- Row 2: Period type · Date input · Action buttons --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-blue-500 text-[17px] pointer-events-none">date_range</span>
                <select x-model="period" name="period"
                    class="h-10 pl-10 pr-7 bg-blue-50 border border-blue-100 rounded-xl text-xs font-black text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer">
                    <option value="daily">Daily — Pick a day</option>
                    <option value="weekly">Weekly — Pick a week</option>
                    <option value="monthly">Monthly — Pick a month</option>
                    <option value="yearly">Yearly — Pick a year</option>
                </select>
                <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-blue-400 text-[16px] pointer-events-none">expand_more</span>
            </div>

            <div x-show="period==='daily'" x-transition class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none">calendar_today</span>
                <input type="date" name="date" x-model="dateVal"
                    class="h-10 pl-10 pr-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
            </div>

            <div x-show="period==='weekly'" x-transition class="relative" style="display:none">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none">calendar_view_week</span>
                <input type="week" name="week" x-model="weekVal"
                    class="h-10 pl-10 pr-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
            </div>

            <div x-show="period==='monthly'" x-transition class="relative" style="display:none">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none">calendar_month</span>
                <input type="month" name="filter_month" x-model="monthVal"
                    class="h-10 pl-10 pr-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
            </div>

            <div x-show="period==='yearly'" x-transition class="relative" style="display:none">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none">event</span>
                <select name="filter_year" x-model="yearVal"
                    class="h-10 pl-10 pr-7 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer">
                    @foreach(range(now()->year-3, now()->year+1) as $y)
                        <option value="{{ $y }}" {{ request('filter_year')==$y?'selected':'' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none">expand_more</span>
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <button type="submit"
                    class="h-10 px-5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-[11px] font-black uppercase tracking-widest rounded-xl flex items-center gap-1.5 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>Filter
                </button>
                <a href="{{ route('admin.attendance.index') }}"
                    class="h-10 px-4 bg-gray-100 hover:bg-gray-200 text-gray-600 text-[11px] font-black uppercase tracking-widest rounded-xl flex items-center gap-1.5 transition-all">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>Reset
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Staff / Courier Tabbed Tables --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden relative" x-data="{ subTab: '{{ request('role')==='kurir' ? 'kurir' : 'karyawan' }}' }">

    <!-- Grid Loading Overlay -->
    <div x-show="loading" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 bg-white/70 backdrop-blur-xs z-30 flex flex-col items-center justify-center min-h-[300px]" x-cloak>
        <div class="flex flex-col items-center gap-3">
            <div class="relative w-12 h-12">
                <div class="absolute inset-0 rounded-full border-4 border-blue-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-blue-600 border-t-transparent animate-spin"></div>
            </div>
            <p class="text-xs font-black text-blue-600 uppercase tracking-widest animate-pulse">Load Attendance</p>
        </div>
    </div>

    {{-- Pill Tab Header (payroll-style) --}}
    <div class="flex items-center gap-2 p-4 bg-gray-50/50 border-b border-gray-100">
        <button type="button" @click="subTab='karyawan'"
            :class="subTab==='karyawan' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border border-gray-150 text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-xs">badge</span>
            Staff Attendance ({{ $karyawanAttendances->total() }})
        </button>
        <button type="button" @click="subTab='kurir'"
            :class="subTab==='kurir' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border border-gray-150 text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-xs">local_shipping</span>
            Courier Attendance ({{ $kurirAttendances->total() }})
        </button>
    </div>

    {{-- Staff Table --}}
    <div x-show="subTab==='karyawan'" class="overflow-x-auto">
        <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
            <span>{{ $karyawanAttendances->total() }} records found</span>
            <span>Page {{ $karyawanAttendances->currentPage() }} / {{ $karyawanAttendances->lastPage() }}</span>
        </div>
        <table class="min-w-full divide-y divide-gray-100">
            <thead>
                <tr class="text-left text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/30">
                    <th class="px-5 py-3">Employee</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3">Check In / Out</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-center">Proof</th>
                    <th class="px-5 py-3 text-center">Approval</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($karyawanAttendances as $record)
                    @include('admin.attendance._row')
                @empty
                    <tr><td colspan="7" class="px-6 py-16 text-center text-gray-400 italic text-sm font-bold">No staff attendance records found for the selected period.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($karyawanAttendances->hasPages())
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">{{ $karyawanAttendances->links() }}</div>
        @endif
    </div>

    {{-- Courier Table --}}
    <div x-show="subTab==='kurir'" class="overflow-x-auto" style="display:none">
        <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
            <span>{{ $kurirAttendances->total() }} records found</span>
            <span>Page {{ $kurirAttendances->currentPage() }} / {{ $kurirAttendances->lastPage() }}</span>
        </div>
        <table class="min-w-full divide-y divide-gray-100">
            <thead>
                <tr class="text-left text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/30">
                    <th class="px-5 py-3">Courier</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3">Check In / Out</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-center">Proof</th>
                    <th class="px-5 py-3 text-center">Approval</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($kurirAttendances as $record)
                    @include('admin.attendance._row')
                @empty
                    <tr><td colspan="7" class="px-6 py-16 text-center text-gray-400 italic text-sm font-bold">No courier attendance records found for the selected period.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($kurirAttendances->hasPages())
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">{{ $kurirAttendances->links() }}</div>
        @endif
    </div>
</div>

{{-- Slide-over Detail Modal --}}
<div x-show="showDetail" class="fixed inset-0 z-50 overflow-hidden" style="display:none" x-cloak>
    <div class="absolute inset-0 overflow-hidden">
        <div x-show="showDetail" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="showDetail=false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div x-show="showDetail" x-transition:enter="transform transition ease-in-out duration-500" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-500" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                 class="pointer-events-auto w-screen max-w-md">
                <div class="flex h-full flex-col overflow-y-auto bg-white shadow-2xl border-l border-gray-100">
                    {{-- Panel Header --}}
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-7 text-white relative">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-sm font-black uppercase tracking-widest text-blue-100">Attendance Log</h2>
                                <p class="text-[9px] text-white/70 font-bold uppercase tracking-wider mt-0.5">Real-time verification details</p>
                            </div>
                            <button @click="showDetail=false" class="text-white/80 hover:text-white hover:bg-white/10 p-1.5 rounded-xl transition-all">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                        <div class="mt-5 flex items-center gap-3.5 bg-white/10 p-3.5 rounded-2xl border border-white/10 backdrop-blur-xs">
                            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center font-black text-white text-xl shadow-xs" x-text="selectedRecord ? selectedRecord.user.name.substring(0,1).toUpperCase() : ''"></div>
                            <div>
                                <div class="font-black text-sm text-white" x-text="selectedRecord ? selectedRecord.user.name : ''"></div>
                                <div class="text-[9px] text-blue-100/80 font-black uppercase tracking-widest mt-0.5" x-text="selectedRecord ? (selectedRecord.user.role==='karyawan' ? 'Staff' : 'Courier') : ''"></div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Panel Body --}}
                    <div class="flex-1 px-6 py-6 space-y-5">
                        {{-- Date / Status --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-gray-50/50 to-white p-4 rounded-2xl border border-gray-150 shadow-xs">
                                <span class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px] text-gray-400">calendar_today</span> Date
                                </span>
                                <div class="text-xs font-bold text-gray-800" x-text="selectedRecord ? fd(selectedRecord.date) : ''"></div>
                            </div>
                            <div class="bg-gradient-to-br from-gray-50/50 to-white p-4 rounded-2xl border border-gray-150 shadow-xs">
                                <span class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px] text-gray-400">verified</span> Status
                                </span>
                                <div class="inline-flex">
                                    <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase border"
                                          :class="{
                                              'bg-emerald-50 text-emerald-600 border-emerald-100': selectedRecord?.status === 'present',
                                              'bg-amber-50 text-amber-600 border-amber-100': selectedRecord?.status === 'late',
                                              'bg-rose-50 text-rose-600 border-rose-100': selectedRecord?.status === 'absent',
                                              'bg-blue-50 text-blue-600 border-blue-100': selectedRecord?.status === 'permit',
                                              'bg-indigo-50 text-indigo-600 border-indigo-100': selectedRecord?.status === 'leave'
                                          }"
                                          x-text="selectedRecord ? selectedRecord.status : ''"></span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Check In/Out times --}}
                        <template x-if="selectedRecord && !['permit','leave','absent'].includes(selectedRecord.status)">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gradient-to-br from-gray-50/50 to-white p-4 rounded-2xl border border-gray-150 shadow-xs">
                                    <span class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1.5 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px] text-blue-500">login</span> Check In
                                    </span>
                                    <div class="text-sm font-black text-blue-600" x-text="selectedRecord ? ft(selectedRecord.check_in) : '--:--'"></div>
                                </div>
                                <div class="bg-gradient-to-br from-gray-50/50 to-white p-4 rounded-2xl border border-gray-155 shadow-xs">
                                    <span class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1.5 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px] text-gray-500">logout</span> Check Out
                                    </span>
                                    <div class="text-sm font-black text-gray-800" x-text="selectedRecord ? ft(selectedRecord.check_out) : '--:--'"></div>
                                </div>
                            </div>
                        </template>
                        
                        {{-- Geolocation Tracker Card --}}
                        <template x-if="selectedRecord && selectedRecord.location_name">
                            <div class="bg-gradient-to-br from-blue-50/20 to-white p-4.5 rounded-2xl border border-blue-100/60 shadow-xs space-y-3">
                                <span class="block text-[8px] font-black text-blue-500 uppercase tracking-widest flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px] text-blue-500">my_location</span> Geolocation Check-in Location
                                </span>
                                <div class="flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                                        <span class="material-symbols-outlined text-base">location_on</span>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-800 leading-normal" x-text="selectedRecord.location_name"></div>
                                        <div class="text-[9px] text-gray-400 font-bold mt-1" x-text="'Coordinates: ' + selectedRecord.latitude + ', ' + selectedRecord.longitude"></div>
                                    </div>
                                </div>
                                <div class="pt-2 border-t border-gray-100 flex">
                                    <a :href="'https://www.google.com/maps/search/?api=1&query='+selectedRecord.latitude+','+selectedRecord.longitude"
                                       target="_blank" class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase text-blue-600 hover:text-blue-700 transition-all hover:underline">
                                        <span class="material-symbols-outlined text-sm">map</span>Open Google Maps pin →
                                    </a>
                                </div>
                            </div>
                        </template>
                        
                        {{-- Approval box --}}
                        <template x-if="selectedRecord && ['permit','leave'].includes(selectedRecord.status)">
                            <div class="p-4 rounded-2xl border space-y-3 shadow-xs"
                                :class="{
                                    'bg-amber-50/40 border-amber-100 text-amber-800': selectedRecord.approval_status==='pending',
                                    'bg-emerald-50/40 border-emerald-100 text-emerald-800': selectedRecord.approval_status==='approved',
                                    'bg-rose-50/40 border-rose-100 text-rose-800': selectedRecord.approval_status==='rejected'
                                }">
                                <div class="flex items-center justify-between">
                                    <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Approval Status</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border border-current" x-text="selectedRecord.approval_status"></span>
                                </div>
                                <template x-if="selectedRecord.reject_reason">
                                    <div class="text-xs border-t border-black/5 pt-2.5">
                                        <span class="font-black text-[8px] text-gray-400 uppercase tracking-widest block mb-1">Reason / Notes</span>
                                        <p class="italic text-gray-700 font-medium" x-text="'&ldquo;' + selectedRecord.reject_reason + '&rdquo;'"></p>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        {{-- Proof (Photo or Document) --}}
                        <div>
                            <span class="block text-[8px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2 mb-3.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px] text-gray-400">description</span> Supporting Proof
                            </span>

                            {{-- Check-in photo --}}
                            <template x-if="selectedRecord && selectedRecord.photo_path">
                                <div class="space-y-3">
                                    <div class="rounded-2xl overflow-hidden border border-gray-150 shadow-inner bg-gray-50">
                                        <img :src="'/storage/'+selectedRecord.photo_path" class="w-full max-h-56 object-contain mx-auto" alt="Attendance photo">
                                    </div>
                                    <button type="button"
                                            @click="openProofViewer('/storage/'+selectedRecord.photo_path, false, 'Attendance Photo')"
                                            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase flex items-center justify-center gap-1.5 shadow-md shadow-blue-100 transition-all">
                                        <span class="material-symbols-outlined text-[14px]">fullscreen</span> View Full
                                    </button>
                                </div>
                            </template>

                            {{-- Leave / Permission document (image) --}}
                            <template x-if="selectedRecord && selectedRecord.document_path && isImagePath(selectedRecord.document_path)">
                                <div class="space-y-3">
                                    <div class="rounded-2xl overflow-hidden border border-gray-150 shadow-inner bg-gray-50">
                                        <img :src="'/storage/'+selectedRecord.document_path" class="w-full max-h-56 object-contain mx-auto" alt="Supporting proof">
                                    </div>
                                    <button type="button"
                                            @click="openProofViewer('/storage/'+selectedRecord.document_path, false, 'Supporting Proof')"
                                            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase flex items-center justify-center gap-1.5 shadow-md shadow-blue-100 transition-all">
                                        <span class="material-symbols-outlined text-[14px]">fullscreen</span> View Full
                                    </button>
                                </div>
                            </template>

                            {{-- Leave / Permission document (PDF) --}}
                            <template x-if="selectedRecord && selectedRecord.document_path && isPdfPath(selectedRecord.document_path)">
                                <div class="space-y-3">
                                    <div class="rounded-2xl overflow-hidden border border-gray-150 shadow-inner bg-white">
                                        <iframe :src="'/storage/'+selectedRecord.document_path" class="w-full h-52" title="PDF preview"></iframe>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button"
                                                @click="openProofViewer('/storage/'+selectedRecord.document_path, true, 'Supporting Proof')"
                                                class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase flex items-center justify-center gap-1.5 shadow-md shadow-blue-100 transition-all">
                                            <span class="material-symbols-outlined text-[14px]">fullscreen</span> View Full
                                        </button>
                                        <button type="button"
                                                @click="downloadProof('/storage/'+selectedRecord.document_path, selectedRecord.document_path.split('/').pop())"
                                                :disabled="downloadingProof"
                                                :class="downloadingProof ? 'opacity-70 pointer-events-none cursor-not-allowed' : 'hover:bg-gray-50'"
                                                class="flex-1 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-[10px] font-black uppercase flex items-center justify-center gap-1.5 transition-all">
                                            <template x-if="downloadingProof">
                                                <svg class="animate-spin h-4 w-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </template>
                                            <template x-if="!downloadingProof">
                                                <span class="material-symbols-outlined text-[14px]">download</span>
                                            </template>
                                            <span x-text="downloadingProof ? 'Downloading...' : 'Download'"></span>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="selectedRecord && !selectedRecord.photo_path && !selectedRecord.document_path">
                                <div class="py-7 text-center text-gray-300 italic text-xs bg-gray-50 rounded-2xl border border-gray-150 shadow-inner">
                                    No proof uploaded.
                                </div>
                            </template>
                        </div>
                        
                        {{-- Reject Form --}}
                        <div x-show="showRejectForm" class="p-4 bg-rose-50/40 border border-rose-100 rounded-2xl space-y-3 shadow-xs" style="display:none" x-cloak>
                            <span class="block text-[8px] font-black text-rose-800 uppercase tracking-widest flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px] text-rose-500">cancel</span> Rejection Reason
                            </span>
                            <form :action="'/admin/attendance/'+(selectedRecord ? selectedRecord.id : '')+'/reject'" method="POST" class="space-y-3">
                                @csrf
                                <textarea name="reject_reason" required placeholder="State reason for rejection..."
                                    class="w-full p-3 bg-white border border-rose-200 rounded-xl text-xs font-medium focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 h-20 resize-none transition-all"></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="showRejectForm=false" class="px-4 py-2 bg-gray-200 text-gray-700 text-[9px] font-black uppercase rounded-xl hover:bg-gray-300 transition-all">Cancel</button>
                                    <button type="submit" class="px-5 py-2 bg-rose-600 text-white text-[9px] font-black uppercase rounded-xl hover:bg-rose-700 shadow-md shadow-rose-100 transition-all">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    {{-- Panel Footer --}}
                    <div class="border-t border-gray-100 px-6 py-5 bg-gray-50 flex gap-3"
                         x-show="selectedRecord && ['permit','leave'].includes(selectedRecord?.status) && selectedRecord?.approval_status==='pending' && !showRejectForm" x-cloak>
                        <button type="button" @click="showRejectForm=true"
                            class="flex-1 py-2.5 bg-white border border-rose-100 text-rose-600 text-xs font-black uppercase rounded-xl hover:bg-rose-50 transition-all shadow-sm">
                            Reject
                        </button>
                        <form :action="'/admin/attendance/'+(selectedRecord ? selectedRecord.id : '')+'/approve'" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase rounded-xl transition-all shadow-md shadow-blue-100">
                                Approve Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Proof Viewer Modal --}}
<div x-show="showProofViewer"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display: none;"
     x-cloak>

    <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden max-h-[90vh] flex flex-col"
         @click.away="showProofViewer = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4">

        <div class="p-5 border-b border-gray-100 flex items-center gap-3 bg-gradient-to-r from-gray-50 to-white shrink-0">
            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                <span class="material-symbols-outlined text-[20px]" x-text="proofViewerIsPdf ? 'picture_as_pdf' : 'photo_camera'"></span>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-black text-gray-900 truncate" x-text="proofViewerTitle"></h3>
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">In-page preview</p>
            </div>
            <button @click="showProofViewer = false" class="ml-auto text-gray-400 hover:text-gray-650 transition-colors p-1.5 rounded-lg hover:bg-gray-100 shrink-0">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <div class="p-5 overflow-auto flex-1 bg-gray-50/50">
            <template x-if="proofViewerIsPdf">
                <iframe :src="proofViewerUrl" class="w-full h-[70vh] rounded-2xl border border-gray-200 bg-white shadow-inner" title="PDF viewer"></iframe>
            </template>
            <template x-if="!proofViewerIsPdf">
                <div class="rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-inner flex items-center justify-center">
                    <img :src="proofViewerUrl" class="w-full max-h-[75vh] object-contain" alt="Full preview">
                </div>
            </template>
        </div>
    </div>
</div>

</div>
</div>


</x-app-layout>
