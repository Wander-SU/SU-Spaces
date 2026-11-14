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
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('code', 40)->nullable();
            $table->smallInteger('floor')->default(0);        // -1 basement, 0 ground, 1+ upper floors
            $table->string('type', 30)->default('room');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['code', 'floor', 'type']);
        });
    }
};
