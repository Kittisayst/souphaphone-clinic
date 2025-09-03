<?php

namespace Database\Factories;

use App\Models\Queue;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VitalSign>
 */
class VitalSignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = VitalSign::class;
    public function definition(): array
    {
        return [
            'queue_id' => Queue::factory(),
            'temperature' => $this->faker->randomFloat(1, 35.5, 39.5), // ອຸນຫະພູມ 35.5-39.5°C
            'weight' => $this->faker->randomFloat(2, 40, 120), // ນ້ຳໜັກ 40-120 kg
            'height' => $this->faker->randomFloat(2, 140, 190), // ຄວາມສູງ 140-190 cm
            'blood_pressure_sys' => $this->faker->numberBetween(90, 180), // Systolic 90-180
            'blood_pressure_dia' => $this->faker->numberBetween(60, 120), // Diastolic 60-120
            'heart_rate' => $this->faker->numberBetween(60, 120), // ການເຕັ້ນຫົວໃຈ 60-120
            'recorded_by_id' => User::factory()->nurse(),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    // State: ຄ່າປົກກະຕິ
    public function normal(): static
    {
        return $this->state(fn(array $attributes) => [
            'temperature' => $this->faker->randomFloat(1, 36.0, 37.5),
            'blood_pressure_sys' => $this->faker->numberBetween(110, 130),
            'blood_pressure_dia' => $this->faker->numberBetween(70, 85),
            'heart_rate' => $this->faker->numberBetween(70, 90),
        ]);
    }

    // State: ມີໄຂ້
    public function fever(): static
    {
        return $this->state(fn(array $attributes) => [
            'temperature' => $this->faker->randomFloat(1, 37.6, 39.5),
            'notes' => 'ຄົນໄຂ້ມີໄຂ້',
        ]);
    }

    // State: ຄວາມດັນສູງ
    public function highBloodPressure(): static
    {
        return $this->state(fn(array $attributes) => [
            'blood_pressure_sys' => $this->faker->numberBetween(140, 180),
            'blood_pressure_dia' => $this->faker->numberBetween(90, 120),
            'notes' => 'ຄວາມດັນເລືອດສູງ',
        ]);
    }
}
