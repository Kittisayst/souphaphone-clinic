<?php

namespace Database\Factories;

use App\Models\Lab;
use App\Models\QueueService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lab>
 */
class LabFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Lab::class;
    public function definition(): array
    {
        $status = $this->faker->randomElement(['Pending', 'In_Progress', 'Completed', 'Doctor_Reviewed', 'Patient_Notified']);
        
        // ຜົນກວດຕົວຢ່າງ
        $bloodTestResults = [
            'hemoglobin' => $this->faker->randomFloat(1, 10, 16),
            'white_blood_cells' => $this->faker->numberBetween(4000, 12000),
            'red_blood_cells' => $this->faker->randomFloat(2, 3.5, 5.5),
            'glucose' => $this->faker->randomFloat(1, 80, 150),
        ];

        $performedTime = $status !== 'Pending' ? $this->faker->dateTimeBetween('-3 days', 'now') : null;
        $reviewedTime = in_array($status, ['Doctor_Reviewed', 'Patient_Notified']) 
                       ? $this->faker->dateTimeBetween($performedTime ?? '-1 day', 'now') : null;
        $notifiedTime = $status === 'Patient_Notified' 
                       ? $this->faker->dateTimeBetween($reviewedTime ?? '-1 day', 'now') : null;

        return [
            'queue_service_id' => QueueService::factory(),
            'lab_code' => 'LAB' . str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'test_results' => $bloodTestResults,
            'result_summary' => $this->faker->paragraph(2),
            'reference_values' => 'Hemoglobin: 12-15 g/dL, WBC: 4000-11000 cells/μL',
            'interpretation' => $this->faker->randomElement([
                'ຼົນການກວດປົກກະຕິ', 'ມີຄ່າບາງອັນສູງເລັກນ້ອຍ', 'ຄວນຕິດຕາມຕໍ່ໄປ'
            ]),
            'images_attachments' => $this->faker->optional(0.3)->randomElement([
                ['xray_001.jpg', 'xray_002.jpg'],
                ['ultrasound_001.png'],
                []
            ]),
            'performed_by_id' => $performedTime ? User::factory() : null,
            'performed_at' => $performedTime,
            'reviewed_by_doctor_id' => $reviewedTime ? User::factory()->doctor() : null,
            'reviewed_at' => $reviewedTime,
            'patient_notified' => $status === 'Patient_Notified',
            'notified_at' => $notifiedTime,
            'lab_status' => $status,
        ];
    }

    // State: ລໍຖ້າທ່ານໝໍເບິ່ງ
    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'lab_status' => 'Completed',
            'performed_at' => now()->subHours($this->faker->numberBetween(1, 24)),
            'reviewed_by_doctor_id' => null,
            'reviewed_at' => null,
            'patient_notified' => false,
            'notified_at' => null,
        ]);
    }

    // State: ຜົນກວດເລືອດ
    public function bloodTest(): static
    {
        return $this->state(fn (array $attributes) => [
            'test_results' => [
                'hemoglobin' => $this->faker->randomFloat(1, 10, 16),
                'hematocrit' => $this->faker->randomFloat(1, 30, 50),
                'white_blood_cells' => $this->faker->numberBetween(4000, 12000),
                'platelets' => $this->faker->numberBetween(150000, 450000),
                'glucose' => $this->faker->randomFloat(1, 70, 140),
                'cholesterol' => $this->faker->numberBetween(150, 250),
            ],
            'reference_values' => 'Hemoglobin: 12-15 g/dL, Hematocrit: 36-46%, WBC: 4000-11000, Platelets: 150000-400000, Glucose: 70-110 mg/dL',
        ]);
    }

    // State: ຜົນ X-Ray
    public function xrayResult(): static
    {
        return $this->state(fn (array $attributes) => [
            'test_results' => [
                'examination' => 'Chest X-Ray',
                'findings' => $this->faker->randomElement([
                    'ປອດສະອາດ ບໍ່ມີອາການຜິດປົກກະຕິ',
                    'ມີເງົາເລັກນ້ອຍທາງຂວາ', 
                    'ຫົວໃຈຂະໜາດປົກກະຕິ'
                ]),
            ],
            'images_attachments' => ['xray_' . $this->faker->uuid() . '.jpg'],
        ]);
    }
}
