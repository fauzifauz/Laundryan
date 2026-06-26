<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daily Attendance') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-8">
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl font-bold flex items-center gap-3">
                            <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl font-bold flex items-center gap-3">
                            <span class="material-symbols-outlined text-rose-500">error</span>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <!-- Location Status Indicator -->
                    <div id="location-indicator" class="mb-6 p-4 bg-blue-50 border border-blue-150 text-blue-800 rounded-xl font-medium flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-500 animate-spin">autorenew</span>
                        <span id="location-text">Acquiring current location coordinates...</span>
                    </div>

                    <div class="flex flex-col items-center">
                        <div class="text-center mb-10">
                            <h3 class="text-3xl font-black text-gray-900 mb-2">{{ now()->format('H:i') }}</h3>
                            <p class="text-sm text-gray-500 font-bold uppercase tracking-widest">{{ now()->format('l, d F Y') }}</p>
                        </div>

                        @if(!$attendance)
                            <!-- Step 1: Check-in / Request Izin or Cuti Tabs -->
                            <div x-data="{ activeTab: 'checkin' }" class="w-full max-w-md">
                                <!-- Tab Switcher Header -->
                                <div class="flex border-b border-gray-150 mb-8">
                                    <button @click="activeTab = 'checkin'" :class="activeTab === 'checkin' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600'"
                                        class="w-1/2 pb-3.5 text-center border-b-2 font-black text-xs uppercase tracking-widest transition-all">
                                        Check In Now
                                    </button>
                                    <button @click="activeTab = 'request'" :class="activeTab === 'request' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600'"
                                        class="w-1/2 pb-3.5 text-center border-b-2 font-black text-xs uppercase tracking-widest transition-all">
                                        Request Izin / Cuti
                                    </button>
                                </div>

                                <!-- Webcam Check-in Tab -->
                                <div x-show="activeTab === 'checkin'" class="space-y-6">
                                    <div class="relative rounded-3xl overflow-hidden bg-black aspect-[3/4] shadow-2xl border-4 border-white">
                                        <video id="video" class="w-full h-full object-cover" autoplay playsinline></video>
                                        <canvas id="canvas" class="hidden"></canvas>
                                        <div class="absolute inset-0 border-[30px] border-black/10 pointer-events-none"></div>
                                    </div>

                                    <form id="attendance-form" action="{{ route('karyawan.attendance.check-in') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="photo" id="photo-input">
                                        <input type="hidden" name="latitude" id="lat-input">
                                        <input type="hidden" name="longitude" id="lon-input">
                                        <input type="hidden" name="location_name" id="loc-input">
                                        
                                        <button type="button" onclick="capturePhoto()" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl text-lg hover:bg-blue-700 transition-all shadow-xl active:scale-95">
                                            Check In Now
                                        </button>
                                    </form>
                                    <p class="text-center text-[10px] text-gray-400 font-bold uppercase tracking-tighter">Your photo and location will be recorded</p>
                                </div>

                                <!-- Request Permit/Leave Tab -->
                                <div x-show="activeTab === 'request'" class="space-y-6" style="display: none;">
                                    <form action="{{ route('karyawan.attendance.request') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Request Type</label>
                                            <select name="type" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 font-bold text-gray-700">
                                                <option value="permit">Izin (Permit)</option>
                                                <option value="leave">Cuti (Leave / Vacation)</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Target Date</label>
                                            <input type="date" name="date" required value="{{ today()->toDateString() }}" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 font-bold text-gray-700">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Supporting Proof (PDF/Image)</label>
                                            <input type="file" name="document" required accept="image/*,.pdf" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 font-medium text-gray-700 file:mr-4 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Explanation Note</label>
                                            <textarea name="reason" placeholder="Provide notes or reasons for this request..." class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 font-medium h-24 resize-none"></textarea>
                                        </div>

                                        <button type="submit" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl text-lg hover:bg-blue-700 transition-all shadow-xl active:scale-95">
                                            Submit Request
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @elseif($attendance->status === 'permit' || $attendance->status === 'leave')
                            <!-- Excused Card (Permit/Leave) -->
                            <div class="w-full max-w-md text-center space-y-6 py-10">
                                @php
                                    $approvalColors = [
                                        'pending' => 'bg-amber-50 border-amber-200 text-amber-800',
                                        'approved' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                                        'rejected' => 'bg-rose-50 border-rose-200 text-rose-800',
                                    ];
                                    $appStyle = $approvalColors[$attendance->approval_status] ?? 'bg-gray-50 border-gray-200 text-gray-800';
                                    $typeLabel = $attendance->status === 'permit' ? 'Izin (Permit)' : 'Cuti (Leave)';
                                @endphp
                                
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4 {{ $attendance->approval_status === 'approved' ? 'bg-emerald-100 text-emerald-600' : ($attendance->approval_status === 'rejected' ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600') }}">
                                    <span class="material-symbols-outlined text-4xl">
                                        {{ $attendance->approval_status === 'approved' ? 'verified_user' : ($attendance->approval_status === 'rejected' ? 'gpp_bad' : 'pending_actions') }}
                                    </span>
                                </div>

                                <div>
                                    <h4 class="text-2xl font-black text-gray-900 mb-1">Request Submitted</h4>
                                    <p class="text-sm text-gray-500 font-bold uppercase tracking-wider mb-6">{{ $typeLabel }} for {{ \Carbon\Carbon::parse($attendance->date)->format('d F Y') }}</p>
                                </div>

                                <div class="p-6 border rounded-3xl {{ $appStyle }} flex flex-col items-center shadow-sm">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Status</span>
                                    <span class="px-3.5 py-1 inline-flex text-xs font-black rounded-full border border-current uppercase tracking-wider mb-3 shadow-sm">
                                        {{ $attendance->approval_status }}
                                    </span>
                                    @if($attendance->reject_reason)
                                        <div class="mt-2 text-xs pt-3 border-t border-black/5 w-full text-center">
                                            <span class="font-bold text-[9px] text-gray-400 uppercase tracking-widest block mb-1">Note / Response</span>
                                            <p class="italic text-gray-700 font-medium">"{{ $attendance->reject_reason }}"</p>
                                        </div>
                                    @endif
                                </div>

                                @if($attendance->document_path)
                                    <div class="mt-4">
                                        <a href="{{ asset('storage/' . $attendance->document_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-black uppercase text-blue-600 hover:underline">
                                            <span class="material-symbols-outlined text-sm">description</span>
                                            View Submitted Document Proof
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @elseif(!$attendance->check_out)
                            <!-- Step 2: Check-out -->
                            <div class="w-full max-w-md text-center space-y-8 py-10">
                                <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 text-green-600 rounded-full mb-4">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold text-gray-900">Checked In at {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</h4>
                                    <p class="text-sm text-gray-500">Status: <span class="font-bold uppercase text-blue-600">{{ $attendance->status }}</span></p>
                                </div>
                                
                                <form action="{{ route('karyawan.attendance.check-out') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-gray-900 text-white font-black py-4 rounded-2xl text-lg hover:bg-red-600 transition-all shadow-xl active:scale-95">
                                        Check Out
                                    </button>
                                </form>
                            </div>
                        @else
                            <!-- Step 3: Finished -->
                            <div class="w-full max-w-md text-center py-20">
                                <h4 class="text-2xl font-bold text-gray-900 mb-2">Shift Completed!</h4>
                                <p class="text-sm text-gray-500">See you tomorrow!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const photoInput = document.getElementById('photo-input');
        const form = document.getElementById('attendance-form');

        if (video) {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
                .then(stream => { video.srcObject = stream; })
                .catch(err => { console.error("Camera error: ", err); });
        }

        function capturePhoto() {
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
                const indicatorText = document.getElementById('location-text');
                const indicator = document.getElementById('location-indicator');
                
                if (latInput) latInput.value = lat;
                if (lonInput) lonInput.value = lon;
                
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
                    if (indicatorText) indicatorText.textContent = "Location acquired: " + address;
                    if (indicator) {
                        indicator.className = "mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl font-medium flex items-center gap-3";
                        indicator.querySelector('.material-symbols-outlined').textContent = "location_on";
                        indicator.querySelector('.material-symbols-outlined').className = "material-symbols-outlined text-emerald-500";
                    }
                })
                .catch(err => {
                    console.error("Geocoding fetch failed:", err);
                    const fallback = `Coordinates: ${lat}, ${lon}`;
                    if (locInput) locInput.value = fallback;
                    if (indicatorText) indicatorText.textContent = "Location coordinates acquired.";
                });
            }, function(error) {
                console.error("Geolocation failed:", error);
                const indicatorText = document.getElementById('location-text');
                const indicator = document.getElementById('location-indicator');
                if (indicatorText) indicatorText.textContent = "Unable to acquire location coordinates. Please grant location access.";
                if (indicator) {
                    indicator.className = "mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl font-medium flex items-center gap-3";
                    indicator.querySelector('.material-symbols-outlined').textContent = "gpp_bad";
                    indicator.querySelector('.material-symbols-outlined').className = "material-symbols-outlined text-rose-500";
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
