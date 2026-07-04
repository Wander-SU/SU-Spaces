<?php
namespace App\Traits\Concerns;

use App\Models\BaseBooking;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

trait ComputeStatuses
{
    public function computeStatuses(){
        // Error handling, don't compute statuses if there is no time or date
        if(!$this->search_date || !$this->start_time_id || !$this->end_time_id){
            $this->roomStatuses = [];
            return;
        }

        $day = Carbon::parse($this->search_date)->englishDayOfWeek;

        $cacheKey = "room-statuses:{$this->search_date}:{$this->start_time_id}:{$this->end_time_id}";
        $statuses = Cache::remember($cacheKey, now()->addSeconds(20), function () use ($day) {
            $baseOccupied = BaseBooking::query()
            ->join('rooms as r','r.id','=','base_bookings.room_id','inner',false)
            ->where('lesson_day',$day)
            ->where('base_bookings.start_time_id','<=',$this->end_time_id)
            ->where('base_bookings.end_time_id','>=',$this->start_time_id)
            ->pluck('r.room_name')
            ->toArray();

            $adHocFull = Booking::query()
            ->join('rooms as r','r.id','=','bookings.room_id','inner',false)
            ->select('r.room_name','r.capacity',DB::raw('sum(bookings.attendee_count) as total_attendees'))
            ->where('bookings.status','Booked')
            ->where('bookings.booking_date',$this->search_date)
            ->where('bookings.start_time_id','<=',$this->end_time_id)
            ->where('bookings.end_time_id','>=',$this->start_time_id)
            ->groupby('r.room_name','r.capacity')
            ->havingRaw('sum(bookings.attendee_count) >= r.capacity')
            ->pluck('r.room_name')
            ->toArray();

            $computed = [];
            foreach(array_unique(array_merge($baseOccupied,$adHocFull)) as $name){
                $computed[$name] = in_array($name,$baseOccupied)
                ? 'base_booking'
                : 'at_capacity';
            }

            return $computed;
        });

        $this->roomStatuses = $statuses;

        $this->dispatch('statusUpdated',statuses:$this->roomStatuses);
    }
}

?>