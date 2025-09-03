<?php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\QueueService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QueueServiceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creating queue services demo data...');

        // ດຶງຂໍ້ມູນທີ່ຈຳເປັນ
        $queues = Queue::limit(4)->get();
        $services = Service::limit(6)->get();
        $doctors = User::where('role', 'doctor')->get();
        $receptionist = User::where('role', 'cashier')->first();

        if ($queues->isEmpty() || $services->isEmpty() || $doctors->isEmpty() || !$receptionist) {
            $this->command->error('❌ ຕ້ອງມີຂໍ້ມູນ Queue, Service, Doctor ແລະ Receptionist ກ່ອນ!');
            return;
        }

        // ການເລືອກບໍລິການທົດລອງ 4 ແບບ
        $queueServicesData = [
            // ຄິວທີ 1: ກວດທົ່ວໄປ + ກວດເລືອດ (ສຳເລັດແລ້ວ)
            [
                'queue_index' => 0,
                'services' => [
                    [
                        'service_index' => 0, // ກວດທົ່ວໄປ
                        'priority_order' => 1,
                        'added_by' => 'receptionist',
                        'service_status' => 'Completed',
                        'scheduled_at' => now()->subHours(3),
                        'started_at' => now()->subHours(2),
                        'completed_at' => now()->subHours(1),
                        'notes' => 'ກວດທົ່ວໄປເບື້ອງຕົ້ນ'
                    ],
                    [
                        'service_index' => 1, // ກວດເລືອດ
                        'priority_order' => 2,
                        'added_by' => 'doctor',
                        'service_status' => 'Completed',
                        'scheduled_at' => now()->subHours(2),
                        'started_at' => now()->subHour(),
                        'completed_at' => now()->subMinutes(30),
                        'notes' => 'ທ່ານໝໍສັ່ງກວດເພີ່ມ'
                    ]
                ]
            ],

            // ຄິວທີ 2: ກວດທົ່ວໄປ + X-Ray (ກຳລັງລໍຖ້າຜົນ)
            [
                'queue_index' => 1,
                'services' => [
                    [
                        'service_index' => 0, // ກວດທົ່ວໄປ
                        'priority_order' => 1,
                        'added_by' => 'receptionist',
                        'service_status' => 'Completed',
                        'scheduled_at' => now()->subHours(2),
                        'started_at' => now()->subHour(),
                        'completed_at' => now()->subMinutes(45),
                        'notes' => 'ກວດເບື້ອງຕົ້ນສຳເລັດ'
                    ],
                    [
                        'service_index' => 2, // X-Ray
                        'priority_order' => 2,
                        'added_by' => 'doctor',
                        'service_status' => 'In_Progress',
                        'scheduled_at' => now()->subMinutes(30),
                        'started_at' => now()->subMinutes(15),
                        'completed_at' => null,
                        'notes' => 'ກຳລັງຖ່າຍ X-Ray ໜ້າເອິກ'
                    ]
                ]
            ],

            // ຄິວທີ 3: ແຕ່ກວດທົ່ວໄປ (ຍັງບໍ່ເລີ່ມ)
            [
                'queue_index' => 2,
                'services' => [
                    [
                        'service_index' => 0, // ກວດທົ່ວໄປ
                        'priority_order' => 1,
                        'added_by' => 'receptionist',
                        'service_status' => 'Added',
                        'scheduled_at' => null,
                        'started_at' => null,
                        'completed_at' => null,
                        'notes' => 'ລໍຖ້າເອີ້ນຄິວ'
                    ]
                ]
            ],

            // ຄິວທີ 4: ກວດຫຼາຍບໍລິການ (ທ່ານໝໍສັ່ງເພີ່ມ)
            [
                'queue_index' => 3,
                'services' => [
                    [
                        'service_index' => 0, // ກວດທົ່ວໄປ
                        'priority_order' => 1,
                        'added_by' => 'receptionist',
                        'service_status' => 'Completed',
                        'scheduled_at' => now()->subHours(4),
                        'started_at' => now()->subHours(3),
                        'completed_at' => now()->subHours(2),
                        'notes' => 'ກວດເບື້ອງຕົ້ນ'
                    ],
                    [
                        'service_index' => 1, // ກວດເລືອດ
                        'priority_order' => 2,
                        'added_by' => 'doctor',
                        'service_status' => 'Completed',
                        'scheduled_at' => now()->subHours(2),
                        'started_at' => now()->subHour(),
                        'completed_at' => now()->subMinutes(45),
                        'notes' => 'ທ່ານໝໍສັ່ງກວດເລືອດ'
                    ],
                    [
                        'service_index' => 3, // Ultrasound
                        'priority_order' => 3,
                        'added_by' => 'doctor',
                        'service_status' => 'Scheduled',
                        'scheduled_at' => now()->addMinutes(30),
                        'started_at' => null,
                        'completed_at' => null,
                        'notes' => 'ທ່ານໝໍສັ່ງເພີ່ມ Ultrasound'
                    ]
                ]
            ]
        ];

        foreach ($queueServicesData as $queueData) {
            if (!isset($queues[$queueData['queue_index']]))
                continue;

            $queue = $queues[$queueData['queue_index']];

            $this->command->info("📋 Creating services for Queue #{$queue->queue_number}");

            foreach ($queueData['services'] as $serviceData) {
                if (!isset($services[$serviceData['service_index']]))
                    continue;

                $service = $services[$serviceData['service_index']];

                // ກຳນົດຜູ້ເພີ່ມບໍລິການ
                $addedBy = $serviceData['added_by'] === 'doctor'
                    ? $doctors->random()->id
                    : $receptionist->id;

                // ກຳນົດຜູ້ຮັບມອບໝາຍ
                $assignedTo = in_array($serviceData['service_status'], ['Scheduled', 'In_Progress', 'Completed'])
                    ? $doctors->random()->id
                    : null;

                // ກວດສອບວ່າມີແລ້ວບໍ່ (ເພື່ອຫຼີກເວັ້ນ Duplicate)
                $existingQueueService = QueueService::where('queue_id', $queue->id)
                    ->where('service_id', $service->id)
                    ->first();

                if ($existingQueueService) {
                    $this->command->warn("   ⚠️ {$service->service_name} - ມີຢູ່ແລ້ວ (ຂ້າມໄປ)");
                    continue;
                }

                QueueService::create([
                    'queue_id' => $queue->id,
                    'service_id' => $service->id,
                    'added_by_id' => $addedBy,
                    'service_status' => $serviceData['service_status'],
                    'priority_order' => $serviceData['priority_order'],
                    'assigned_to_id' => $assignedTo,
                    'scheduled_at' => $serviceData['scheduled_at'],
                    'started_at' => $serviceData['started_at'],
                    'completed_at' => $serviceData['completed_at'],
                    'notes' => $serviceData['notes'],
                ]);

                $this->command->info("   ✅ {$service->service_name} - {$serviceData['service_status']}");
            }
        }

        // ສະຖິຕິການເລືອກບໍລິການ
        $this->displayQueueServiceStatistics();

        $this->command->info('✅ Created queue services for demo queues');
    }

    /**
     * ສະແດງສະຖິຕິການເລືອກບໍລິການ
     */
    private function displayQueueServiceStatistics(): void
    {
        $totalQueueServices = QueueService::count();
        $completedServices = QueueService::where('service_status', 'Completed')->count();
        $inProgressServices = QueueService::where('service_status', 'In_Progress')->count();
        $pendingServices = QueueService::where('service_status', 'Added')->count();
        $scheduledServices = QueueService::where('service_status', 'Scheduled')->count();

        // ບໍລິການທີ່ນິຍົມ
        $popularServices = QueueService::selectRaw('service_id, COUNT(*) as count')
            ->groupBy('service_id')
            ->with('service:id,service_name')
            ->orderByDesc('count')
            ->limit(3)
            ->get();

        $this->command->info('📈 Queue Services Statistics:');
        $this->command->info("   - ລວມການເລືອກບໍລິການ: {$totalQueueServices} ຄັ້ງ");
        $this->command->info("   - ສຳເລັດແລ້ວ: {$completedServices} ຄັ້ງ");
        $this->command->info("   - ກຳລັງເຮັດ: {$inProgressServices} ຄັ້ງ");
        $this->command->info("   - ນັດເວລາແລ້ວ: {$scheduledServices} ຄັ້ງ");
        $this->command->info("   - ລໍຖ້າເລີ່ມ: {$pendingServices} ຄັ້ງ");

        if ($popularServices->isNotEmpty()) {
            $this->command->info('🔥 ບໍລິການທີ່ນິຍົມ:');
            foreach ($popularServices as $service) {
                $serviceName = $service->service->service_name ?? 'N/A';
                $this->command->info("   - {$serviceName}: {$service->count} ຄັ້ງ");
            }
        }
    }
}
