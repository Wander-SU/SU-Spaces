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
        $facultyMap = [
            'School of Computing and Engineering Science (SCES)' => 'SCES',
            'School of Computing and Engineering Science' => 'SCES',
            'Strathmore Institute of Mathematical Sciences (SIMS)' => 'SIMS',
            'Strathmore Institute of Mathematical Sciences' => 'SIMS',
            'Strathmore Law School (SLS)' => 'SLS',
            'Strathmore Law School' => 'SLS',
            'Strathmore Business School (SBS)' => 'SBS',
            'Strathmore Business School' => 'SBS',
            'School of Tourism and Hospitality (STH)' => 'STH',
            'School of Tourism and Hospitality' => 'STH',
            'School of Humanities and Social Sciences (SHSS)' => 'SHSS',
            'School of Humanities and Social Sciences' => 'SHSS',
            'SI' => 'SIMS',
        ];

        foreach ($facultyMap as $from => $to) {
            DB::table('users')->where('faculty', $from)->update(['faculty' => $to]);
        }

        $courseMap = [
            'Bachelor of Informatics and Computer Science (BICS)' => 'BICS',
            'Bachelor of Informatics and Computer Science' => 'BICS',
            'Bachelor of Cyber Networks and Security (BCNS)' => 'BCNS',
            'Bachelor of Cyber Networks and Security' => 'BCNS',
            'Bachelor of Business Information and Technology (BBIT)' => 'BBIT',
            'Bachelor of Business Information and Technology' => 'BBIT',
            'Bachelor of Science in Electrical and Electronics Engineering (BSEEE)' => 'BSEEE',
            'Bachelor of Science in Electrical and Electronics Engineering' => 'BSEEE',
            'Bachelor of Business Science in Financial Engineering (BBS.FENG)' => 'BBS.FENG',
            'Bachelor of Business Science in Financial Engineering' => 'BBS.FENG',
            'Bachelor of Business Science in Financial Economics (BBS.FE)' => 'BBS.FE',
            'Bachelor of Business Science in Financial Economics' => 'BBS.FE',
            'Bachelor of Business Science in Acturial Science (BBS.ACT)' => 'BBS.ACT',
            'Bachelor of Business Science in Acturial Science' => 'BBS.ACT',
            'Bachelor of Business Science in Actuarial Science' => 'BBS.ACT',
            'Bachelor of Science in Statistics and Data Science (BSc.SDS)' => 'BSc.SDS',
            'Bachelor of Science in Statistics and Data Science' => 'BSc.SDS',
            'Bachelor of Laws (LLB)' => 'LLB',
            'Bachelor of Laws' => 'LLB',
            'Bachelor of Financial Services (BFS)' => 'BFS',
            'Bachelor of Financial Services' => 'BFS',
            'Bachelor of Supply Chain and Operations Management (BSCM)' => 'BSCM',
            'Bachelor of Supply Chain and Operations Management' => 'BSCM',
            'Bachelor of Commerce (BCOM)' => 'BCOM',
            'Bachelor of Commerce' => 'BCOM',
            'Bachelor of Science in Tourism Management (BTM)' => 'BTM',
            'Bachelor of Science in Tourism Management' => 'BTM',
            'Bachelor of Science in Hospitality Management (BHM)' => 'BHM',
            'Bachelor of Science in Hospitality Management' => 'BHM',
            'Bachelor of Development and Philosophy (BDP)' => 'BDP',
            'Bachelor of Development and Philosophy' => 'BDP',
            'Bachelor of Arts in Communication (BAC)' => 'BAC',
            'Bachelor of Arts in Communication' => 'BAC',
            'Bachelor of International Studies (BIS)' => 'BIS',
            'Bachelor of International Studies' => 'BIS',
            // Legacy values migrated to current abbreviations where mappings are clear.
            'BSc Computer Science' => 'BICS',
            'BSc Data Science' => 'BSc.SDS',
            'BSc Information Technology' => 'BBIT',
        ];

        foreach ($courseMap as $from => $to) {
            DB::table('users')->where('course', $from)->update(['course' => $to]);
        }

        $allowedFaculties = ['SCES', 'SIMS', 'SLS', 'SBS', 'STH', 'SHSS'];
        $coursesByFaculty = [
            'SCES' => ['BICS', 'BCNS', 'BBIT', 'BSEEE'],
            'SIMS' => ['BBS.FENG', 'BBS.FE', 'BBS.ACT', 'BSc.SDS'],
            'SLS' => ['LLB'],
            'SBS' => ['BFS', 'BSCM', 'BCOM'],
            'STH' => ['BTM', 'BHM'],
            'SHSS' => ['BDP', 'BAC', 'BIS'],
        ];

        DB::table('users')
            ->whereNotNull('faculty')
            ->whereNotIn('faculty', $allowedFaculties)
            ->update(['faculty' => null, 'course' => null]);

        foreach ($coursesByFaculty as $faculty => $allowedCourses) {
            DB::table('users')
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
        // Data normalization is not safely reversible.
    }
};
