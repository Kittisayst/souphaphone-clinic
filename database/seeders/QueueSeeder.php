<?php

// =================================================================================
// database/seeders/QueueSeeder.php
// Seeder ສຳລັບສ້າງຄິວທົດລອງ - ແບບງ່າຍ 3 ຄິວ
// =================================================================================

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\QueueService;
use App\Models\VitalSign;
use App\Models\Treatment;
use App\Models\Lab;
use App\Models\Prescription;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\User;
use App\Models\Service;
use App\Models\Medicine;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creating demo queues...');

        // ตรวจสอบข้อมูลพื้นฐาน
        $patients = Patient::limit(3)->get();
        $doctors = User::where('role', 'doctor')->limit(2)->get();
        $nurses = User::where('role', 'nurse')->first();
        $receptionist = User::where('role', 'cashier')->first();
        $services = Service::limit(4)->get();
        $medicines = Medicine::limit(3)->get();

        if ($patients->count() < 3 || $doctors->count() < 1) {
            $this->command->error('❌ ຕ້ອງມີຂໍ້ມູນ Patients ແລະ Users ກ່ອນ!');
            return;
        }

        // ສ້າງຄິວທົດລອງ 3 ຄິວ
        $queueData = [
            // ຄິວທີ 1: ສຳເລັດແລ້ວ
            [
                'queue_number' => 1,
                'patient_id' => $patients->get(0)->id,
                'initial_complaint' => 'ປວດຫົວ ແລະ ໄຂ້',
                'queue_status' => 'Completed',
                'services' => [
                    ['service_id' => 1, 'priority' => 1], // ກວດທົ່ວໄປ
                    ['service_id' => 2, 'priority' => 2], // ກວດເລືອດ
                ],
                'medicines' => [
                    ['medicine_id' => 1, 'quantity' => 10], // Paracetamol
                    ['medicine_id' => 2, 'quantity' => 6],  // Ibuprofen
                ]
            ],

            // ຄິວທີ 2: ກຳລັງລໍຖ້າຜົນກວດ
            [
                'queue_number' => 2,
                'patient_id' => $patients->get(1)->id,
                'initial_complaint' => 'ໄອ ແລະ ເຈັບໜ້າເອິກ',
                'queue_status' => 'Lab_Testing',
                'services' => [
                    ['service_id' => 1, 'priority' => 1], // ກວດທົ່ວໄປ
                    ['service_id' => 3, 'priority' => 2], // X-Ray
                ],
                'medicines' => [], // ຍັງບໍ່ສັ່ງຢາ
            ],

            // ຄິວທີ 3: ຍັງລໍຖ້າກວດເບື້ອງຕົ້ນ
            [
                'queue_number' => 3,
                'patient_id' => $patients->get(2)->id,
                'initial_complaint' => 'ປວດທ້ອງ',
                'queue_status' => 'Registered',
                'services' => [
                    ['service_id' => 1, 'priority' => 1], // ກວດທົ່ວໄປ
                ],
                'medicines' => [], // ຍັງບໍ່ສັ່ງຢາ
            ]
        ];

        foreach ($queueData as $data) {
            // 1. ສ້າງຄິວ
            $queue = Queue::create([
                'patient_id' => $data['patient_id'],
                'queue_number' => $data['queue_number'],
                'queue_date' => now()->format('Y-m-d'),
                'initial_complaint' => $data['initial_complaint'],
                'doctor_id' => $doctors->first()->id,
                'queue_status' => $data['queue_status'],
                'priority_level' => 'Normal',
                'created_by' => $receptionist->id,
                'vital_checked_at' => in_array($data['queue_status'], ['Vital_Checked', 'With_Doctor', 'Lab_Testing', 'Results_Ready', 'Completed']) ? now()->subHours(2) : null,
                'doctor_start_at' => in_array($data['queue_status'], ['With_Doctor', 'Lab_Testing', 'Results_Ready', 'Completed']) ? now()->subHours(1) : null,
                'completed_at' => $data['queue_status'] === 'Completed' ? now()->subMinutes(30) : null,
            ]);

            $this->command->info("📝 Created Queue #{$data['queue_number']} - {$data['queue_status']}");

            // 2. ສ້າງ Vital Signs (ຖ້າກວດເບື້ອງຕົ້ນແລ້ວ)
            if ($queue->vital_checked_at) {
                VitalSign::create([
                    'queue_id' => $queue->id,
                    'temperature' => fake()->randomFloat(1, 36.0, 38.5),
                    'weight' => fake()->randomFloat(2, 50, 80),
                    'height' => fake()->randomFloat(2, 150, 170),
                    'blood_pressure_sys' => fake()->numberBetween(110, 130),
                    'blood_pressure_dia' => fake()->numberBetween(70, 85),
                    'heart_rate' => fake()->numberBetween(70, 90),
                    'recorded_by' => $nurses->id,
                ]);
            }

            $serviceTotal = 0;

            // 3. ສ້າງ Queue Services
            foreach ($data['services'] as $serviceData) {
                if (!isset($services[$serviceData['service_id'] - 1])) continue;
                
                $service = $services[$serviceData['service_id'] - 1];
                $serviceTotal += $service->base_price;

                $isCompleted = $data['queue_status'] === 'Completed';
                $isInProgress = in_array($data['queue_status'], ['Lab_Testing', 'Results_Ready']);

                $queueService = QueueService::create([
                    'queue_id' => $queue->id,
                    'service_id' => $service->id,
                    'added_by' => $receptionist->id,
                    'service_status' => $isCompleted ? 'Completed' : ($isInProgress ? 'In_Progress' : 'Added'),
                    'priority_order' => $serviceData['priority'],
                    'assigned_to' => $doctors->first()->id,
                    'scheduled_at' => $queue->doctor_start_at,
                    'started_at' => $queue->doctor_start_at,
                    'completed_at' => $isCompleted ? now()->subMinutes(45) : null,
                ]);

                // 4. ສ້າງ Treatment
                if ($queue->doctor_start_at) {
                    Treatment::create([
                        'queue_service_id' => $queueService->id,
                        'room_id' => 1, // ຫ້ອງກວດທົ່ວໄປ
                        'performed_by' => $doctors->first()->id,
                        'treatment_started_at' => $queue->doctor_start_at,
                        'treatment_ended_at' => $isCompleted ? now()->subMinutes(45) : null,
                        'examination_notes' => "ກວດພົບອາການ: {$data['initial_complaint']}",
                        'findings' => 'ອາການເບົາໆ ບໍ່ຮ້າຍແຮງ',
                        'recommendations' => 'ພັກຜ່ອນ ແລະ ກິນຢາຕາມເວລາ',
                        'status' => $isCompleted ? 'Completed' : 'In_Progress',
                    ]);
                }

                // 5. ສ້າງ Lab Results (ຖ້າບໍລິການມີຜົນກວດ)
                if ($service->has_lab_result && $queue->doctor_start_at) {
                    $isLabCompleted = $data['queue_status'] === 'Completed';
                    
                    Lab::create([
                        'queue_service_id' => $queueService->id,
                        'lab_code' => 'LAB' . date('Ymd') . str_pad($queue->queue_number * 10 + $serviceData['priority'], 3, '0', STR_PAD_LEFT),
                        'test_results' => [
                            'parameter1' => fake()->numberBetween(10, 15),
                            'parameter2' => fake()->numberBetween(4000, 8000),
                            'conclusion' => 'ຜົນການກວດໃນເກນປົກກະຕິ'
                        ],
                        'result_summary' => 'ຜົນການກວດປົກກະຕິ',
                        'reference_values' => 'ຕາມມາດຕະຖານການແພດ',
                        'interpretation' => 'ບໍ່ມີຄວາມຜິດປົກກະຕິ',
                        'performed_by' => $nurses->id,
                        'performed_at' => now()->subHour(),
                        'reviewed_by_doctor_id' => $isLabCompleted ? $doctors->first()->id : null,
                        'reviewed_at' => $isLabCompleted ? now()->subMinutes(30) : null,
                        'patient_notified' => $isLabCompleted,
                        'notified_at' => $isLabCompleted ? now()->subMinutes(20) : null,
                        'lab_status' => $isLabCompleted ? 'Patient_Notified' : 'Completed',
                    ]);
                }
            }

            // 6. ສ້າງ Prescriptions (ຖ້າມີການສັ່ງຢາ)
            $medicineTotal = 0;
            foreach ($data['medicines'] as $medicineData) {
                if (!isset($medicines[$medicineData['medicine_id'] - 1])) continue;
                
                $medicine = $medicines[$medicineData['medicine_id'] - 1];
                $totalPrice = $medicineData['quantity'] * $medicine->unit_price;
                $medicineTotal += $totalPrice;

                $isDispensed = $data['queue_status'] === 'Completed';

                Prescription::create([
                    'queue_id' => $queue->id,
                    'medicine_id' => $medicine->id,
                    'dosage' => '1 ແຜັດ',
                    'frequency' => 'ວັນລະ 3 ເທື່ອ',
                    'duration' => '5 ວັນ',
                    'quantity' => $medicineData['quantity'],
                    'instructions' => 'ກິນຫຼັງອາຫານ, ດື່ມນ້ຳຫຼາຍໆ',
                    'prescribed_by_id' => $doctors->first()->id,
                    'dispensed_by_id' => $isDispensed ? $nurses->id : null,
                    'dispensed_quantity' => $isDispensed ? $medicineData['quantity'] : null,
                    'dispensed_at' => $isDispensed ? now()->subMinutes(15) : null,
                    'unit_price' => $medicine->unit_price,
                    'total_price' => $totalPrice,
                    'status' => $isDispensed ? 'Dispensed' : 'Prescribed',
                ]);
            }

            // 7. ສ້າງ Payment (ຖ້າສຳເລັດແລ້ວ)
            if ($data['queue_status'] === 'Completed') {
                $subtotal = $serviceTotal + $medicineTotal;
                $finalAmount = $subtotal;

                Payment::create([
                    'queue_id' => $queue->id,
                    'service_total' => $serviceTotal,
                    'medicine_total' => $medicineTotal,
                    'subtotal' => $subtotal,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'final_amount' => $finalAmount,
                    'payment_method' => 'Cash',
                    'payment_status' => 'Paid',
                    'paid_at' => now()->subMinutes(10),
                    'received_by' => $receptionist->id,
                    'receipt_number' => 'REC' . date('Ymd') . str_pad($data['queue_number'], 3, '0', STR_PAD_LEFT),
                ]);
            }
        }

        $this->command->info('✅ Created 3 demo queues with different statuses');
        $this->command->info('📋 Queue Summary:');
        $this->command->info('   - Queue #1: ສຳເລັດແລ້ວ (Completed)');
        $this->command->info('   - Queue #2: ລໍຖ້າຜົນກວດ (Lab_Testing)');
        $this->command->info('   - Queue #3: ຍັງບໍ່ກວດ (Registered)');
    }
}