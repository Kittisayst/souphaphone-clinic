<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $this->command->info('🔄 Creating prescription demo data...');

        // ດຶງຂໍ້ມູນທີ່ຈຳເປັນ
        $queues = Queue::limit(10)->get();
        $medicines = Medicine::all();
        $doctors = User::where('role', 'doctor')->get();
        $nurses = User::where('role', 'nurse')->get();

        if ($queues->isEmpty() || $medicines->isEmpty() || $doctors->isEmpty()) {
            $this->command->error('❌ ກະລຸນາ run UserSeeder, MedicineSeeder, ແລະ ມີຂໍ້ມູນ Queue ກ່ອນ!');
            return;
        }

        // ໃບສັ່ງຢາແບບທົ່ວໄປ (Common Prescriptions)
        $commonPrescriptions = [
            // ໃບສັ່ງຢາສຳລັບອາການໄຂ້ + ປວດ
            [
                'condition' => 'ໄຂ້ + ປວດຫົວ',
                'medicines' => [
                    [
                        'medicine_name' => 'Paracetamol',
                        'dosage' => '1 ແຜັດ',
                        'frequency' => 'ວັນລະ 3 ເທື່ອ',
                        'duration' => '5 ວັນ',
                        'quantity' => 15,
                        'instructions' => 'ກິນຫຼັງອາຫານ, ດື່ມນ້ຳຫຼາຍໆ'
                    ],
                    [
                        'medicine_name' => 'Ibuprofen',
                        'dosage' => '1 ແຜັດ',
                        'frequency' => 'ວັນລະ 2 ເທື່ອ',
                        'duration' => '3 ວັນ',
                        'quantity' => 6,
                        'instructions' => 'ກິນເມື່ອເຈັບຫຼາຍ, ບໍ່ເກີນວັນລະ 2 ແຜັດ'
                    ]
                ]
            ],

            // ໃບສັ່ງຢາສຳລັບການຕິດເຊື້ອ
            [
                'condition' => 'ຕິດເຊື້ອ + ອັກເສບ',
                'medicines' => [
                    [
                        'medicine_name' => 'Amoxicillin',
                        'dosage' => '1 ແຄັບຊູນ',
                        'frequency' => 'ວັນລະ 3 ເທື່ອ',
                        'duration' => '7 ວັນ',
                        'quantity' => 21,
                        'instructions' => 'ກິນໃຫ້ຄົບ 7 ວັນ ເຖິງວ່າອາການດີແລ້ວກໍ່ຕາມ'
                    ],
                    [
                        'medicine_name' => 'Diclofenac',
                        'dosage' => '1 ແຜັດ',
                        'frequency' => 'ວັນລະ 2 ເທື່ອ',
                        'duration' => '5 ວັນ',
                        'quantity' => 10,
                        'instructions' => 'ກິນຫຼັງອາຫານເພື່ອປ້ອງກັນກະເພາະອາຫານ'
                    ]
                ]
            ],

            // ໃບສັ່ງຢາສຳລັບເດັກ
            [
                'condition' => 'ໄຂ້ໃນເດັກ',
                'medicines' => [
                    [
                        'medicine_name' => 'Paracetamol',
                        'dosage' => '1/2 ແຜັດ',
                        'frequency' => 'ວັນລະ 3 ເທື່ອ',
                        'duration' => '3 ວັນ',
                        'quantity' => 5,  // 1.5 ແຜັດ/ວັນ x 3 ວັນ
                        'instructions' => 'ບີບແຜັດໃຫ້ແຫຼວ ປົນກັບນ້ຳເລັກນ້ອຍ'
                    ],
                    [
                        'medicine_name' => 'Vitamin C',
                        'dosage' => '1 ແຜັດ',
                        'frequency' => 'ວັນລະ 1 ເທື່ອ',
                        'duration' => '7 ວັນ',
                        'quantity' => 7,
                        'instructions' => 'ກິນຫຼັງອາຫານເຊົ້າ'
                    ]
                ]
            ],

            // ໃບສັ່ງຢາສຳລັບຄວາມດັນສູງ
            [
                'condition' => 'ຄວາມດັນເລືອດສູງ',
                'medicines' => [
                    [
                        'medicine_name' => 'Amlodipine',
                        'dosage' => '1 ແຜັດ',
                        'frequency' => 'ວັນລະ 1 ເທື່ອ',
                        'duration' => '30 ວັນ',
                        'quantity' => 30,
                        'instructions' => 'ກິນຕອນເຊົ້າຂອງທຸກວັນໃນເວລາດຽວກັນ'
                    ]
                ]
            ],

            // ໃບສັ່ງຢາສຳລັບກະເພາະອາຫານ
            [
                'condition' => 'ກະເພາະອາຫານ + ຈຸກເສັ້ນ',
                'medicines' => [
                    [
                        'medicine_name' => 'Omeprazole',
                        'dosage' => '1 ແຄັບຊູນ',
                        'frequency' => 'ວັນລະ 1 ເທື່ອ',
                        'duration' => '14 ວັນ',
                        'quantity' => 14,
                        'instructions' => 'ກິນກ່ອນອາຫານເຊົ້າ 30 ນາທີ'
                    ],
                    [
                        'medicine_name' => 'Vitamin B-Complex',
                        'dosage' => '1 ແຜັດ',
                        'frequency' => 'ວັນລະ 1 ເທື່ອ',
                        'duration' => '30 ວັນ',
                        'quantity' => 30,
                        'instructions' => 'ກິນຫຼັງອາຫານເຊົ້າ'
                    ]
                ]
            ]
        ];

        // ສ້າງໃບສັ່ງຢາສຳລັບແຕ່ລະຄິວ
        foreach ($queues as $index => $queue) {
            // ເລືອກໃບສັ່ງຢາແບບ (ວົນຮອບ)
            $prescriptionTemplate = $commonPrescriptions[$index % count($commonPrescriptions)];
            
            $this->command->info("📋 Creating prescription for Queue #{$queue->queue_number} - {$prescriptionTemplate['condition']}");

            foreach ($prescriptionTemplate['medicines'] as $medicineData) {
                // ຊອກຫາຢາທີ່ກົງກັນ
                $medicine = $medicines->filter(function ($med) use ($medicineData) {
                    return strpos($med->medicine_name, $medicineData['medicine_name']) !== false;
                })->first();

                if (!$medicine) {
                    $this->command->warn("⚠️ ບໍ່ພົບຢາ: {$medicineData['medicine_name']}");
                    continue;
                }

                // ຄຳນວນລາຄາ
                $totalPrice = $medicineData['quantity'] * $medicine->unit_price;
                
                // ສ້າງໃບສັ່ງຢາ
                Prescription::create([
                    'queue_id' => $queue->id,
                    'medicine_id' => $medicine->id,
                    'dosage' => $medicineData['dosage'],
                    'frequency' => $medicineData['frequency'],
                    'duration' => $medicineData['duration'],
                    'quantity' => $medicineData['quantity'],
                    'instructions' => $medicineData['instructions'],
                    'prescribed_by' => $queue->assigned_doctor_id ?? $doctors->random()->id,
                    'dispensed_by' => $index < 7 ? $nurses->random()->id : null, // 7 ຄິວທຳອິດຈ່າຍຢາແລ້ວ
                    'dispensed_quantity' => $index < 7 ? $medicineData['quantity'] : null,
                    'dispensed_at' => $index < 7 ? now()->subMinutes(rand(10, 60)) : null,
                    'unit_price' => $medicine->unit_price,
                    'total_price' => $totalPrice,
                    'status' => $index < 7 ? 'Dispensed' : 'Prescribed',
                ]);

                // ອັບເດດສະຕ໋ອກຢາ (ຖ້າຈ່າຍແລ້ວ)
                if ($index < 7) {
                    $medicine->decrement('stock_quantity', $medicineData['quantity']);
                }
            }
        }

        // ເພີ່ມໃບສັ່ງຢາພິເສດບາງໃບ
        $this->createSpecialPrescriptions();

        $this->command->info('✅ Created prescriptions for all demo queues');
    }

    /**
     * ສ້າງໃບສັ່ງຢາພິເສດ (ກໍລະນີພິເສດຕ່າງໆ)
     */
    private function createSpecialPrescriptions(): void
    {
        $queues = Queue::limit(3)->get();
        $medicines = Medicine::all();
        $doctors = User::where('role', 'doctor')->get();
        $nurses = User::where('role', 'nurse')->get();

        // ກໍລະນີ 1: ໃບສັ່ງຢາຫຼາຍຊະນິດ (ຄົນໄຂ້ມີບັນຫາຫຼາຍຢ່າງ)
        if ($queue1 = $queues->get(0)) {
            $complexMedicines = [
                ['medicine' => 'Paracetamol', 'dosage' => '2 ແຜັດ', 'frequency' => 'ວັນລະ 3 ເທື່ອ', 'duration' => '7 ວັນ', 'quantity' => 42],
                ['medicine' => 'Amoxicillin', 'dosage' => '1 ແຄັບຊູນ', 'frequency' => 'ວັນລະ 3 ເທື່ອ', 'duration' => '10 ວັນ', 'quantity' => 30],
                ['medicine' => 'Omeprazole', 'dosage' => '1 ແຄັບຊູນ', 'frequency' => 'ວັນລະ 1 ເທື່ອ', 'duration' => '14 ວັນ', 'quantity' => 14],
                ['medicine' => 'Vitamin B-Complex', 'dosage' => '1 ແຜັດ', 'frequency' => 'ວັນລະ 1 ເທື່ອ', 'duration' => '30 ວັນ', 'quantity' => 30],
                ['medicine' => 'Betamethasone Cream', 'dosage' => 'ທາບາງໆ', 'frequency' => 'ວັນລະ 2 ເທື່ອ', 'duration' => '7 ວັນ', 'quantity' => 1],
            ];

            foreach ($complexMedicines as $medData) {
                $medicine = $medicines->filter(fn($m) => strpos($m->medicine_name, $medData['medicine']) !== false)->first();
                if ($medicine) {
                    Prescription::create([
                        'queue_id' => $queue1->id,
                        'medicine_id' => $medicine->id,
                        'dosage' => $medData['dosage'],
                        'frequency' => $medData['frequency'],
                        'duration' => $medData['duration'],
                        'quantity' => $medData['quantity'],
                        'instructions' => 'ຕາມໃບສັ່ງຢາ - ກິນໃຫ້ຄົບຖ້ວນ',
                        'prescribed_by' => $doctors->random()->id,
                        'dispensed_by' => $nurses->random()->id,
                        'dispensed_quantity' => $medData['quantity'],
                        'dispensed_at' => now()->subMinutes(30),
                        'unit_price' => $medicine->unit_price,
                        'total_price' => $medData['quantity'] * $medicine->unit_price,
                        'status' => 'Dispensed',
                    ]);
                }
            }
        }

        // ກໍລະນີ 2: ໃບສັ່ງຢາທີ່ຍັງບໍ່ຈ່າຍ (Pending)
        if ($queue2 = $queues->get(1)) {
            $pendingMedicines = [
                ['medicine' => 'Ciprofloxacin', 'dosage' => '1 ແຜັດ', 'frequency' => 'ວັນລະ 2 ເທື່ອ', 'duration' => '10 ວັນ', 'quantity' => 20],
                ['medicine' => 'Prednisolone', 'dosage' => '1 ແຜັດ', 'frequency' => 'ວັນລະ 1 ເທື່ອ', 'duration' => '5 ວັນ', 'quantity' => 5],
            ];

            foreach ($pendingMedicines as $medData) {
                $medicine = $medicines->filter(fn($m) => strpos($m->medicine_name, $medData['medicine']) !== false)->first();
                if ($medicine) {
                    Prescription::create([
                        'queue_id' => $queue2->id,
                        'medicine_id' => $medicine->id,
                        'dosage' => $medData['dosage'],
                        'frequency' => $medData['frequency'], 
                        'duration' => $medData['duration'],
                        'quantity' => $medData['quantity'],
                        'instructions' => 'ລໍຖ້າການຈ່າຍຢາ - ໃຫ້ມາຮັບໃນຕອນບ່າຍ',
                        'prescribed_by' => $doctors->random()->id,
                        'dispensed_by' => null,
                        'dispensed_quantity' => null,
                        'dispensed_at' => null,
                        'unit_price' => $medicine->unit_price,
                        'total_price' => $medData['quantity'] * $medicine->unit_price,
                        'status' => 'Prescribed', // ຍັງບໍ່ຈ່າຍ
                    ]);
                }
            }
        }

        // ກໍລະນີ 3: ໃບສັ່ງຢາທີ່ຈ່າຍບາງສ່ວນ (Partial Dispensing)
        if ($queue3 = $queues->get(2)) {
            $partialMedicine = $medicines->where('medicine_name', 'LIKE', '%Amoxicillin%')->first();
            if ($partialMedicine) {
                Prescription::create([
                    'queue_id' => $queue3->id,
                    'medicine_id' => $partialMedicine->id,
                    'dosage' => '1 ແຄັບຊູນ',
                    'frequency' => 'ວັນລະ 3 ເທື່ອ',
                    'duration' => '10 ວັນ',
                    'quantity' => 30, // ສັ່ງ 30 ແຄັບຊູນ
                    'instructions' => 'ກິນໃຫ້ຄົບ 10 ວັນ - ມາຮັບເພີ່ມເມື່ອໝົດ',
                    'prescribed_by' => $doctors->random()->id,
                    'dispensed_by' => $nurses->random()->id,
                    'dispensed_quantity' => 15, // ຈ່າຍໄດ້ 15 ແຄັບຊູນເທົ່ານັ້ນ (ສະຕ໋ອກບໍ່ພໍ)
                    'dispensed_at' => now()->subMinutes(45),
                    'unit_price' => $partialMedicine->unit_price,
                    'total_price' => 30 * $partialMedicine->unit_price, // ລາຄາຕາມທີ່ສັ່ງ
                    'status' => 'Dispensed', // ຈ່າຍແລ້ວ ແຕ່ບໍ່ຄົບ
                ]);
            }
        }

        // ກໍລະນີ 4: ໃບສັ່ງຢາທີ່ຍົກເລີກ
        if ($queue4 = $queues->get(3)) {
            $cancelledMedicine = $medicines->first();
            Prescription::create([
                'queue_id' => $queue4->id,
                'medicine_id' => $cancelledMedicine->id,
                'dosage' => '1 ແຜັດ',
                'frequency' => 'ວັນລະ 2 ເທື່ອ',
                'duration' => '5 ວັນ',
                'quantity' => 10,
                'instructions' => 'ຍົກເລີກເນື່ອງຈາກຄົນໄຂ້ແພ້ຢາ',
                'prescribed_by' => $doctors->random()->id,
                'dispensed_by' => null,
                'dispensed_quantity' => null,
                'dispensed_at' => null,
                'unit_price' => $cancelledMedicine->unit_price,
                'total_price' => 10 * $cancelledMedicine->unit_price,
                'status' => 'Cancelled', // ຍົກເລີກ
            ]);
        }

        // ສ້າງໃບສັ່ງຢາເພີ່ມດ້ວຍ Factory ສຳລັບຄິວອື່ນໆ
        $remainingQueues = Queue::whereNotIn('id', $queues->take(4)->pluck('id'))->limit(10)->get();
        
        foreach ($remainingQueues as $queue) {
            // ສຸ່ມຈຳນວນຢາ 1-4 ຊະນິດ
            $randomMedicines = $medicines->random(rand(1, 4));
            
            foreach ($randomMedicines as $medicine) {
                $quantity = fake()->numberBetween(5, 30);
                $isDispensed = fake()->boolean(80); // 80% ຈ່າຍແລ້ວ

                Prescription::create([
                    'queue_id' => $queue->id,
                    'medicine_id' => $medicine->id,
                    'dosage' => fake()->randomElement(['1 ແຜັດ', '2 ແຜັດ', '1/2 ແຜັດ', '1 ແຄັບຊູນ']),
                    'frequency' => fake()->randomElement(['ວັນລະ 1 ເທື່ອ', 'ວັນລະ 2 ເທື່ອ', 'ວັນລະ 3 ເທື່ອ']),
                    'duration' => fake()->randomElement(['3 ວັນ', '5 ວັນ', '7 ວັນ', '10 ວັນ', '14 ວັນ']),
                    'quantity' => $quantity,
                    'instructions' => fake()->randomElement([
                        'ກິນຫຼັງອາຫານ',
                        'ກິນກ່ອນອາຫານ 30 ນາທີ', 
                        'ກິນພ້ອມນ້ຳຫຼາຍໆ',
                        'ບໍ່ໃຫ້ດື່ມເຫຼົ້າ',
                        'ກິນໃຫ້ຄົບຕາມກຳນົດ'
                    ]),
                    'prescribed_by' => $queue->assigned_doctor_id ?? $doctors->random()->id,
                    'dispensed_by' => $isDispensed ? $nurses->random()->id : null,
                    'dispensed_quantity' => $isDispensed ? $quantity : null,
                    'dispensed_at' => $isDispensed ? now()->subMinutes(rand(10, 120)) : null,
                    'unit_price' => $medicine->unit_price,
                    'total_price' => $quantity * $medicine->unit_price,
                    'status' => $isDispensed ? 'Dispensed' : 'Prescribed',
                ]);
            }
        }

        // ສະຖິຕິ
        $totalPrescriptions = Prescription::count();
        $dispensedCount = Prescription::where('status', 'Dispensed')->count();
        $pendingCount = Prescription::where('status', 'Prescribed')->count();
        $cancelledCount = Prescription::where('status', 'Cancelled')->count();

        $this->command->info('📊 Prescription Statistics:');
        $this->command->info("   - ລວມໃບສັ່ງຢາ: {$totalPrescriptions} ໃບ");
        $this->command->info("   - ຈ່າຍແລ້ວ: {$dispensedCount} ໃບ");
        $this->command->info("   - ລໍຖ້າຈ່າຍ: {$pendingCount} ໃບ");  
        $this->command->info("   - ຍົກເລີກ: {$cancelledCount} ໃບ");
    }
}
