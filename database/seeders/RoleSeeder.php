<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::truncate(); // Removes all the data on roles before seeding
        
        // Admin
        $admin = new Role();
        $admin->role_name = 'System Admin';
        $admin->role_abbrev = 'SysAdmin';
        $admin->save();

        // Student
        $student = new Role();
        $student->role_name = 'Student';
        $student->role_abbrev = 'Student';
        $student->save();

        // Lecturer
        $lecturer = new Role();
        $lecturer->role_name = 'Lecturer';
        $lecturer->role_abbrev = 'Lecturer';
        $lecturer->save();

        // IT Support
        $managers =  new Role();
        $managers->role_name = 'IT Support';
        $managers->role_abbrev = 'ITSupport';
        $managers->save();
    }
}
