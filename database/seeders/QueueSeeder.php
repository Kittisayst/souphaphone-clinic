<?php
// database/seeders/QueueSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Queue, Patient, User, Service, QueueService, VitalSign, Treatment, Room};
use Carbon\Carbon;

class QueueSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('ກຳລັງສ້າງຂໍ້ມູນຄິວທົດສອບ...');

        // ຫາຂໍ້ມູນພື້ນຖານທີ່ຈຳເປັນ
        $patients = Patient::limit(3)->get();
        $doctor = User::where('role', 'doctor')->first();
        $nurse = User::where('role', 'nurse')->first();
        $receptionist = User::where('role', 'receptionist')->first();

        $consultationService = Service::where('service_category', 'Consultation')->first();
        $bloodTestService = Service::where('service_category', 'Blood_Test')->first();
        $xrayService = Service::where('service_category', 'X_Ray')->first();

        $consultationRoom = Room::where('room_type', 'Consultation')->first();
        $labRoom = Room::where('room_type', 'Laboratory')->first();

        if (!$patients->count() || !$doctor || !$consultationService) {
            $this->command->error('ກະລຸນາ run PatientSeeder, UserSeeder, ServiceSeeder ກ່ອນ');
            return;
        }

        $today = now()->toDateString();

        // ===================================
        // 1. ຄິວທີ່ກວດສຳເລັດທຸກຢ່າງ (ມີ 2 queue_services)
        // ===================================

        $this->command->info('1. ສ້າງຄິວທີ່ກວດສຳເລັດທຸກຢ່າງ...');

        $completedQueue = Queue::create([
            'patient_id' => $patients[0]->id,
            'queue_number' => 1,
            'waiting_number' => 0, // ສຳເລັດແລ້ວ
            'queue_date' => $today,
            'initial_complaint' => 'ປວດຫົວ, ໄຂ້ ມາກວດສຸຂະພາບ',
            'doctor_id' => $doctor->id,
            'assigned_room_id' => $consultationRoom->id,
            'queue_status' => 'Completed',
            'priority_level' => 'Normal',
            'created_by' => $receptionist->id,

            // ເວລາຕ່າງໆ (ຈຳລອງວ່າຜ່ານມາແລ້ວ)
            'vital_checked_at' => now()->subHours(2),
            'room_assigned_at' => now()->subMinutes(90), 
            'doctor_start_at' => now()->subMinutes(90), 
            'lab_start_at' => now()->subMinutes(45),
            'results_ready_at' => now()->subMinutes(15),
            'completed_at' => now()->subMinutes(5),
        ]);

        // Vital Signs ສຳລັບຄິວທີ 1
        VitalSign::create([
            'queue_id' => $completedQueue->id,
            'temperature' => 37.2,
            'weight' => 65.5,
            'height' => 170.0,
            'heart_rate' => 78,
            'blood_pressure_sys' => 120,
            'blood_pressure_dia' => 80,
            'recorded_by' => $nurse->id,
            'notes' => 'ການກວດເບື້ອງຕົ້ນປົກກະຕິ'
        ]);

        // Queue Service 1: ການປຶກສາ
        $consultationQS = QueueService::create([
            'queue_id' => $completedQueue->id,
            'service_id' => $consultationService->id,
            'added_by' => $receptionist->id,
            'service_status' => 'Completed',
            'priority_order' => 1,
            'assigned_to' => $doctor->id,
            'scheduled_at' => now()->subMinutes(90),
            'started_at' => now()->subMinutes(90),
            'completed_at' => now()->subMinutes(30),
            'notes' => 'ການກວດທົ່ວໄປ'
        ]);

        // Treatment ສຳລັບການປຶກສາ
        Treatment::create([
            'queue_service_id' => $consultationQS->id,
            'room_id' => $consultationRoom->id,
            'performed_by' => $doctor->id,
            'treatment_started_at' => now()->subMinutes(90),
            'treatment_ended_at' => now()->subMinutes(30),

            // ຂໍ້ມູນການກວດ
            'medical_history_notes' => 'ເຄີຍມີໄຂ້ຫວັດເມື່ອອາທິດທີ່ແລ້ວ, ບໍ່ເຄີຍແພ້ຢາ',
            'current_symptoms' => 'ປວດຫົວດ້ານຫຼັງ, ໄຂ້ເລັກນ້ອຍ, ເມື່ອຍລ້າ',
            'physical_examination' => 'ກວດຫູ ຄໍ ດັງ ປົກກະຕິ, ບໍ່ມີອາການຜິດປົກກະຕິ',
            'examination_notes' => 'ຄົນໄຂ້ມີອາການໄຂ້ຫວັດທົ່ວໄປ, ບໍ່ຮ້າຍແຮງ',

            // ການວິນິໄຈ
            'initial_assessment' => 'ອາການຄ້າຍໄຂ້ຫວັດທົ່ວໄປ',
            'findings' => 'ອຸນຫະພູມຂຶ້ນເລັກນ້ອຍ, ບໍ່ມີອາການແຊກຊ້ອນ',
            'diagnosis' => 'ໄຂ້ຫວັດທົ່ວໄປ (Common Cold)',

            // ການປິ່ນປົວ
            'treatment_plan' => 'ກິນຢາລົດໄຂ້, ພັກຜ່ອນ, ດື່ມນ້ຳຫຼາຍໆ',
            'recommendations' => 'ພັກຜ່ອນ 2-3 ວັນ, ຫຼີກເວັ້ນອາຫານເຢັນ',
            'doctor_recommendations' => 'ຖ້າໄຂ້ບໍ່ຫາຍໃນ 3 ວັນໃຫ້ກັບມາກວດ',

            // ການຕິດຕາມ
            'follow_up_required' => true,
            'follow_up_date' => now()->addDays(3)->toDateString(),
            'follow_up_notes' => 'ກວດເບິ່ງອາການຖ້າບໍ່ດີຂຶ້ນ',

            'status' => 'Completed',
            'updated_by' => $doctor->id
        ]);

        // Queue Service 2: ກວດເລືອດ
        if ($bloodTestService) {
            $bloodTestQS = QueueService::create([
                'queue_id' => $completedQueue->id,
                'service_id' => $bloodTestService->id,
                'added_by' => $doctor->id, // ທ່ານໝໍສັ່ງເພີ່ມ
                'service_status' => 'Completed',
                'priority_order' => 2,
                'assigned_to' => $nurse->id,
                'scheduled_at' => now()->subMinutes(45),
                'started_at' => now()->subMinutes(45),
                'completed_at' => now()->subMinutes(15),
                'notes' => 'ທ່ານໝໍສັ່ງກວດເລືອດເພື່ອຢືນຢັນ'
            ]);

            // Treatment ສຳລັບການກວດເລືອດ
            Treatment::create([
                'queue_service_id' => $bloodTestQS->id,
                'room_id' => $labRoom->id,
                'performed_by' => $nurse->id,
                'treatment_started_at' => now()->subMinutes(45),
                'treatment_ended_at' => now()->subMinutes(15),

                'examination_notes' => 'ເຈາະເລືອດຈາກແຂນຊ້າຍ',
                'findings' => 'ຄ່າເລືອດປົກກະຕິ, ບໍ່ມີການອັກເສບ',
                'diagnosis' => 'ຜົນກວດເລືອດປົກກະຕິ',
                'recommendations' => 'ຜົນກວດປົກກະຕິ, ຕິດຕາມອາການຕໍ່ໄປ',

                'status' => 'Completed',
                'updated_by' => $nurse->id
            ]);
        }

        // ===================================
        // 2. ຄິວທີ່ລົງທະບຽນຍັງບໍ່ໄດ້ເຮັດຫຍັງ
        // ===================================

        $this->command->info('2. ສ້າງຄິວທີ່ລົງທະບຽນຍັງບໍ່ໄດ້ເຮັດຫຍັງ...');

        $registeredQueue1 = Queue::create([
            'patient_id' => $patients[1]->id,
            'queue_number' => 2,
            'waiting_number' => 1, // ລໍຖ້າທີ 1
            'queue_date' => $today,
            'initial_complaint' => 'ປວດທ້ອງ, ຖ່າຍຫຼວງ',
            'queue_status' => 'Registered',
            'priority_level' => 'Normal',
            'created_by' => $receptionist->id,
        ]);

        // ເພີ່ມບໍລິການການປຶກສາ
        QueueService::create([
            'queue_id' => $registeredQueue1->id,
            'service_id' => $consultationService->id,
            'added_by' => $receptionist->id,
            'service_status' => 'Added',
            'priority_order' => 1,
            'notes' => 'ການກວດທົ່ວໄປ'
        ]);

        // ===================================
        // 3. ຄິວທີ່ລົງທະບຽນຍັງບໍ່ໄດ້ເຮັດຫຍັງ (ອີກໜຶ່ງຄິວ)
        // ===================================

        $this->command->info('3. ສ້າງຄິວທີ່ລົງທະບຽນຍັງບໍ່ໄດ້ເຮັດຫຍັງ (ອີກໜຶ່ງຄິວ)...');

        $registeredQueue2 = Queue::create([
            'patient_id' => $patients[2]->id,
            'queue_number' => 3,
            'waiting_number' => 2, // ລໍຖ້າທີ 2
            'queue_date' => $today,
            'initial_complaint' => 'ກວດສຸຂະພາບປະຈຳປີ',
            'queue_status' => 'Registered',
            'priority_level' => 'Normal',
            'created_by' => $receptionist->id,
        ]);

        // ເພີ່ມບໍລິການການປຶກສາ
        QueueService::create([
            'queue_id' => $registeredQueue2->id,
            'service_id' => $consultationService->id,
            'added_by' => $receptionist->id,
            'service_status' => 'Added',
            'priority_order' => 1,
            'notes' => 'ການກວດສຸຂະພາບປະຈຳປີ'
        ]);

        // ເພີ່ມບໍລິການ X-Ray (ຖ້າມີ)
        if ($xrayService) {
            QueueService::create([
                'queue_id' => $registeredQueue2->id,
                'service_id' => $xrayService->id,
                'added_by' => $receptionist->id,
                'service_status' => 'Added',
                'priority_order' => 2,
                'notes' => 'ກວດ X-Ray ໜ້າເອິກປະຈຳປີ'
            ]);
        }

        // ===================================
        // ສ້າງຄິວເພີ່ມເຕີມສຳລັບການທົດສອບ
        // ===================================

        $this->command->info('ສ້າງຄິວເພີ່ມເຕີມ...');

        // ຄິວທີ່ກວດເບື້ອງຕົ້ນແລ້ວ
        if ($patients->count() > 3) {
            $vitalCheckedQueue = Queue::create([
                'patient_id' => $patients[3]->id,
                'queue_number' => 4,
                'waiting_number' => 3, // ລໍຖ້າທີ 3
                'queue_date' => $today,
                'initial_complaint' => 'ປວດຫຼັງ, ຂໍ້ອັກເສບ',
                'queue_status' => 'Vital_Checked',
                'priority_level' => 'Urgent',
                'created_by' => $receptionist->id,
                'vital_checked_at' => now()->subMinutes(30),
            ]);

            // Vital Signs
            VitalSign::create([
                'queue_id' => $vitalCheckedQueue->id,
                'temperature' => 36.8,
                'weight' => 72.0,
                'height' => 175.0,
                'heart_rate' => 85,
                'blood_pressure_sys' => 130,
                'blood_pressure_dia' => 85,
                'recorded_by' => $nurse->id,
                'notes' => 'ຄວາມດັນເລືອດສູງເລັກນ້ອຍ'
            ]);

            // ເພີ່ມບໍລິການ
            QueueService::create([
                'queue_id' => $vitalCheckedQueue->id,
                'service_id' => $consultationService->id,
                'added_by' => $receptionist->id,
                'service_status' => 'Added',
                'priority_order' => 1,
                'notes' => 'ກວດປວດຫຼັງ'
            ]);
        }

        // ຄິວທີ່ຍົກເລີກ
        if ($patients->count() > 4) {
            $cancelledQueue = Queue::create([
                'patient_id' => $patients[4]->id,
                'queue_number' => 5,
                'waiting_number' => 0, // ຍົກເລີກແລ້ວ
                'queue_date' => $today,
                'initial_complaint' => 'ມີເຫດຸການດ່ວນ ຂໍຍົກເລີກ',
                'queue_status' => 'Cancelled',
                'priority_level' => 'Normal',
                'created_by' => $receptionist->id,
            ]);

            QueueService::create([
                'queue_id' => $cancelledQueue->id,
                'service_id' => $consultationService->id,
                'added_by' => $receptionist->id,
                'service_status' => 'Cancelled',
                'priority_order' => 1,
                'notes' => 'ຄົນໄຂ້ຍົກເລີກເອງ'
            ]);
        }

        // ===================================
        // ສະຫຼຸບຜົນ
        // ===================================

        $this->command->info('');
        $this->command->info('✅ ສ້າງຂໍ້ມູນຄິວທົດສອບສຳເລັດ!');
        $this->command->info('');

        $this->command->table(
            ['ຄິວ', 'ຄົນໄຂ້', 'ສະຖານະ', 'ເລກລໍຖ້າ', 'ບໍລິການ'],
            [
                [
                    '#1',
                    $patients[0]->full_name ?? 'ຄົນໄຂ້ 1',
                    'ສຳເລັດທຸກຢ່າງ',
                    '0 (ສຳເລັດ)',
                    'ປຶກສາ + ກວດເລືອດ'
                ],
                [
                    '#2',
                    $patients[1]->full_name ?? 'ຄົນໄຂ້ 2',
                    'ລົງທະບຽນແລ້ວ',
                    '1',
                    'ປຶກສາ'
                ],
                [
                    '#3',
                    $patients[2]->full_name ?? 'ຄົນໄຂ້ 3',
                    'ລົງທະບຽນແລ້ວ',
                    '2',
                    'ປຶກສາ + X-Ray'
                ],
                [
                    '#4',
                    $patients[3]->full_name ?? 'ຄົນໄຂ້ 4',
                    'ກວດເບື້ອງຕົ້ນແລ້ວ',
                    '3',
                    'ປຶກສາ'
                ],
                [
                    '#5',
                    $patients[4]->full_name ?? 'ຄົນໄຂ້ 5',
                    'ຍົກເລີກ',
                    '0 (ຍົກເລີກ)',
                    'ປຶກສາ (ຍົກເລີກ)'
                ]
            ]
        );

        $this->command->info('');
        $this->command->info('📊 ສະຖິຕິ:');
        $this->command->info('   - ຄິວທັງໝົດ: 5 ຄິວ');
        $this->command->info('   - ກຳລັງລໍຖ້າ: 3 ຄິວ');
        $this->command->info('   - ສຳເລັດແລ້ວ: 1 ຄິວ');
        $this->command->info('   - ຍົກເລີກ: 1 ຄິວ');
        $this->command->info('');
        $this->command->info('🎯 ພ້ອມທົດສອບ MVP Queue System!');
    }
}