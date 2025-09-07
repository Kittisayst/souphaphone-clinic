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
                'name' => 'ແອັດມິນ ລະບົບ',
                'email' => 'admin@clinic.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '020-12345678',
                'is_active' => true,
            ],

            // Doctors
            [
                'name' => 'ສົມພອນ ວົງດວງ',
                'email' => 'doctor1@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'doctor',
                'phone' => '020-11111111',
                'is_active' => true,
            ],
            [
                'name' => 'ບຸນມີ ເພັງໄຊ',
                'email' => 'doctor2@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'doctor',
                'phone' => '020-22222222',
                'is_active' => true,
            ],

            // Nurses
            [
                'name' => 'ຄຳດວງ ໄຊຍະວົງ',
                'email' => 'nurse1@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'nurse',
                'phone' => '020-33333333',
                'is_active' => true,
            ],
            [
                'name' => 'ສີ ຈັນທະໂວງ',
                'email' => 'nurse2@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'nurse',
                'phone' => '020-44444444',
                'is_active' => true,
            ],

            // Cashier
            [
                'name' => 'ມານີ ພອນວິເສດ',
                'email' => 'cashier@clinic.la',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'phone' => '020-77777777',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info("✅ ສ້າງຜູ້ໃຊ້ລະບົບ: " . count($users) . " ຄົນ");
    }
}