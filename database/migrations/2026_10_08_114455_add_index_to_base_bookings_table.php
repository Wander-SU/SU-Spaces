<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('base_bookings', function (Blueprint $table) {
            $table->index('room_id');
            $table->index('start_time_id');
            $table->index('lesson_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('base_bookings', function (Blueprint $table) {
            $table->dropIndex('room_id');
            $table->dropIndex('start_time_id');
            $table->dropIndex('lesson_day');
        });
    }
};
