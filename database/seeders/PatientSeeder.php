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

        $receptionist = User::where('role', 'receptionist')->first();

        $patients = [
            [
                'patient_code' => 'P0001',
                'first_name' => 'ສົມຊາຍ',
                'last_name' => 'ວົງສະຫວັນ',
                'gender' => 'Male',
                'birth_date' => '1980-05-15',
                'phone' => '020-11111111',
                'email' => null,
                'address' => 'ບ້ານ ນາຫຼວງ, ເມືອງ ຈັນທະບູລີ, ນະຄອນຫຼວງວຽງຈັນ',
                'village' => 'ນາຫຼວງ',
                'district' => 'ຈັນທະບູລີ',
                'province' => 'ນະຄອນຫຼວງວຽງຈັນ',
                'emergency_contact_name' => 'ນາງ ບຸນມີ ວົງສະຫວັນ',
                'emergency_contact_phone' => '020-11111112',
                'emergency_contact_relationship' => 'ພັນລະຍາ',
                'medical_history' => 'ມີປະຫວັດຄວາມດັນໂລຫິດສູງ',
                'allergies' => 'ແພ້ຢາ Penicillin',
                'chronic_conditions' => 'ໂລກຄວາມດັນສູງ',
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
                'email' => 'buntid@email.com',
                'address' => 'ບ້ານ ໂພນໃໝ່, ເມືອງ ໄຊທານີ, ນະຄອນຫຼວງວຽງຈັນ',
                'village' => 'ໂພນໃໝ່',
                'district' => 'ໄຊທານີ',
                'province' => 'ນະຄອນຫຼວງວຽງຈັນ',
                'emergency_contact_name' => 'ທ້າວ ວິໄລ ພອນວິເສດ',
                'emergency_contact_phone' => '020-22222223',
                'emergency_contact_relationship' => 'ສາມີ',
                'medical_history' => 'ບໍ່ມີປະຫວັດການປ່ວຍຮ້າຍແຮງ',
                'allergies' => null,
                'chronic_conditions' => null,
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
                'email' => null,
                'address' => 'ບ້ານ ດົງໂດກ, ເມືອງ ສີໂຄດຕະບອງ, ນະຄອນຫຼວງວຽງຈັນ',
                'village' => 'ດົງໂດກ',
                'district' => 'ສີໂຄດຕະບອງ',
                'province' => 'ນະຄອນຫຼວງວຽງຈັນ',
                'emergency_contact_name' => 'ນາງ ມານີ ສີວິລາຍ',
                'emergency_contact_phone' => '020-33333333',
                'emergency_contact_relationship' => 'ແມ່',
                'medical_history' => 'ເດັກປົກກະຕິ, ໄດ້ຮັບວັກຊີນຄົບຖ້ວນ',
                'allergies' => null,
                'chronic_conditions' => null,
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
                'email' => null,
                'address' => 'ບ້ານ ຫາດຊາຍ, ເມືອງ ຫາດຊາຍໂຝນ, ນະຄອນຫຼວງວຽງຈັນ',
                'village' => 'ຫາດຊາຍ',
                'district' => 'ຫາດຊາຍໂຝນ',
                'province' => 'ນະຄອນຫຼວງວຽງຈັນ',
                'emergency_contact_name' => 'ທ້າວ ບຸນສົມ ອິນທະວົງ',
                'emergency_contact_phone' => '020-44444445',
                'emergency_contact_relationship' => 'ສາມີ',
                'medical_history' => 'ມີປະຫວັດເບົາຫວານ, ຄວາມດັນສູງ',
                'allergies' => 'ແພ້ອາຫານທະເລ',
                'chronic_conditions' => 'ເບົາຫວານ, ຄວາມດັນສູງ',
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
                'email' => 'anupab@email.com',
                'address' => 'ບ້ານ ຊຽງຍືນ, ເມືອງ ຊຽງຍືນ, ນະຄອນຫຼວງວຽງຈັນ',
                'village' => 'ຊຽງຍືນ',
                'district' => 'ຊຽງຍືນ',
                'province' => 'ນະຄອນຫຼວງວຽງຈັນ',
                'emergency_contact_name' => 'ນາງ ນິດດາ ລາວັນ',
                'emergency_contact_phone' => '020-55555556',
                'emergency_contact_relationship' => 'ພັນລະຍາ',
                'medical_history' => 'ມີປະຫວັດປວດຫຼັງ',
                'allergies' => null,
                'chronic_conditions' => null,
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
