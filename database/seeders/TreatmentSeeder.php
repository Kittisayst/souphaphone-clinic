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

        // ຫາຄິວທີ 1 (ຄິວທີ່ສຳເລັດແລ້ວ)
        $queue = Queue::where('queue_number', 1)->first();
        
        if (!$queue) {
            $this->command->error('ບໍ່ພົບຄິວທີ 1 - ກະລຸນາ run QueueSeeder ກ່ອນ');
            return;
        }

        // ຫາຂໍ້ມູນທີ່ຈຳເປັນ
        $doctor = User::where('role', 'doctor')->first();
        $nurse = User::where('role', 'nurse')->first();
        $consultationRoom = Room::where('room_type', 'Consultation')->first();
        $labRoom = Room::where('room_type', 'Laboratory')->first();

        // ຫາ QueueServices ຂອງຄິວທີ 1
        $queueServices = $queue->queueServices()->get();

        if ($queueServices->isEmpty()) {
            $this->command->error('ບໍ່ພົບ QueueService ສຳລັບຄິວທີ 1');
            return;
        }

        $this->command->info("ພົບ {$queueServices->count()} QueueService(s) ສຳລັບຄິວທີ 1");

        // ==================================
        // ສ້າງ Treatment ສຳລັບແຕ່ລະ QueueService
        // ==================================

        foreach ($queueServices as $index => $queueService) {
            $serviceCategory = $queueService->service->service_category;
            $serviceName = $queueService->service->service_name;
            
            $this->command->info("ກຳລັງສ້າງ Treatment ສຳລັບ: {$serviceName} ({$serviceCategory})");

            if ($serviceCategory === 'Consultation') {
                // ການປິ່ນປົວສຳລັບການປຶກສາ
                $this->createConsultationTreatment($queueService, $doctor, $consultationRoom);
                
            } elseif ($serviceCategory === 'Blood_Test') {
                // ການປິ່ນປົວສຳລັບການກວດເລືອດ
                $this->createBloodTestTreatment($queueService, $nurse, $labRoom);
                
            } elseif ($serviceCategory === 'X_Ray') {
                // ການປິ່ນປົວສຳລັບ X-Ray
                $this->createXRayTreatment($queueService, $nurse, $labRoom);
                
            } else {
                // ການປິ່ນປົວທົ່ວໄປ
                $this->createGeneralTreatment($queueService, $doctor, $consultationRoom);
            }
        }

        $this->command->info('');
        $this->command->info('✅ ສ້າງຂໍ້ມູນ Treatment ສຳເລັດ!');
        
        // ສະແດງສະຫຼຸບ
        $treatments = Treatment::whereIn('queue_service_id', $queueServices->pluck('id'))->get();
        
        $this->command->table(
            ['ບໍລິການ', 'ຫ້ອງ', 'ຜູ້ເຮັດ', 'ສະຖານະ', 'ການວິນິໄຈ'],
            $treatments->map(function ($treatment) {
                return [
                    $treatment->queueService->service->service_name,
                    $treatment->room->room_name ?? '-',
                    $treatment->performedBy->name,
                    $treatment->status,
                    $treatment->diagnosis ?? '-'
                ];
            })->toArray()
        );
    }

    /**
     * ສ້າງ Treatment ສຳລັບການປຶກສາ
     */
    private function createConsultationTreatment($queueService, $doctor, $room)
    {
        Treatment::create([
            'queue_service_id' => $queueService->id,
            'room_id' => $room->id,
            'performed_by' => $doctor->id,
            'treatment_started_at' => now()->subMinutes(90), // 1.5 ຊົ່ວໂມງກ່ອນ
            'treatment_ended_at' => now()->subMinutes(30),   // 30 ນາທີກ່ອນ
            
            // ຂໍ້ມູນການກວດ
            'medical_history_notes' => 'ເຄີຍມີໄຂ້ຫວັດເມື່ອອາທິດທີ່ແລ້ວ, ບໍ່ເຄີຍແພ້ຢາ, ບໍ່ເຄີຍຜ່າຕັດ, ຄອບຄົວບໍ່ມີປະຫວັດເປັນໂລກຮ້າຍແຮງ',
            'current_symptoms' => 'ປວດຫົວດ້ານຫຼັງ, ໄຂ້ເລັກນ້ອຍ, ເມື່ອຍລ້າ, ເບື່ອອາຫານ, ນອນບໍ່ຫຼັບ',
            'physical_examination' => 'ກວດຫູ: ປົກກະຕິ, ຄໍ: ບໍ່ບວມ, ດັງ: ຟັງແລ້ວປົກກະຕິ, ໃຈ: ປົກກະຕິ, ທ້ອງ: ອ່ອນນຸ່ມ ບໍ່ເຈັບ',
            'examination_notes' => 'ຄົນໄຂ້ມີອາການໄຂ້ຫວັດທົ່ວໄປ, ບໍ່ຮ້າຍແຮງ, ບໍ່ມີອາການແຊກຊ້ອນ, ການກວດທາງກາຍະພາບບໍ່ພົບຄວາມຜິດປົກກະຕິ',
            
            // ການວິເຄາະແລະການວິນິໄຈ
            'initial_assessment' => 'ອາການຄ້າຍໄຂ້ຫວັດທົ່ວໄປ, ບໍ່ມີອາການແນວຊັບຊ້ອນ, ສາມາດປິ່ນປົວທີ່ບ້ານໄດ້',
            'findings' => 'ອຸນຫະພູມຂຶ້ນເລັກນ້ອຍ 37.2°C, ບໍ່ມີອາການອັກເສບຮ້າຍແຮງ, ການກວດທາງກາຍະພາບປົກກະຕິ',
            'diagnosis' => 'ໄຂ້ຫວັດທົ່ວໄປ (Common Cold) - Upper Respiratory Tract Infection',
            
            // ການປິ່ນປົວແລະຄຳແນະນຳ
            'treatment_plan' => 'ປິ່ນປົວດ້ວຍຢາຕາມອາການ: ຢາລົດໄຂ້ (Paracetamol), ຢາຂະຫຍາຍເສັ້ນເລືອດດັງ, ພັກຜ່ອນເພີ່ມ',
            'recommendations' => 'ພັກຜ່ອນເຕັມທີ່ 2-3 ວັນ, ດື່ມນ້ຳສະອາດຫຼາຍໆ, ກິນອາຫານງ່າຍໆ, ຫຼີກເວັ້ນອາຫານເຢັນ',
            'doctor_recommendations' => 'ຖ້າໄຂ້ບໍ່ຫາຍໃນ 3 ວັນ ຫຼືມີອາການໜັກຂຶ້ນ ໃຫ້ກັບມາກວດທັນທີ, ຖ້າມີອາການເຮືອກຫອບ ໃຫ້ມາໂຮງໝໍທັນທີ',
            
            // ການຕິດຕາມ
            'follow_up_required' => true,
            'follow_up_date' => now()->addDays(3)->toDateString(),
            'follow_up_notes' => 'ກວດເບິ່ງອາການຖ້າບໍ່ດີຂຶ້ນ, ປະເມີນຄວາມຈຳເປັນໃນການປິ່ນປົວເພີ່ມເຕີມ',
            
            'status' => 'Completed',
            'updated_by' => $doctor->id
        ]);
    }

    /**
     * ສ້າງ Treatment ສຳລັບການກວດເລືອດ
     */
    private function createBloodTestTreatment($queueService, $nurse, $room)
    {
        Treatment::create([
            'queue_service_id' => $queueService->id,
            'room_id' => $room->id,
            'performed_by' => $nurse->id,
            'treatment_started_at' => now()->subMinutes(45),
            'treatment_ended_at' => now()->subMinutes(15),
            
            'medical_history_notes' => 'ທ່ານໝໍສັ່ງກວດເລືອດເພື່ອຢືນຢັນວ່າບໍ່ມີການຕິດເຊື້ອແບັກທີເຣຍ',
            'current_symptoms' => 'ຕາມທີ່ທ່ານໝໍສັ່ງກວດ',
            'physical_examination' => 'ເຈາະເລືອດຈາກເສັ້ນເລືອດດຳແຂນຊ້າຍ, ໄດ້ເລືອດ 5ml',
            'examination_notes' => 'ເຈາະເລືອດສຳເລັດ, ບໍ່ມີອາການແຊກຊ້ອນ, ຄົນໄຂ້ທົນໄດ້ດີ',
            
            // ຜົນການກວດ
            'findings' => 'ຜົນກວດເລືອດ: WBC ປົກກະຕິ (6,500/μl), ບໍ່ມີການອັກເສບ, ຄ່າອື່ນໆ ຢູ່ໃນເກນປົກກະຕິ',
            'diagnosis' => 'ຜົນກວດເລືອດປົກກະຕິ - ບໍ່ພົບການຕິດເຊື້ອແບັກທີເຣຍ',
            'recommendations' => 'ຜົນກວດສະໜັບສະໜູນການວິນິໄຈວ່າເປັນໄຂ້ຫວັດທົ່ວໄປ, ຕິດຕາມອາການຕໍ່ໄປ',
            
            'status' => 'Completed',
            'updated_by' => $nurse->id
        ]);
    }

    /**
     * ສ້າງ Treatment ສຳລັບ X-Ray
     */
    private function createXRayTreatment($queueService, $nurse, $room)
    {
        Treatment::create([
            'queue_service_id' => $queueService->id,
            'room_id' => $room->id,
            'performed_by' => $nurse->id,
            'treatment_started_at' => now()->subMinutes(60),
            'treatment_ended_at' => now()->subMinutes(40),
            
            'medical_history_notes' => 'ທ່ານໝໍສັ່ງຖ່າຍ X-Ray ເພື່ອກວດເບິ່ງປອດ',
            'physical_examination' => 'ຖ່າຍ X-Ray ໜ້າເອິກ (PA view)',
            'examination_notes' => 'ຖ່າຍພາບສຳເລັດ, ຄົນໄຂ້ຮ່ວມມືດີ, ຄຸນນະພາບຮູບພາບຊັດເຈນ',
            
            'findings' => 'X-Ray ໜ້າເອິກ: ປອດທັງສອງຂ້າງປົກກະຕິ, ບໍ່ມີເງົາຂຸ່ນ, ຫົວໃຈຂະໜາດປົກກະຕິ',
            'diagnosis' => 'X-Ray ໜ້າເອິກປົກກະຕິ',
            'recommendations' => 'ຜົນ X-Ray ບໍ່ພົບຄວາມຜິດປົກກະຕິ, ສອດຄ່ອງກັບການວິນິໄຈໄຂ້ຫວັດທົ່ວໄປ',
            
            'status' => 'Completed',
            'updated_by' => $nurse->id
        ]);
    }

    /**
     * ສ້າງ Treatment ທົ່ວໄປ
     */
    private function createGeneralTreatment($queueService, $doctor, $room)
    {
        Treatment::create([
            'queue_service_id' => $queueService->id,
            'room_id' => $room->id,
            'performed_by' => $doctor->id,
            'treatment_started_at' => now()->subMinutes(60),
            'treatment_ended_at' => now()->subMinutes(30),
            
            'examination_notes' => "ເຮັດການ {$queueService->service->service_name}",
            'findings' => 'ຜົນການກວດປົກກະຕິ',
            'diagnosis' => 'ປົກກະຕິ',
            'recommendations' => 'ຕິດຕາມອາການຕໍ່ໄປ',
            
            'status' => 'Completed',
            'updated_by' => $doctor->id
        ]);
    }
}