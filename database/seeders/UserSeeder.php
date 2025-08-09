<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'ຜູ້ຄຸ້ມຄອງລະບົບ',
            'email' => 'admin@clinic.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'permissions' => null, // Admin ມີສິດທັງໝົດ
            'is_active' => true,
        ]);

        // Doctor User
        User::create([
            'name' => 'ທ່ານໝໍ ສົມຊາຍ',
            'email' => 'doctor@clinic.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'permissions' => [
                'manage_patients',
                'manage_examinations',
                'view_reports'
            ],
            'is_active' => true,
        ]);

        // Nurse User
        User::create([
            'name' => 'ພະຍາບານ ສົມຍິງ',
            'email' => 'nurse@clinic.com',
            'password' => Hash::make('password'),
            'role' => 'nurse',
            'permissions' => [
                'manage_patients',
                'manage_queues',
                'manage_examinations'
            ],
            'is_active' => true,
        ]);

        // Cashier User
        User::create([
            'name' => 'ເຄົາເຕີ ສົມປອງ',
            'email' => 'cashier@clinic.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'permissions' => [
                'manage_patients',
                'manage_queues',
                'manage_billing'
            ],
            'is_active' => true,
        ]);
    }
}
