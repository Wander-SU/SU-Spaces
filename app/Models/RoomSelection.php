<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomSelection extends Model
{
    protected $fillable = ['room_id', 'user_id', 'starts_at', 'ends_at', 'participants'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Selections whose slot has not ended yet. */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('ends_at', '>', now());
    }

    /** Selections overlapping the given period. */
    public function scopeOverlapping(Builder $q, $from, $to): Builder
    {
        return $q->where('starts_at', '<', $to)->where('ends_at', '>', $from);
    }
}