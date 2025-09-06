<?php
// database/seeders/QueueServiceSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Queue, QueueService, Service, User};

class QueueServiceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('ກຳລັງສ້າງບໍລິການສຳລັບຄິວທີ 1...');

        // ຫາຄິວທີ 1
        $queue = Queue::where('queue_number', 1)->first();

        if (!$queue) {
            $this->command->error('ບໍ່ພົບຄິວທີ 1 - ກະລຸນາສ້າງຄິວກ່ອນ');
            return;
        }

        // ຫາບໍລິການທີ່ຕ້ອງການ
        $consultationService = Service::where('service_category', 'Consultation')->first();
        $bloodTestService = Service::where('service_category', 'Blood_Test')->first();

        if (!$consultationService) {
            $this->command->error('ບໍ່ພົບບໍລິການ Consultation - ກະລຸນາ run ServiceSeeder ກ່ອນ');
            return;
        }

        // ຫາຜູ້ໃຊ້
        $doctor = User::where('role', 'doctor')->first();
        $nurse = User::where('role', 'nurse')->first();
        $receptionist = User::where('role', 'receptionist')->first() ?? User::first();

        if (!$doctor || !$receptionist) {
            $this->command->error('ບໍ່ພົບຜູ້ໃຊ້ງານທີ່ຈຳເປັນ - ກະລຸນາ run UserSeeder ກ່ອນ');
            return;
        }

        // ລຶບ QueueService ເກົ່າຖ້າມີ
        QueueService::where('queue_id', $queue->id)->forceDelete();

        $this->command->info('ລຶບບໍລິການເກົ່າແລ້ວ');

        // ===================================
        // 1. ສ້າງບໍລິການການປຶກສາ (ຫຼັກ)
        // ===================================

        $consultationQS = QueueService::create([
            'queue_id' => $queue->id,
            'service_id' => $consultationService->id,
            'added_by' => $receptionist->id,
            'service_status' => 'Completed',
            'priority_order' => 1,
            'assigned_to' => $doctor->id,
            'scheduled_at' => now()->subMinutes(90),   // ນັດເວລາ 1.5 ຊົ່ວໂມງກ່ອນ
            'started_at' => now()->subMinutes(90),     // ເລີ່ມ 1.5 ຊົ່ວໂມງກ່ອນ
            'completed_at' => now()->subMinutes(30),   // ສຳເລັດ 30 ນາທີກ່ອນ
            'notes' => 'ການກວດທົ່ວໄປ - ອາການໄຂ້ຫວັດ'
        ]);

        $this->command->info("✅ ສ້າງບໍລິການ: {$consultationService->service_name}");

        // ===================================
        // 2. ສ້າງບໍລິການກວດເລືອດ (ທ່ານໝໍສັ່ງເພີ່ມ)
        // ===================================

        if ($bloodTestService) {
            $bloodTestQS = QueueService::create([
                'queue_id' => $queue->id,
                'service_id' => $bloodTestService->id,
                'added_by' => $doctor->id,  // ທ່ານໝໍສັ່ງເພີ່ມ
                'service_status' => 'Completed',
                'priority_order' => 2,
                'assigned_to' => $nurse ? $nurse->id : $doctor->id,
                'scheduled_at' => now()->subMinutes(45),  // ນັດເວລາ 45 ນາທີກ່ອນ
                'started_at' => now()->subMinutes(45),    // ເລີ່ມ 45 ນາທີກ່ອນ
                'completed_at' => now()->subMinutes(15),  // ສຳເລັດ 15 ນາທີກ່ອນ
                'notes' => 'ທ່ານໝໍສັ່ງກວດເລືອດເພື່ອຢືນຢັນວ່າບໍ່ມີການຕິດເຊື້ອແບັກທີເຣຍ'
            ]);

            $this->command->info("✅ ສ້າງບໍລິການ: {$bloodTestService->service_name}");
        } else {
            $this->command->warn('ບໍ່ພົບບໍລິການກວດເລືອດ - ຂ້າມໄປ');
        }

        // ===================================
        // ສະຫຼຸບຜົນ
        // ===================================

        $queueServices = $queue->queueServices()->with(['service', 'addedBy', 'assignedTo'])->get();

        $this->command->info('');
        $this->command->info('✅ ສ້າງບໍລິການສຳລັບຄິວທີ 1 ສຳເລັດ!');
        $this->command->info('');

        $this->command->table(
            ['ລຳດັບ', 'ບໍລິການ', 'ຜູ້ເພີ່ມ', 'ຜູ້ຮັບຜິດຊອບ', 'ສະຖານະ', 'ໝາຍເຫດ'],
            $queueServices->map(function ($qs) {
                return [
                    $qs->priority_order,
                    $qs->service->service_name,
                    $qs->addedBy->name,
                    $qs->assignedTo->name ?? '-',
                    match ($qs->service_status) {
                        'Added' => 'ເພີ່ມແລ້ວ',
                        'Scheduled' => 'ນັດເວລາແລ້ວ',
                        'In_Progress' => 'ກຳລັງເຮັດ',
                        'Completed' => 'ສຳເລັດ',
                        'Cancelled' => 'ຍົກເລີກ',
                        default => $qs->service_status
                    },
                    $qs->notes
                ];
            })->toArray()
        );

        $this->command->info('');
        $this->command->info('📊 ສະຖິຕິ:');
        $this->command->info("   - ບໍລິການທັງໝົດ: {$queueServices->count()} ລາຍການ");
        $this->command->info("   - ສຳເລັດແລ້ວ: {$queueServices->where('service_status', 'Completed')->count()} ລາຍການ");
        $this->command->info('');
        $this->command->info('🎯 ພ້ອມສຳລັບ TreatmentSeeder!');
    }
}