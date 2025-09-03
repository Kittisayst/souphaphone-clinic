<?php

namespace Database\Factories;

use App\Models\QueueService;
use App\Models\Room;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Treatment>
 */
class TreatmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Treatment::class;
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('-1 day', 'now');
        $endTime = $this->faker->optional(0.7)->dateTimeBetween($startTime, 'now');
        $status = $endTime ? 'Completed' : 'In_Progress';

        $findings = [
            'ກວດພົບອຸນຫະພູມສູງ', 'ຫາຍໃຈປົກກະຕິ', 'ຫົວໃຈເຕັ້ນປົກກະຕິ',
            'ບໍ່ມີອາການຜິດປົກກະຕິ', 'ມີອາການອັກເສບເລັກນ້ອຍ', 'ຄວນຕິດຕາມຕໍ່ໄປ'
        ];

        $recommendations = [
            'ດື່ມນ້ຳຫຼາຍໆ', 'ພັກຜ່ອນຢ່າງພຽງພໍ', 'ກິນຢາຕາມເວລາ',
            'ຫຼີກເວັ້ນອາຫານເຜັດ', 'ມາຕິດຕາມອີກ 1 ອາທິດ', 'ອອກກຳລັງກາຍເບົາໆ'
        ];

        return [
            'queue_service_id' => QueueService::factory(),
            'room_id' => Room::factory(),
            'performed_by_id' => User::factory()->doctor(),
            'treatment_started_at' => $startTime,
            'treatment_ended_at' => $endTime,
            'examination_notes' => $this->faker->paragraph(3),
            'findings' => $this->faker->randomElement($findings),
            'recommendations' => $this->faker->randomElement($recommendations),
            'status' => $status,
        ];
    }

    // State: ການປິ່ນປົວທີ່ກຳລັງດຳເນີນ
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'In_Progress',
            'treatment_started_at' => now()->subMinutes($this->faker->numberBetween(5, 120)),
            'treatment_ended_at' => null,
        ]);
    }

    // State: ການປິ່ນປົວທີ່ສຳເລັດ
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Completed',
            'treatment_ended_at' => now(),
        ]);
    }
}
