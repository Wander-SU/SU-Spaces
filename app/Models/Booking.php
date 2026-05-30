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
        'start_time',
        'end_time',
        'status',
        'attendee_count',
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
}
