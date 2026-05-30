<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    /** @use HasFactory<\Database\Factories\BuildingFactory> */
    use HasFactory;

    protected $fillable = [
        'building_name',
        'building_abbrev',
        'phase_id'
    ];

    // A building has many rooms
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    // A building belongs to a phase
    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }
}
