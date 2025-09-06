<?php

namespace Database\Seeders;

use App\Models\MedicationInstruction;
use App\Models\Medicine;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $this->command->info('💊 ສ້າງຂໍ້ມູນການສັ່ງຢາ...');

        $treatments = Treatment::all();
        $medicines = Medicine::all();
        $doctors = User::where('role', 'doctor')->get();
        $pharmacist = User::where('role', 'pharmacist')->first();

        $medications = [];

        foreach ($treatments as $treatment) {
            $queue = $treatment->getQueue();
            
            // ສັ່ງຢາສຳລັບແຕ່ລະກໍລະນີ
            switch ($queue->id) {
                case 1: // ໄຂ້ຫວັດ - ສຳເລັດແລ້ວ
                    $medications = array_merge($medications, [
                        [
                            'treatment_id' => $treatment->id,
                            'medicine_id' => $medicines->where('medicine_name', 'Paracetamol 500mg')->first()->id,
                            'dosage' => '1 ເມັດ',
                            'frequency' => 'ວັນລະ 3 ເທື່ອ',
                            'duration' => '5 ວັນ',
                            'total_quantity' => 15,
                            'administration_route' => 'ກິນ',
                            'special_instructions' => 'ກິນຫຼັງອາຫານ',
                            'unit_price' => 500,
                            'total_price' => 7500,
                            'dispensing_status' => 'Dispensed',
                            'prescribed_by' => $doctors[0]->id,
                            'dispensed_by' => $pharmacist->id,
                            'dispensed_at' => now()->subHours(1),
                            'created_at' => now()->subHours(3),
                        ],
                        [
                            'treatment_id' => $treatment->id,
                            'medicine_id' => $medicines->where('medicine_name', 'Cough Syrup')->first()->id,
                            'dosage' => '10ml',
                            'frequency' => 'ວັນລະ 3 ເທື່ອ',
                            'duration' => '5 ວັນ',
                            'total_quantity' => 150,
                            'administration_route' => 'ກິນ',
                            'special_instructions' => 'ສັ່ນກ່ອນດື່ມ',
                            'unit_price' => 150,
                            'total_price' => 22500,
                            'dispensing_status' => 'Dispensed',
                            'prescribed_by' => $doctors[0]->id,
                            'dispensed_by' => $pharmacist->id,
                            'dispensed_at' => now()->subHours(1),
                            'created_at' => now()->subHours(3),
                        ],
                    ]);
                    break;

                case 2: // ລໍຖ້າຜົນ Lab
                    // ຍັງບໍ່ສັ່ງຢາ ລໍຖ້າຜົນກວດກ່ອນ
                    break;

                case 3: // ເດັກ - ກຳລັງກວດ
                    $medications[] = [
                        'treatment_id' => $treatment->id,
                        'medicine_id' => $medicines->where('medicine_name', 'Paracetamol 500mg')->first()->id,
                        'dosage' => '1/2 ເມັດ',
                        'frequency' => 'ວັນລະ 2 ເທື່ອ',
                        'duration' => '3 ວັນ',
                        'total_quantity' => 3,
                        'administration_route' => 'ກິນ',
                        'special_instructions' => 'ບີບແລ້ວປົນນ້ຳໃຫ້ເດັກດື່ມ',
                        'warnings' => 'ຖ້າໄຂ້ບໍ່ລົງໃຫ້ພາມາໂຮງໝໍ',
                        'unit_price' => 500,
                        'total_price' => 1500,
                        'dispensing_status' => 'Prescribed',
                        'prescribed_by' => $doctors[1]->id,
                        'created_at' => now()->subMinutes(20),
                    ];
                    break;

                case 4: // ຄວາມດັນສູງ, ເບົາຫວານ
                    $medications = array_merge($medications, [
                        [
                            'treatment_id' => $treatment->id,
                            'medicine_id' => $medicines->where('medicine_name', 'Amlodipine 5mg')->first()->id,
                            'dosage' => '1 ເມັດ',
                            'frequency' => 'ວັນລະ 1 ເທື່ອ',
                            'duration' => '1 ເດືອນ',
                            'total_quantity' => 30,
                            'administration_route' => 'ກິນ',
                            'special_instructions' => 'ກິນຕອນເຊົ້າ ກ່ອນອາຫານ',
                            'warnings' => 'ຫ້າມຢຸດກິນກະທັນຫັນ',
                            'unit_price' => 1500,
                            'total_price' => 45000,
                            'dispensing_status' => 'Prescribed',
                            'prescribed_by' => $doctors[0]->id,
                            'created_at' => now()->subMinutes(10),
                        ],
                        [
                            'treatment_id' => $treatment->id,
                            'medicine_id' => $medicines->where('medicine_name', 'Metformin 500mg')->first()->id,
                            'dosage' => '1 ເມັດ',
                            'frequency' => 'ວັນລະ 2 ເທື່ອ',
                            'duration' => '1 ເດືອນ',
                            'total_quantity' => 60,
                            'administration_route' => 'ກິນ',
                            'special_instructions' => 'ກິນກັບອາຫານ ເຊົ້າ-ແລງ',
                            'warnings' => 'ຖ້າມີອາການຄື່ນເຫຍື່ອໃຫ້ແຈ້ງແພດ',
                            'unit_price' => 1000,
                            'total_price' => 60000,
                            'dispensing_status' => 'Prescribed',
                            'prescribed_by' => $doctors[0]->id,
                            'created_at' => now()->subMinutes(10),
                        ],
                    ]);
                    break;
            }
        }

        foreach ($medications as $medicationData) {
            MedicationInstruction::create($medicationData);
        }

        $this->command->info("✅ ສ້າງການສັ່ງຢາ: " . count($medications) . " ລາຍການ");
    }

    
}
