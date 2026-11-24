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
        Schema::create('room_feedback', function (Blueprint $t) {
            $t->id();
            $t->foreignId('room_selection_id')->unique()->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('rating');
            $t->boolean('has_issue')->default(false);
            $t->text('comment')->nullable();
            $t->timestamps();
        });

        // Used in Phase 4 so the "How was it?" prompt is only sent once.
        Schema::table('room_selections', function (Blueprint $t) {
            $t->timestamp('feedback_prompted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('room_selections', fn (Blueprint $t) => $t->dropColumn('feedback_prompted_at'));
        Schema::dropIfExists('room_feedback');
    }
};
