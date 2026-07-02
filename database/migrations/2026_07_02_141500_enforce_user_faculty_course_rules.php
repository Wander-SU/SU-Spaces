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

        $allowedFaculties = array_keys($coursesByFaculty);

        // System Admin and IT Support should not have faculty/course values.
        DB::table('users')
            ->whereIn('role_id', [1, 4])
            ->update([
                'account_type' => null,
                'faculty' => null,
                'course' => null,
            ]);

        // Students must always have a valid faculty.
        $studentIdsMissingFaculty = DB::table('users')
            ->where('account_type', 'student')
            ->where(function ($query) use ($allowedFaculties): void {
                $query->whereNull('faculty')
                    ->orWhereNotIn('faculty', $allowedFaculties);
            })
            ->pluck('id');

        foreach ($studentIdsMissingFaculty as $studentId) {
            $faculty = $allowedFaculties[array_rand($allowedFaculties)];
            DB::table('users')->where('id', $studentId)->update(['faculty' => $faculty]);
        }

        // Students must always have a course that belongs to their faculty.
        foreach ($coursesByFaculty as $faculty => $allowedCourses) {
            $studentIdsForFaculty = DB::table('users')
                ->where('account_type', 'student')
                ->where('faculty', $faculty)
                ->pluck('id');

            foreach ($studentIdsForFaculty as $studentId) {
                $course = $allowedCourses[array_rand($allowedCourses)];
                DB::table('users')->where('id', $studentId)->update(['course' => $course]);
            }
        }

        // Lecturers must have a valid faculty; course may be null.
        $lecturerIdsMissingFaculty = DB::table('users')
            ->where('account_type', 'lecturer')
            ->whereNotIn('role_id', [1, 4])
            ->where(function ($query) use ($allowedFaculties): void {
                $query->whereNull('faculty')
                    ->orWhereNotIn('faculty', $allowedFaculties);
            })
            ->pluck('id');

        foreach ($lecturerIdsMissingFaculty as $lecturerId) {
            $faculty = $allowedFaculties[array_rand($allowedFaculties)];
            DB::table('users')->where('id', $lecturerId)->update(['faculty' => $faculty]);
        }

        // If lecturer course is present but not valid for faculty, clear it.
        foreach ($coursesByFaculty as $faculty => $allowedCourses) {
            DB::table('users')
                ->where('account_type', 'lecturer')
                ->whereNotIn('role_id', [1, 4])
                ->where('faculty', $faculty)
                ->whereNotNull('course')
                ->whereNotIn('course', $allowedCourses)
                ->update(['course' => null]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty: this migration normalizes live user data.
    }
};
