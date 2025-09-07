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
        $receptionist = User::where('role', 'cashier')->first();
        $nurse = User::where('role', 'nurse')->first();

        $queues = [
            // ຄິວທີ 1 - ສຳເລັດແລ້ວ
            [
                'patient_id' => $patients[0]->id,
                'queue_number' => 1,
                'waiting_number' => 0,
                'queue_date' => today(),
                'queue_status' => 'Completed',
                'created_by' => $receptionist->id,
                'updated_by' => $doctors[0]->id,
            ],

            // ຄິວທີ 2 - ລໍຖ້າຜົນ Lab
            [
                'patient_id' => $patients[1]->id,
                'queue_number' => 2,
                'waiting_number' => 0,
                'queue_date' => today(),
                'queue_status' => 'Waiting_Test_Results',
                'created_by' => $receptionist->id,
                'updated_by' => $doctors[0]->id,
            ],

            // ຄິວທີ 3 - ກຳລັງກວດ
            [
                'patient_id' => $patients[2]->id,
                'queue_number' => 3,
                'waiting_number' => 0,
                'queue_date' => today(),
                'queue_status' => 'With_Doctor',
                'created_by' => $receptionist->id,
                'updated_by' => $doctors[1]->id,
            ],

            // ຄິວທີ 4 - ກວດ vital signs ແລ້ວ
            [
                'patient_id' => $patients[3]->id,
                'queue_number' => 4,
                'waiting_number' => 1,
                'queue_date' => today(),
                'queue_status' => 'Vital_Checked',
                'created_by' => $receptionist->id,
                'updated_by' => $receptionist->id,
            ],

            // ຄິວທີ 5 - ລົງທະບຽນແລ້ວ
            [
                'patient_id' => $patients[4]->id,
                'queue_number' => 5,
                'waiting_number' => 2,
                'queue_date' => today(),
                'queue_status' => 'Registered',
                'created_by' => $receptionist->id,
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
            'created_by' => $nurse->id,
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
                'blood_pressure' => fake()->numberBetween(90, 110)."/".fake()->numberBetween(50, 90),
                'heart_rate' => fake()->numberBetween(80, 120),
                'notes' => 'ເດັກມີອາການປົກກະຕິ'
            ]);
        } else {
            // ຜູ້ໃຫຍ່
            $vitalData = array_merge($vitalData, [
                'temperature' => fake()->randomFloat(1, 36.0, 38.0),
                'weight' => fake()->randomFloat(2, 45, 85),
                'height' => fake()->randomFloat(2, 150, 180),
                'blood_pressure' => fake()->numberBetween(90, 110)."/".fake()->numberBetween(50, 90),
                'heart_rate' => fake()->numberBetween(60, 100),
                'notes' => 'ອາການປົກກະຕິ'
            ]);
        }

        VitalSign::create($vitalData);
    }
}