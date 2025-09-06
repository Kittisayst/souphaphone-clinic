<?php

namespace Database\Seeders;

use App\Models\MedicationInstruction;
use App\Models\Medicine;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicationInstructionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $this->command->info('🔄 Creating MedicationInstruction demo data...');

       // ດຶງ Treatment ທີ່ສຳເລັດແລ້ວ
        $treatment = Treatment::where('status', 'Completed')->first();
        $medicines = Medicine::limit(2)->get();
        $doctor = User::where('role', 'doctor')->first();
        $nurse = User::where('role', 'nurse')->first();

        if (!$treatment || $medicines->isEmpty() || !$doctor) {
            $this->command->warn('ຕ້ອງມີຂໍ້ມູນ Treatment, Medicine ແລະ Doctor ກ່ອນ');
            return;
        }

        // ຢາທີ 1: Paracetamol - ຈ່າຍແລ້ວ
        MedicationInstruction::create([
            'treatment_id' => $treatment->id,
            'medicine_id' => $medicines[0]->id,
            'dosage' => '500mg',
            'frequency' => 'ວັນລະ 3 ເທື່ອ',
            'duration' => '5 ວັນ',
            'quantity' => 15.00, // 5 ວັນ × 3 ເທື່ອ
            'instructions' => 'ກິນຫຼັງອາຫານ, ດື່ມນ້ຳຫຼາຍໆ',
            'unit_price' => 500.00, // 500 ກີບຕໍ່ແຜັດ
            'total_price' => 7500.00, // 15 × 500
            'prescribed_by' => $doctor->id,
            'prescribed_at' => now()->subHours(1),
            'dispensed_by' => $nurse?->id ?? $doctor->id,
            'dispensed_quantity' => 15.00,
            'dispensed_at' => now()->subMinutes(30),
            'status' => 'Dispensed',
            'created_at' => now()->subHours(1),
            'updated_at' => now()->subMinutes(30),
        ]);

        // ຢາທີ 2: ຢາແກ້ໄອ - ສັ່ງແລ້ວແຕ່ຍັງບໍ່ຈ່າຍ
        if ($medicines->count() > 1) {
            MedicationInstruction::create([
                'treatment_id' => $treatment->id,
                'medicine_id' => $medicines[1]->id,
                'dosage' => '10ml',
                'frequency' => 'ວັນລະ 3 ເທື່ອ',
                'duration' => '3 ວັນ',
                'quantity' => 100.00, // 1 ຂວດ 100ml
                'instructions' => 'ກິນກ່ອນນອນ, ບໍ່ຄວນຂັບລົດ',
                'unit_price' => 15000.00, // 15,000 ກີບຕໍ່ຂວດ
                'total_price' => 15000.00,
                'prescribed_by' => $doctor->id,
                'prescribed_at' => now()->subHours(1),
                'status' => 'Prescribed',
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ]);
        }

        $this->command->info('✅ ສ້າງຂໍ້ມູນ Medication Instruction ສຳເລັດ: 2 ລາຍການ');
    }

    
}
