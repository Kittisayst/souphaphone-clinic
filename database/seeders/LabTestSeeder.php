<?php

namespace Database\Seeders;

use App\Models\Lab;
use App\Models\LabTest;
use App\Models\QueueService;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creating lab results demo data...');

        // ດຶງ Treatment ທີ່ລໍຖ້າຜົນ Lab
        $treatment = Treatment::where('status', 'Waiting_Lab_Results')->first();
        $doctor = User::where('role', 'doctor')->first();
        $technician = User::where('role', 'technician')->first();

        if (!$treatment || !$doctor) {
            $this->command->warn('ຕ້ອງມີຂໍ້ມູນ Treatment (Waiting_Lab_Results) ແລະ Doctor ກ່ອນ');
            return;
        }

        // Lab Test 1: CBC - ສຳເລັດແລ້ວ
        LabTest::create([
            'treatment_id' => $treatment->id,
            'lab_test_code' => 'CBC',
            'lab_test_name' => 'ການກວດເລືອດຄົບຊຸດ',
            'test_result_values' => [
                'WBC' => '7.2',
                'RBC' => '4.5',
                'Hb' => '13.8',
                'Hct' => '41.2',
                'Platelet' => '285'
            ],
            'reference_range' => 'WBC: 4.0-10.0, RBC: 4.0-5.5, Hb: 12.0-16.0',
            'abnormal_flag' => 'Normal',
            'interpretation' => 'ຜົນກວດເລືອດປົກກະຕິທຸກດ້ານ',
            'sample_type' => 'ເລືອດ',
            'sample_collected_at' => now()->subHours(4),
            'sample_collected_by' => $technician?->id ?? $doctor->id,
            'tested_at' => now()->subHours(2),
            'tested_by' => $technician?->id ?? $doctor->id,
            'reviewed_by' => $doctor->id,
            'reviewed_at' => now()->subMinutes(30),
            'technician_notes' => 'ຕົວຢ່າງດີ, ກວດແລ້ວບໍ່ມີບັນຫາ',
            'doctor_notes' => 'ຜົນປົກກະຕິ',
            'status' => 'Reviewed',
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subMinutes(30),
        ]);

        // Lab Test 2: FBS - ຍັງບໍ່ສຳເລັດ
        LabTest::create([
            'treatment_id' => $treatment->id,
            'lab_test_code' => 'FBS',
            'lab_test_name' => 'ການກວດນ້ຳຕານໃນເລືອດ',
            'sample_type' => 'ເລືອດ',
            'sample_collected_at' => now()->subHours(4),
            'sample_collected_by' => $technician?->id ?? $doctor->id,
            'tested_at' => now()->subHours(1),
            'tested_by' => $technician?->id ?? $doctor->id,
            'technician_notes' => 'ກຳລັງກວດຜົນ',
            'status' => 'Completed',
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subHours(1),
        ]);

        $this->command->info('✅ ສ້າງຂໍ້ມູນ Lab Test ສຳເລັດ: 2 ລາຍການ');
    }
}
