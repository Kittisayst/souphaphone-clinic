<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Queue;
use App\Models\QueueService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QueueService>
 */
class QueueServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = QueueService::class;
    public function definition(): array
    {
        $status = $this->faker->randomElement(['Added', 'Scheduled', 'In_Progress', 'Completed']);

        // ກຳນົດເວລາຕາມສະຖານະ
        $addedTime = $this->faker->dateTimeBetween('-1 day', 'now');
        $scheduledTime = in_array($status, ['Scheduled', 'In_Progress', 'Completed'])
            ? $this->faker->dateTimeBetween($addedTime, 'now') : null;
        $startedTime = in_array($status, ['In_Progress', 'Completed'])
            ? $this->faker->dateTimeBetween($scheduledTime ?? $addedTime, 'now') : null;
        $completedTime = $status === 'Completed'
            ? $this->faker->dateTimeBetween($startedTime ?? $addedTime, 'now') : null;

        return [
            'queue_id' => Queue::factory(),
            'service_id' => Service::factory(),
            'added_by_id' => User::factory()->state(['user_type' => $this->faker->randomElement(['Doctor', 'Receptionist'])]),
            'service_status' => $status,
            'priority_order' => $this->faker->numberBetween(1, 5),
            'assigned_to_id' => $scheduledTime ? User::factory() : null,
            'scheduled_at' => $scheduledTime,
            'started_at' => $startedTime,
            'completed_at' => $completedTime,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    // State: ບໍລິການທີ່ລໍຖ້າ
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'service_status' => 'Added',
            'scheduled_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'assigned_to_id' => null,
        ]);
    }

    // State: ກຳລັງເຮັດ
    public function inProgress(): static
    {
        return $this->state(fn(array $attributes) => [
            'service_status' => 'In_Progress',
            'started_at' => now()->subMinutes($this->faker->numberBetween(5, 60)),
            'completed_at' => null,
        ]);
    }
}
