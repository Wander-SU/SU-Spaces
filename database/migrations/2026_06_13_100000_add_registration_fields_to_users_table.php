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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('gender', 10)->nullable()->after('last_name');
            $table->string('account_type', 20)->nullable()->after('gender');
            $table->string('admission_number', 50)->nullable()->unique()->after('account_type');
            $table->string('employee_id', 50)->nullable()->unique()->after('admission_number');
            $table->string('faculty', 20)->nullable()->after('employee_id');
            $table->string('year_of_study', 10)->nullable()->after('faculty');
            $table->string('office_location', 150)->nullable()->after('year_of_study');
            $table->string('username', 100)->nullable()->unique()->after('office_location');
            $table->string('course', 120)->nullable()->after('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['admission_number']);
            $table->dropUnique(['employee_id']);
            $table->dropUnique(['username']);

            $table->dropColumn([
                'first_name',
                'last_name',
                'gender',
                'account_type',
                'admission_number',
                'employee_id',
                'faculty',
                'year_of_study',
                'office_location',
                'username',
                'course',
            ]);
        });
    }
};
