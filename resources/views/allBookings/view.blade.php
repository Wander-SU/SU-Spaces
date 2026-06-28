@extends('layouts.app')

@section('title', 'All Bookings')
@section('page-title', 'All Bookings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('bookings.previous') }}">Previous Bookings</a></li>
    <li class="breadcrumb-item active" aria-current="page">All Bookings</li>
@endsection

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .all-bookings-page {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .all-bookings-page h2,
        .all-bookings-page .section-title {
            font-weight: 700;
            color: #000000;
        }

        .all-bookings-page .room-name {
            font-weight: 600;
            text-transform: uppercase;
        }

        .all-bookings-page .booking-details {
            font-weight: 400;
            color: #333333;
        }

        .all-bookings-page .booking-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            background-color: #F9FAFB;
        }

        .all-bookings-page .status-confirmed::before {
            content: "";
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #10B981;
            border-radius: 50%;
            margin-right: 8px;
        }

        .all-bookings-page .status-cancel::before {
            content: "";
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #6B7280;
            border-radius: 50%;
            margin-right: 8px;
        }

        .all-bookings-page .cancelled-text {
            text-decoration: line-through;
        }

        .all-bookings-page .priority-alert {
            background-color: #FEF2F2;
            border: 1px solid #FEE2E2;
        }
    </style>
@endpush

@section('content')
    @php
        // Resolve filter/UI defaults when query params are missing.
        $today = $today ?? \Carbon\Carbon::today()->toDateString();
        $fromDate = $fromDate ?? old('from_date', request('from_date'));
        $toDate = $toDate ?? old('to_date', request('to_date'));
        $sortBy = old('sort_by', request('sort_by', 'newest'));
        $bookings = collect($bookings ?? []);
        $priorityAlerts = collect($priorityAlerts ?? []);
        $hasAnyBookings = (bool) ($hasAnyBookings ?? false);
        $hasDateFilter = !empty($fromDate) || !empty($toDate);

        // Dynamic heading text that reflects the selected date-window mode.
        $headingWindowText = null;
        if (!empty($fromDate) && empty($toDate)) {
            $headingWindowText = 'from ' . \Carbon\Carbon::parse($fromDate)->format('jS F Y');
        } elseif (empty($fromDate) && !empty($toDate)) {
            $headingWindowText = 'until ' . \Carbon\Carbon::parse($toDate)->format('jS F Y');
        } elseif (!empty($fromDate) && !empty($toDate)) {
            $headingWindowText = \Carbon\Carbon::parse($fromDate)->format('jS F Y') . ' to ' . \Carbon\Carbon::parse($toDate)->format('jS F Y');
        }
    @endphp

    <div class="all-bookings-page rounded-xl border border-[#e3e3e0] bg-white p-4 md:p-6 dark:border-[#3E3E3A] dark:bg-[#161615]">
        {{-- Top filter bar: drives server-side query and re-renders list/alerts --}}
        <form id="bookings-filters" method="GET" action="{{ route('bookings.previous') }}" class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div>
                    <label for="from_date" class="mb-1 block text-sm text-[#1b1b18] dark:text-[#EDEDEC]">From...</label>
                    <input
                        id="from_date"
                        name="from_date"
                        type="date"
                        value="{{ $fromDate }}"
                        max="{{ $today }}"
                        class="w-full border border-[#e3e3e0] rounded-md px-2 py-1 text-sm text-[#1b1b18] bg-white dark:border-[#3E3E3A] dark:bg-white dark:text-[#1b1b18]"
                    >
                </div>

                <div>
                    <label for="to_date" class="mb-1 block text-sm text-[#1b1b18] dark:text-[#EDEDEC]">To...</label>
                    <input
                        id="to_date"
                        name="to_date"
                        type="date"
                        value="{{ $toDate }}"
                        max="{{ $today }}"
                        class="w-full border border-[#e3e3e0] rounded-md px-2 py-1 text-sm text-[#1b1b18] bg-white dark:border-[#3E3E3A] dark:bg-white dark:text-[#1b1b18]"
                    >
                </div>
            </div>

            <div class="md:ml-auto">
                <label for="sort_by" class="mb-1 block text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Sort By...</label>
                <select
                    id="sort_by"
                    name="sort_by"
                    class="w-full border border-[#e3e3e0] rounded-md px-2 py-1 text-sm text-[#1b1b18] bg-white dark:border-[#3E3E3A] dark:bg-white dark:text-[#1b1b18]"
                >
                    <option value="newest" @selected($sortBy === 'newest')>Newest</option>
                    <option value="oldest" @selected($sortBy === 'oldest')>Oldest</option>
                </select>
            </div>
        </form>

        <section class="mb-8">
            <h2 class="section-title text-[#1b1b18] dark:text-[#EDEDEC] font-semibold text-lg mb-4">
                Confirmed Bookings @if($hasDateFilter && $headingWindowText) ({{ $headingWindowText }})@endif
            </h2>

            @if($bookings->isEmpty())
                <div class="rounded-lg border border-dashed border-[#e3e3e0] p-8 text-center dark:border-[#3E3E3A]">
                    <div class="mx-auto mb-5 flex h-28 w-28 items-center justify-center rounded-full bg-[#f6f6f4] dark:bg-[#232322]">
                        <svg class="h-16 w-16 text-[#1b1b18] dark:text-[#EDEDEC]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3M4 9h16M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
                        </svg>
                    </div>
                    <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
                        {{ $hasDateFilter ? 'No confirmed bookings found for the selected date range.' : 'No confirmed bookings found yet. Let\'s find you a room!' }}
                    </p>
                    <div class="mt-5">
                        <a href="{{ route('bookings.index') }}" class="inline-flex items-center rounded-md bg-gradient-to-r from-[#0048AD] to-[#FF383C] px-6 py-2 font-medium text-white transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[#0048AD]/30">
                            Find a room now
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($bookings as $booking)
                        {{-- Confirmed booking card with status badge and cancel action. --}}
                        <article class="booking-card flex flex-col gap-3 rounded-lg border border-[#e3e3e0] bg-white p-4 md:flex-row md:items-center md:justify-between dark:border-[#3E3E3A] dark:bg-[#161615]">
                            <div class="md:w-1/4">
                                <span class="room-name inline-flex rounded-md bg-[#1b1b18] px-3 py-2 text-sm font-semibold tracking-wide text-white dark:bg-[#eeeeec] dark:text-black">
                                    {{ $booking['room_name'] ?? data_get($booking, 'room_name', 'ROOM') }}
                                </span>
                            </div>

                            <div class="booking-details text-sm text-[#1b1b18] dark:text-[#EDEDEC] md:w-2/4">
                                {{ $booking['schedule'] ?? data_get($booking, 'schedule', 'Date and time not available') }}
                            </div>

                            <div class="md:w-1/4 md:text-right flex flex-col items-start gap-2 md:items-end">
                                <span class="status-confirmed inline-flex items-center text-green-600 bg-green-50 px-2.5 py-1 rounded-full text-sm font-medium">
                                    {{ $booking['status'] ?? data_get($booking, 'status', 'Confirmed') }}
                                </span>

                                <form method="POST" action="{{ route('bookings.previous.cancel', data_get($booking, 'id')) }}" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    @csrf
                                    {{-- Preserve current filters so refresh stays on the same view window after POST redirect. --}}
                                    <input type="hidden" name="from_date" value="{{ $fromDate }}">
                                    <input type="hidden" name="to_date" value="{{ $toDate }}">
                                    <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                                    <button type="submit" class="status-cancel inline-flex items-center text-gray-600 bg-gray-100 px-2.5 py-1 rounded-full text-sm font-medium hover:bg-gray-200">
                                        Cancel Booking
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="mb-6 border-t border-[#e3e3e0] dark:border-[#3E3E3A]"></div>

        <section>
            <h2 class="section-title mb-3 text-[#1b1b18] dark:text-[#EDEDEC] text-base font-semibold">Priority Alerts</h2>

            @foreach($priorityAlerts as $alert)
                {{-- Alerts are derived from voided bookings returned by the backend mapper. --}}
                <article class="priority-alert rounded-lg p-4 mb-6">
                    <div class="flex items-start justify-between gap-3">
                        <p class="cancelled-text text-sm font-semibold text-[#7a1e1e]">
                            {{ $alert['room_name'] ?? data_get($alert, 'room_name', 'ROOM') }}
                        </p>
                        <p class="text-sm font-semibold text-red-600">● {{ $alert['status'] ?? data_get($alert, 'status', 'Reassigned') }}</p>
                    </div>
                    <p class="mt-2 text-sm text-[#7a1e1e] italic">
                        {{ $alert['note'] ?? data_get($alert, 'note') }}
                    </p>
                </article>
            @endforeach
        </section>
    </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('bookings-filters');
                if (!form) {
                    return;
                }

                let submitTimer = null;
                // Apply filters quickly on change with a short debounce to avoid duplicate submits.
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
        </script>
@endsection
