<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomFeedback extends Model
{
    protected $table = 'room_feedback';
    protected $fillable = ['room_selection_id', 'user_id', 'rating', 'has_issue', 'comment'];
    protected function casts(): array { return ['has_issue' => 'boolean']; }

    public function selection(): BelongsTo
    {
        return $this->belongsTo(RoomSelection::class, 'room_selection_id');
    }
}