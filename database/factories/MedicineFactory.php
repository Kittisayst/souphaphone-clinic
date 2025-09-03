<?php

namespace Database\Factories;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medicine>
 */
class MedicineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Medicine::class;
    public function definition(): array
    {
        $medicineTypes = [
            'ແກ້ໄຂ້ແກ້ປວດ' => [
                'names' => ['Paracetamol', 'Ibuprofen', 'Aspirin'],
                'strengths' => ['500mg', '400mg', '100mg'],
                'units' => ['tablet', 'capsule'],
                'prices' => [1000, 1500, 2000]
            ],
            'ຊີວະພາບ' => [
                'names' => ['Amoxicillin', 'Ciprofloxacin', 'Azithromycin'],
                'strengths' => ['500mg', '250mg', '750mg'],
                'units' => ['capsule', 'tablet'],
                'prices' => [3000, 4000, 5000]
            ],
            'ແກ້ອັກເສບ' => [
                'names' => ['Diclofenac', 'Prednisolone', 'Hydrocortisone'],
                'strengths' => ['50mg', '5mg', '10mg'],
                'units' => ['tablet', 'cream', 'ointment'],
                'prices' => [2000, 3000, 2500]
            ]
        ];

        $type = $this->faker->randomElement(array_keys($medicineTypes));
        $medicines = $medicineTypes[$type];

        return [
            'medicine_code' => 'MED' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'medicine_name' => $this->faker->randomElement($medicines['names']),
            'generic_name' => $this->faker->optional(0.8)->word,
            'medicine_type' => $type,
            'unit' => $this->faker->randomElement($medicines['units']),
            'strength' => $this->faker->randomElement($medicines['strengths']),
            'manufacturer' => $this->faker->randomElement([
                'ບໍລິສັດຢາລາວ',
                'ຢາໄທ',
                'Pharma International',
                'ຢາອິນເດຍ',
                'ຢາຫວຽດນາມ'
            ]),
            'stock_quantity' => $this->faker->numberBetween(10, 500),
            'min_stock_level' => $this->faker->numberBetween(5, 20),
            'unit_price' => $this->faker->randomElement($medicines['prices']),
            'expiry_date' => $this->faker->dateTimeBetween('+6 months', '+3 years'),
            'storage_condition' => $this->faker->randomElement([
                'ເກັບໃນອຸນຫະພູມຫ້ອງ',
                'ເກັບໃນຕູ້ເຢັນ 2-8°C',
                'ຫ່າງຈາກແສງແດດ',
                'ເກັບໃນທີ່ແຫ້ງ'
            ]),
        ];
    }

    // State: ຢາທີ່ໃກ້ໝົດອາຍຸ
    public function nearExpiry(): static
    {
        return $this->state(fn(array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('now', '+2 months'),
        ]);
    }

    // State: ຢາທີ່ສະຕ໋ອກຕ່ຳ
    public function lowStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'stock_quantity' => $this->faker->numberBetween(1, 5),
        ]);
    }

    // State: ຢາໝົດສະຕ໋ອກ
    public function outOfStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }
}
