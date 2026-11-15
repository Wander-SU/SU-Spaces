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
            'name' => 'Maria Muwale',
            'first_name' => 'Maria',
            'last_name' => 'Muwale',
            'email' => 'maria.muwale@strathmore.edu',
            'password' => 'qwertyuiop',        // hashed automatically by the model
            'active' => 1,
            'role_id' => 1,                  // System Admin
            'employee_id' => '192414',
            'username' => '192414',
            'office_location' => 'Admin Block',
        ]);

        User::create([
            'name' => 'Maria Muwale',
            'first_name' => 'Maria',
            'last_name' => 'Muwale',
            'email' => 'mmuwale12@gmail.com',
            'password' => 'qwertyuiop',
            'active' => 1,
            'role_id' => 2,                  // Student
            'admission_number' => '123456',
            'username' => '123456',
        ]);
    }
}
