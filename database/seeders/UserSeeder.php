<?php

// =================================================================================
// database/seeders/UserSeeder.php
// UserSeeder ທີ່ປັບປຸງໃໝ່ຕາມໂຄງສ້າງ Migration ທີ່ປ່ຽນແປງ
// =================================================================================

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
       $this->command->info('👥 ສ້າງຂໍ້ມູນຜູ້ໃຊ້ລະບົບ...');

        $users = [
            // Admin
            [
                'employee_code' => 'ADM001',
                'name' => 'ແອັດມິນ ລະບົບ',
                'email' => 'admin@clinic.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '020-12345678',
                'gender' => 'Male',
                'birth_date' => '1985-01-01',
                'is_active' => true,
            ],
            
            // Doctors
            [
                'employee_code' => 'DOC001',
                'name' => 'ທ່ານໝໍ ສົມພອນ ວົງດວງ',
                'email' => 'doctor1@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'doctor',
                'phone' => '020-11111111',
                'gender' => 'Male',
                'birth_date' => '1975-05-15',
                'license_number' => 'MD001',
                'specializations' => ['ແພດທົ່ວໄປ', 'ແພດພາຍໃນ'],
                'is_active' => true,
            ],
            [
                'employee_code' => 'DOC002',
                'name' => 'ທ່ານໝໍຍິງ ບຸນມີ ເພັງໄຊ',
                'email' => 'doctor2@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'doctor',
                'phone' => '020-22222222',
                'gender' => 'Female',
                'birth_date' => '1980-08-20',
                'license_number' => 'MD002',
                'specializations' => ['ແພດສູຕິ-ນາລີ', 'ແພດເດັກ'],
                'is_active' => true,
            ],
            
            // Nurses
            [
                'employee_code' => 'NUR001',
                'name' => 'ພະຍາບານ ຄຳດວງ ໄຊຍະວົງ',
                'email' => 'nurse1@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'nurse',
                'phone' => '020-33333333',
                'gender' => 'Female',
                'birth_date' => '1990-03-10',
                'is_active' => true,
            ],
            [
                'employee_code' => 'NUR002',
                'name' => 'ພະຍາບານ ນາງສີ ຈັນທະໂວງ',
                'email' => 'nurse2@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'nurse',
                'phone' => '020-44444444',
                'gender' => 'Female',
                'birth_date' => '1992-07-25',
                'is_active' => true,
            ],
            
            // Technicians
            [
                'employee_code' => 'TEC001',
                'name' => 'ເຕັກນິກ ພອນ ວັນນະລາດ',
                'email' => 'tech1@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'technician',
                'phone' => '020-55555555',
                'gender' => 'Male',
                'birth_date' => '1988-12-05',
                'is_active' => true,
            ],
            
            // Pharmacist
            [
                'employee_code' => 'PHA001',
                'name' => 'ເ຀ມີ ວິໄລ ບຸນເລືອງ',
                'email' => 'pharmacist@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'pharmacist',
                'phone' => '020-66666666',
                'gender' => 'Female',
                'birth_date' => '1985-09-18',
                'is_active' => true,
            ],
            
            // Cashier
            [
                'employee_code' => 'CAS001',
                'name' => 'ເກັບເງິນ ມານີ ພອນວິເສດ',
                'email' => 'cashier@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'phone' => '020-77777777',
                'gender' => 'Female',
                'birth_date' => '1995-02-14',
                'is_active' => true,
            ],
            
            // Receptionist
            [
                'employee_code' => 'REC001',
                'name' => 'ຮັບບ້ານ ແສງດາວ ຈັນທະລາ',
                'email' => 'receptionist@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'receptionist',
                'phone' => '020-88888888',
                'gender' => 'Female',
                'birth_date' => '1993-11-30',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info("✅ ສ້າງຜູ້ໃຊ້ລະບົບ: " . count($users) . " ຄົນ");
    }
}