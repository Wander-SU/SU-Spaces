<?php

namespace App\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    /** @use HasFactory<\Database\Factories\TimeSlotFactory> */
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time'
    ];

    // A Time Slot has many bookings
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // A time Slot has many basebookings
    public function base_bookings(): HasMany
    {
        return $this->hasMany(BaseBooking::class);
    }
}
