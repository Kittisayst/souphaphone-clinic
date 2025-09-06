<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 ເລີ່ມ Seed ຂໍ້ມູນທັງໝົດ...');

        // Core Data (ຂໍ້ມູນພື້ນຖານ)
        $this->call([
            UserSeeder::class,           // 1. ຜູ້ໃຊ້ລະບົບ
            RoomSeeder::class,           // 2. ຫ້ອງຕ່າງໆ
            ServiceSeeder::class,        // 3. ບໍລິການ
            MedicineSeeder::class,       // 4. ຢາ
        ]);

        // Demo Data (ຂໍ້ມູນຕົວຢ່າງ)
        $this->call([
            PatientSeeder::class,        // 5. ຄົນໄຂ້
            QueueSeeder::class,          // 6. ຄິວ + Vital Signs
            QueueServiceSeeder::class,   // 7. ບໍລິການໃນຄິວ
            TreatmentSeeder::class,      // 8. ການປິ່ນປົວ
            MedicationSeeder::class,     // 9. ການສັ່ງຢາ
            PaymentSeeder::class,        // 10. ການຈ່າຍເງິນ
        ]);

        $this->command->info('✅ Seed ຂໍ້ມູນສຳເລັດ!');
    }
}
