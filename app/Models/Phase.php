<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Phase extends Model
{
    /** @use HasFactory<\Database\Factories\PhaseFactory> */
    use HasFactory;

    protected $fillable = [
        'phase_name'
    ];

    // A phase has many buildings
    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }
}
