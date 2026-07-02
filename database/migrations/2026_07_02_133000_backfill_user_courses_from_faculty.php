<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $coursesByFaculty = [
            'SCES' => ['BICS', 'BCNS', 'BBIT', 'BSEEE'],
            'SIMS' => ['BBS.FENG', 'BBS.FE', 'BBS.ACT', 'BSc.SDS'],
            'SLS' => ['LLB'],
            'SBS' => ['BFS', 'BSCM', 'BCOM'],
            'STH' => ['BTM', 'BHM'],
            'SHSS' => ['BDP', 'BAC', 'BIS'],
        ];

        foreach ($coursesByFaculty as $faculty => $courses) {
            $fallbackCourse = $courses[0] ?? null;

            if ($fallbackCourse === null) {
                continue;
            }

            DB::table('users')
                ->where('faculty', $faculty)
                ->whereNull('course')
                ->update(['course' => $fallbackCourse]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty because we cannot safely determine
        // which null courses were backfilled by this migration.
    }
};
