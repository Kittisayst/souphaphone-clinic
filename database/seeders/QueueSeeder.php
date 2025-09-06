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
        $this->command->info('📋 ສ້າງຂໍ້ມູນຄິວ...');

        $patients = Patient::all();
        $doctors = User::where('role', 'doctor')->get();
        $nurse = User::where('role', 'nurse')->first();
        $receptionist = User::where('role', 'receptionist')->first();

        $queues = [
            // ຄິວທີ 1 - ສຳເລັດແລ້ວ
            [
                'patient_id' => $patients[0]->id,
                'queue_number' => 1,
                'waiting_number' => 0,
                'queue_date' => today(),
                'initial_complaint' => 'ປວດຫົວ, ໄຂ້',
                'queue_status' => 'Completed',
                'doctor_id' => $doctors[0]->id,
                'assigned_room_id' => 1,
                'room_assigned_at' => now()->subHours(3),
                'doctor_start_at' => now()->subHours(3),
                'tests_completed_at' => now()->subHours(2),
                'payment_completed_at' => now()->subHour(),
                'priority_level' => 'Normal',
                'created_by' => $receptionist->id,
                'updated_by' => $doctors[0]->id,
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHour(),
            ],

            // ຄິວທີ 2 - ລໍຖ້າຜົນ Lab
            [
                'patient_id' => $patients[1]->id,
                'queue_number' => 2,
                'waiting_number' => 0,
                'queue_date' => today(),
                'initial_complaint' => 'ເມື່ອຍ, ຢາກກວດເລືອດ',
                'queue_status' => 'Waiting_Test_Results',
                'doctor_id' => $doctors[0]->id,
                'assigned_room_id' => 1,
                'room_assigned_at' => now()->subHours(2),
                'doctor_start_at' => now()->subHours(2),
                'priority_level' => 'Normal',
                'created_by' => $receptionist->id,
                'updated_by' => $doctors[0]->id,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(1),
            ],

            // ຄິວທີ 3 - ກຳລັງກວດ
            [
                'patient_id' => $patients[2]->id,
                'queue_number' => 3,
                'waiting_number' => 0,
                'queue_date' => today(),
                'initial_complaint' => 'ໄອ, ຈາມ (ເດັກ)',
                'queue_status' => 'With_Doctor',
                'doctor_id' => $doctors[1]->id,
                'assigned_room_id' => 2,
                'room_assigned_at' => now()->subMinutes(30),
                'doctor_start_at' => now()->subMinutes(30),
                'priority_level' => 'Normal',
                'created_by' => $receptionist->id,
                'updated_by' => $doctors[1]->id,
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subMinutes(30),
            ],

            // ຄິວທີ 4 - ກວດ vital signs ແລ້ວ
            [
                'patient_id' => $patients[3]->id,
                'queue_number' => 4,
                'waiting_number' => 1,
                'queue_date' => today(),
                'initial_complaint' => 'ວຽນຫົວ, ຄວາມດັນສູງ',
                'queue_status' => 'Vital_Checked',
                'priority_level' => 'Urgent',
                'created_by' => $receptionist->id,
                'updated_by' => $nurse->id,
                'created_at' => now()->subMinutes(45),
                'updated_at' => now()->subMinutes(20),
            ],

            // ຄິວທີ 5 - ລົງທະບຽນແລ້ວ
            [
                'patient_id' => $patients[4]->id,
                'queue_number' => 5,
                'waiting_number' => 2,
                'queue_date' => today(),
                'initial_complaint' => 'ກວດສຸຂະພາບປົກກະຕິ',
                'queue_status' => 'Registered',
                'priority_level' => 'Normal',
                'created_by' => $receptionist->id,
                'created_at' => now()->subMinutes(15),
                'updated_at' => now()->subMinutes(15),
            ],
        ];

        foreach ($queues as $queueData) {
            $queue = Queue::create($queueData);

            // ສ້າງ Vital Signs ສຳລັບຄິວທີ່ກວດແລ້ວ
            if (in_array($queue->queue_status, ['Vital_Checked', 'With_Doctor', 'Waiting_Test_Results', 'Results_Ready', 'Ready_For_Payment', 'Completed'])) {
                $this->createVitalSigns($queue, $nurse);
            }
        }

        $this->command->info("✅ ສ້າງຄິວ: " . count($queues) . " ຄິວ");
    }

    private function createVitalSigns(Queue $queue, User $nurse): void
    {
        $vitalData = [
            'queue_id' => $queue->id,
            'recorded_by' => $nurse->id,
            'created_at' => $queue->created_at->addMinutes(10),
            'updated_at' => $queue->created_at->addMinutes(10),
        ];

        // ສ້າງຂໍ້ມູນ vital signs ຕາມອາຍຸ
        $age = $queue->patient->birth_date?->age ?? 30;

        if ($age < 18) {
            // ເດັກ
            $vitalData = array_merge($vitalData, [
                'temperature' => fake()->randomFloat(1, 36.0, 37.5),
                'weight' => fake()->randomFloat(2, 15, 40),
                'height' => fake()->randomFloat(2, 100, 150),
                'blood_pressure_sys' => fake()->numberBetween(90, 110),
                'blood_pressure_dia' => fake()->numberBetween(60, 80),
                'heart_rate' => fake()->numberBetween(80, 120),
                'respiratory_rate' => fake()->numberBetween(18, 25),
                'notes' => 'ເດັກມີອາການປົກກະຕິ'
            ]);
        } else {
            // ຜູ້ໃຫຍ່
            $vitalData = array_merge($vitalData, [
                'temperature' => fake()->randomFloat(1, 36.0, 38.0),
                'weight' => fake()->randomFloat(2, 45, 85),
                'height' => fake()->randomFloat(2, 150, 180),
                'blood_pressure_sys' => fake()->numberBetween(110, 140),
                'blood_pressure_dia' => fake()->numberBetween(70, 90),
                'heart_rate' => fake()->numberBetween(60, 100),
                'respiratory_rate' => fake()->numberBetween(12, 20),
                'oxygen_saturation' => fake()->randomFloat(2, 95, 100),
            ]);
        }

        VitalSign::create($vitalData);
    }
}