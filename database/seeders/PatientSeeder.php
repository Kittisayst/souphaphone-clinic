<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 ສ້າງຂໍ້ມູນຄົນໄຂ້...');

        $receptionist = User::where('role', 'cashier')->first();

        $patients = [
            [
                'patient_code' => 'P0001',
                'first_name' => 'ສົມຊາຍ',
                'last_name' => 'ວົງສະຫວັນ',
                'gender' => 'Male',
                'birth_date' => '1980-05-15',
                'phone' => '020-11111111',
                'address' => 'ບ້ານ ນາຫຼວງ, ເມືອງ ຈັນທະບູລີ, ນະຄອນຫຼວງວຽງຈັນ',
                'allergies' => 'ແພ້ຢາ Penicillin',
                'is_active' => true,
                'created_by' => $receptionist->id,
            ],
            [
                'patient_code' => 'P0002',
                'first_name' => 'ນາງ ບຸນທິດ',
                'last_name' => 'ພອນວິເສດ',
                'gender' => 'Female',
                'birth_date' => '1992-08-20',
                'phone' => '020-22222222',
                'address' => 'ບ້ານ ໂພນໃໝ່, ເມືອງ ໄຊທານີ, ນະຄອນຫຼວງວຽງຈັນ',
                'allergies' => null,
                'is_active' => true,
                'created_by' => $receptionist->id,
            ],
            [
                'patient_code' => 'P0003',
                'first_name' => 'ເດັກຊາຍ ບຸນມີ',
                'last_name' => 'ສີວິລາຍ',
                'gender' => 'Male',
                'birth_date' => '2015-03-10',
                'phone' => '020-33333333',
                'address' => 'ບ້ານ ດົງໂດກ, ເມືອງ ສີໂຄດຕະບອງ, ນະຄອນຫຼວງວຽງຈັນ',
                'allergies' => null,
                'is_active' => true,
                'created_by' => $receptionist->id,
            ],
            [
                'patient_code' => 'P0004',
                'first_name' => 'ຜູ້ຍິງ ຄຳພອນ',
                'last_name' => 'ອິນທະວົງ',
                'gender' => 'Female',
                'birth_date' => '1965-12-05',
                'phone' => '020-44444444',
                'address' => 'ບ້ານ ຫາດຊາຍ, ເມືອງ ຫາດຊາຍໂຝນ, ນະຄອນຫຼວງວຽງຈັນ',
                'allergies' => 'ແພ້ອາຫານທະເລ',
                'is_active' => true,
                'created_by' => $receptionist->id,
            ],
            [
                'patient_code' => 'P0005',
                'first_name' => 'ທ້າວ ອານຸພາບ',
                'last_name' => 'ລາວັນ',
                'gender' => 'Male',
                'birth_date' => '1975-07-25',
                'phone' => '020-55555555',
                'address' => 'ບ້ານ ຊຽງຍືນ, ເມືອງ ຊຽງຍືນ, ນະຄອນຫຼວງວຽງຈັນ',
                'allergies' => null,
                'is_active' => true,
                'created_by' => $receptionist->id,
            ],
        ];

        foreach ($patients as $patientData) {
            Patient::create($patientData);
        }

        $this->command->info("✅ ສ້າງຄົນໄຂ້: " . count($patients) . " ຄົນ");
    }
}
