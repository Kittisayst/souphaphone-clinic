<?php
// database/seeders/TreatmentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Queue, QueueService, Treatment, Room, User, Service};

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('ກຳລັງສ້າງຂໍ້ມູນການປິ່ນປົວສຳລັບຄິວທີ 1...');

        // ດຶງຂໍ້ມູນທີ່ຈຳເປັນ
        $queueServices = QueueService::where('service_status', 'Completed')
            ->whereHas('service', function ($query) {
                $query->where('service_category', 'Consultation');
            })
            ->limit(2)->get();

        $doctor = User::where('role', 'doctor')->first();

        if ($queueServices->isEmpty() || !$doctor) {
            $this->command->warn('ຕ້ອງມີຂໍ້ມູນ QueueService (Consultation) ແລະ Doctor ກ່ອນ');
            return;
        }

        // ການປິ່ນປົວທີ 1 - ສຳເລັດແລ້ວ
        Treatment::create([
            'queue_service_id' => $queueServices[0]->id,
            'room_id' => 1, // ສົມມຸດວ່າມີຫ້ອງ ID 1
            'doctor_id' => $doctor->id,
            'examination_notes' => 'ກວດພົບ: ອຸນຫະພູມ 38.5°C, ຄໍ້ແດງ, ປວດຫົວ',
            'findings' => 'ອາການຄ້າຍກັບໄຂ້ຫວັດ, ບໍ່ມີອາການຮ້າຍແຮງ',
            'medical_history_notes' => 'ບໍ່ເຄີຍມີປະຫວັດການປ່ວຍຮ້າຍແຮງ, ບໍ່ແພ້ຢາ',
            'diagnosis' => 'ໄຂ້ຫວັດທົ່ວໄປ (Common Cold)',
            'treatment_plan' => 'ພັກຜ່ອນ, ດື່ມນ້ຳຫຼາຍໆ, ກິນຢາລົດໄຂ້',
            'follow_up_required' => false,
            'status' => 'Completed',
            'updated_by' => $doctor->id,
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHour(),
        ]);

        // ການປິ່ນປົວທີ 2 - ລໍຖ້າຜົນ Lab
        if ($queueServices->count() > 1) {
            Treatment::create([
                'queue_service_id' => $queueServices[1]->id,
                'room_id' => 1,
                'doctor_id' => $doctor->id,
                'examination_notes' => 'ກວດສຸຂະພາບປົກກະຕິ, ປ່ວຍສ່ວນ 45 ປີ',
                'findings' => 'ຄົນໄຂ້ຮູ້ສຶກເມື່ອຍ, ຢາກກວດເລືອດ',
                'medical_history_notes' => 'ມີປະຫວັດຄອບຄົວເປັນເບົາຫວານ',
                'diagnosis' => null, // ຍັງບໍ່ມີການວິນິໄຈ
                'treatment_plan' => null, // ລໍຖ້າຜົນ Lab ກ່ອນ
                'follow_up_required' => true,
                'follow_up_date' => today()->addWeek(),
                'follow_up_notes' => 'ມາເບິ່ງຜົນກວດເລືອດ',
                'status' => 'Waiting_Lab_Results',
                'updated_by' => $doctor->id,
                'created_at' => now()->subMinutes(20),
                'updated_at' => now()->subMinutes(10),
            ]);
        }

        $this->command->info('✅ ສ້າງຂໍ້ມູນ Treatment ສຳເລັດ: 2 ລາຍການ');
    }
}