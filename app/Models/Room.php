<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory;

    protected $fillable = [
        'room_name',
        'code',
        'floor',
        'capacity',
        'type',
        'building_id'
    ];

    // A room has many bookings
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // A room has many base bookings
    public function baseBookings(): HasMany
    {
        return $this->hasMany(BaseBooking::class);
    }

    // A room belongs to a building
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function getFloorLabelAttribute(): string
    {
        return match (true) {
            $this->floor === null => '',
            $this->floor < 0      => 'Basement',
            $this->floor === 0    => 'Ground Floor',
            default               => 'Floor ' . $this->floor,
        };
    }

    public function getUnitAttribute(): string
    {
        return config("spaces.types.{$this->type}.unit", 'seats');
    }

    public function getIllustrationKeyAttribute(): string
    {
        return config("spaces.types.{$this->type}.illustration", 'room');
    }

    public function getIllustrationSvgAttribute()
    {
        return null;
    }
}
