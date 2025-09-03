<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prescription>
 */
class PrescriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Prescription::class;
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(5, 30);
        $unitPrice = $this->faker->numberBetween(1000, 5000);
        $totalPrice = $quantity * $unitPrice;

        $dosages = ['1 ແຜັດ', '2 ແຜັດ', '1/2 ແຜັດ', '5ml', '10ml'];
        $frequencies = [
            'ວັນລະ 1 ເທື່ອ',
            'ວັນລະ 2 ເທື່ອ',
            'ວັນລະ 3 ເທື່ອ',
            'ກິນຕອນເຊົ້າ-ແລງ',
            'ກິນເວລາເຈັບ',
            'ກິນກ່ອນນອນ'
        ];
        $durations = ['3 ວັນ', '5 ວັນ', '7 ວັນ', '10 ວັນ', '2 ສັບປະດາ', '1 ເດືອນ'];
        $instructions = [
            'ກິນຫຼັງອາຫານ',
            'ກິນກ່ອນອາຫານ',
            'ກິນພ້ອມນ້ຳ',
            'ບໍ່ໃຫ້ດື່ມເຫຼົ້າ',
            'ຫຼີກເວັ້ນແສງແດດ',
            'ດື່ມນ້ຳຫຼາຍໆ'
        ];

        $status = $this->faker->randomElement(['Prescribed', 'Dispensed']);
        $dispensedTime = $status === 'Dispensed' ? $this->faker->dateTimeBetween('now', '+1 hour') : null;
        $dispensedQuantity = $status === 'Dispensed' ? $quantity : null;

        return [
            'queue_id' => Queue::factory(),
            'medicine_id' => Medicine::factory(),
            'dosage' => $this->faker->randomElement($dosages),
            'frequency' => $this->faker->randomElement($frequencies),
            'duration' => $this->faker->randomElement($durations),
            'quantity' => $quantity,
            'instructions' => $this->faker->randomElement($instructions),
            'prescribed_by_id' => User::factory()->doctor(),
            'dispensed_by_id' => $dispensedTime ? User::factory() : null,
            'dispensed_quantity' => $dispensedQuantity,
            'dispensed_at' => $dispensedTime,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'status' => $status,
        ];
    }

    // State: ຍັງບໍ່ຈ່າຍຢາ
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'Prescribed',
            'dispensed_by_id' => null,
            'dispensed_quantity' => null,
            'dispensed_at' => null,
        ]);
    }

    // State: ຈ່າຍແລ້ວ
    public function dispensed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'Dispensed',
            'dispensed_at' => now(),
            'dispensed_quantity' => $attributes['quantity'] ?? $this->faker->numberBetween(5, 30),
        ]);
    }

    // State: ຢາສຳລັບເດັກ
    public function forChild(): static
    {
        return $this->state(fn(array $attributes) => [
            'dosage' => $this->faker->randomElement(['1/2 ແຜັດ', '1/4 ແຜັດ', '2.5ml', '5ml']),
            'frequency' => 'ວັນລະ 2 ເທື່ອ',
            'instructions' => 'ກິນຫຼັງອາຫານ ແລະ ດື່ມນ້ຳຫຼາຍໆ',
        ]);
    }
}
