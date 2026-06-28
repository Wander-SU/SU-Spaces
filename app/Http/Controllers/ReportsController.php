<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Building;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * Display the administrative analytics dashboard.
     */
    public function dashboard(Request $request)
    {
        $now = Carbon::now();
        $today = Carbon::today()->toDateString();

        $fromDate = (string) $request->query('from_date', '');
        $toDate = (string) $request->query('to_date', '');
        $filterBuilding = (string) $request->query('filter_building', 'all');
        $filterBookingType = (string) $request->query('filter_booking_type', 'all');
        $selectedBuildingId = $filterBuilding !== 'all' ? (int) $filterBuilding : null;

        $buildings = Building::query()
            ->withCount('rooms')
            ->orderBy('building_name', 'asc')
            ->get(['id', 'building_name', 'building_abbrev']);

        // 1) Top metric cards
        $totalUsers = User::all()
            ->filter(fn (User $user) => (bool) $user->active === true)
            ->count();

        $bookingsQuery = Booking::query()
            ->with(['room.building', 'startTimeSlot', 'endTimeSlot']);

        if (!empty($fromDate)) {
            $bookingsQuery->whereDate('booking_date', '>=', $fromDate);
        }

        if (!empty($toDate)) {
            $bookingsQuery->whereDate('booking_date', '<=', $toDate);
        }

        if ($selectedBuildingId !== null) {
            $bookingsQuery->whereHas('room', function (Builder $roomQuery) use ($selectedBuildingId) {
                $roomQuery->where('building_id', '=', $selectedBuildingId);
            });
        }

        if ($filterBookingType === 'student') {
            $bookingsQuery->where(function ($query) {
                $query->whereRaw('LOWER(purpose) LIKE ?', ['%individual study%'])
                    ->orWhereRaw('LOWER(purpose) LIKE ?', ['%group study%']);
            });
        } elseif ($filterBookingType === 'lecturer') {
            $bookingsQuery->where(function ($query) {
                $query->whereRaw('LOWER(purpose) LIKE ?', ['%cat%'])
                    ->orWhereRaw('LOWER(purpose) LIKE ?', ['%exam%'])
                    ->orWhereRaw('LOWER(purpose) LIKE ?', ['%examination%']);
            });
        }

        $bookings = $bookingsQuery->get();

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

        $systemOverrides = $reportableBookings
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

        // 2) Pie chart distribution:
        // - All buildings selected: bookings per building.
        // - Single building selected + room count > 6: bookings per floor within building.
        // - Single building selected + room count <= 6: bookings per room within building.
        $buildingPieTitle = 'Percentage of Buildings booked';
        $pieRoomsBySlice = [];

        if ($selectedBuildingId === null) {
            $buildingBookingDistribution = $reportableBookings
                ->map(function (Booking $booking) {
                    $building = optional(optional($booking->room)->building);
                    return $building->building_abbrev ?: ($building->building_name ?: 'Unknown');
                })
                ->filter()
                ->countBy()
                ->sortKeys()
                ->map(fn ($count) => (int) $count)
                ->toArray();

            $pieRoomsBySlice = $reportableBookings
                ->groupBy(function (Booking $booking) {
                    $building = optional(optional($booking->room)->building);
                    return $building->building_abbrev ?: ($building->building_name ?: 'Unknown');
                })
                ->map(function ($group) {
                    return $group
                        ->map(fn (Booking $booking) => (string) (optional($booking->room)->room_name ?? ''))
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values()
                        ->all();
                })
                ->toArray();
        } else {
            $selectedBuilding = $buildings->firstWhere('id', $selectedBuildingId);
            $selectedBuildingRoomCount = (int) ($selectedBuilding->rooms_count ?? 0);
            $selectedBuildingName = (string) ($selectedBuilding->building_abbrev ?: $selectedBuilding->building_name ?: 'Selected Building');
            $selectedBuildingAbbrev = strtoupper((string) ($selectedBuilding->building_abbrev ?? ''));

            if ($selectedBuilding !== null && $this->isCentralBuilding($selectedBuilding)) {
                $buildingPieTitle = 'Booking Distribution by Section (' . $selectedBuildingName . ')';

                $sectionBuckets = [
                    'Left Wing' => 0,
                    'Central Part' => 0,
                    'Right Wing' => 0,
                ];
                $sectionRooms = [
                    'Left Wing' => [],
                    'Central Part' => [],
                    'Right Wing' => [],
                ];

                foreach ($reportableBookings as $booking) {
                    $roomName = (string) (optional($booking->room)->room_name ?? '');
                    $section = $this->resolveCentralSectionLabelFromRoomName($roomName);
                    $sectionBuckets[$section] = (int) ($sectionBuckets[$section] ?? 0) + 1;

                    if ($roomName !== '') {
                        $sectionRooms[$section][$roomName] = true;
                    }
                }

                $buildingBookingDistribution = $sectionBuckets;
                $pieRoomsBySlice = collect($sectionRooms)
                    ->map(function (array $roomSet) {
                        $rooms = array_keys($roomSet);
                        sort($rooms);
                        return $rooms;
                    })
                    ->toArray();
            } elseif ($selectedBuildingRoomCount > 6) {
                $buildingPieTitle = 'Booking Distribution by Floor (' . $selectedBuildingName . ')';

                $floorOrder = [
                    'Basement' => 0,
                    'GF' => 1,
                    'F1' => 2,
                    'F2' => 3,
                    'F3' => 4,
                    'F4' => 5,
                    'F5' => 6,
                    'F6' => 7,
                    'F7' => 8,
                    'F8' => 9,
                    'F9' => 10,
                    'F10' => 11,
                    'Unspecified Floor' => 99,
                ];

                $floorDistribution = $reportableBookings
                    ->map(function (Booking $booking) {
                        return $this->resolveFloorLabelFromRoomName((string) optional($booking->room)->room_name);
                    })
                    ->filter()
                    ->countBy()
                    ->map(fn ($count) => (int) $count);

                $hasStructuredFloors = $floorDistribution
                    ->except(['Unspecified Floor'])
                    ->isNotEmpty();

                if ($selectedBuildingAbbrev === 'STMB' || $hasStructuredFloors) {
                    if ($selectedBuildingAbbrev === 'STMB') {
                        // Keep only required STMB floors visible, including floors with zero bookings.
                        $floorDistribution = collect([
                            'Basement' => 0,
                            'GF' => 0,
                            'F1' => 0,
                            'F2' => 0,
                            'F5' => 0,
                        ])->merge($floorDistribution)
                            ->except(['F3', 'F4']);
                    }

                    $buildingBookingDistribution = $floorDistribution
                        ->map(fn ($count) => (int) $count)
                        ->sortBy(function (int $count, string $label) use ($floorOrder) {
                            return $floorOrder[$label] ?? 98;
                        })
                        ->toArray();

                    $floorRooms = $reportableBookings
                        ->groupBy(function (Booking $booking) {
                            return $this->resolveFloorLabelFromRoomName((string) optional($booking->room)->room_name);
                        })
                        ->map(function ($group) {
                            return $group
                                ->map(fn (Booking $booking) => (string) (optional($booking->room)->room_name ?? ''))
                                ->filter()
                                ->unique()
                                ->sort()
                                ->values()
                                ->all();
                        });

                    $pieRoomsBySlice = collect(array_keys($buildingBookingDistribution))
                        ->mapWithKeys(function (string $label) use ($floorRooms) {
                            return [$label => $floorRooms->get($label, [])];
                        })
                        ->toArray();
                } else {
                    // If rooms do not encode floor metadata (e.g., MSB), fallback to room-level slices.
                    $buildingPieTitle = 'Booking Distribution by Room (' . $selectedBuildingName . ')';

                    $buildingBookingDistribution = $reportableBookings
                        ->map(fn (Booking $booking) => (string) (optional($booking->room)->room_name ?: 'Unknown Room'))
                        ->filter()
                        ->countBy()
                        ->sortKeys()
                        ->map(fn ($count) => (int) $count)
                        ->toArray();

                    $pieRoomsBySlice = collect(array_keys($buildingBookingDistribution))
                        ->mapWithKeys(fn (string $roomName) => [$roomName => [$roomName]])
                        ->toArray();
                }
            } else {
                $buildingPieTitle = 'Booking Distribution by Room (' . $selectedBuildingName . ')';

                $buildingBookingDistribution = $reportableBookings
                    ->map(fn (Booking $booking) => (string) (optional($booking->room)->room_name ?: 'Unknown Room'))
                    ->filter()
                    ->countBy()
                    ->sortKeys()
                    ->map(fn ($count) => (int) $count)
                    ->toArray();

                $pieRoomsBySlice = collect(array_keys($buildingBookingDistribution))
                    ->mapWithKeys(fn (string $roomName) => [$roomName => [$roomName]])
                    ->toArray();
            }
        }

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

        $trendRoomsByTimeBlock = $reportableBookings
            ->map(function (Booking $booking) use ($bookingWindowStart, $bookingWindowEnd) {
                $startTime = optional($booking->startTimeSlot)->start_time;
                if (empty($startTime)) {
                    return null;
                }

                $slotStart = Carbon::createFromTimeString($startTime);
                if ($slotStart->lt($bookingWindowStart) || $slotStart->gt($bookingWindowEnd)) {
                    return null;
                }

                return [
                    'time' => $slotStart->format('H:i'),
                    'room' => (string) (optional($booking->room)->room_name ?? ''),
                ];
            })
            ->filter()
            ->groupBy('time')
            ->map(function ($group) {
                return collect($group)
                    ->map(fn ($item) => is_array($item) ? ($item['room'] ?? '') : '')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();
            })
            ->sortKeys()
            ->toArray();

        // 4) Bookings by room capacity bands
        $bookingsByRoomSize = [
            '1-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0,
            '101+' => 0,
        ];
        $roomsByCapacityBand = [
            '1-20' => [],
            '21-40' => [],
            '41-60' => [],
            '61-80' => [],
            '81-100' => [],
            '101+' => [],
        ];

        foreach ($reportableBookings as $booking) {
            $capacity = (int) (optional($booking->room)->capacity ?? 0);

            if ($capacity >= 1 && $capacity <= 20) {
                $bookingsByRoomSize['1-20']++;
                $roomName = (string) (optional($booking->room)->room_name ?? '');
                if ($roomName !== '') {
                    $roomsByCapacityBand['1-20'][$roomName] = true;
                }
            } elseif ($capacity >= 21 && $capacity <= 40) {
                $bookingsByRoomSize['21-40']++;
                $roomName = (string) (optional($booking->room)->room_name ?? '');
                if ($roomName !== '') {
                    $roomsByCapacityBand['21-40'][$roomName] = true;
                }
            } elseif ($capacity >= 41 && $capacity <= 60) {
                $bookingsByRoomSize['41-60']++;
                $roomName = (string) (optional($booking->room)->room_name ?? '');
                if ($roomName !== '') {
                    $roomsByCapacityBand['41-60'][$roomName] = true;
                }
            } elseif ($capacity >= 61 && $capacity <= 80) {
                $bookingsByRoomSize['61-80']++;
                $roomName = (string) (optional($booking->room)->room_name ?? '');
                if ($roomName !== '') {
                    $roomsByCapacityBand['61-80'][$roomName] = true;
                }
            } elseif ($capacity >= 81 && $capacity <= 100) {
                $bookingsByRoomSize['81-100']++;
                $roomName = (string) (optional($booking->room)->room_name ?? '');
                if ($roomName !== '') {
                    $roomsByCapacityBand['81-100'][$roomName] = true;
                }
            } elseif ($capacity >= 101) {
                $bookingsByRoomSize['101+']++;
                $roomName = (string) (optional($booking->room)->room_name ?? '');
                if ($roomName !== '') {
                    $roomsByCapacityBand['101+'][$roomName] = true;
                }
            }
        }

        $roomsByCapacityBand = collect($roomsByCapacityBand)
            ->map(function (array $roomSet) {
                $rooms = array_keys($roomSet);
                sort($rooms);
                return $rooms;
            })
            ->toArray();

        // 5) Peak utilization heatmap series (Mon-Fri x time windows)
        $heatmapWeekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $heatmapTimeWindows = ['07:00-09:00', '09:00-11:00', '11:00-13:00', '13:00-15:00', '15:00-17:00', '17:00-19:00', '19:00-21:00'];

        $heatmapLookup = [];
        $heatmapRoomsLookup = [];
        foreach ($reportableBookings as $booking) {
            $startTime = optional($booking->startTimeSlot)->start_time;
            if (empty($startTime)) {
                continue;
            }

            $hour = (int) Carbon::parse($startTime)->format('H');
            $timeWindow = null;

            if ($hour >= 7 && $hour < 9) {
                $timeWindow = '07:00-09:00';
            } elseif ($hour >= 9 && $hour < 11) {
                $timeWindow = '09:00-11:00';
            } elseif ($hour >= 11 && $hour < 13) {
                $timeWindow = '11:00-13:00';
            } elseif ($hour >= 13 && $hour < 15) {
                $timeWindow = '13:00-15:00';
            } elseif ($hour >= 15 && $hour < 17) {
                $timeWindow = '15:00-17:00';
            } elseif ($hour >= 17 && $hour < 19) {
                $timeWindow = '17:00-19:00';
            } elseif ($hour >= 19 && $hour < 21) {
                $timeWindow = '19:00-21:00';
            }

            if ($timeWindow === null) {
                continue;
            }

            $weekday = Carbon::parse($booking->booking_date)->format('l');
            if (!in_array($weekday, $heatmapWeekdays, true)) {
                continue;
            }

            $heatmapLookup[$weekday][$timeWindow] = (int) ($heatmapLookup[$weekday][$timeWindow] ?? 0) + 1;
            $roomName = (string) (optional($booking->room)->room_name ?? '');
            if ($roomName !== '') {
                $heatmapRoomsLookup[$weekday][$timeWindow][$roomName] = true;
            }
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

        $heatmapRoomsBySlot = [];
        foreach ($heatmapWeekdays as $weekday) {
            $heatmapRoomsBySlot[$weekday] = [];
            foreach ($heatmapTimeWindows as $window) {
                $rooms = array_keys($heatmapRoomsLookup[$weekday][$window] ?? []);
                sort($rooms);
                $heatmapRoomsBySlot[$weekday][$window] = $rooms;
            }
        }

        return view('reports.dashboard', compact(
            'totalUsers',
            'activeBookings',
            'systemOverrides',
            'cancelledBookings',
            'buildingBookingDistribution',
            'buildingPieTitle',
            'pieRoomsBySlice',
            'roomBookingTrendByTimeBlock',
            'trendRoomsByTimeBlock',
            'bookingsByRoomSize',
            'roomsByCapacityBand',
            'peakUtilizationHeatmapSeries',
            'heatmapRoomsBySlot',
            'heatmapWeekdays',
            'heatmapTimeWindows',
            'buildings',
            'fromDate',
            'toDate',
            'filterBuilding',
            'filterBookingType',
            'today'
        ));
    }

    private function isCentralBuilding(Building $building): bool
    {
        $abbrev = strtoupper((string) ($building->building_abbrev ?? ''));
        $name = strtoupper((string) ($building->building_name ?? ''));

        return $abbrev === 'CB' || str_contains($name, 'CENTRAL');
    }

    private function resolveCentralSectionLabelFromRoomName(string $roomName): string
    {
        $normalized = strtoupper(trim($roomName));

        if ($normalized === '') {
            return 'Central Part';
        }

        if (preg_match('/^LT\s*\d+$/', $normalized) === 1 || $normalized === 'KITCHEN 5') {
            return 'Left Wing';
        }

        if (
            preg_match('/^RM\s*[1-3]$/', $normalized) === 1
            || in_array($normalized, ['SUSWA LAB', 'MASINGA LAB', 'ELGON LAB', 'LONGONOT LAB', 'ABERDARE LAB', 'RM B'], true)
        ) {
            return 'Central Part';
        }

        return 'Right Wing';
    }

    private function resolveFloorLabelFromRoomName(string $roomName): string
    {
        $normalized = strtoupper(trim($roomName));

        if ($normalized === '') {
            return 'Unspecified Floor';
        }

        // MSB rooms are numerically named; map known number bands to floor buckets.
        if (preg_match('/^MSB\s+(\d+)$/', $normalized, $matches) === 1) {
            $roomNumber = (int) $matches[1];

            return match (true) {
                $roomNumber >= 1 && $roomNumber <= 2 => 'GF',
                $roomNumber >= 3 && $roomNumber <= 6 => 'F1',
                $roomNumber >= 7 && $roomNumber <= 11 => 'F2',
                $roomNumber >= 12 && $roomNumber <= 14 => 'F3',
                default => 'Unspecified Floor',
            };
        }

        if (str_contains($normalized, 'MSB SEMINAR')) {
            return 'F3';
        }

        if (preg_match('/\bBASEMENT\b/', $normalized) === 1 || preg_match('/\bB-\d+\b/', $normalized) === 1) {
            return 'Basement';
        }

        if (preg_match('/\bGF-\d+\b/', $normalized) === 1 || preg_match('/\bGROUND\b/', $normalized) === 1) {
            return 'GF';
        }

        if (preg_match('/\bF(\d+)-\d+\b/', $normalized, $matches) === 1) {
            return 'F' . $matches[1];
        }

        if (preg_match('/\bF(\d+)\b/', $normalized, $matches) === 1) {
            return 'F' . $matches[1];
        }

        return 'Unspecified Floor';
    }
}
