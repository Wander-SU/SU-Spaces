<?php

namespace App\Support;

use App\Models\Room;

class RoomPresenter
{
    /** The shape the Flutter `Room.fromJson` expects. */
    public static function card(Room $r, array $extra = []): array
    {
        return array_merge([
            'id' => $r->id,
            'name' => $r->room_name,
            'code' => $r->code ?? '',
            'building' => $r->building->building_name,
            'floor' => $r->floor_label,
            'type' => $r->type,
            'capacity' => $r->capacity,
            'capacity_unit' => $r->unit,
            'illustration' => $r->illustration_key,
            'illustration_svg' => $r->illustration_svg, // null unless set per room
        ], $extra);
    }
}
