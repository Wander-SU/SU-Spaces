<?php

namespace App\Services;

use App\Models\BaseBooking;
use Illuminate\Support\Collection;

/**
 * All the "is this room free?" logic lives here. Times are plain minutes since
 * midnight, so it is database-agnostic (MySQL/PostgreSQL) and easy to unit test.
 *
 * Free time is computed in PHP from ONE query per request (all of the day's
 * sessions for the candidate rooms), so there is no N+1 and no dialect-specific SQL.
 */
class AvailabilityService
{
    public function __construct(
        private ?string $open = null,   // "HH:mm", defaults to config('spaces.open')
        private ?string $close = null,
    ) {
    }

    public static function toMin(string $time): int
    {
        // Convert the value from hours and minutes to minutes from midnight
        [$h, $m] = array_map('intval', explode(':', substr($time, 0, 5)));
        return $h * 60 + $m;
    }

    public static function fmt(int $minutes): string
    {
        // Returns the minutes values to hours and minutes again
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /** 14:00 -> "2:00 PM" for human messages. */
    public static function fmt12(int $minutes): string
    {
        $h = intdiv($minutes, 60) % 24;
        $m = $minutes % 60;
        return sprintf('%d:%02d %s', $h % 12 === 0 ? 12 : $h % 12, $m, $h >= 12 ? 'PM' : 'AM');
    }

    // Convert the opening and closing times into minutes
    // Can be read from the config folder
    public function openMin(): int
    {
        return self::toMin($this->open ?? config('spaces.open'));
    }

    public function closeMin(): int
    {
        return self::toMin($this->close ?? config('spaces.close'));
    }

    /**
     * ADAPTER POINT. Returns the sessions (occupied blocks) of the given rooms on
     * one date, grouped by room_id and ordered by start_time. Each item needs:
     * start_time, end_time ("HH:mm[:ss]"), title, description.
     *
     * If your existing timetable lives in other tables (weekly recurring slots,
     * bookings, an imported timetable), rewrite ONLY this method - build the same
     * shape from your tables and everything else keeps working.
     *
     * @param  iterable<int>  $roomIds
     * @return Collection<int, Collection<int, object>>
     */
    public function sessionsByRoom(iterable $roomIds, string $date): Collection
    {
        return BaseBooking::query()
            ->with(['startTimeSlot:id,start_time','endTimeSlot:id,end_time'])
            ->where('lesson_day', $date)
            ->whereIn('room_id', is_array($roomIds) ? $roomIds : iterator_to_array($roomIds))
            ->orderBy('start_time_id')
            ->get()
            ->groupBy('room_id');
    }

    /**
     * Turn a room's sessions into free + busy windows (minutes), clamped to
     * operating hours. Overlapping sessions are handled.
     *
     * @param  Collection<int, object>  $sessions
     * @return array{0: array<int, array{start:int,end:int}>, 1: array<int, array{start:int,end:int,label:?string}>}
     */
    public function windows(Collection $sessions): array
    {
        $open = $this->openMin();
        $close = $this->closeMin();

        // Load up the busy sessions in an array
        $busy = [];
        foreach ($sessions as $s) {
            $a = max($open, self::toMin($s->startTimeSlot->start_time));
            $b = min($close, self::toMin($s->endTimeSlot->end_time));
            if ($b > $a) {
                $busy[] = ['start' => $a, 'end' => $b, 'label' => $s->title ?? null];
            }
        }
        usort($busy, fn ($x, $y) => $x['start'] <=> $y['start']);

        // How we get the free sessions
        // We crawl the busy array and get the sessions that are empty 
        $free = [];
        $cursor = $open;
        foreach ($busy as $b) {
            if ($b['start'] > $cursor) {
                $free[] = ['start' => $cursor, 'end' => $b['start']];
            }
            $cursor = max($cursor, $b['end']);
        }
        // If after crawling the entire busy array you are still not at the close time, add that block
        if ($cursor < $close) {
            $free[] = ['start' => $cursor, 'end' => $close];
        }

        return [$free, $busy];
    }

    /**
     * Compare free windows with a requested range.
     *  free        -> the whole range sits inside one free window
     *  partly_free -> some, but not all, of the range is free
     *  occupied    -> none of it is free
     */
    // Based on a user request classify as free, partly free or fully occupied
    public function classify(array $free, int $from, int $to): string
    {
        // Check the free status of the room
        foreach ($free as $w) {
            if ($w['start'] <= $from && $w['end'] >= $to) {
                return 'free';
            }
        }

        $freeMinutes = 0;
        foreach ($free as $w) {
            $freeMinutes += max(0, min($w['end'], $to) - max($w['start'], $from));
        }

        return $freeMinutes > 0 ? 'partly_free' : 'occupied';
    }

    /**
     * Status right now.
     * @return array{0:string,1:?int,2:?int}  [status, free_until (minutes), minutes_left]
     */
    public function nowState(array $free, int $nowMin): array
    {
        foreach ($free as $w) {
            if ($w['start'] <= $nowMin && $nowMin < $w['end']) {
                return ['free', $w['end'], $w['end'] - $nowMin];
            }
        }
        return ['occupied', null, null];
    }

    /** Windows -> API shape ("HH:mm" strings). */
    public static function present(array $windows): array
    {
        return array_map(fn ($w) => array_filter([
            'start' => self::fmt($w['start']),
            'end' => self::fmt($w['end']),
            'label' => $w['label'] ?? null,
        ], fn ($v) => $v !== null), $windows);
    }

    /**
     * Contiguous slots covering open..close for the detail timeline:
     * session blocks interleaved with "free" gaps.
     */
    public function daySlots(Collection $sessions): array
    {
        $open = $this->openMin();
        $close = $this->closeMin();
        $slots = [];
        $cursor = $open;

        $free = fn (int $a, int $b) => [
            'start' => self::fmt($a), 'end' => self::fmt($b),
            'state' => 'free', 'title' => 'Free', 'subtitle' => 'Free for open study',
        ];

        foreach ($sessions->sortBy('start_time') as $s) {
            // Guards the timings, starts cannot be less than where we currently are
            // Ends have to be less than the overall close time
            $a = max($cursor, self::toMin($s->startTimeSlot->start_time));
            $b = min($close, self::toMin($s->endTimeSlot->end_time));
            if ($b <= $a) {
                continue;
            }
            if ($a > $cursor) {
                $slots[] = $free($cursor, $a);
            }
            // Get the title data and stores it as m
            // We handle the time before we reach the busy block
            // We handle the times that are specified in that session block
            preg_match('/^[A-Za-z]+/', (string) $s->title, $m);
            // dd($s);
            $slots[] = [
                'start' => self::fmt($a), 'end' => self::fmt($b), 'state' => 'busy',
                'title' => $m[0] ?? $s->title,
                'subtitle' => trim('In use • ' . $s->course_number . ' ' . ($s->course?? '')),
            ];
            $cursor = $b;
        }
        if ($cursor < $close) {
            $slots[] = $free($cursor, $close);
        }

        return $slots;
    }
}
