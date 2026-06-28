<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * Display the administrative analytics dashboard.
     */
    public function dashboard(Request $request)
    {
        $now = Carbon::now();

        // 1) Top metric cards
        $totalUsers = User::all()
            ->filter(fn (User $user) => (bool) $user->active === true)
            ->count();

        $bookings = Booking::query()
            ->with(['room.building', 'startTimeSlot', 'endTimeSlot'])
            ->get();

        $reportableBookings = $bookings->filter(function (Booking $booking) {
            return !in_array((string) $booking->status, ['Cancelled', 'Reassigned', 'Voided'], true);
        });

        $activeBookings = $bookings
            ->filter(function (Booking $booking) use ($now) {
                $isConfirmed = in_array((string) $booking->status, ['Confirmed', 'Booked'], true);
                if (!$isConfirmed) {
                    return false;
                }

                $endTime = optional($booking->endTimeSlot)->end_time ?: '23:59:59';
                return Carbon::parse($booking->booking_date . ' ' . $endTime)->greaterThanOrEqualTo($now);
            })
            ->count();

        $systemOverrides = $bookings
            ->filter(function (Booking $booking) {
                $purpose = strtolower((string) $booking->purpose);
                return str_contains($purpose, 'cat') || str_contains($purpose, 'exam');
            })
            ->count();

        $cancelledBookings = $bookings
            ->filter(function (Booking $booking) {
                return in_array((string) $booking->status, ['Cancelled', 'Reassigned', 'Voided'], true);
            })
            ->count();

        // 2) Building booking distribution (bookings -> rooms -> buildings)
        $buildingBookingDistribution = $bookings
            ->map(function (Booking $booking) {
                $building = optional(optional($booking->room)->building);
                return $building->building_abbrev ?: ($building->building_name ?: 'Unknown');
            })
            ->filter()
            ->countBy()
            ->sortKeys()
            ->map(fn ($count) => (int) $count)
            ->toArray();

        // 3) Room booking trend by real start-time slots within booking window.
        $bookingWindowStart = Carbon::createFromTimeString('07:00:00');
        $bookingWindowEnd = Carbon::createFromTimeString('17:30:00');

        $roomBookingTrendByTimeBlock = $reportableBookings
            ->map(function (Booking $booking) use ($bookingWindowStart, $bookingWindowEnd) {
                $startTime = optional($booking->startTimeSlot)->start_time;
                if (empty($startTime)) {
                    return null;
                }

                $slotStart = Carbon::createFromTimeString($startTime);
                if ($slotStart->lt($bookingWindowStart) || $slotStart->gt($bookingWindowEnd)) {
                    return null;
                }

                return $slotStart->format('H:i');
            })
            ->filter()
            ->countBy()
            ->sortKeys()
            ->map(fn ($count) => (int) $count)
            ->toArray();

        // 4) Bookings by room capacity bands
        $bookingsByRoomSize = [
            '1-30' => 0,
            '31-60' => 0,
            '61-100' => 0,
            '101+' => 0,
        ];

        foreach ($reportableBookings as $booking) {
            $capacity = (int) (optional($booking->room)->capacity ?? 0);

            if ($capacity >= 1 && $capacity <= 30) {
                $bookingsByRoomSize['1-30']++;
            } elseif ($capacity >= 31 && $capacity <= 60) {
                $bookingsByRoomSize['31-60']++;
            } elseif ($capacity >= 61 && $capacity <= 100) {
                $bookingsByRoomSize['61-100']++;
            } elseif ($capacity >= 101) {
                $bookingsByRoomSize['101+']++;
            }
        }

        // 5) Peak utilization heatmap series (Mon-Fri x time windows)
        $heatmapWeekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $heatmapTimeWindows = ['08:00-10:00', '10:00-12:00', '12:00-14:00', '14:00-16:00', '16:00-18:00'];

        $heatmapLookup = [];
        foreach ($bookings as $booking) {
            $startTime = optional($booking->startTimeSlot)->start_time;
            if (empty($startTime)) {
                continue;
            }

            $hour = (int) Carbon::parse($startTime)->format('H');
            $timeWindow = null;

            if ($hour >= 8 && $hour < 10) {
                $timeWindow = '08:00-10:00';
            } elseif ($hour >= 10 && $hour < 12) {
                $timeWindow = '10:00-12:00';
            } elseif ($hour >= 12 && $hour < 14) {
                $timeWindow = '12:00-14:00';
            } elseif ($hour >= 14 && $hour < 16) {
                $timeWindow = '14:00-16:00';
            } elseif ($hour >= 16 && $hour < 18) {
                $timeWindow = '16:00-18:00';
            }

            if ($timeWindow === null) {
                continue;
            }

            $weekday = Carbon::parse($booking->booking_date)->format('l');
            if (!in_array($weekday, $heatmapWeekdays, true)) {
                continue;
            }

            $heatmapLookup[$weekday][$timeWindow] = (int) ($heatmapLookup[$weekday][$timeWindow] ?? 0) + 1;
        }

        $peakUtilizationHeatmapSeries = [];
        foreach ($heatmapWeekdays as $weekday) {
            $rowData = [];
            foreach ($heatmapTimeWindows as $window) {
                $rowData[] = [
                    'x' => $window,
                    'y' => (int) ($heatmapLookup[$weekday][$window] ?? 0),
                ];
            }

            $peakUtilizationHeatmapSeries[] = [
                'name' => $weekday,
                'data' => $rowData,
            ];
        }

        return view('reports.dashboard', compact(
            'totalUsers',
            'activeBookings',
            'systemOverrides',
            'cancelledBookings',
            'buildingBookingDistribution',
            'roomBookingTrendByTimeBlock',
            'bookingsByRoomSize',
            'peakUtilizationHeatmapSeries'
        ));
    }
}
