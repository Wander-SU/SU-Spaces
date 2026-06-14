<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $fillable =[
        'start_time_id',
        'end_time_id',
        'status',
        'attendee_count',
        'booking_date',
        'purpose',
        'room_id',
        'user_id'
    ];

    // A booking belongs to a room
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    // A booking belongs to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // A booking start time belongs to a timeSlot
    public function startTimeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class,'start_time_id');
    }

    // A booking end time belongs to a timeSlot
    public function endTimeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class,'end_time_id');
    }
}
