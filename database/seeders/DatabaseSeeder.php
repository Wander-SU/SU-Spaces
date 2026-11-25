<?php

namespace Database\Seeders;

use App\Http\Controllers\BaseBookingController;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable all foreign key constraints first before seeding
        Schema::disableForeignKeyConstraints();

        $this->call([
            RoleSeeder::class,
            PhaseSeeder::class,
            BuildingSeeder::class,
            RoomSeeder::class,
            RoomDetailsSeeder::class,
            UserSeeder::class,
            BookingSeeder::class,
            TimeSlotSeeder::class
        ]);
        
        User::factory(60)->create();
        User::factory(10)->unverified()->create();
        User::factory(20)->lecturer()->create();
        User::factory(6)->itSupport()->create();
        User::factory(4)->admin()->create();
        Booking::factory(100)->create();

        // Call the base booking controller to seed the base timetable data immediately
        $base_booking_controller =  app()->make(BaseBookingController::class);
        $base_booking_controller->updateFull();

        // Enable the foreign key constraints at the end
        Schema::enableForeignKeyConstraints();
    }
}