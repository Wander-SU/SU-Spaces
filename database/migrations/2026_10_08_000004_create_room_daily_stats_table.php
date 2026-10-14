<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Cheap usage counters: one row per room per day.
        // Tracks the number of clicks and searches over a day
        Schema::create('room_daily_stats', function (Blueprint $t) {
            $t->foreignId('room_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->unsignedInteger('clicks')->default(0);
            $t->unsignedInteger('searches')->default(0);
            $t->primary(['room_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_daily_stats');
    }
};
