@extends('layouts.app')

{{-- Page Title in Browser Tab --}}
@section('title', 'Reporting Dashboard')

{{-- Page Heading --}}
@section('page-title', 'Reporting Dashboard')

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
    @php
        $today = $today ?? \Carbon\Carbon::today()->toDateString();
        $fromDate = $fromDate ?? old('from_date', request('from_date'));
        $toDate = $toDate ?? old('to_date', request('to_date'));
        $filterBuilding = $filterBuilding ?? old('filter_building', request('filter_building', 'all'));
        $filterBookingType = $filterBookingType ?? old('filter_booking_type', request('filter_booking_type', 'all'));
        $buildings = $buildings ?? collect();
    @endphp

    <div class="dashboard-page space-y-6">
        <form id="reports-filters" method="GET" action="{{ route('reports.dashboard') }}" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-4 mb-6 bg-white dark:bg-[#161615] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-xl shadow-xs font-sans">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">From...</span>
                    <input
                        type="date"
                        name="from_date"
                        value="{{ $fromDate }}"
                        max="{{ $today }}"
                        class="border border-gray-300 dark:border-gray-600 bg-[#FDFDFC] dark:bg-[#0a0a0a] rounded-md px-3 py-1.5 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-0"
                    >
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">To...</span>
                    <input
                        type="date"
                        name="to_date"
                        value="{{ $toDate }}"
                        max="{{ $today }}"
                        class="border border-gray-300 dark:border-gray-600 bg-[#FDFDFC] dark:bg-[#0a0a0a] rounded-md px-3 py-1.5 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-0"
                    >
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Building:</span>
                    <select name="filter_building" class="border border-gray-300 dark:border-gray-600 bg-[#FDFDFC] dark:bg-[#0a0a0a] rounded-md px-3 py-1.5 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-0">
                        <option value="all" @selected($filterBuilding === 'all')>All Buildings</option>
                        @foreach($buildings as $building)
                            <option value="{{ $building->id }}" @selected((string) $filterBuilding === (string) $building->id)>
                                {{ $building->building_name }}{{ !empty($building->building_abbrev) ? ' (' . $building->building_abbrev . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Booking Type:</span>
                    <select name="filter_booking_type" class="border border-gray-300 dark:border-gray-600 bg-[#FDFDFC] dark:bg-[#0a0a0a] rounded-md px-3 py-1.5 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-0">
                        <option value="all" @selected($filterBookingType === 'all')>All Bookings</option>
                        <option value="student" @selected($filterBookingType === 'student')>Student Bookings</option>
                        <option value="lecturer" @selected($filterBookingType === 'lecturer')>Lecturer Bookings</option>
                    </select>
                </div>
            </div>

            <div>
                <a
                    href="{{ route('reports.dashboard') }}"
                    class="inline-flex items-center rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A] bg-gray-50 dark:bg-gray-800/50 px-3 py-1.5 text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-gray-100 dark:hover:bg-gray-700/60"
                >
                    Reset filters
                </a>
            </div>
        </form>

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
                    <p class="text-sm font-sans text-[#706f6c] dark:text-[#A1A09A] mt-1">Priority Bookings (CATs/Exams)</p>
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
                    {{ $buildingPieTitle ?? 'Percentage of Buildings booked' }}
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
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('reports-filters');
            if (!form) {
                return;
            }

            let submitTimer = null;
            // Apply filters quickly on change with a short debounce.
            form.querySelectorAll('input, select').forEach((field) => {
                field.addEventListener('change', () => {
                    if (submitTimer) {
                        clearTimeout(submitTimer);
                    }

                    submitTimer = setTimeout(() => {
                        form.submit();
                    }, 120);
                });
            });
        });

        // Shared global charts styling attributes
        const chartForeColor = '#9CA3AF';
        const chartToolbar = { show: true, tools: { download: true } };

        // --- Data Layer Safe Mappings via Blade JSON Macros ---
        const buildingDistribution = {!! json_encode($buildingBookingDistribution ?? []) !!};
        const buildingLabels = Object.keys(buildingDistribution).length
            ? Object.keys(buildingDistribution)
            : ['No data'];
        const buildingSeries = Object.values(buildingDistribution).length
            ? Object.values(buildingDistribution)
            : [0];

        const trendLabels = {!! json_encode(array_keys($roomBookingTrendByTimeBlock ?? ['08:00' => 0, '10:00' => 0, '12:00' => 0, '14:00' => 0, '16:00' => 0])) !!};
        const trendSeries = {!! json_encode(array_values($roomBookingTrendByTimeBlock ?? [0, 0, 0, 0, 0])) !!};
        const trendRoomsByTimeBlock = {!! json_encode($trendRoomsByTimeBlock ?? []) !!};

        const capacityLabels = {!! json_encode(array_keys($bookingsByRoomSize ?? ['1-20' => 0, '21-40' => 0, '41-60' => 0, '61-80' => 0, '81-100' => 0, '101+' => 0])) !!};
        const capacitySeries = {!! json_encode(array_values($bookingsByRoomSize ?? [0, 0, 0, 0, 0, 0])) !!};
        const roomsByCapacityBand = {!! json_encode($roomsByCapacityBand ?? []) !!};

        const heatmapXCategories = {!! json_encode($heatmapTimeWindows ?? ['07:00-09:00', '09:00-11:00', '11:00-13:00', '13:00-15:00', '15:00-17:00', '17:00-19:00', '19:00-21:00']) !!};
        const heatmapYCategories = {!! json_encode($heatmapWeekdays ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']) !!};
        const heatmapSeries = {!! json_encode($peakUtilizationHeatmapSeries ?? []) !!};
        const heatmapRoomsBySlot = {!! json_encode($heatmapRoomsBySlot ?? []) !!};
        const pieRoomsBySlice = {!! json_encode($pieRoomsBySlice ?? []) !!};

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function roomsTooltipHtml(title, valueLabel, value, rooms) {
            const hasValue = Number(value) > 0;
            const visibleRooms = hasValue ? rooms : [];
            const limitedRooms = visibleRooms.slice(0, 12);
            const extraCount = Math.max(visibleRooms.length - limitedRooms.length, 0);
            const roomsList = limitedRooms.length
                ? limitedRooms.map((room) => `<li>${escapeHtml(room)}</li>`).join('')
                : '<li>None</li>';
            const extraLine = extraCount > 0 ? `<li>+${extraCount} more</li>` : '';

            return `
                <div style="padding:8px 10px; max-width:260px; font-size:12px; line-height:1.35; color:#111827; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px;">
                    <div style="font-weight:600; margin-bottom:4px; color:#111827;">${escapeHtml(title)}</div>
                    <div style="margin-bottom:6px; color:#374151;">${escapeHtml(valueLabel)}: <strong style="color:#111827;">${Number(value)}</strong></div>
                    <div style="font-weight:600; margin-bottom:3px; color:#111827;">Rooms</div>
                    <ul style="margin:0; padding-left:16px;">${roomsList}${extraLine}</ul>
                </div>
            `;
        }

        // 1. Percentage of Buildings Booked (Pie Chart)
        new ApexCharts(document.querySelector("#building-pie-chart"), {
            chart: { type: 'pie', height: 350, toolbar: chartToolbar },
            labels: buildingLabels,
            series: buildingSeries,
            tooltip: {
                custom: function ({ series, seriesIndex, w }) {
                    const label = w.globals.labels[seriesIndex] ?? 'Slice';
                    const value = series[seriesIndex] ?? 0;
                    const rooms = pieRoomsBySlice[label] || [];
                    return roomsTooltipHtml(label, 'Bookings', value, rooms);
                }
            },
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
            tooltip: {
                custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                    const value = (series[seriesIndex] || [])[dataPointIndex] || 0;
                    const timeLabel = trendLabels[dataPointIndex] || 'Time Block';
                    const rooms = value > 0 ? (trendRoomsByTimeBlock[timeLabel] || []) : [];
                    return roomsTooltipHtml(timeLabel, 'Bookings', value, rooms);
                }
            },
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
            tooltip: {
                custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                    const value = (series[seriesIndex] || [])[dataPointIndex] || 0;
                    const capacityBand = (w.globals.labels || [])[dataPointIndex] || 'Capacity Band';
                    const rooms = value > 0 ? (roomsByCapacityBand[capacityBand] || []) : [];
                    return roomsTooltipHtml(capacityBand, 'Bookings', value, rooms);
                }
            },
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
            tooltip: {
                custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                    const day = (w.config.series[seriesIndex] || {}).name || 'Day';
                    const slot = ((w.config.series[seriesIndex] || {}).data || [])[dataPointIndex]?.x || 'Time Slot';
                    const value = (series[seriesIndex] || [])[dataPointIndex] || 0;
                    const rooms = value > 0
                        ? (((heatmapRoomsBySlot[day] || {})[slot]) || [])
                        : [];
                    return roomsTooltipHtml(`${day} - ${slot}`, 'Bookings', value, rooms);
                }
            },
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
