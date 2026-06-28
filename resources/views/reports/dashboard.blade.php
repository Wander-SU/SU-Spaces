@extends('layouts.app')

{{-- Page Title in Browser Tab --}}
@section('title', 'Administrative Analytics Dashboard')

{{-- Page Heading --}}
@section('page-title', 'SU-Spaces Administrative Analytics Reporting Dashboard')

{{-- Breadcrumb --}}
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">Home</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .dashboard-page {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
@endpush

{{-- Main Content --}}
@section('content')
    <div class="dashboard-page space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="flex items-center justify-between p-6 bg-white dark:bg-[#161615] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-xl shadow-xs transition-all">
                <div class="flex flex-col">
                    <h3 class="text-2xl font-bold font-sans text-[#1b1b18] dark:text-[#EDEDEC]">{{ number_format($totalUsers ?? 0) }}</h3>
                    <p class="text-sm font-sans text-[#706f6c] dark:text-[#A1A09A] mt-1">Registered Users</p>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-xl">
                    <i class="bi bi-people text-blue-500"></i>
                </div>
            </div>

            <div class="flex items-center justify-between p-6 bg-white dark:bg-[#161615] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-xl shadow-xs transition-all">
                <div class="flex flex-col">
                    <h3 class="text-2xl font-bold font-sans text-[#1b1b18] dark:text-[#EDEDEC]">{{ number_format($activeBookings ?? 0) }}</h3>
                    <p class="text-sm font-sans text-[#706f6c] dark:text-[#A1A09A] mt-1">Active Bookings</p>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-xl">
                    <i class="bi bi-calendar-check text-green-500"></i>
                </div>
            </div>

            <div class="flex items-center justify-between p-6 bg-white dark:bg-[#161615] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-xl shadow-xs transition-all">
                <div class="flex flex-col">
                    <h3 class="text-2xl font-bold font-sans text-[#1b1b18] dark:text-[#EDEDEC]">{{ number_format($systemOverrides ?? 0) }}</h3>
                    <p class="text-sm font-sans text-[#706f6c] dark:text-[#A1A09A] mt-1">Priority Overrides (CATs/Exams)</p>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-xl">
                    <i class="bi bi-shield-exclamation text-amber-500"></i>
                </div>
            </div>

            <div class="flex items-center justify-between p-6 bg-white dark:bg-[#161615] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-xl shadow-xs transition-all">
                <div class="flex flex-col">
                    <h3 class="text-2xl font-bold font-sans text-[#1b1b18] dark:text-[#EDEDEC]">{{ number_format($cancelledBookings ?? 0) }}</h3>
                    <p class="text-sm font-sans text-[#706f6c] dark:text-[#A1A09A] mt-1">Cancelled Slots</p>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-xl">
                    <i class="bi bi-calendar-x text-red-500"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-[#161615] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-xl shadow-xs p-6">
                <h5 class="font-sans font-semibold text-base tracking-wide text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                    Percentage of Buildings booked
                </h5>
                <div id="building-pie-chart"></div>
            </div>

            <div class="bg-white dark:bg-[#161615] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-xl shadow-xs p-6">
                <h5 class="font-sans font-semibold text-base tracking-wide text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                    Trend of Room Bookings
                </h5>
                <div id="room-trend-line-chart"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-[#161615] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-xl shadow-xs p-6">
                <h5 class="font-sans font-semibold text-base tracking-wide text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                    Number of Bookings (by Room Capacity)
                </h5>
                <div id="capacity-bar-chart"></div>
            </div>

            <div class="bg-white dark:bg-[#161615] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-xl shadow-xs p-6">
                <h5 class="font-sans font-semibold text-base tracking-wide text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                    Peak Space Utilization Grid
                </h5>
                <div id="peak-utilization-heatmap-chart"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        // Shared global charts styling attributes
        const chartForeColor = '#9CA3AF';
        const chartToolbar = { show: true, tools: { download: true } };

        // --- Data Layer Safe Mappings via Blade JSON Macros ---
        const buildingLabels = {!! json_encode(array_keys($buildingBookingDistribution ?? ['MSB' => 0, 'STMB' => 0, 'STC' => 0])) !!};
        const buildingSeries = {!! json_encode(array_values($buildingBookingDistribution ?? [0, 0, 0])) !!};

        const trendLabels = {!! json_encode(array_keys($roomBookingTrendByTimeBlock ?? ['08:00' => 0, '10:00' => 0, '12:00' => 0, '14:00' => 0, '16:00' => 0])) !!};
        const trendSeries = {!! json_encode(array_values($roomBookingTrendByTimeBlock ?? [0, 0, 0, 0, 0])) !!};

        const capacityLabels = {!! json_encode(array_keys($bookingsByRoomSize ?? ['1-30' => 0, '31-60' => 0, '61-100' => 0, '101+' => 0])) !!};
        const capacitySeries = {!! json_encode(array_values($bookingsByRoomSize ?? [0, 0, 0, 0])) !!};

        const heatmapXCategories = {!! json_encode($heatmapTimeWindows ?? ['08:00-10:00', '10:00-12:00', '12:00-14:00', '14:00-16:00', '16:00-18:00']) !!};
        const heatmapYCategories = {!! json_encode($heatmapWeekdays ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']) !!};
        const heatmapSeries = {!! json_encode($peakUtilizationHeatmapSeries ?? []) !!};

        // 1. Percentage of Buildings Booked (Pie Chart)
        new ApexCharts(document.querySelector("#building-pie-chart"), {
            chart: { type: 'pie', height: 350, toolbar: chartToolbar },
            labels: buildingLabels,
            series: buildingSeries,
            legend: { position: 'bottom', labels: { colors: chartForeColor } },
            dataLabels: {
                enabled: true,
                formatter: function(val) { return val.toFixed(1) + "%"; }
            }
        }).render();

        // 2. Trend of Room Bookings (Smooth Line Graph)
        new ApexCharts(document.querySelector("#room-trend-line-chart"), {
            chart: { type: 'line', height: 350, toolbar: chartToolbar },
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 4 },
            series: [{ name: 'Bookings', data: trendSeries }],
            xaxis: {
                categories: trendLabels,
                labels: { style: { colors: chartForeColor } }
            },
            yaxis: { labels: { style: { colors: chartForeColor } } }
        }).render();

        // 3. Number of Bookings by Room Size (Bar Graph)
        new ApexCharts(document.querySelector("#capacity-bar-chart"), {
            chart: { type: 'bar', height: 350, toolbar: chartToolbar },
            plotOptions: { bar: { columnWidth: '50%', distributed: false } },
            series: [{ name: 'Bookings', data: capacitySeries }],
            xaxis: {
                categories: capacityLabels,
                labels: { style: { colors: chartForeColor } }
            },
            yaxis: { labels: { style: { colors: chartForeColor } } },
            dataLabels: { enabled: true }
        }).render();

        // 4. Peak Space Utilization Grid (Advanced Heatmap Matrix)
        const normalizedHeatmapSeries = (heatmapSeries && heatmapSeries.length)
            ? heatmapSeries
            : heatmapYCategories.map(day => ({
                name: day,
                data: heatmapXCategories.map(slot => ({ x: slot, y: 0 }))
            }));

        new ApexCharts(document.querySelector("#peak-utilization-heatmap-chart"), {
            chart: { type: 'heatmap', height: 350, toolbar: chartToolbar },
            series: normalizedHeatmapSeries,
            xaxis: {
                categories: heatmapXCategories,
                labels: { style: { colors: chartForeColor } }
            },
            yaxis: { labels: { style: { colors: chartForeColor } } },
            plotOptions: {
                heatmap: {
                    shadeIntensity: 0.5,
                    radius: 2,
                    colorScale: {
                        ranges: [{ from: 0, to: 0, color: '#E5E7EB', name: 'Empty' }]
                    }
                }
            }
        }).render();
    </script>
@endpush
