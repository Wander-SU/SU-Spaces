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
            $table->string('Course',30);
            $table->string('Semester',30);
            $table->string('Academic_Year',30);
            $table->string('Academic_Session',30);
            $table->string('Subject',30);
            $table->string('Course_Number',30);
            $table->string('Unit_Name',30);
            $table->string('Lesson_Day',30);
            $table->time('Start_Time');
            $table->time('End_Time');
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
