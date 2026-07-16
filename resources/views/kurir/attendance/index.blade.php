<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">Daily Attendance</h2>
                <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-wider">Record your shift and check your history</p>
            </div>
            <div class="flex flex-col items-start md:items-end gap-2 shrink-0">
                <span class="text-xs font-black text-gray-400 uppercase tracking-widest">System Date</span>
                <span class="text-sm font-black text-blue-600 bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-100 whitespace-nowrap">{{ now()->format('l, d F Y') }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50" x-data="{
        showToast: {{ session('success') || session('error') ? 'true' : 'false' }},
        toastMessage: '{{ session('success') ?? session('error') ?? '' }}',
        toastTitle: '{{ session('success') ? (str_contains(session('success'), 'Leave request') ? 'Leave Request Sent' : (str_contains(session('success'), 'Permission request') ? 'Permission Request Sent' : 'Success')) : 'Alert' }}',
        toastType: '{{ session('success') ? 'success' : 'warning' }}',
        activeTab: 'checkin',
        historyTab: 'attendance',
        showRequestModal: false,
        requestType: 'permit',
        showViewerModal: false,
        viewerUrl: '',
        viewerIsPdf: false,
        viewerTitle: 'Document',
        attachmentPreview: null,
        attachmentFileName: '',
        attachmentIsImage: false,
        openDocumentViewer(url, filename) {
            this.viewerUrl = url;
            this.viewerIsPdf = (filename || url).toLowerCase().includes('.pdf');
            this.viewerTitle = filename || 'Document';
            this.showViewerModal = true;
        },
        openPhotoViewer(url) {
            this.viewerUrl = url;
            this.viewerIsPdf = false;
            this.viewerTitle = 'Attendance Photo';
            this.showViewerModal = true;
        },
        handleAttachmentChange(event) {
            const file = event.target.files[0];
            if (!file) {
                this.attachmentPreview = null;
                this.attachmentFileName = '';
                this.attachmentIsImage = false;
                return;
            }
            this.attachmentFileName = file.name;
            this.attachmentIsImage = file.type.startsWith('image/');
            if (this.attachmentIsImage) {
                const reader = new FileReader();
                reader.onload = (e) => { this.attachmentPreview = e.target.result; };
                reader.readAsDataURL(file);
            } else {
                this.attachmentPreview = null;
            }
        },
        resetAttachmentPreview() {
            this.attachmentPreview = null;
            this.attachmentFileName = '';
            this.attachmentIsImage = false;
            const input = document.getElementById('request-document-input');
            if (input) input.value = '';
        }
    }">
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

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- LEFT COLUMN: Attendance Form / Actions -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Location Indicator Card -->
                    <div id="location-indicator" class="p-4 bg-blue-50 border border-blue-150 text-blue-800 rounded-2xl font-bold flex items-center gap-3 shadow-sm">
                        <span class="material-symbols-outlined text-blue-500 animate-spin text-xl">autorenew</span>
                        <span id="location-text" class="text-xs uppercase tracking-wider">Acquiring current location...</span>
                    </div>

                    <!-- Main Action Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 text-center">
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-1">Current Time</span>
                            <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ now()->format('H:i') }}</h3>
                        </div>

                        <div class="p-6">
                            @if(!$attendance)
                                <!-- Step 1: Check-in -->
                                <div class="w-full space-y-6">
                                    <div class="relative rounded-2xl overflow-hidden bg-black aspect-[3/4] shadow-md border border-gray-100">
                                        <video id="video" class="w-full h-full object-cover" autoplay playsinline muted></video>
                                        <canvas id="canvas" class="hidden"></canvas>
                                        <div class="absolute inset-0 border-[15px] border-black/10 pointer-events-none"></div>
                                    </div>

                                    <form id="attendance-form" action="{{ route('kurir.attendance.check-in') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="photo" id="photo-input">
                                        <input type="hidden" name="latitude" id="lat-input">
                                        <input type="hidden" name="longitude" id="lon-input">
                                        <input type="hidden" name="location_name" id="loc-input">

                                        <button type="button" onclick="capturePhoto()"
                                                class="w-full bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-black py-4 rounded-2xl text-xs uppercase tracking-widest transition-all shadow-md shadow-blue-100 flex items-center justify-center gap-2">
                                            <span class="material-symbols-outlined text-[18px]">fingerprint</span> Check In Now
                                        </button>
                                    </form>
                                    <p class="text-center text-[9px] text-gray-400 font-bold uppercase tracking-widest">Webcam & coordinates will be saved</p>
                                </div>
                            @elseif($attendance->status === 'permit' || $attendance->status === 'leave')
                                @if($attendance->approval_status === 'approved')
                                    <!-- Active Approved Leave / Permission -->
                                    <div class="text-center space-y-5 py-6">
                                        @php
                                            $isLeave = $attendance->status === 'leave';
                                            $activeLabel = $isLeave ? 'Leave' : 'Permission';
                                            $activeMessage = $isLeave ? 'You are currently on Leave.' : 'You are currently on Permission.';
                                        @endphp

                                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full {{ $isLeave ? 'bg-indigo-100 text-indigo-600' : 'bg-blue-100 text-blue-600' }}">
                                            <span class="material-symbols-outlined text-4xl">{{ $isLeave ? 'beach_access' : 'event_available' }}</span>
                                        </div>

                                        <div class="p-6 rounded-2xl border-2 {{ $isLeave ? 'bg-indigo-50 border-indigo-200 text-indigo-900' : 'bg-blue-50 border-blue-200 text-blue-900' }} shadow-sm">
                                            <h4 class="text-lg font-black mb-1">{{ $activeMessage }}</h4>
                                            <p class="text-[10px] font-bold uppercase tracking-wider opacity-70">Attendance is not required today</p>
                                            <p class="text-xs font-bold mt-3 {{ $isLeave ? 'text-indigo-700' : 'text-blue-700' }}">
                                                {{ $activeLabel }} period: {{ \Carbon\Carbon::parse($attendance->date)->format('d F Y') }}
                                            </p>
                                        </div>

                                        @if($attendance->reject_reason)
                                            <div class="bg-gray-50 border border-gray-100 p-4 rounded-2xl text-left w-full">
                                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block mb-1">Note</span>
                                                <p class="text-[11px] italic text-gray-700 font-bold">"{{ $attendance->reject_reason }}"</p>
                                            </div>
                                        @endif

                                        @if($attendance->document_path)
                                            <button type="button"
                                                    @click="openDocumentViewer('{{ asset('storage/' . $attendance->document_path) }}', '{{ basename($attendance->document_path) }}')"
                                                    class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase {{ $isLeave ? 'text-indigo-600 hover:text-indigo-800' : 'text-blue-600 hover:text-blue-800' }} transition-colors">
                                                <span class="material-symbols-outlined text-[14px]">description</span> View Submitted Document
                                            </button>
                                        @endif
                                    </div>
                                @else
                                <!-- Pending / Rejected Request State -->
                                <div class="text-center space-y-5 py-6">
                                    @php
                                        $approvalColors = [
                                            'pending' => 'bg-amber-50 border-amber-200 text-amber-800',
                                            'approved' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                                            'rejected' => 'bg-rose-50 border-rose-200 text-rose-800',
                                        ];
                                        $appStyle = $approvalColors[$attendance->approval_status] ?? 'bg-gray-50 border-gray-200 text-gray-800';
                                        $typeLabel = $attendance->status === 'permit' ? 'Permission' : 'Leave / Vacation';
                                    @endphp
                                    
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full {{ $attendance->approval_status === 'approved' ? 'bg-emerald-100 text-emerald-600' : ($attendance->approval_status === 'rejected' ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600') }}">
                                        <span class="material-symbols-outlined text-3xl">
                                            {{ $attendance->approval_status === 'approved' ? 'verified_user' : ($attendance->approval_status === 'rejected' ? 'gpp_bad' : 'pending_actions') }}
                                        </span>
                                    </div>

                                    <div>
                                        <h4 class="text-lg font-black text-gray-900 mb-0.5">Request Submitted</h4>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">{{ $typeLabel }} for {{ \Carbon\Carbon::parse($attendance->date)->format('d F Y') }}</p>
                                    </div>

                                    <div class="p-5 border rounded-2xl {{ $appStyle }} flex flex-col items-center shadow-xs">
                                        <span class="text-[8px] font-black uppercase tracking-widest text-gray-400 mb-1">Approval status</span>
                                        <span class="px-3 py-0.5 inline-flex text-[10px] font-black rounded-full border border-current uppercase tracking-wider mb-2">
                                            {{ $attendance->approval_status }}
                                        </span>
                                        @if($attendance->reject_reason)
                                            <div class="mt-2 text-[10px] pt-3 border-t border-black/5 w-full">
                                                <span class="font-black text-[8px] text-gray-400 uppercase tracking-widest block mb-0.5">Note</span>
                                                <p class="italic text-gray-700 font-bold">"{{ $attendance->reject_reason }}"</p>
                                            </div>
                                        @endif
                                    </div>

                                    @if($attendance->document_path)
                                        <button type="button"
                                                @click="openDocumentViewer('{{ asset('storage/' . $attendance->document_path) }}', '{{ basename($attendance->document_path) }}')"
                                                class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-blue-600 hover:text-blue-800 transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">description</span> View Submitted Document
                                        </button>
                                    @endif
                                </div>
                                @endif
                            @elseif(!$attendance->check_out)
                                <!-- Step 2: Check-out -->
                                <div class="text-center space-y-6 py-6">
                                    <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full">
                                        <span class="material-symbols-outlined text-3xl">login</span>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-black text-gray-900">Checked In Successfully</h4>
                                        <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mt-1">Checked In at {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</p>
                                    </div>

                                    <div class="bg-gray-50 border border-gray-250 p-4 rounded-2xl text-left">
                                        <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest block mb-1">Check-in Location</span>
                                        <p class="text-[10px] font-bold text-gray-700 leading-normal">{{ $attendance->location_name }}</p>
                                    </div>

                                    <form id="checkout-form" action="{{ route('kurir.attendance.check-out') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="latitude" id="checkout-lat-input">
                                        <input type="hidden" name="longitude" id="checkout-lon-input">
                                        <input type="hidden" name="location_name" id="checkout-loc-input">

                                        <button type="submit"
                                                class="w-full bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-black py-4 rounded-2xl text-xs uppercase tracking-widest transition-all shadow-md shadow-rose-100 flex items-center justify-center gap-2">
                                            <span class="material-symbols-outlined text-[18px]">logout</span> Check Out Shift
                                        </button>
                                    </form>
                                </div>
                            @else
                                <!-- Step 3: Finished -->
                                <div class="text-center py-10 space-y-4">
                                    <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full">
                                        <span class="material-symbols-outlined text-3xl">done_all</span>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-black text-gray-900">Shift Completed</h4>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Checked out at {{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}</p>
                                    </div>
                                    <div class="pt-4 border-t border-gray-100 text-[10px] font-bold text-gray-400 uppercase tracking-wide">
                                        See you tomorrow!
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Leave / Permission Request Button -->
                    @if($canSubmitLeaveRequest)
                        <button type="button" @click="showRequestModal = true"
                                class="w-full bg-white hover:bg-blue-50 active:scale-[0.98] text-blue-600 font-black py-4 rounded-2xl text-xs uppercase tracking-widest transition-all border border-blue-100 shadow-sm flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">event_note</span>
                            Leave / Permission Request
                        </button>
                        <p class="text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest">
                            Annual quota: {{ $leaveQuotaUsed }} / {{ $leaveQuotaMax }} used
                        </p>
                    @else
                        <div class="w-full p-4 bg-amber-50 border border-amber-200 rounded-2xl text-center">
                            <p class="text-[10px] font-bold text-amber-800 leading-relaxed">
                                Your annual leave/permission quota has been fully used. You cannot submit another request this year.
                            </p>
                            <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mt-2">
                                Quota: {{ $leaveQuotaUsed }} / {{ $leaveQuotaMax }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- RIGHT COLUMN: History & Period Filters -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Period Filter Panel -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                         x-data="{ period: '{{ $period }}', dateVal: '{{ $dateVal }}', weekVal: '{{ $weekVal }}', monthVal: '{{ $filterMonth }}', yearVal: '{{ $filterYear }}' }">
                        <form method="GET" action="{{ route('kurir.attendance.index') }}" class="p-5 space-y-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <!-- Period Selection -->
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-blue-500 text-[18px] pointer-events-none">date_range</span>
                                    <select x-model="period" name="period"
                                            class="h-10 pl-10 pr-7 bg-blue-50 border border-blue-100 rounded-xl text-xs font-black text-blue-700 focus:outline-none appearance-none cursor-pointer">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-blue-400 text-[15px] pointer-events-none">expand_more</span>
                                </div>

                                <!-- Daily Picker -->
                                <div x-show="period==='daily'" x-transition class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none">calendar_today</span>
                                    <input type="date" name="date" x-model="dateVal"
                                           class="h-10 pl-10 pr-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none cursor-pointer">
                                </div>

                                <!-- Weekly Picker -->
                                <div x-show="period==='weekly'" x-transition class="relative" style="display:none">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none">calendar_view_week</span>
                                    <input type="week" name="week" x-model="weekVal"
                                           class="h-10 pl-10 pr-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none cursor-pointer">
                                </div>

                                <!-- Monthly Picker -->
                                <div x-show="period==='monthly'" x-transition class="relative" style="display:none">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none">calendar_month</span>
                                    <input type="month" name="filter_month" x-model="monthVal"
                                           class="h-10 pl-10 pr-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none cursor-pointer">
                                </div>

                                <!-- Yearly Picker -->
                                <div x-show="period==='yearly'" x-transition class="relative" style="display:none">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none">event</span>
                                    <select name="filter_year" x-model="yearVal"
                                            class="h-10 pl-10 pr-7 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none appearance-none cursor-pointer">
                                        @foreach(range(now()->year-3, now()->year+1) as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-[15px] pointer-events-none">expand_more</span>
                                </div>

                                <div class="flex items-center gap-2 ml-auto">
                                    <button type="submit"
                                            class="h-10 px-5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-[11px] font-black uppercase tracking-widest rounded-xl flex items-center gap-1.5 transition-all shadow-sm">
                                        <span class="material-symbols-outlined text-[16px]">filter_alt</span>Filter
                                    </button>
                                    <a href="{{ route('kurir.attendance.index') }}"
                                       class="h-10 px-4 bg-gray-150 text-gray-600 text-[11px] font-black uppercase tracking-widest rounded-xl flex items-center gap-1.5 transition-all">
                                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- History Container with Subtabs -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                        <!-- Subtab Headers -->
                        <div class="flex items-center gap-2 p-4 bg-gray-50/50 border-b border-gray-100">
                            <button type="button" @click="historyTab='attendance'"
                                    :class="historyTab==='attendance' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border border-gray-150 text-gray-500 hover:bg-gray-50'"
                                    class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-xs">checklist</span>
                                Attendance Log
                            </button>
                            <button type="button" @click="historyTab='leave'"
                                    :class="historyTab==='leave' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border border-gray-150 text-gray-500 hover:bg-gray-50'"
                                    class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-xs">event_busy</span>
                                Leave History ({{ $leaveHistory->count() }})
                            </button>
                            <button type="button" @click="historyTab='permit'"
                                    :class="historyTab==='permit' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border border-gray-150 text-gray-500 hover:bg-gray-50'"
                                    class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-xs">assignment_late</span>
                                Permission History ({{ $permissionHistory->count() }})
                            </button>
                        </div>

                        <!-- 1. Attendance Log Panel -->
                        <div x-show="historyTab==='attendance'" class="divide-y divide-gray-100 max-h-[480px] overflow-y-auto">
                            @forelse($dates as $dateStr)
                                @php
                                    $rec = $attendances->get($dateStr);
                                    $dateFormatted = \Carbon\Carbon::parse($dateStr)->format('d M Y');
                                    $dayName = \Carbon\Carbon::parse($dateStr)->format('l');
                                @endphp

                                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-gray-50/50 transition-colors">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black text-gray-900">{{ $dateFormatted }}</span>
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">({{ $dayName }})</span>
                                        </div>

                                        @if($rec)
                                            <!-- If record exists -->
                                            @if(in_array($rec->status, ['present', 'late']))
                                                <!-- Present or Late checkin -->
                                                <div class="mt-2 text-xs font-bold text-gray-500 flex flex-wrap items-center gap-x-3 gap-y-1">
                                                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm text-blue-500">login</span> In: {{ $rec->check_in ? substr($rec->check_in, 0, 5) : '--:--' }}</span>
                                                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm text-gray-400">logout</span> Out: {{ $rec->check_out ? substr($rec->check_out, 0, 5) : '--:--' }}</span>
                                                </div>

                                                @if($rec->location_name)
                                                    <div class="mt-1 text-[10px] font-medium text-gray-400 flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-xs text-rose-500">location_on</span>
                                                        <span>{{ Str::limit($rec->location_name, 50) }}</span>
                                                        @if($rec->latitude && $rec->longitude)
                                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $rec->latitude }},{{ $rec->longitude }}" target="_blank" class="text-blue-500 hover:underline font-bold text-[9px] uppercase ml-1 flex items-center gap-0.5">
                                                                <span class="material-symbols-outlined text-[10px]">map</span>Pin
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endif
                                            @elseif(in_array($rec->status, ['permit', 'leave']))
                                                <!-- Permit / Leave requested but might be rejected or pending -->
                                                @if($rec->approval_status === 'approved')
                                                    <div class="mt-1.5 text-xs text-emerald-700 font-bold uppercase tracking-wide flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-sm text-emerald-500">verified</span>
                                                        Approved {{ $rec->status }}
                                                    </div>
                                                @elseif($rec->approval_status === 'rejected')
                                                    <!-- Rejected request means absent (alpha) -->
                                                    <div class="mt-1.5 text-xs text-rose-700 font-bold uppercase tracking-wide flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-sm text-rose-500">cancel</span>
                                                        Absent (Unexcused) — Request Rejected
                                                    </div>
                                                    @if($rec->reject_reason)
                                                        <p class="text-[10px] font-medium text-rose-600/80 italic mt-0.5">Reason: "{{ $rec->reject_reason }}"</p>
                                                    @endif
                                                @else
                                                    <!-- Pending request -->
                                                    <div class="mt-1.5 text-xs text-amber-700 font-bold uppercase tracking-wide flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-sm text-amber-500">pending_actions</span>
                                                        Pending {{ $rec->status }} request
                                                    </div>
                                                @endif
                                            @else
                                                <!-- Explicitly absent -->
                                                <div class="mt-1.5 text-xs text-rose-700 font-bold uppercase tracking-wide flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-sm text-rose-500">gpp_bad</span>
                                                    Absent (Unexcused)
                                                </div>
                                            @endif
                                        @else
                                            <!-- No record -> automatically Absent (Unexcused) -->
                                            <div class="mt-1.5 text-xs text-rose-700 font-bold uppercase tracking-wide flex items-center gap-1">
                                                <span class="material-symbols-outlined text-sm text-rose-500">gpp_bad</span>
                                                Absent (Unexcused)
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3">
                                        @if($rec)
                                            <!-- Status Badge -->
                                            @if(in_array($rec->status, ['present', 'late']))
                                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border {{ $rec->status === 'present' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100' }}">
                                                    {{ $rec->status }}
                                                </span>
                                            @elseif(in_array($rec->status, ['permit', 'leave']))
                                                @if($rec->approval_status === 'approved')
                                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border {{ $rec->status === 'permit' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-indigo-50 text-indigo-600 border-indigo-100' }}">
                                                        {{ $rec->status }}
                                                    </span>
                                                @elseif($rec->approval_status === 'rejected')
                                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border bg-rose-50 text-rose-600 border-rose-100">
                                                        absent
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border bg-amber-50 text-amber-600 border-amber-100">
                                                        pending
                                                    </span>
                                                @endif
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border bg-rose-50 text-rose-600 border-rose-100">
                                                    absent
                                                </span>
                                            @endif

                                            <!-- Proof Trigger -->
                                            @if($rec->photo_path)
                                                <button type="button"
                                                        @click="openPhotoViewer('{{ asset('storage/' . $rec->photo_path) }}')"
                                                        class="w-16 h-16 rounded-xl overflow-hidden border-2 border-gray-200 shadow-md hover:scale-105 hover:border-blue-300 transition-all">
                                                    <img src="{{ asset('storage/' . $rec->photo_path) }}" class="w-full h-full object-cover" alt="Attendance photo">
                                                </button>
                                            @elseif($rec->document_path)
                                                <button type="button"
                                                        @click="openDocumentViewer('{{ asset('storage/' . $rec->document_path) }}', '{{ basename($rec->document_path) }}')"
                                                        class="w-16 h-16 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border-2 border-blue-100 hover:scale-105 hover:border-blue-300 transition-all" title="View Document">
                                                    <span class="material-symbols-outlined text-2xl">description</span>
                                                </button>
                                            @endif
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border bg-rose-50 text-rose-600 border-rose-100">
                                                absent
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 text-center text-gray-400 italic text-sm font-bold">No records found for the selected period.</div>
                            @endforelse
                        </div>

                        <!-- 2. Leave History Panel -->
                        <div x-show="historyTab==='leave'" class="divide-y divide-gray-100 max-h-[480px] overflow-y-auto" style="display:none">
                            @forelse($leaveHistory as $rec)
                                <div class="p-5 flex items-center justify-between gap-4">
                                    <div>
                                        <div class="text-sm font-black text-gray-900">{{ \Carbon\Carbon::parse($rec->date)->format('d M Y') }}</div>
                                        @if($rec->reject_reason)
                                            <p class="text-[10px] font-medium text-gray-500 italic mt-0.5">Note: "{{ $rec->reject_reason }}"</p>
                                        @endif
                                        @if($rec->document_path)
                                            <button type="button"
                                                    @click="openDocumentViewer('{{ asset('storage/' . $rec->document_path) }}', '{{ basename($rec->document_path) }}')"
                                                    class="inline-flex items-center gap-0.5 text-[9px] font-black uppercase text-blue-500 hover:text-blue-700 mt-1 transition-colors">
                                                <span class="material-symbols-outlined text-[10px]">description</span>Document
                                            </button>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border
                                            {{ $rec->approval_status === 'approved' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : ($rec->approval_status === 'rejected' ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-amber-50 text-amber-600 border-amber-100') }}">
                                            {{ $rec->approval_status }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 text-center text-gray-400 italic text-sm font-bold">No leave requests found.</div>
                            @endforelse
                        </div>

                        <!-- 3. Permission History Panel -->
                        <div x-show="historyTab==='permit'" class="divide-y divide-gray-100 max-h-[480px] overflow-y-auto" style="display:none">
                            @forelse($permissionHistory as $rec)
                                <div class="p-5 flex items-center justify-between gap-4">
                                    <div>
                                        <div class="text-sm font-black text-gray-900">{{ \Carbon\Carbon::parse($rec->date)->format('d M Y') }}</div>
                                        @if($rec->reject_reason)
                                            <p class="text-[10px] font-medium text-gray-500 italic mt-0.5">Note: "{{ $rec->reject_reason }}"</p>
                                        @endif
                                        @if($rec->document_path)
                                            <button type="button"
                                                    @click="openDocumentViewer('{{ asset('storage/' . $rec->document_path) }}', '{{ basename($rec->document_path) }}')"
                                                    class="inline-flex items-center gap-0.5 text-[9px] font-black uppercase text-blue-500 hover:text-blue-700 mt-1 transition-colors">
                                                <span class="material-symbols-outlined text-[10px]">description</span>Document
                                            </button>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border
                                            {{ $rec->approval_status === 'approved' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : ($rec->approval_status === 'rejected' ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-amber-50 text-amber-600 border-amber-100') }}">
                                            {{ $rec->approval_status }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 text-center text-gray-400 italic text-sm font-bold">No permission requests found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Leave / Permission Request Modal --}}
        <div x-show="showRequestModal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             x-cloak>

            <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden"
                 @click.away="showRequestModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">

                <div class="p-6 border-b border-gray-100 flex items-center gap-3 bg-gradient-to-r from-gray-50 to-white">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                        <span class="material-symbols-outlined text-[20px]">event_note</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-gray-900">Leave / Permission Request</h3>
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Submit absence or leave application</p>
                    </div>
                    <button @click="showRequestModal = false; resetAttachmentPreview()" class="ml-auto text-gray-400 hover:text-gray-650 transition-colors p-1.5 rounded-lg hover:bg-gray-100">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form action="{{ route('kurir.attendance.request') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Request Type <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-[18px]">category</span>
                            <select name="type" x-model="requestType" required
                                    class="w-full text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-10 pr-10 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-50 shadow-inner appearance-none cursor-pointer">
                                <option value="permit">Permission</option>
                                <option value="leave">Leave / Vacation</option>
                            </select>
                            <span class="material-symbols-outlined absolute right-3 top-2.5 text-gray-400 text-[18px] pointer-events-none">expand_more</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Target Date <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-[18px]">calendar_today</span>
                            <input type="date" name="date" required value="{{ today()->toDateString() }}"
                                   class="w-full text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-50 shadow-inner">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Supporting Proof (Photo / PDF) <span class="text-rose-500">*</span></label>
                        <input type="file" id="request-document-input" name="document" required accept="image/*,.pdf"
                               @change="handleAttachmentChange($event)"
                               class="w-full text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-50 file:mr-4 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-[9px] file:font-black file:uppercase file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

                        {{-- Attachment Preview --}}
                        <div x-show="attachmentFileName" x-cloak class="mt-3 p-4 bg-gray-50 border border-gray-100 rounded-2xl">
                            <template x-if="attachmentIsImage && attachmentPreview">
                                <div class="space-y-2">
                                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Image Preview</span>
                                    <div class="rounded-xl overflow-hidden border border-gray-200 bg-white">
                                        <img :src="attachmentPreview" class="w-full max-h-48 object-contain" alt="Attachment preview">
                                    </div>
                                </div>
                            </template>
                            <template x-if="!attachmentIsImage && attachmentFileName">
                                <div class="flex items-center gap-3 p-3 bg-white border border-blue-100 rounded-xl">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-xl">picture_as_pdf</span>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Document Selected</span>
                                        <p class="text-xs font-bold text-gray-800 truncate" x-text="attachmentFileName"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Reason Note <span class="text-rose-500">*</span></label>
                        <textarea name="reason" required placeholder="Explain why you are applying..."
                                  class="w-full text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl px-4 py-3 h-24 resize-none focus:border-blue-500 focus:ring-2 focus:ring-blue-50 shadow-inner"></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showRequestModal = false; resetAttachmentPreview()"
                                class="w-1/3 text-center py-3 bg-white border border-gray-200 text-gray-700 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 active:scale-95 transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-2/3 text-center py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">send</span>
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Document / Photo Viewer Modal --}}
        <div x-show="showViewerModal"
             class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             x-cloak>

            <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden max-h-[90vh] flex flex-col"
                 @click.away="showViewerModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">

                <div class="p-5 border-b border-gray-100 flex items-center gap-3 bg-gradient-to-r from-gray-50 to-white shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                        <span class="material-symbols-outlined text-[20px]" x-text="viewerIsPdf ? 'picture_as_pdf' : (viewerTitle === 'Attendance Photo' ? 'photo_camera' : 'description')"></span>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-black text-gray-900 truncate" x-text="viewerTitle"></h3>
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">In-page preview</p>
                    </div>
                    <button @click="showViewerModal = false" class="ml-auto text-gray-400 hover:text-gray-650 transition-colors p-1.5 rounded-lg hover:bg-gray-100 shrink-0">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <div class="p-5 overflow-auto flex-1 bg-gray-50/50">
                    <template x-if="viewerIsPdf">
                        <iframe :src="viewerUrl" class="w-full h-[60vh] rounded-2xl border border-gray-200 bg-white shadow-inner" title="Document preview"></iframe>
                    </template>
                    <template x-if="!viewerIsPdf">
                        <div class="rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-inner flex items-center justify-center">
                            <img :src="viewerUrl" class="w-full max-h-[65vh] object-contain" alt="Preview">
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const photoInput = document.getElementById('photo-input');
        const form = document.getElementById('attendance-form');

        let isCameraReady = false;

        if (video) {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
                .then(stream => {
                    video.srcObject = stream;
                    // Pastikan video benar-benar memutar sebelum dianggap siap
                    video.onloadeddata = () => {
                        video.play().then(() => {
                            isCameraReady = true;
                        });
                    };
                })
                .catch(err => {
                    console.error("Camera error: ", err);
                    alert('Tidak bisa mengakses kamera. Pastikan izin kamera sudah diberikan.');
                });
        }

        function capturePhoto() {
            if (!isCameraReady || video.videoWidth === 0 || video.videoHeight === 0) {
                alert('Kamera belum siap, tunggu sebentar lalu coba lagi.');
                return;
            }

            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);

            const dataUrl = canvas.toDataURL('image/png');
            photoInput.value = dataUrl;
            form.submit();
        }

        // Geolocation reverse geocoding
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                const latInput = document.getElementById('lat-input');
                const lonInput = document.getElementById('lon-input');
                const locInput = document.getElementById('loc-input');

                const colatInput = document.getElementById('checkout-lat-input');
                const colonInput = document.getElementById('checkout-lon-input');
                const colocInput = document.getElementById('checkout-loc-input');

                const indicatorText = document.getElementById('location-text');
                const indicator = document.getElementById('location-indicator');
                
                if (latInput) latInput.value = lat;
                if (lonInput) lonInput.value = lon;
                if (colatInput) colatInput.value = lat;
                if (colonInput) colonInput.value = lon;
                
                // Fetch reverse geocode address using OpenStreetMap Nominatim API
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`, {
                    headers: {
                        'User-Agent': 'LaundryanAttendance/1.0'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    const address = data.display_name || `Coordinates: ${lat}, ${lon}`;
                    if (locInput) locInput.value = address;
                    if (colocInput) colocInput.value = address;
                    if (indicatorText) indicatorText.textContent = "Location acquired: " + address;
                    if (indicator) {
                        indicator.className = "p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-2xl font-bold flex items-center gap-3 shadow-sm";
                        indicator.querySelector('.material-symbols-outlined').textContent = "location_on";
                        indicator.querySelector('.material-symbols-outlined').className = "material-symbols-outlined text-emerald-500 text-xl";
                    }
                })
                .catch(err => {
                    console.error("Geocoding fetch failed:", err);
                    const fallback = `Coordinates: ${lat}, ${lon}`;
                    if (locInput) locInput.value = fallback;
                    if (colocInput) colocInput.value = fallback;
                    if (indicatorText) indicatorText.textContent = "Location coordinates acquired.";
                });
            }, function(error) {
                console.error("Geolocation failed:", error);
                const indicatorText = document.getElementById('location-text');
                const indicator = document.getElementById('location-indicator');
                if (indicatorText) indicatorText.textContent = "Unable to acquire location coordinates. Please grant location access.";
                if (indicator) {
                    indicator.className = "p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-2xl font-bold flex items-center gap-3 shadow-sm";
                    indicator.querySelector('.material-symbols-outlined').textContent = "gpp_bad";
                    indicator.querySelector('.material-symbols-outlined').className = "material-symbols-outlined text-rose-500 text-xl";
                }
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        } else {
            const indicatorText = document.getElementById('location-text');
            if (indicatorText) indicatorText.textContent = "Geolocation is not supported by your browser.";
        }
    </script>
</x-app-layout>
