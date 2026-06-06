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
        Schema::create('base_bookings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('course',30);
            $table->string('semester',30);
            $table->string('academic_year',30);
            $table->string('academic_session',30);
            $table->string('subject',30);
            $table->string('course_number',30);
            $table->string('unit_name',160);
            $table->string('lesson_day',30);
            $table->time('start_time');
            $table->time('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('base_bookings');
    }
};
