<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Queue>
 */
class QueueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Queue::class;
    public function definition(): array
    {
        $complaints = [
            'ປວດຫົວ',
            'ໄຂ້',
            'ປວດທ້ອງ',
            'ໄອ',
            'ເຈັບຄໍ',
            'ປວດຫຼັງ',
            'ວິນຫົວ',
            'ຖອກທ້ອງ',
            'ແພ້',
            'ນອນບໍ່ຫຼັບ',
            'ເມື່ອຍລ້າ',
            'ເຈັບຂໍ້',
            'ອາເຈຍນ',
            'ໜ້າມືດ'
        ];

        $statuses = ['Registered', 'Vital_Checked', 'With_Doctor', 'Lab_Testing', 'Results_Ready', 'Completed'];
        $status = $this->faker->randomElement($statuses);

        // ກຳນົດເວລາຕາມສະຖານະ
        $baseDate = $this->faker->dateTimeBetween('-1 week', 'now');
        $vitalTime = $status !== 'Registered' ? $this->faker->dateTimeBetween($baseDate, 'now') : null;
        $doctorTime = in_array($status, ['With_Doctor', 'Lab_Testing', 'Results_Ready', 'Completed'])
            ? $this->faker->dateTimeBetween($vitalTime ?? $baseDate, 'now') : null;
        $completedTime = $status === 'Completed' ? $this->faker->dateTimeBetween($doctorTime ?? $baseDate, 'now') : null;

        return [
            'patient_id' => Patient::factory(),
            'queue_number' => $this->faker->numberBetween(1, 100),
            'queue_date' => $this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
            'initial_complaint' => $this->faker->randomElement($complaints),
            'assigned_doctor_id' => $doctorTime ? User::factory()->doctor() : null,
            'queue_status' => $status,
            'vital_checked_at' => $vitalTime,
            'doctor_start_at' => $doctorTime,
            'completed_at' => $completedTime,
            'priority_level' => $this->faker->randomElement(['Normal', 'Urgent', 'Emergency']),
            'created_by_id' => User::factory()->state(['user_type' => 'Receptionist']),
        ];
    }

    // State: ຄິວວັນນີ້
    public function today(): static
    {
        return $this->state(fn(array $attributes) => [
            'queue_date' => now()->format('Y-m-d'),
            'queue_number' => $this->faker->numberBetween(1, 50),
        ]);
    }

    // State: ຄິວທີ່ລໍຖ້າ
    public function waiting(): static
    {
        return $this->state(fn(array $attributes) => [
            'queue_status' => 'Registered',
            'vital_checked_at' => null,
            'doctor_start_at' => null,
            'completed_at' => null,
            'assigned_doctor_id' => null,
        ]);
    }

    // State: ຄິວທີ່ສຳເລັດ
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'queue_status' => 'Completed',
            'completed_at' => now(),
        ]);
    }

    // State: ຄິວສຸກເສີນ
    public function emergency(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority_level' => 'Emergency',
            'initial_complaint' => $this->faker->randomElement([
                'ເຈັບໜ້າເອິກ',
                'ຫາຍໃຈຫຍຸ້ງຍາກ',
                'ບາດເຈັບຮ້າຍແຮງ',
                'ໄຂ້ສູງຫຼາຍ'
            ]),
        ]);
    }
}
