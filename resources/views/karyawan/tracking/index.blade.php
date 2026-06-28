<x-app-layout>
    <!-- Modern Typography & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    
    <!-- Leaflet Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

    <style>
        :root {
            --primary: #005bc0;
            --primary-dark: #004899;
            --accent: #F59E0B;
            --success: #10B981;
            --danger: #EF4444;
            --bg-glass: rgba(255, 255, 255, 0.85);
            --bg-glass-dark: rgba(255, 255, 255, 0.95);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC;
        }

        /* Full Screen Map Layout */
        .tracking-container {
            height: calc(100vh - 64px); /* Subtract header height */
            position: relative;
            overflow: hidden;
            background: #E2E8F0;
        }

        #trackingMap {
            height: 100%;
            width: 100%;
            z-index: 1;
        }

        /* Glassmorphism Sidebar */
        .glass-panel {
            background: var(--bg-glass);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border-radius: 24px;
        }

        .sidebar-tracking {
            position: absolute;
            top: 24px;
            right: 24px;
            bottom: 24px;
            width: 380px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Floating Top Bar */
        .top-control-bar {
            position: absolute;
            top: 24px;
            left: 24px;
            right: 428px; /* Offset for sidebar */
            z-index: 10001; /* Must be higher than any other map overlay */
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            pointer-events: auto;
        }

        #toggleHeatmap {
            cursor: pointer !important;
        }

        /* Courier Markers */
        .courier-marker {
            width: 44px !important;
            height: 44px !important;
            background: var(--primary);
            border: 3px solid #fff;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 14px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .courier-marker > div {
            transform: rotate(45deg);
            text-transform: uppercase;
        }
        .courier-marker.pickup { background: #F59E0B !important; }
        .courier-marker.delivery { background: #10B981 !important; }
        .courier-marker.idle { background: #3B82F6 !important; }
        .courier-marker.offline { background: #64748B !important; filter: grayscale(0.5); }
        .courier-marker:hover { transform: rotate(-45deg) scale(1.1); z-index: 1001 !important; }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.2); }

        /* Routing Overlays */
        .leaflet-routing-container { display: none !important; }

        /* Animations */
        @keyframes pulse-soft {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }
        .pulse-active { animation: pulse-soft 2s infinite ease-in-out; }

        /* Card Styles */
        .courier-card {
            border: 1px solid rgba(0,0,0,0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
        }
        .courier-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.06);
            border-color: var(--primary);
        }
    </style>

    <div class="tracking-container">
        <!-- Main Map -->
        <div id="trackingMap"></div>

        <!-- Top Floating Controls -->
        <div class="top-control-bar">
            <button onclick="resetFocus()" class="glass-panel p-2 text-gray-500 hover:text-blue-600 transition-all shadow-lg cursor-pointer" title="Reset View">
                <span class="material-symbols-outlined text-2xl">restart_alt</span>
            </button>

            <div class="glass-panel px-4 py-2 flex items-center gap-3 flex-1">
                <span class="material-symbols-outlined text-gray-400">search</span>
                <input type="text" id="searchCourier" placeholder="Search courier name..." class="bg-transparent border-none focus:ring-0 w-full text-sm font-medium text-gray-700">
            </div>
            
            <div class="glass-panel px-4 py-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-gray-400">tune</span>
                <select id="filterCourier" class="bg-transparent border-none focus:ring-0 text-sm font-bold text-gray-700 cursor-pointer">
                    <option value="all">All Status</option>
                    <option value="pickup">On Pickup</option>
                    <option value="delivery">On Delivery</option>
                    <option value="idle">Idle (Waiting)</option>
                    <option value="offline">Offline / No Signal</option>
                </select>
            </div>

            <button id="toggleHeatmap" class="glass-panel px-6 py-2 flex items-center gap-2 hover:bg-white transition-all text-orange-600 font-bold text-sm shadow-lg cursor-pointer">
                <span class="material-symbols-outlined text-xl">local_fire_department</span>
                <span>Heatmap</span>
            </button>
        </div>

        <!-- Right Sidebar -->
        <aside class="sidebar-tracking glass-panel">
            <div class="p-6 pb-2">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 tracking-tight leading-none">Courier Monitor</h2>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1.5">Real-time Operations</p>
                    </div>
                    <div class="bg-blue-50 text-blue-600 p-2 rounded-2xl">
                        <span class="material-symbols-outlined text-xl font-bold">local_shipping</span>
                    </div>
                </div>

                <!-- Live Clock -->
                <div class="flex items-center justify-between mb-4 bg-gray-50 rounded-2xl px-4 py-2.5 border border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-400 text-base">schedule</span>
                        <span id="live-clock" class="text-sm font-black text-gray-800 tabular-nums">--:--:--</span>
                    </div>
                    <span id="live-date" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">--- --, ----</span>
                </div>

                <!-- Courier Stats Grid (4 stats) -->
                <div class="grid grid-cols-4 gap-2 mb-4" id="courier-stats">
                    <div id="stat-card-total" onclick="filterByStat('all')" class="stat-card bg-gray-50/80 rounded-2xl p-3 border border-gray-100 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-100 hover:border-gray-300 transition-all active:scale-95">
                        <span class="text-sm font-black text-gray-900 leading-none" id="stat-total">0</span>
                        <span class="text-[7px] font-black text-gray-400 uppercase mt-1 tracking-tighter">Total</span>
                    </div>
                    <div id="stat-card-active" onclick="filterByStat('active')" class="stat-card bg-emerald-50/50 rounded-2xl p-3 border border-emerald-100 flex flex-col items-center justify-center cursor-pointer hover:bg-emerald-100 hover:border-emerald-300 transition-all active:scale-95">
                        <span class="text-sm font-black text-emerald-600 leading-none" id="stat-active">0</span>
                        <span class="text-[7px] font-black text-emerald-500 uppercase mt-1 tracking-tighter">Active</span>
                    </div>
                    <div id="stat-card-idle" onclick="filterByStat('idle')" class="stat-card bg-blue-50/50 rounded-2xl p-3 border border-blue-100 flex flex-col items-center justify-center cursor-pointer hover:bg-blue-100 hover:border-blue-300 transition-all active:scale-95">
                        <span class="text-sm font-black text-blue-600 leading-none" id="stat-idle">0</span>
                        <span class="text-[7px] font-black text-blue-500 uppercase mt-1 tracking-tighter">Idle</span>
                    </div>
                    <div id="stat-card-offline" onclick="filterByStat('offline')" class="stat-card bg-slate-50/50 rounded-2xl p-3 border border-slate-100 flex flex-col items-center justify-center cursor-pointer hover:bg-slate-100 hover:border-slate-300 transition-all active:scale-95">
                        <span class="text-sm font-black text-slate-500 leading-none" id="stat-offline">0</span>
                        <span class="text-[7px] font-black text-slate-400 uppercase mt-1 tracking-tighter">Offline</span>
                    </div>
                </div>

                <!-- Search & Filter -->
                <div class="space-y-3 mb-6">
                    <div id="wsIndicator" class="flex items-center gap-2">
                        <div class="relative flex h-2.5 w-2.5">
                            <span id="wsPing" class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-gray-400"></span>
                            <span id="wsDot" class="relative inline-flex rounded-full h-2.5 w-2.5 bg-gray-400"></span>
                        </div>
                        <p id="wsLabel" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Connecting...</p>
                    </div>

                    <button onclick="resetFocus()" class="w-full bg-gray-900 text-white text-[10px] font-black py-2.5 rounded-xl hover:bg-black active:scale-95 transition-all flex items-center justify-center gap-2 shadow-lg">
                        <span class="material-symbols-outlined text-sm">filter_alt_off</span>
                        RESET VIEW
                    </button>
                </div>
            </div>

            <div id="courierList" class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar">
                <!-- Skeleton Loader -->
                <div class="animate-pulse space-y-4">
                    <div class="h-24 bg-gray-100 rounded-2xl w-full"></div>
                    <div class="h-24 bg-gray-100 rounded-2xl w-full"></div>
                    <div class="h-24 bg-gray-100 rounded-2xl w-full"></div>
                </div>
            </div>

            <div class="p-4 bg-gray-50/50 rounded-b-[24px] border-t border-gray-100">
                <div class="flex flex-wrap justify-center gap-x-6 gap-y-3 text-[10px] font-black uppercase tracking-wider text-gray-400">
                    <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Pickup</div>
                    <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Delivery</div>
                    <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-600"></span> Idle</div>
                    <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-slate-400"></span> Offline</div>
                </div>
            </div>
        </aside>

        <!-- Bottom Floating Info Bar -->
        <div class="absolute bottom-6 left-[70px] z-[1000] flex flex-wrap gap-3" style="right: 428px;">
            <div class="glass-panel px-5 py-3 flex items-center gap-3">
                <span class="material-symbols-outlined text-amber-500 text-lg">assignment</span>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Active Orders</p>
                    <p class="text-base font-black text-gray-900 leading-tight" id="info-active-orders">0</p>
                </div>
            </div>
            <div class="glass-panel px-5 py-3 flex items-center gap-3">
                <span class="material-symbols-outlined text-emerald-500 text-lg">task_alt</span>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Completed Today</p>
                    <p class="text-base font-black text-gray-900 leading-tight" id="info-completed">{{ \App\Models\Order::whereDate('updated_at', today())->where('status', 'completed')->count() }}</p>
                </div>
            </div>
            <div class="glass-panel px-5 py-3 flex items-center gap-3">
                <span class="material-symbols-outlined text-blue-500 text-lg">percent</span>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Courier Utilization</p>
                    <p class="text-base font-black text-gray-900 leading-tight" id="info-utilization">0%</p>
                </div>
            </div>
            <div class="glass-panel px-5 py-3 flex items-center gap-3">
                <span class="material-symbols-outlined text-rose-500 text-lg">pending_actions</span>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Pending Orders</p>
                    <p class="text-base font-black text-gray-900 leading-tight" id="info-pending">{{ \App\Models\Order::whereIn('status', ['pending_payment','waiting_pickup','ready_for_delivery'])->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Custom Toast Notification -->
    <div id="customToast" class="fixed top-6 left-1/2 transform -translate-x-1/2 z-[99999] hidden opacity-0 transition-all duration-300 ease-out">
        <div class="bg-white/95 backdrop-blur-md px-6 py-4 flex items-center gap-3 shadow-2xl border border-rose-100 rounded-3xl min-w-[320px]">
            <div id="toastIconContainer" class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-500 shadow-inner flex-shrink-0">
                <span id="toastIcon" class="material-symbols-outlined font-bold text-[20px]">warning</span>
            </div>
            <div class="min-w-0 flex-1">
                <h4 id="toastTitle" class="font-black text-xs uppercase tracking-wider text-gray-900 leading-none">Notification</h4>
                <p id="toastMessage" class="text-[11px] text-gray-500 font-semibold mt-1 leading-normal break-words"></p>
            </div>
            <button onclick="hideToast()" class="ml-auto text-gray-400 hover:text-gray-650 transition-colors p-1.5 rounded-xl hover:bg-gray-100/50 flex-shrink-0">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    </div>

    <!-- Audio Notification -->
    <audio id="notificationSound" preload="auto">
        <source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg">
    </audio>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

    <script>
        // Global Custom Toast helpers
        window.showCustomToast = (title, message, type = 'danger') => {
            const toast = document.getElementById('customToast');
            if (!toast) return;
            const tTitle = document.getElementById('toastTitle');
            const tMessage = document.getElementById('toastMessage');
            const tIcon = document.getElementById('toastIcon');
            const tIconContainer = document.getElementById('toastIconContainer');
            
            tTitle.textContent = title;
            tMessage.textContent = message;
            
            if (type === 'danger') {
                tIcon.textContent = 'warning';
                tIconContainer.className = 'w-10 h-10 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-500 shadow-inner flex-shrink-0';
                toast.firstElementChild.className = 'bg-white/95 backdrop-blur-md px-6 py-4 flex items-center gap-3 shadow-2xl border border-rose-100 rounded-3xl min-w-[320px]';
            } else if (type === 'success') {
                tIcon.textContent = 'check_circle';
                tIconContainer.className = 'w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-500 shadow-inner flex-shrink-0';
                toast.firstElementChild.className = 'bg-white/95 backdrop-blur-md px-6 py-4 flex items-center gap-3 shadow-2xl border border-emerald-100 rounded-3xl min-w-[320px]';
            } else {
                tIcon.textContent = 'info';
                tIconContainer.className = 'w-10 h-10 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-500 shadow-inner flex-shrink-0';
                toast.firstElementChild.className = 'bg-white/95 backdrop-blur-md px-6 py-4 flex items-center gap-3 shadow-2xl border border-blue-100 rounded-3xl min-w-[320px]';
            }
            
            toast.classList.remove('hidden');
            // Force browser reflow/repaint to apply transition
            toast.offsetHeight; 
            toast.classList.remove('opacity-0');
            
            if (window.toastTimeout) clearTimeout(window.toastTimeout);
            window.toastTimeout = setTimeout(() => {
                hideToast();
            }, 4000);
        };
        
        window.hideToast = () => {
            const toast = document.getElementById('customToast');
            if (toast) {
                toast.classList.add('opacity-0');
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 300);
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            // ─── MAP INIT ────────────────────────────────────────────────────
            const map = L.map('trackingMap', { 
                zoomControl: false, // Custom position below
                attributionControl: false
            }).setView([-6.1664983, 106.5602886], 14);

            L.control.zoom({ position: 'bottomleft' }).addTo(map);

            const standardMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                subdomains: 'abcd', maxZoom: 20
            });
            const darkMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                subdomains: 'abcd', maxZoom: 20
            });
            const satelliteMap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19
            });
            
            standardMap.addTo(map);

            L.control.layers({ 
                "Standard": standardMap, 
                "Dark Mode": darkMap, 
                "Satellite": satelliteMap 
            }, null, { position: 'bottomleft' }).addTo(map);

            // Laundry Base Marker
            const laundryIcon = L.divIcon({
                html: `<div class="bg-blue-900 text-white h-12 w-12 rounded-full flex items-center justify-center shadow-2xl border-4 border-white animate-pulse"><span class="material-symbols-outlined text-2xl">local_laundry_service</span></div>`,
                className: '', iconSize: [48, 48], iconAnchor: [24, 24], popupAnchor: [0, -24]
            });
            L.marker([-6.1664983, 106.5602886], { icon: laundryIcon })
                .bindPopup('<div class="p-2 font-black text-center text-blue-900">LAUNDRYAN HQ<br><span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Base Operations</span></div>')
                .addTo(map);

            // ─── STATE ───────────────────────────────────────────────────────
            let markers     = {};
            let polylines   = {};
            let destMarkers = {};
            let trails      = {};
            let couriersData = {};
            let isFirstLoad = true;
            let hasFocusedCourierOnLoad = false;
            let hasAppliedUrlHighlight = false;
            let focusedCourierId = null;
            let playbackLayer = null;
            let playbackMarker = null;
            let heatLayer = null;
            let isHeatmapVisible = false;

            // ─── FOCUS ORDER FROM URL ────────────────────────────────────────
            const urlParams = new URLSearchParams(window.location.search);
            const focusOrderId = urlParams.get('focus_order');
            const focusCourierId = urlParams.get('focus_courier');
            const focusLat = parseFloat(urlParams.get('lat'));
            const focusLng = parseFloat(urlParams.get('lng'));
            const focusLabel = urlParams.get('label') || 'Target Location';
            let focusMarker = null;

            if (focusOrderId && !isNaN(focusLat) && !isNaN(focusLng)) {
                isFirstLoad = false; // Disable default bounds zoom
                map.setView([focusLat, focusLng], 17);

                const targetIcon = L.divIcon({
                    html: `
                    <div class="relative h-12 w-12 flex items-center justify-center">
                        <div class="absolute inset-0 bg-blue-500 rounded-full opacity-20 animate-ping"></div>
                        <div class="bg-blue-600 text-white h-10 w-10 rounded-full flex items-center justify-center shadow-2xl border-4 border-white z-10">
                            <span class="material-symbols-outlined text-lg font-bold">my_location</span>
                        </div>
                    </div>`,
                    className: '', iconSize: [48, 48], iconAnchor: [24, 24]
                });

                focusMarker = L.marker([focusLat, focusLng], { icon: targetIcon })
                    .bindPopup(`<div class="p-2 font-black text-center text-blue-900">${focusLabel}<br><span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Real-time Target</span></div>`)
                    .addTo(map);

                setTimeout(() => {
                    if (focusMarker) focusMarker.openPopup();
                }, 800);
            }

            // ─── WS INDICATOR ────────────────────────────────────────────────
            const setWsStatus = (connected) => {
                const wsPing = document.getElementById('wsPing');
                const wsDot = document.getElementById('wsDot');
                const wsLabel = document.getElementById('wsLabel');
                
                const color = connected ? 'bg-green-500' : 'bg-red-500';
                if (wsDot) wsDot.className = `relative inline-flex rounded-full h-2.5 w-2.5 ${color}`;
                if (wsPing) wsPing.className = `animate-ping absolute inline-flex h-full w-full rounded-full ${color} opacity-75`;
                if (wsLabel) {
                    wsLabel.textContent = connected ? 'System Live - Realtime' : 'Connection Lost';
                    wsLabel.className = `text-[10px] font-bold uppercase tracking-widest ${connected ? 'text-green-500' : 'text-red-500'}`;
                }
            };

            // ─── HELPERS ─────────────────────────────────────────────────────
            const isOffline = (location) => {
                if (!location?.updated_at_raw) return false;
                return (new Date().getTime() - new Date(location.updated_at_raw).getTime()) > (5 * 60 * 1000);
            };

            // ─── UI COMPONENTS ───────────────────────────────────────────────
            function buildPopup(courier, orders, location, offline) {
                let statusBadge = offline 
                    ? `<span class="bg-gray-100 text-gray-500 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter">Offline</span>`
                    : `<span id="eta-${courier.id}" class="bg-blue-50 text-blue-600 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter animate-pulse">Calculating ETA...</span>`;

                let html = `
                <div class="p-2 min-w-[240px] font-['Plus_Jakarta_Sans']">
                    <div class="flex justify-between items-start border-b border-gray-100 pb-3 mb-3">
                        <div>
                            <h4 class="font-black text-gray-900 text-base leading-tight">${courier.name}</h4>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">${courier.phone || 'No Phone'}</p>
                        </div>
                        ${statusBadge}
                    </div>`;

                if (orders?.length > 0 && !offline) {
                    html += `<div class="max-h-[320px] overflow-y-auto pr-1.5 custom-scrollbar pb-1">`;
                    orders.forEach(order => {
                        const color = order.type === 'pickup' ? 'amber' : 'emerald';
                        html += `
                        <div class="mb-3 bg-gray-50 rounded-2xl p-4 border border-gray-100 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-${color}-500"></div>
                            <div class="flex items-center gap-2 mb-3">
                                <div class="bg-${color}-100 text-${color}-600 p-1.5 rounded-lg">
                                    <span class="material-symbols-outlined text-base font-bold">inventory_2</span>
                                </div>
                                <span class="font-black text-gray-800 text-[10px] uppercase tracking-wider">Active Task</span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-black text-xs text-gray-900">${order.order_code}</span>
                                <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-${color}-100 text-${color}-700">${order.status}</span>
                            </div>
                            <p class="text-xs text-gray-600 font-bold mb-1">${order.customer_name}</p>
                            <p class="text-[10px] text-gray-400 font-medium line-clamp-1 mb-3">${order.address}</p>
                            <a href="/karyawan/orders/${order.id}" target="_blank" class="block w-full text-center bg-white border border-gray-200 hover:bg-gray-900 hover:text-white hover:border-gray-900 text-gray-900 text-[10px] font-black py-2 rounded-xl transition-all no-underline shadow-sm">VIEW DETAILS</a>
                        </div>`;
                    });
                    html += `</div>`;
                } else if (offline) {
                    html += `<div class="bg-red-50 text-red-500 p-3 rounded-xl text-[11px] font-bold border border-red-100 mb-2 italic">⚠️ GPS signal lost for over 5 minutes.</div>`;
                } else {
                    html += `<div class="py-6 text-center">
                        <span class="material-symbols-outlined text-gray-200 text-4xl block mb-2">sensor_occupied</span>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Awaiting Assignment</p>
                    </div>`;
                }

                const phone = courier.phone || '';
                const cleanPhone = phone.replace(/[^0-9]/g, '');
                const waPhone = cleanPhone ? (cleanPhone.startsWith('0') ? '62' + cleanPhone.substring(1) : (cleanPhone.startsWith('62') ? cleanPhone : '62' + cleanPhone)) : '';
                
                const waAction = waPhone ? `window.open('https://wa.me/${waPhone}', '_blank')` : `showCustomToast('WhatsApp Alert', 'No WhatsApp number registered for this courier.', 'danger')`;
                const callAction = phone ? `window.location.href='tel:${phone}'` : `showCustomToast('Call Alert', 'No phone number registered for this courier.', 'danger')`;

                html += `
                <div class="grid grid-cols-3 gap-2 mt-4">
                    <button onclick="${waAction}" class="bg-[#25D366] text-white p-2.5 rounded-xl flex flex-col items-center justify-center gap-1 hover:brightness-95 transition-all shadow-md">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.412-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.309 1.656zm6.29-4.143c1.589.943 3.503 1.441 5.455 1.442 5.512 0 10.003-4.489 10.006-9.998.001-2.671-1.039-5.181-2.929-7.071-1.889-1.891-4.401-2.932-7.069-2.933-5.514 0-10.003 4.49-10.006 10.001-.001 1.761.46 3.478 1.332 4.978l-.991 3.619 3.708-.973zm11.238-5.715c-.301-.15-1.778-.878-2.053-.978-.275-.1-.476-.15-.676.15-.2.3-.776 1.001-.951 1.201-.175.2-.35.225-.651.075-.301-.15-1.269-.467-2.417-1.492-.893-.796-1.496-1.78-1.671-2.081-.175-.3-.018-.462.13-.611.135-.133.3-.35.45-.525.151-.175.2-.3.3-.5.1-.2.05-.375-.025-.525-.075-.15-.676-1.628-.926-2.228-.243-.586-.491-.506-.676-.515-.175-.009-.375-.01-.576-.01s-.525.075-.8.375c-.276.3-1.051 1.026-1.051 2.502s1.076 2.903 1.226 3.102c.15.2 2.117 3.232 5.128 4.534.715.311 1.275.496 1.709.635.719.227 1.371.196 1.888.118.577-.089 1.778-.727 2.028-1.427.25-.7.25-1.3.175-1.428-.075-.125-.275-.2-.575-.351z"/></svg>
                        <span class="text-[8px] font-black">WHATSAPP</span>
                    </button>
                    <button onclick="${callAction}" class="bg-[#3B82F6] text-white p-2.5 rounded-xl flex flex-col items-center justify-center gap-1 hover:brightness-95 transition-all shadow-md">
                        <span class="material-symbols-outlined text-lg">call</span>
                        <span class="text-[8px] font-black">CALL</span>
                    </button>
                    <button onclick="startPlayback(${courier.id})" class="bg-[#64748B] text-white p-2.5 rounded-xl flex flex-col items-center justify-center gap-1 hover:brightness-95 transition-all shadow-md">
                        <span class="material-symbols-outlined text-lg">history</span>
                        <span class="text-[8px] font-black">PLAYBACK</span>
                    </button>
                </div>`;

                html += `<div class="mt-4 pt-2 border-t border-gray-100 flex justify-between items-center text-[9px] font-bold text-gray-300">
                    <span>LAUNDRYAN MONITOR</span>
                    <span>${location.updated_at}</span>
                </div></div>`;
                return html;
            }

            // ─── RENDER ENGINE ───────────────────────────────────────────────
            function renderCourier(courierId) {
                const d = couriersData[courierId];
                if (!d?.location) return;

                const latLng = [d.location.lat, d.location.lng];
                const offline = isOffline(d.location);
                let activeType = 'idle';
                if (offline) {
                    activeType = 'offline';
                } else if (d.orders?.length > 0) {
                    const hasPickup = d.orders.some(o => o.type === 'pickup');
                    activeType = hasPickup ? 'pickup' : 'delivery';
                }
                const lineColor = activeType === 'pickup' ? '#f59e0b' : '#10b981';

                const search = (document.getElementById('searchCourier')?.value || '').toLowerCase();
                const filter = document.getElementById('filterCourier')?.value || 'all';

                let matchesSearch = !search || d.courier.name.toLowerCase().includes(search);
                if (search && !matchesSearch && d.orders && d.orders.length > 0) {
                    matchesSearch = d.orders.some(o => 
                        (o.order_code && o.order_code.toLowerCase().includes(search)) ||
                        (o.customer_name && o.customer_name.toLowerCase().includes(search)) ||
                        (o.address && o.address.toLowerCase().includes(search))
                    );
                }

                if (!matchesSearch || (filter !== 'all' && activeType !== filter)) {
                    if (markers[courierId]) map.removeLayer(markers[courierId]);
                    removeRoute(courierId);
                    renderList();
                    return;
                }

                // Marker Update
                const hasOrder = activeType === 'pickup' || activeType === 'delivery';
                const courierPhoto = d.courier.photo;
                const iconHtml = `
                    <div class="courier-marker ${activeType} overflow-visible">
                        <div class="w-full h-full rounded-full overflow-hidden border-2 border-white shadow-inner bg-white">
                            <img src="${courierPhoto}" class="w-full h-full object-cover" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(d.courier.name)}&color=005bc0&background=EBF4FF'">
                        </div>
                        ${hasOrder ? '<div class="absolute -top-1 -right-1 bg-white text-blue-600 rounded-full h-5 w-5 flex items-center justify-center shadow-lg border border-blue-50 animate-bounce z-10"><span class="material-symbols-outlined text-[12px] font-black">inventory_2</span></div>' : ''}
                    </div>`;
                const icon = L.divIcon({ html: iconHtml, className: '', iconSize: [44, 44], iconAnchor: [22, 44], popupAnchor: [0, -44] });

                if (!markers[courierId]) {
                    markers[courierId] = L.marker(latLng, { icon })
                        .bindPopup(buildPopup(d.courier, d.orders, d.location, offline))
                        .on('popupopen', () => {
                            if (focusedCourierId !== courierId) {
                                focusedCourierId = courierId;
                                // Re-render all to update routes (current focused will show route, others will remove)
                                Object.keys(couriersData).forEach(cid => renderCourier(cid));
                            }
                        })
                        .addTo(map);
                } else {
                    // Smooth transition instead of direct jump
                    const currentMarker = markers[courierId];
                    const startLatLng = currentMarker.getLatLng();
                    const endLatLng = L.latLng(latLng[0], latLng[1]);
                    
                    if (startLatLng.lat !== endLatLng.lat || startLatLng.lng !== endLatLng.lng) {
                        const duration = 2500; // Animate over 2.5 seconds (matches real-time refresh rates nicely)
                        const startTime = performance.now();
                        
                        function animate(time) {
                            const elapsed = time - startTime;
                            const progress = Math.min(elapsed / duration, 1);
                            
                            // Smooth ease-in-out quadratic interpolation
                            const easeProgress = progress < 0.5 
                                ? 2 * progress * progress 
                                : -1 + (4 - 2 * progress) * progress;
                            
                            const nextLat = startLatLng.lat + (endLatLng.lat - startLatLng.lat) * easeProgress;
                            const nextLng = startLatLng.lng + (endLatLng.lng - startLatLng.lng) * easeProgress;
                            
                            if (markers[courierId]) {
                                markers[courierId].setLatLng([nextLat, nextLng]);
                            }
                            
                            if (progress < 1) {
                                requestAnimationFrame(animate);
                            } else {
                                if (markers[courierId]) {
                                    markers[courierId].setLatLng(endLatLng);
                                }
                            }
                        }
                        requestAnimationFrame(animate);
                    }
                    
                    currentMarker.setIcon(icon);
                    currentMarker.setPopupContent(buildPopup(d.courier, d.orders, d.location, offline));
                    if (!map.hasLayer(currentMarker)) currentMarker.addTo(map);
                }

                // ─── ROUTE OPTIMIZATION (NEAREST NEIGHBOR) ───
                let optimizedOrders = [];
                if (d.orders?.length > 0) {
                    let remaining = [...d.orders];
                    let currentPos = { lat: d.location.lat, lng: d.location.lng };

                    while (remaining.length > 0) {
                        let nearestIdx = 0;
                        let minDist = Infinity;

                        remaining.forEach((o, idx) => {
                            const lat = o.dest_lat || o.pickup_lat;
                            const lng = o.dest_lng || o.pickup_lng;
                            const dist = Math.sqrt(Math.pow(lat - currentPos.lat, 2) + Math.pow(lng - currentPos.lng, 2));
                            if (dist < minDist) {
                                minDist = dist;
                                nearestIdx = idx;
                            }
                        });

                        const nearest = remaining.splice(nearestIdx, 1)[0];
                        optimizedOrders.push(nearest);
                        currentPos = { lat: nearest.dest_lat || nearest.pickup_lat, lng: nearest.dest_lng || nearest.pickup_lng };
                    }
                }

                // Route Update
                if (!offline && optimizedOrders.length > 0) {
                    let wps = [L.latLng(latLng[0], latLng[1])];
                    let dData = [];
                    optimizedOrders.forEach((o, idx) => {
                        wps.push(L.latLng(o.dest_lat, o.dest_lng));
                        dData.push({ name: o.customer_name, type: o.type, photo: o.customer_photo, step: idx + 1 });
                    });
                    
                    // Store destination info for upsertRoute
                    if (!this.destData) this.destData = {};
                    this.destData[courierId] = dData;
                    
                    upsertRoute(courierId, wps, lineColor);
                } else {
                    removeRoute(courierId);
                }

                // Snail Trail
                if (d.location.location_history?.length >= 2) upsertTrail(courierId, d.location.location_history);

                // fitBounds on first load is handled in fetchData after all couriers are processed
                renderList();
            }

            function upsertRoute(id, waypoints, color) {
                if (focusedCourierId && focusedCourierId != id) { removeRoute(id); return; }
                if (polylines[id]) {
                    polylines[id].setWaypoints(waypoints);
                } else {
                    polylines[id] = L.Routing.control({
                        waypoints,
                        createMarker: () => null,
                        routeWhileDragging: false,
                        show: false,
                        fitSelectedRoutes: false, // Prevent auto-zoom to single route
                        lineOptions: { styles: [{ color, opacity: 0.7, weight: 6 }] }
                    }).on('routesfound', function(e) {
                        const s       = e.routes[0].summary;
                        // Akurasi Tinggi: (Waktu Tempuh * 1.3 Traffic Factor) + 5 Menit Buffer Serah Terima
                        const minutes = Math.round((s.totalTime / 60) * 1.3) + 5; 
                        const km      = (s.totalDistance / 1000).toFixed(1);
                        const etaEl   = document.getElementById(`eta-${id}`);
                        if (etaEl) {
                            etaEl.classList.remove('animate-pulse');
                            etaEl.textContent = minutes > 5 ? `± ${minutes} MIN` : 'ARRIVED';
                        }
                    }).addTo(map);
                }
                
                if (!destMarkers[id]) destMarkers[id] = L.layerGroup().addTo(map);
                else destMarkers[id].clearLayers();
                
                // Render Customer Destination Markers
                waypoints.slice(1).forEach((wp, idx) => {
                    const destInfo = (this.destData && this.destData[id]) ? this.destData[id][idx] : null;
                    const customerPhoto = destInfo ? destInfo.photo : `https://ui-avatars.com/api/?name=?&color=FFFFFF&background=${color === '#F59E0B' ? 'F59E0B' : '10B981'}`;
                    const stepNum = destInfo ? destInfo.step : (idx + 1);
                    
                    const destIcon = L.divIcon({
                        html: `
                        <div class="relative h-12 w-12 flex items-center justify-center">
                            <!-- Step Number (Outside & Prominent) -->
                            <div class="absolute -top-1 -left-1 bg-gray-900 text-white text-[11px] h-6 w-6 rounded-full flex items-center justify-center font-black border-2 border-white shadow-xl z-50 transform -translate-x-1/4 -translate-y-1/4">
                                ${stepNum}
                            </div>
                            
                            <!-- Customer Photo Frame -->
                            <div class="bg-white p-0.5 h-11 w-11 rounded-full border-4 border-${color === '#f59e0b' ? 'amber' : 'emerald'}-500 flex items-center justify-center shadow-2xl animate-pulse overflow-hidden z-10">
                                <img src="${customerPhoto}" class="w-full h-full rounded-full object-cover">
                            </div>
                            
                            <!-- Label -->
                            <div class="absolute -bottom-2 bg-${color === '#f59e0b' ? 'amber' : 'emerald'}-500 text-white text-[7px] px-2 py-0.5 rounded-full font-black uppercase tracking-tighter shadow-md z-30">
                                TARGET
                            </div>
                        </div>`,
                        className: '', iconSize: [48, 48], iconAnchor: [24, 48]
                    });

                    L.marker(wp, { icon: destIcon })
                        .bindPopup(`<div class="font-black text-xs text-center">${destInfo ? destInfo.name : 'Customer'}</div>`)
                        .addTo(destMarkers[id]);
                });
            }

            function removeRoute(id) {
                if (polylines[id]) { map.removeControl(polylines[id]); delete polylines[id]; }
                if (destMarkers[id]) { destMarkers[id].clearLayers(); }
            }

            function upsertTrail(id, path) {
                if (focusedCourierId) { if (trails[id]) { map.removeLayer(trails[id]); delete trails[id]; } return; }
                const wps = path.slice(-10).map(c => L.latLng(c[0], c[1]));
                if (trails[id]) trails[id].setWaypoints(wps);
                else {
                    trails[id] = L.Routing.control({
                        waypoints: wps, createMarker: () => null, show: false,
                        fitSelectedRoutes: false,
                        lineOptions: { styles: [{ color: '#94A3B8', opacity: 0.3, weight: 4, dashArray: '5, 10' }] }
                    }).addTo(map);
                }
            }

            function renderList() {
                const el = document.getElementById('courierList');
                let items = Object.values(couriersData);
                const search = (document.getElementById('searchCourier')?.value || '').toLowerCase();
                const filter = document.getElementById('filterCourier')?.value || 'all';

                // Update Stats
                const total = items.length;
                const active = items.filter(d => d.orders?.length > 0 && !isOffline(d.location)).length;
                const idle = items.filter(d => (!d.orders || d.orders.length === 0) && !isOffline(d.location)).length;
                const offline = items.filter(d => isOffline(d.location)).length;

                document.getElementById('stat-total').textContent = total;
                document.getElementById('stat-active').textContent = active;
                document.getElementById('stat-idle').textContent = idle;
                const offlineEl = document.getElementById('stat-offline');
                if (offlineEl) offlineEl.textContent = offline;

                // Update bottom info bar
                const activeOrdersCount = items.reduce((sum, d) => sum + (d.orders?.length || 0), 0);
                const utilization = total > 0 ? Math.round((active / total) * 100) : 0;
                const infoActiveEl = document.getElementById('info-active-orders');
                const infoUtilEl = document.getElementById('info-utilization');
                if (infoActiveEl) infoActiveEl.textContent = activeOrdersCount;
                if (infoUtilEl) infoUtilEl.textContent = utilization + '%';

                el.innerHTML = '';
                items = items.filter(d => {
                    const off = isOffline(d.location);
                    const type = off ? 'offline' : (d.orders?.length > 0 ? d.orders[0].type : 'idle');
                    
                    let matchesSearch = !search || d.courier.name.toLowerCase().includes(search);
                    if (search && !matchesSearch && d.orders && d.orders.length > 0) {
                        matchesSearch = d.orders.some(o => 
                            (o.order_code && o.order_code.toLowerCase().includes(search)) ||
                            (o.customer_name && o.customer_name.toLowerCase().includes(search)) ||
                            (o.address && o.address.toLowerCase().includes(search))
                        );
                    }
                    
                    const matchesDropdown = filter === 'all' || type === filter;

                    // Stat card filter override
                    if (activeStatFilter === 'active') {
                        return matchesSearch && !off && d.orders?.length > 0;
                    }
                    if (activeStatFilter === 'idle') {
                        return matchesSearch && !off && (!d.orders || d.orders.length === 0);
                    }

                    return matchesSearch && matchesDropdown;
                }).sort((a, b) => isOffline(a.location) - isOffline(b.location));

                if (items.length === 0) {
                    el.innerHTML = '<div class="py-12 text-center text-gray-400 font-bold uppercase tracking-widest text-[10px]">No Couriers Active</div>';
                    return;
                }

                items.forEach(d => {
                    const off = isOffline(d.location);
                    const orders = d.orders || [];
                    const status = off ? 'offline' : (orders.length > 0 ? orders[0].type : 'idle');
                    const colors = { pickup: 'amber', delivery: 'emerald', idle: 'blue', offline: 'slate' };
                    const c = colors[status];
                    const courierPhoto = d.courier.photo;

                    let orderHtml = '';
                    if (orders.length > 0 && !off) {
                        orderHtml = `<div class="mt-4 space-y-2 max-h-[140px] overflow-y-auto pr-1 custom-scrollbar">`;
                        orders.forEach(o => {
                            const color = o.type === 'pickup' ? 'amber' : 'emerald';
                            orderHtml += `
                            <div class="mt-2 bg-gray-50/50 rounded-2xl p-3 border border-gray-100">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">${o.order_code}</span>
                                    <span class="text-[9px] font-black text-${color}-600 uppercase">${o.status}</span>
                                </div>
                                <div class="text-[11px] font-black text-gray-800 mb-2 truncate">${o.customer_name}</div>
                                <a href="/karyawan/orders/${o.id}" target="_blank" onclick="event.stopPropagation()" class="block w-full text-center bg-white border border-gray-200 hover:bg-gray-900 hover:text-white text-gray-900 text-[9px] font-black py-1.5 rounded-lg transition-all shadow-sm no-underline">ORDER DETAILS</a>
                            </div>`;
                        });
                        orderHtml += `</div>`;
                    }

                    el.insertAdjacentHTML('beforeend', `
                        <div class="courier-card p-4 rounded-3xl cursor-pointer ${off ? 'opacity-60 grayscale' : ''}" onclick="focusCourier(${d.courier.id})">
                            <div class="flex justify-between items-start mb-1">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl border-2 border-white shadow-md overflow-hidden bg-gray-50 flex-shrink-0">
                                        <img src="${courierPhoto}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-black text-gray-900 text-sm tracking-tight leading-none truncate">${d.courier.name}</h4>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-1.5">${d.courier.phone || 'NO PHONE'}</p>
                                    </div>
                                </div>
                                <div class="h-2.5 w-2.5 rounded-full bg-${c}-500 shadow-lg shadow-${c}-500/50 flex-shrink-0"></div>
                            </div>
                            ${orderHtml}
                        </div>`);
                });
            }

            // ─── ACTIONS ─────────────────────────────────────────────────────
            window.focusCourier = (id) => {
                if (markers[id]) { 
                    map.setView(markers[id].getLatLng(), 16); 
                    // Use a small timeout to ensure Leaflet has finished updating layers
                    setTimeout(() => {
                        markers[id].openPopup();
                    }, 100);
                }
            };

            window.resetFocus = () => {
                stopPlayback();
                focusedCourierId = null;
                // Also reset the stat filter
                filterByStat('all');
                
                if (focusMarker) {
                    map.removeLayer(focusMarker);
                    focusMarker = null;
                }
                
                const visibleMarkers = [];
                Object.keys(couriersData).forEach(id => {
                    renderCourier(id);
                    if (markers[id] && map.hasLayer(markers[id])) {
                        visibleMarkers.push(markers[id].getLatLng());
                    }
                });

                if (visibleMarkers.length > 0) {
                    const bounds = L.latLngBounds(visibleMarkers);
                    bounds.extend([-6.1664983, 106.5602886]); // Include Laundry Base
                    map.flyToBounds(bounds, { padding: [100, 100], duration: 1.5 });
                } else {
                    map.flyTo([-6.1664983, 106.5602886], 14, { animate: true, duration: 1.5 });
                }
            };

            // ─── STAT CARD FILTER ────────────────────────────────────────────
            window.filterByStat = (type) => {
                const dropdown = document.getElementById('filterCourier');
                activeStatFilter = type;

                // Remove active ring from all cards
                document.querySelectorAll('.stat-card').forEach(card => {
                    card.classList.remove(
                        'ring-2', 'ring-gray-400', 'ring-emerald-400',
                        'ring-blue-400', 'ring-slate-400', 'shadow-md'
                    );
                });

                // Add ring to the selected card & sync dropdown
                if (type === 'all') {
                    document.getElementById('stat-card-total')?.classList.add('ring-2', 'ring-gray-400', 'shadow-md');
                    if (dropdown) dropdown.value = 'all';
                } else if (type === 'active') {
                    document.getElementById('stat-card-active')?.classList.add('ring-2', 'ring-emerald-400', 'shadow-md');
                    if (dropdown) dropdown.value = 'all';
                } else if (type === 'idle') {
                    document.getElementById('stat-card-idle')?.classList.add('ring-2', 'ring-blue-400', 'shadow-md');
                    if (dropdown) dropdown.value = 'idle';
                } else if (type === 'offline') {
                    document.getElementById('stat-card-offline')?.classList.add('ring-2', 'ring-slate-400', 'shadow-md');
                    if (dropdown) dropdown.value = 'offline';
                }

                // Render and Fit Bounds
                focusedCourierId = null; // Reset focus
                const visibleMarkers = [];
                Object.keys(couriersData).forEach(id => {
                    renderCourier(id);
                    if (markers[id] && map.hasLayer(markers[id])) {
                        visibleMarkers.push(markers[id].getLatLng());
                        // Also include destination coordinates if available
                        const d = couriersData[id];
                        if (d.orders?.length > 0) {
                            d.orders.forEach(o => {
                                if (o.dest_lat && o.dest_lng) visibleMarkers.push(L.latLng(o.dest_lat, o.dest_lng));
                                if (o.pickup_lat && o.pickup_lng) visibleMarkers.push(L.latLng(o.pickup_lat, o.pickup_lng));
                            });
                        }
                    }
                });

                if (visibleMarkers.length > 0) {
                    const bounds = L.latLngBounds(visibleMarkers);
                    bounds.extend([-6.1664983, 106.5602886]); // Always include Laundry Base for context
                    map.flyToBounds(bounds, { padding: [50, 50], duration: 1.5 });
                } else {
                    map.flyTo([-6.1664983, 106.5602886], 14, { animate: true, duration: 1.5 });
                }
            };

            // ─── LIVE CLOCK ──────────────────────────────────────────────────
            function updateClock() {
                const now = new Date();
                const hh = String(now.getHours()).padStart(2, '0');
                const mm = String(now.getMinutes()).padStart(2, '0');
                const ss = String(now.getSeconds()).padStart(2, '0');
                const clockEl = document.getElementById('live-clock');
                const dateEl  = document.getElementById('live-date');
                if (clockEl) clockEl.textContent = `${hh}:${mm}:${ss}`;
                if (dateEl) {
                    const opts = { weekday: 'short', month: 'short', day: 'numeric' };
                    dateEl.textContent = now.toLocaleDateString('en-US', opts).toUpperCase();
                }
            }
            updateClock();
            setInterval(updateClock, 1000);

            // Initialize stat filter state
            let activeStatFilter = 'all';
            filterByStat('all');

            window.startPlayback = (id) => {
                stopPlayback();
                const d = couriersData[id];
                const path = d?.location?.location_history;
                if (!path || path.length < 2) return showCustomToast('Playback Failed', 'No tracking history found for this courier.', 'danger');

                focusedCourierId = id;
                Object.keys(couriersData).forEach(cid => renderCourier(cid));
                
                playbackLayer = L.polyline(path, { color: '#64748B', weight: 8, opacity: 0.5, dashArray: '10, 15' }).addTo(map);
                const icon = L.divIcon({ html: `<div class="bg-slate-900 text-white h-8 w-8 rounded-full border-2 border-white shadow-2xl flex items-center justify-center text-[10px] font-black italic">PB</div>`, className: '', iconSize: [32, 32], iconAnchor: [16, 16] });
                playbackMarker = L.marker(path[0], { icon }).addTo(map);

                let i = 0;
                const interval = setInterval(() => {
                    if (i >= path.length || focusedCourierId != id) return clearInterval(interval);
                    playbackMarker.setLatLng(path[i]);
                    map.panTo(path[i]);
                    i++;
                }, 150);
            };

            window.stopPlayback = () => {
                if (playbackLayer) map.removeLayer(playbackLayer); playbackLayer = null;
                if (playbackMarker) map.removeLayer(playbackMarker); playbackMarker = null;
            };

            // ─── DATA SYNC ───────────────────────────────────────────────────
            const fetchData = () => {
                fetch('{{ route('karyawan.tracking.data') }}')
                    .then(r => r.json())
                    .then(data => {
                        data.tracking.forEach(d => {
                            couriersData[d.courier.id] = d;
                            renderCourier(d.courier.id);
                        });

                        // ─── First-load: fit map to show ALL courier locations ─
                        if (isFirstLoad) {
                            isFirstLoad = false;
                            const allPoints = Object.values(couriersData)
                                .filter(cd => cd.location?.lat && cd.location?.lng)
                                .map(cd => [cd.location.lat, cd.location.lng]);
                            allPoints.push([-6.1664983, 106.5602886]); // Always include HQ
                            if (allPoints.length > 0) {
                                map.fitBounds(L.latLngBounds(allPoints), { padding: [80, 80], animate: true });
                            }
                        }

                        // Focus the courier carrying the focused order
                        if (focusOrderId) {
                            let foundCourierId = null;
                            data.tracking.forEach(d => {
                                if (d.orders && d.orders.some(o => o.id == focusOrderId)) {
                                    foundCourierId = d.courier.id;
                                }
                            });
                            if (foundCourierId) {
                                if (focusedCourierId !== foundCourierId) {
                                    focusedCourierId = foundCourierId;
                                    setTimeout(() => {
                                        focusCourier(foundCourierId);
                                    }, 1000);
                                }
                            }
                        }

                        // Focus specific courier from URL on first load
                        if (focusCourierId && !hasFocusedCourierOnLoad) {
                            if (couriersData[focusCourierId]) {
                                hasFocusedCourierOnLoad = true;
                                focusedCourierId = focusCourierId;
                                setTimeout(() => {
                                    focusCourier(focusCourierId);
                                }, 1000);
                            }
                        }

                        // ─── Apply URL highlight after FIRST data load ───────
                        if (!hasAppliedUrlHighlight) {
                            hasAppliedUrlHighlight = true;
                            const urlSearchParams = new URLSearchParams(window.location.search);
                            const highlightParam = urlSearchParams.get('highlight');
                            if (highlightParam && typeof window.filterByStat === 'function') {
                                setTimeout(() => {
                                    window.filterByStat(highlightParam);
                                }, 500);
                            }
                        }

                        if (data.heatmap) {
                            const pts = data.heatmap.map(h => [parseFloat(h.lat), parseFloat(h.lng), 1.0]); // Max intensity
                            if (heatLayer) {
                                heatLayer.setLatLngs(pts);
                            } else if (typeof L.heatLayer !== 'undefined') {
                                heatLayer = L.heatLayer(pts, { 
                                    radius: 50,      // Larger radius for higher visibility
                                    blur: 12,        // Sharper edges for higher contrast
                                    maxOpacity: 0.95, // Higher opacity to stand out
                                    gradient: {
                                        0.3: '#00F0FF', // Neon Cyan
                                        0.5: '#00FF66', // Neon Green
                                        0.7: '#FFFF00', // Bright Yellow
                                        0.9: '#FF6600', // Vibrant Orange
                                        1.0: '#FF0000'  // Pure Red
                                    }
                                });
                            } else {
                                console.error("Leaflet.heat library not loaded!");
                            }
                            if (heatLayer && isHeatmapVisible && !map.hasLayer(heatLayer)) heatLayer.addTo(map);
                        }
                    });
            };

            document.getElementById('toggleHeatmap').addEventListener('click', function() {
                if (typeof L.heatLayer === 'undefined') {
                    alert("Heatmap module is not loaded. Please check your internet connection.");
                    return;
                }
                isHeatmapVisible = !isHeatmapVisible;
                this.classList.toggle('bg-orange-100', isHeatmapVisible);
                this.classList.toggle('ring-2', isHeatmapVisible);
                this.classList.toggle('ring-orange-400', isHeatmapVisible);
                
                if (heatLayer) {
                    if (isHeatmapVisible) {
                        heatLayer.addTo(map);
                        // Fit map bounds to show heatmap coordinates immediately
                        const latLngs = heatLayer.getLatLngs();
                        if (latLngs && latLngs.length > 0) {
                            const bounds = L.latLngBounds(latLngs.map(pt => [pt.lat ?? pt[0], pt.lng ?? pt[1]]));
                            map.flyToBounds(bounds, { padding: [80, 80], duration: 1.5 });
                        }
                    } else {
                        map.removeLayer(heatLayer);
                        // Reset bounds back to active couriers/laundry base
                        if (typeof window.filterByStat === 'function') {
                            window.filterByStat(activeStatFilter);
                        }
                    }
                } else if (isHeatmapVisible) {
                    console.log("Heatmap layer not ready yet. Waiting for data...");
                    fetchData();
                }
            });

            document.getElementById('searchCourier').addEventListener('input', () => {
                const visibleMarkers = [];
                Object.keys(couriersData).forEach(id => {
                    renderCourier(id);
                    if (markers[id] && map.hasLayer(markers[id])) {
                        visibleMarkers.push(markers[id].getLatLng());
                    }
                });

                if (visibleMarkers.length > 0) {
                    const bounds = L.latLngBounds(visibleMarkers);
                    // If only one result, zoom in closer
                    if (visibleMarkers.length === 1) {
                        map.flyTo(visibleMarkers[0], 16, { animate: true, duration: 1 });
                    } else {
                        map.flyToBounds(bounds, { padding: [100, 100], duration: 1 });
                    }
                }
            });
            document.getElementById('filterCourier').addEventListener('change', () => {
                focusedCourierId = null; // Reset focus so all routes show if applicable
                const visibleMarkers = [];
                Object.keys(couriersData).forEach(id => {
                    renderCourier(id);
                    if (markers[id] && map.hasLayer(markers[id])) {
                        visibleMarkers.push(markers[id].getLatLng());
                        // Also include their destination coordinates if available
                        const d = couriersData[id];
                        if (d.orders?.length > 0) {
                            d.orders.forEach(o => {
                                if (o.dest_lat && o.dest_lng) visibleMarkers.push(L.latLng(o.dest_lat, o.dest_lng));
                                if (o.pickup_lat && o.pickup_lng) visibleMarkers.push(L.latLng(o.pickup_lat, o.pickup_lng));
                            });
                        }
                    }
                });

                if (visibleMarkers.length > 0) {
                    const bounds = L.latLngBounds(visibleMarkers);
                    bounds.extend([-6.1664983, 106.5602886]); // Always include Laundry Base for context
                    map.flyToBounds(bounds, { padding: [50, 50], duration: 1.5 });
                } else {
                    map.flyTo([-6.1664983, 106.5602886], 14, { animate: true, duration: 1.5 });
                }
            });

            // ─── INITIAL LOAD & POLLING ──────────────────────────────────────
            fetchData();
            setInterval(fetchData, 15000); // Reliable fallback polling
            setWsStatus(true); // Default to true once we start fetching

            // ─── REAL-TIME UPDATES (ECHO) ────────────────────────────────────
            if (typeof window.Echo !== 'undefined') {
                const channel = window.Echo.private('admin.tracking');
                
                channel.subscribed(() => {
                    const wsLabel = document.getElementById('wsLabel');
                    if (wsLabel) wsLabel.textContent = 'System Live - Realtime';
                    setWsStatus(true);
                });

                channel.error(() => {
                    console.warn("WebSocket connection error. Falling back to Polling.");
                    const wsLabel = document.getElementById('wsLabel');
                    if (wsLabel) wsLabel.textContent = 'System Live - Polling';
                });

                channel.listen('.courier.location.updated', (e) => {
                    const oldNear = couriersData[e.courier.id]?.location?.is_near_destination;
                    couriersData[e.courier.id] = { courier: e.courier, location: e.location, orders: e.orders };
                    if (e.location.is_near_destination && !oldNear) document.getElementById('notificationSound').play().catch(() => {});
                    renderCourier(e.courier.id);
                });

                channel.listen('.courier.status.updated', (e) => {
                    if (couriersData[e.courier_id]) {
                        couriersData[e.courier_id].orders = e.orders;
                        renderCourier(e.courier_id);
                    }
                });
            } else {
                const wsLabel = document.getElementById('wsLabel');
                if (wsLabel) wsLabel.textContent = 'System Live - Polling';
            }
        });
    </script>
</x-app-layout>
