<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Business Intelligence & Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Key Performance Indicators -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Revenue</p>
                    <h3 class="text-2xl font-black text-blue-600">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</h3>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Orders</p>
                    <h3 class="text-2xl font-black text-gray-900">{{ number_format($stats['total_orders']) }}</h3>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Active Pipeline</p>
                    <h3 class="text-2xl font-black text-gray-900">{{ number_format($stats['active_orders']) }}</h3>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Avg Rating</p>
                    <div class="flex items-center">
                        <h3 class="text-2xl font-black text-yellow-500 mr-2">{{ number_format($stats['avg_rating'], 1) }}</h3>
                        <svg class="w-6 h-6 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Revenue Trend Chart -->
                <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6">Revenue Trend (Last 7 Days)</h3>
                    <div class="h-[300px]">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Service Popularity Chart -->
                <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6">Service Distribution</h3>
                    <div class="h-[300px] flex justify-center">
                        <canvas id="serviceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Revenue Chart
            const revCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($revenueData->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!},
                    datasets: [{
                        label: 'Income (Rp)',
                        data: {!! json_encode($revenueData->pluck('total')) !!},
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 4,
                        pointRadius: 6,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#4f46e5',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. Service Chart
            const servCtx = document.getElementById('serviceChart').getContext('2d');
            new Chart(servCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($serviceData->pluck('service.name')) !!},
                    datasets: [{
                        data: {!! json_encode($serviceData->pluck('total')) !!},
                        backgroundColor: ['#4f46e5', '#ec4899', '#f59e0b', '#10b981', '#6366f1', '#8b5cf6'],
                        borderWidth: 0,
                        hoverOffset: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { weight: 'bold', size: 10 } } }
                    },
                    cutout: '70%'
                }
            });
        });
    </script>
</x-app-layout>
