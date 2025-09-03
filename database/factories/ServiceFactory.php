<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Service::class;
    public function definition(): array
    {
        $categories = ['Consultation', 'X_Ray', 'Ultrasound', 'Blood_Test', 'Urine_Test', 'ECG', 'Other'];
        $category = $this->faker->randomElement($categories);

        // ລາຄາຕາມປະເພດບໍລິການ
        $prices = [
            'Consultation' => $this->faker->randomElement([80000, 100000, 120000]),
            'X_Ray' => $this->faker->randomElement([150000, 180000, 200000]),
            'Ultrasound' => $this->faker->randomElement([200000, 250000, 300000]),
            'Blood_Test' => $this->faker->randomElement([50000, 80000, 120000]),
            'Urine_Test' => $this->faker->randomElement([30000, 40000, 50000]),
            'ECG' => $this->faker->randomElement([80000, 100000]),
            'Other' => $this->faker->randomElement([50000, 100000, 150000])
        ];

        // ຊື່ບໍລິການຕາມປະເພດ
        $serviceNames = [
            'Consultation' => ['ກວດທົ່ວໄປ', 'ປຶກສາທ່ານໝໍ', 'ຕິດຕາມອາການ'],
            'X_Ray' => ['ຖ່າຍ X-Ray ໜ້າອົກ', 'ຖ່າຍ X-Ray ກະດູກ', 'ຖ່າຍ X-Ray ທ້ອງ'],
            'Ultrasound' => ['Ultrasound ທ້ອງ', 'Ultrasound ຫົວໃຈ', 'Ultrasound ຄໍ'],
            'Blood_Test' => ['ກວດເລືອດທົ່ວໄປ', 'ກວດນ້ຳຕານ', 'ກວດໄຂມັນ'],
            'Urine_Test' => ['ກວດປັດສະວະທົ່ວໄປ', 'ກວດປັດສະວະພິເສດ'],
            'ECG' => ['ກວດຫົວໃຈ ECG'],
            'Other' => ['ບໍລິການພິເສດ', 'ການກວດອື່ນໆ']
        ];

        return [
            'service_code' => strtoupper(substr($category, 0, 3)) . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'service_name' => $this->faker->randomElement($serviceNames[$category]),
            'service_category' => $category,
            'base_price' => $prices[$category],
            'description' => $this->faker->optional(0.7)->sentence(10),
            'duration_minutes' => $this->faker->randomElement([15, 30, 45, 60]),
            'requires_room' => $this->faker->boolean(70), // 70% ຕ້ອງການຫ້ອງ
            'room_type_required' => $category !== 'Other' ? $category : null,
            'template_lab' => in_array($category, ['Blood_Test', 'Urine_Test']) ? [
                'fields' => [
                    'hemoglobin' => ['type' => 'number', 'unit' => 'g/dL', 'normal_range' => '12-15'],
                    'white_blood_cells' => ['type' => 'number', 'unit' => 'cells/μL', 'normal_range' => '4000-11000']
                ]
            ] : null,
            'has_lab_result' => in_array($category, ['X_Ray', 'Ultrasound', 'Blood_Test', 'Urine_Test', 'ECG']),
        ];
    }

    // State: ການກວດທົ່ວໄປ
    public function consultation(): static
    {
        return $this->state(fn(array $attributes) => [
            'service_category' => 'Consultation',
            'service_name' => 'ກວດທົ່ວໄປ',
            'requires_room' => true,
            'room_type_required' => 'Consultation',
            'has_lab_result' => false,
        ]);
    }

    // State: ການກວດມີຜົນ
    public function withLabResult(): static
    {
        return $this->state(fn(array $attributes) => [
            'has_lab_result' => true,
            'template_lab' => [
                'fields' => [
                    'result' => ['type' => 'text', 'required' => true],
                    'conclusion' => ['type' => 'text', 'required' => false]
                ]
            ]
        ]);
    }
}
