<?php

namespace App\Models;

use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaseBooking extends Model
{
    /** @use HasFactory<\Database\Factories\BaseBookingFactory> */
    use HasFactory;

    protected $fillable =[
        'course',
        'semester',
        'academic_year',
        'academic_session',
        'subject',
        'course_number',
        'unit_name',
        'lesson_day',
        'start_time',
        'end_time',
        'room_id'
    ];

    // A base booking belongs to a room
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    // A base booking start time belongs to a timeSlot
    public function startTimeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class,'start_time_id');
    }

    // A base booking end time belongs to a timeSlot
    public function endTimeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class,'end_time_id');
    }
}
