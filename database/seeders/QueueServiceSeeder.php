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
        $this->command->info('🔄 ກຳລັງສ້າງບໍລິການສຳລັບຄິວ...');

        // ດຶງຂໍ້ມູນທີ່ຈຳເປັນ
        $queues = Queue::limit(2)->get();
        $consultationService = Service::where('service_category', 'Consultation')->first();
        $labService = Service::where('service_category', 'Lab_Test')->first();
        $doctor = User::where('role', 'doctor')->first();
        $nurse = User::where('role', 'nurse')->first();

        if ($queues->isEmpty() || !$consultationService) {
            $this->command->warn('⚠️  ຕ້ອງມີຂໍ້ມູນ Queue ແລະ Service ກ່ອນ');
            return;
        }

        // ບໍລິການກວດທ່ານໝໍສຳລັບຄິວທີ 1 - ສຳເລັດແລ້ວ
        $this->command->info('📋 ສ້າງບໍລິການກວດທ່ານໝໍສຳລັບຄິວທີ 1...');
        QueueService::create([
            'queue_id' => $queues[0]->id,
            'service_id' => $consultationService->id,
            'assigned_to_id' => $doctor->id,
            'service_status' => 'Completed',
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHour(),
            'actual_duration' => 25, // 25 ນາທີ
            'added_by_id' => $nurse?->id ?? $doctor->id,
            'notes' => 'ກວດປົກກະຕິ, ບໍ່ມີບັນຫາ',
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHour(),
        ]);

        // ບໍລິການ Lab ສຳລັບຄິວທີ 1 - ສຳເລັດແລ້ວ (ຖ້າມີ)
        if ($labService) {
            $this->command->info('🧪 ສ້າງບໍລິການກວດ Lab ສຳລັບຄິວທີ 1...');
            QueueService::create([
                'queue_id' => $queues[0]->id,
                'service_id' => $labService->id,
                'assigned_to_id' => $nurse?->id ?? $doctor->id,
                'service_status' => 'Completed',
                'started_at' => now()->subMinutes(90),
                'completed_at' => now()->subMinutes(60),
                'actual_duration' => 30, // 30 ນາທີ
                'added_by_id' => $doctor->id,
                'notes' => 'ກວດເລືອດແລະປັດສະວະ',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subMinutes(60),
            ]);
        }

        // ບໍລິການສຳລັບຄິວທີ 2 - ກຳລັງເຮັດ
        if ($queues->count() > 1) {
            $this->command->info('⏳ ສ້າງບໍລິການກວດທ່ານໝໍສຳລັບຄິວທີ 2 (ກຳລັງເຮັດ)...');
            QueueService::create([
                'queue_id' => $queues[1]->id,
                'service_id' => $consultationService->id,
                'assigned_to_id' => $doctor->id,
                'service_status' => 'In_Progress',
                'started_at' => now()->subMinutes(20),
                'added_by_id' => $nurse?->id ?? $doctor->id,
                'notes' => 'ກວດສຸຂະພາບປົກກະຕິ',
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subMinutes(20),
            ]);

            // ເພີ່ມບໍລິການ Lab ສຳລັບຄິວທີ 2 - ຍັງບໍ່ເລີ່ມ
            if ($labService) {
                $this->command->info('🧪 ສ້າງບໍລິການກວດ Lab ສຳລັບຄິວທີ 2 (ລໍຖ້າ)...');
                QueueService::create([
                    'queue_id' => $queues[1]->id,
                    'service_id' => $labService->id,
                    'assigned_to_id' => $nurse?->id ?? $doctor->id,
                    'service_status' => 'Added',
                    'added_by_id' => $doctor->id,
                    'notes' => 'ລໍຖ້າການກວດທ່ານໝໍສຳເລັດກ່ອນ',
                    'created_at' => now()->subMinutes(15),
                    'updated_at' => now()->subMinutes(15),
                ]);
            }
        }

        // ສະແດງສະຖິຕິ
        $totalServices = QueueService::count();
        $completedServices = QueueService::where('service_status', 'Completed')->count();
        $inProgressServices = QueueService::where('service_status', 'In_Progress')->count();
        $pendingServices = QueueService::where('service_status', 'Added')->count();

        $this->command->info('✅ ສ້າງຂໍ້ມູນ Queue Service ສຳເລັດ!');
        $this->command->table(
            ['ສະຖານະ', 'ຈຳນວນ'],
            [
                ['ລວມທັງໝົດ', $totalServices],
                ['ສຳເລັດແລ້ວ', $completedServices],
                ['ກຳລັງເຮັດ', $inProgressServices],
                ['ລໍຖ້າ', $pendingServices],
            ]
        );
    }
}