<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'start_time' => fake()->dateTime(),
            'end_time' => fake()->dateTime(),
            'status' => fake()->randomElement(["Booked","Passed","Voided"]),
            'attendee_count' => rand(1,5),
            'purpose' => fake()->sentence(),
            'room_id'=> rand(1,77),
            'user_id'=> rand(1,60), // By default student
        ];
    }
}
