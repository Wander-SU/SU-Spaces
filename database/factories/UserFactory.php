<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    private const COURSES_BY_FACULTY = [
        'SCES' => ['BICS', 'BCNS', 'BBIT', 'BSEEE'],
        'SIMS' => ['BBS.FENG', 'BBS.FE', 'BBS.ACT', 'BSc.SDS'],
        'SLS' => ['LLB'],
        'SBS' => ['BFS', 'BSCM', 'BCOM'],
        'STH' => ['BTM', 'BHM'],
        'SHSS' => ['BDP', 'BAC', 'BIS'],
    ];

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $admissionNumber = str_pad((string) fake()->numberBetween(0, 999999), 6, '0', STR_PAD_LEFT);
        $faculty = fake()->randomElement(array_keys(self::COURSES_BY_FACULTY));

        return [
            'name' => $firstName.' '.$lastName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => fake()->randomElement(['Male', 'Female']),
            'account_type' => 'student',
            'admission_number' => $admissionNumber,
            'employee_id' => null,
            'faculty' => $faculty,
            'year_of_study' => (string) fake()->numberBetween(1, 5),
            'office_location' => null,
            'username' => $admissionNumber,
            'course' => fake()->randomElement(self::COURSES_BY_FACULTY[$faculty]),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'active' => 1,
            'role_id' => 2, // By default student
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'active'=>0,
        ]);
    }

    public function admin(): static
    {
        return $this->state(function (array $attributes): array {
            $employeeId = str_pad((string) fake()->numberBetween(0, 999999), fake()->randomElement([5, 6]), '0', STR_PAD_LEFT);

            return [
                'role_id' => 1,
                'account_type' => null,
                'admission_number' => null,
                'employee_id' => $employeeId,
                'faculty' => null,
                'year_of_study' => null,
                'course' => null,
                'office_location' => 'Admin Block',
                'username' => $employeeId,
            ];
        });
    }

    public function lecturer(): static
    {
        return $this->state(function (array $attributes): array {
            $employeeId = str_pad((string) fake()->numberBetween(0, 999999), fake()->randomElement([5, 6]), '0', STR_PAD_LEFT);
            $faculty = fake()->randomElement(array_keys(self::COURSES_BY_FACULTY));

            return [
                'role_id' => 3,
                'account_type' => 'lecturer',
                'admission_number' => null,
                'employee_id' => $employeeId,
                'faculty' => $faculty,
                'year_of_study' => null,
                'course' => null,
                'office_location' => fake()->randomElement(['MST Block', 'Madaraka Wing', 'SBS Block']),
                'username' => $employeeId,
            ];
        });
    }

    public function itSupport(): static
    {
        return $this->state(function (array $attributes): array {
            $employeeId = str_pad((string) fake()->numberBetween(0, 999999), fake()->randomElement([5, 6]), '0', STR_PAD_LEFT);

            return [
                'role_id' => 4,
                'account_type' => null,
                'admission_number' => null,
                'employee_id' => $employeeId,
                'faculty' => null,
                'year_of_study' => null,
                'course' => null,
                'office_location' => 'ICT Helpdesk',
                'username' => $employeeId,
            ];
        });
    }
}
