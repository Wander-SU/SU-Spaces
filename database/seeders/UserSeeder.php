<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear everything from the user table before you start
        User::truncate();
        // Known development accounts (change these passwords before sharing the project)
        User::create([
            'name' => 'SU Spaces Admin',
            'first_name' => 'SU Spaces',
            'last_name' => 'Admin',
            'email' => 'admin@su-spaces.test',
            'password' => 'password',        // hashed automatically by the model
            'active' => 1,
            'role_id' => 1,                  // System Admin
            'employee_id' => '100001',
            'username' => '100001',
            'office_location' => 'Admin Block',
        ]);

        User::create([
            'name' => 'Test Student',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@su-spaces.test',
            'password' => 'password',
            'active' => 1,
            'role_id' => 2,                  // Student
            'admission_number' => '000001',
            'username' => '000001',
        ]);
    }
}
