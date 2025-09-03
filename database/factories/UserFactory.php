<?php

namespace Database\Factories;

use App\Models\User;
use Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = User::class;
    public function definition(): array
    {
        $userType = $this->faker->randomElement(['Doctor', 'Nurse', 'Admin', 'Receptionist']);

        return [
            'user_code' => 'U' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'username' => $this->faker->unique()->userName,
            'password' => Hash::make('password123'), // ລະຫັດຜ່ານເບື້ອງຕົ້ນ
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'user_type' => $userType,
            'specialization' => $userType === 'Doctor' ? $this->faker->randomElement([
                'ແພດທົ່ວໄປ',
                'ແພດເດັກ',
                'ແພດສູດຕິກ',
                'ແພດໂລກພາຍໃນ',
                'ແພດກະດູກ'
            ]) : null,
            'license_number' => $userType === 'Doctor' ? 'LIC' . $this->faker->unique()->numerify('######') : null,
            'phone_number' => '020' . $this->faker->numerify('########'),
            'email' => $this->faker->unique()->safeEmail,
            'is_active' => $this->faker->boolean(90), // 90% ເປັນ active
            'work_schedule' => $userType === 'Doctor' ? [
                'monday' => ['start' => '08:00', 'end' => '17:00'],
                'tuesday' => ['start' => '08:00', 'end' => '17:00'],
                'wednesday' => ['start' => '08:00', 'end' => '17:00'],
                'thursday' => ['start' => '08:00', 'end' => '17:00'],
                'friday' => ['start' => '08:00', 'end' => '17:00'],
                'saturday' => ['start' => '08:00', 'end' => '12:00'],
            ] : null,
            'last_login' => $this->faker->optional(0.7)->dateTimeBetween('-1 month'),
        ];
    }

    // State: ທ່ານໝໍ
    public function doctor(): static
    {
        return $this->state(fn(array $attributes) => [
            'user_type' => 'Doctor',
            'specialization' => $this->faker->randomElement([
                'ແພດທົ່ວໄປ',
                'ແພດເດັກ',
                'ແພດສູດຕິກ',
                'ແພດໂລກພາຍໃນ',
                'ແພດກະດູກ'
            ]),
            'license_number' => 'LIC' . $this->faker->unique()->numerify('######'),
            'work_schedule' => [
                'monday' => ['start' => '08:00', 'end' => '17:00'],
                'tuesday' => ['start' => '08:00', 'end' => '17:00'],
                'wednesday' => ['start' => '08:00', 'end' => '17:00'],
                'thursday' => ['start' => '08:00', 'end' => '17:00'],
                'friday' => ['start' => '08:00', 'end' => '17:00'],
                'saturday' => ['start' => '08:00', 'end' => '12:00'],
            ]
        ]);
    }

    // State: ພະຍາບານ
    public function nurse(): static
    {
        return $this->state(fn(array $attributes) => [
            'user_type' => 'Nurse',
            'specialization' => null,
            'license_number' => null,
        ]);
    }

    // State: ແອັດມິນ
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'user_type' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@clinic.com',
            'specialization' => null,
            'license_number' => null,
        ]);
    }
}
