<?php
// database/seeders/QueueServiceSeeder.php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\QueueService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class QueueServiceSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('🏥 ສ້າງຂໍ້ມູນ Queue Services...');

        $queues = Queue::all();
        $services = Service::all();
        $doctor = User::where('role', 'doctor')->first();
        $technician = User::where('role', 'technician')->first();

        $queueServices = [];

        foreach ($queues as $queue) {
            // ທຸກຄິວມີການປຶກສາ
            $consultationService = $services->where('service_category', 'Consultation')->first();
            $queueServices[] = [
                'queue_id' => $queue->id,
                'service_id' => $consultationService->id,
                'added_by_id' => $queue->created_by,
                'assigned_to_id' => $queue->doctor_id,
                'service_status' => $this->getServiceStatus($queue->queue_status, 'consultation'),
                'assigned_room_id' => $consultationService->room_id,
                'started_at' => $this->getStartedAt($queue),
                'completed_at' => $this->getCompletedAt($queue, 'consultation'),
                'notes' => 'ການປຶກສາທ່ານໝໍ',
                'service_price' => $consultationService->base_price,
                'created_at' => $queue->created_at,
                'updated_at' => $queue->updated_at,
            ];

            // ບາງຄິວມີການກວດ Lab
            if (in_array($queue->id, [1, 2])) {
                $labService = $services->where('service_category', 'Laboratory')->first();
                $queueServices[] = [
                    'queue_id' => $queue->id,
                    'service_id' => $labService->id,
                    'added_by_id' => $queue->doctor_id,
                    'assigned_to_id' => $technician->id,
                    'service_status' => $this->getServiceStatus($queue->queue_status, 'lab'),
                    'assigned_room_id' => $labService->room_id,
                    'started_at' => $this->getStartedAt($queue, 30),
                    'completed_at' => $this->getCompletedAt($queue, 'lab'),
                    'notes' => 'ກວດເລືອດທົ່ວໄປ - CBC',
                    'service_details' => [
                        'lab_tests' => ['CBC', 'ເລືອດແດງ', 'ເລືອດຂາວ'],
                        'sample_type' => 'ເລືອດ',
                        'doctor_instructions' => 'ກວດເປົ່າທ້ອງ'
                    ],
                    'service_price' => $labService->base_price,
                    'created_at' => $queue->created_at->addMinutes(45),
                    'updated_at' => $queue->updated_at,
                ];
            }

            // ຄິວທີ 4 ມີການກວດ X-Ray
            if ($queue->id == 4) {
                $xrayService = $services->where('service_category', 'X_Ray')->first();
                $queueServices[] = [
                    'queue_id' => $queue->id,
                    'service_id' => $xrayService->id,
                    'added_by_id' => $queue->doctor_id ?? $doctor->id,
                    'assigned_to_id' => $technician->id,
                    'service_status' => 'Added',
                    'assigned_room_id' => $xrayService->room_id,
                    'notes' => 'ຖ່າຍ X-Ray ໜ້າເອິກ',
                    'service_price' => $xrayService->base_price,
                    'created_at' => $queue->created_at->addMinutes(30),
                    'updated_at' => $queue->updated_at,
                ];
            }
        }

        foreach ($queueServices as $qsData) {
            QueueService::create($qsData);
        }

        $this->command->info("✅ ສ້າງ Queue Services: " . count($queueServices) . " ລາຍການ");
    }

    private function getServiceStatus(string $queueStatus, string $serviceType): string
    {
        if ($serviceType === 'consultation') {
            return match ($queueStatus) {
                'Registered', 'Vital_Checked' => 'Added',
                'With_Doctor' => 'In_Progress',
                default => 'Completed'
            };
        }

        if ($serviceType === 'lab') {
            return match ($queueStatus) {
                'Waiting_Test_Results' => 'In_Progress',
                'Results_Ready', 'Ready_For_Payment', 'Completed' => 'Completed',
                default => 'Added'
            };
        }

        return 'Added';
    }

    private function getStartedAt(Queue $queue, int $addMinutes = 0): ?\Carbon\Carbon
    {
        if ($queue->doctor_start_at) {
            return $queue->doctor_start_at->addMinutes($addMinutes);
        }
        return null;
    }

    private function getCompletedAt(Queue $queue, string $serviceType): ?\Carbon\Carbon
    {
        if ($serviceType === 'consultation' && in_array($queue->queue_status, ['Waiting_Test_Results', 'Results_Ready', 'Ready_For_Payment', 'Completed'])) {
            return $queue->doctor_start_at?->addMinutes(30);
        }

        if ($serviceType === 'lab' && in_array($queue->queue_status, ['Results_Ready', 'Ready_For_Payment', 'Completed'])) {
            return $queue->tests_completed_at;
        }

        return null;
    }
}