<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Creating demo patients...');

        // ສ້າງຄົນໄຂ້ທົດລອງບາງຄົນ (ສຳລັບການທົດສອບ)
        $demoPatients = [
            [
                'patient_code' => 'P00001',
                'first_name' => 'ສົມຊາຍ',
                'last_name' => 'ວົງສະຫວັນ',
                'date_of_birth' => '1980-05-15',
                'gender' => 'M',
                'phone_number' => '020 5555 1001',
                'address' => 'ບ້ານໜອງຄາຍ, ເມືອງໄຊເສດຖາ, ນະຄອນຫລວງວຽງຈັນ',
                'emergency_contact' => 'ພັນລະຍາ',
                'emergency_phone' => '020 5555 1002',
                'blood_type' => 'O+',
                'allergies' => null,
                'medical_history' => null,
            ],
            [
                'patient_code' => 'P00002',
                'first_name' => 'ນາງສົມໃຈ',
                'last_name' => 'ພອນສະຫວັນ',
                'date_of_birth' => '1985-08-22',
                'gender' => 'F',
                'phone_number' => '020 5555 2001',
                'address' => 'ບ້ານນາຄຳ, ເມືອງຈັນທະບູລີ, ນະຄອນຫລວງວຽງຈັນ',
                'emergency_contact' => 'ສາມີ',
                'emergency_phone' => '020 5555 2002',
                'blood_type' => 'A+',
                'allergies' => 'ແພ້ Penicillin',
                'medical_history' => 'ເຄີຍເປັນເບົາວານຕອນຖືພາ',
            ],
            [
                'patient_code' => 'P00003',
                'first_name' => 'ເດັກຍິງນ້ອງມານີ',
                'last_name' => 'ບຸນນາງ',
                'date_of_birth' => '2015-12-10',
                'gender' => 'F',
                'phone_number' => '020 5555 3001',
                'address' => 'ບ້ານດົງມະຟື, ເມືອງສີໂຄດຕະບອງ, ນະຄອນຫລວງວຽງຈັນ',
                'emergency_contact' => 'ແມ່',
                'emergency_phone' => '020 5555 3002',
                'blood_type' => 'B+',
                'allergies' => null,
                'medical_history' => 'ເກີດປົກກະຕິ, ສັກຢາຄົບຖ້ວນ',
            ]
        ];

        foreach ($demoPatients as $patient) {
            Patient::create($patient);
        }

        $this->command->info('✅ Created demo patients successfully');
    }
}
