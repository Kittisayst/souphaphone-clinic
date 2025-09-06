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
        $this->command->info('🔄 Creating system users with new structure...');

        // 1. ສ້າງ Admin ຫຼັກ
        $admin = User::create([
            'name' => 'ແອັດມິນລະບົບ',
            'email' => 'admin@clinic.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'phone' => '020 5555 0001',
            'address' => 'ຄລີນິກສາຍຝົນ, ບ້ານໂນນສະຫວ່າງ',
            'license_number' => null,
            'specializations' => null,
        ]);
        $admin->assignDefaultPermissions();

        // 2. ສ້າງທ່ານໝໍ
        $doctors = [
            [
                'name' => 'ທ່ານໝໍສົມຊາຍ ວົງສະຫວັນ',
                'email' => 'doctor1@clinic.com', 
                'phone' => '020 9999 0001',
                'license_number' => 'LIC2024001',
                'specializations' => ['ແພດທົ່ວໄປ'],
            ],
            [
                'name' => 'ທ່ານໝໍສົມໃຈ ພອນສະຫວັນ',
                'email' => 'doctor2@clinic.com',
                'phone' => '020 9999 0002', 
                'license_number' => 'LIC2024002',
                'specializations' => ['ແພດເດັກ', 'ແພດທົ່ວໄປ'],
            ],
            [
                'name' => 'ທ່ານໝໍສົມຍິງ ຈັນທະວົງ',
                'email' => 'doctor3@clinic.com',
                'phone' => '020 9999 0003',
                'license_number' => 'LIC2024003',
                'specializations' => ['ແພດສູດຕິກ', 'ແພດທົ່ວໄປ'],
            ]
        ];

        foreach ($doctors as $doctorData) {
            $doctor = User::create(array_merge($doctorData, [
                'password' => Hash::make('doctor123'),
                'role' => 'doctor',
                'is_active' => true,
                'address' => 'ຄລີນິກສາຍຝົນ',
            ]));
            $doctor->assignDefaultPermissions();
            
            $this->command->info("👨‍⚕️ Created doctor: {$doctor->name}");
        }

        // 3. ສ້າງພະຍາບານ
        $nurses = [
            [
                'name' => 'ນາງສົມແສງ ບຸນນາງ',
                'email' => 'nurse1@clinic.com',
                'phone' => '020 8888 0001',
                'specializations' => ['ພະຍາບານທົ່ວໄປ'],
            ],
            [
                'name' => 'ນາງວັນທີ ສີມູນ', 
                'email' => 'nurse2@clinic.com',
                'phone' => '020 8888 0002',
                'specializations' => ['ພະຍາບານທົ່ວໄປ', 'ພະຍາບານເດັກ'],
            ]
        ];

        foreach ($nurses as $nurseData) {
            $nurse = User::create(array_merge($nurseData, [
                'password' => Hash::make('nurse123'),
                'role' => 'nurse',
                'is_active' => true,
                'address' => 'ຄລີນິກສາຍຝົນ',
                'license_number' => 'NUR' . str_pad(array_search($nurseData, $nurses) + 1, 4, '0', STR_PAD_LEFT),
            ]));
            $nurse->assignDefaultPermissions();
            
            $this->command->info("👩‍⚕️ Created nurse: {$nurse->name}");
        }

        // 4. ສ້າງພະນັກງານການເງິນ/ຮັບບ້ານ
        $cashier = User::create([
            'name' => 'ນາງມະນີ ຄຳພອນ',
            'email' => 'cashier@clinic.com',
            'password' => Hash::make('cashier123'),
            'role' => 'cashier',
            'is_active' => true,
            'phone' => '020 7777 0001',
            'address' => 'ຄລີນິກສາຍຝົນ',
            'license_number' => null,
            'specializations' => null,
        ]);
        $cashier->assignDefaultPermissions();

        // 5. ສ້າງຊ່າງເທັກນິກ
        $technicians = [
            [
                'name' => 'ອ້າຍທອງດີ ສີສະຫວັດ',
                'email' => 'xray@clinic.com',
                'phone' => '020 6666 0001',
                'specializations' => ['ຊ່າງ X-Ray', 'ຊ່າງ ECG'],
            ],
            [
                'name' => 'ນາງບຸນມີ ແກ້ວສີ',
                'email' => 'lab@clinic.com',
                'phone' => '020 6666 0002',
                'specializations' => ['ຊ່າງແລັບ'],
            ]
        ];

        foreach ($technicians as $techData) {
            $technician = User::create(array_merge($techData, [
                'password' => Hash::make('tech123'),
                'role' => 'technician',
                'is_active' => true,
                'address' => 'ຄລີນິກສາຍຝົນ',
                'license_number' => 'TECH' . str_pad(array_search($techData, $technicians) + 1, 3, '0', STR_PAD_LEFT),
            ]));
            $technician->assignDefaultPermissions();
            
            $this->command->info("🔧 Created technician: {$technician->name}");
        }

        // 6. ສ້າງພະນັກງານຕ້ອນຮັບ
        $receptionist = User::create([
            'name' => 'ນາງດອກໄມ້ ດວງມະນີ',
            'email' => 'receptionist@clinic.com',
            'password' => Hash::make('receptionist123'),
            'role' => 'receptionist',
            'is_active' => true,
            'phone' => '020 5555 0002',
            'address' => 'ຄລີນິກສາຍຝົນ',
            'license_number' => null,
            'specializations' => null,
        ]);
        $receptionist->assignDefaultPermissions();

        // ສະແດງສະຖິຕິ
        $this->displayUserStatistics();

        $this->command->info('✅ Created all system users successfully');
        $this->command->info('🔐 Login Credentials:');
        $this->command->info('   Admin: admin@clinic.com / admin123');
        $this->command->info('   Doctor: doctor1@clinic.com / doctor123');
        $this->command->info('   Nurse: nurse1@clinic.com / nurse123');
        $this->command->info('   Cashier: cashier@clinic.com / cashier123');
    }

    /**
     * ສະແດງສະຖິຕິຜູ້ໃຊ້ທີ່ສ້າງ
     */
    private function displayUserStatistics(): void
    {
        $totalUsers = User::count();
        $adminCount = User::admins()->count();
        $doctorCount = User::doctors()->count();
        $nurseCount = User::nurses()->count();
        $cashierCount = User::cashiers()->count();
        $technicianCount = User::technicians()->count();

        $this->command->info('📊 User Statistics:');
        $this->command->info("   - ລວມຜູ້ໃຊ້: {$totalUsers} ຄົນ");
        $this->command->info("   - ແອັດມິນ: {$adminCount} ຄົນ");
        $this->command->info("   - ທ່ານໝໍ: {$doctorCount} ຄົນ");
        $this->command->info("   - ພະຍາບານ: {$nurseCount} ຄົນ");
        $this->command->info("   - ພະນັກງານການເງິນ: {$cashierCount} ຄົນ");
        $this->command->info("   - ຊ່າງເທັກນິກ: {$technicianCount} ຄົນ");

        // ສະແດງລາຍຊື່ທ່ານໝໍພ້ອມຄວາມຊ່ຽວຊານ
        $this->command->info('👨‍⚕️ Doctor Specializations:');
        User::doctors()->get()->each(function ($doctor) {
            $specializations = $doctor->formatted_specializations;
            $this->command->info("   - {$doctor->name}: {$specializations}");
        });
    }
}