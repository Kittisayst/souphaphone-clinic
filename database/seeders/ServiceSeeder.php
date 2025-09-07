<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏥 ສ້າງຂໍ້ມູນບໍລິການ...');

        $services = [
            // ບໍລິການປຶກສາ
            [
                'service_code' => 'CONS01',
                'service_name' => 'ປຶກສາທ່ານໝໍທົ່ວໄປ',
                'service_category' => 'Consultation',
                'price' => 50000,
                'duration_minutes' => 30,
                'notes' => 'ການປຶກສາແລະກວດຮ່າງກາຍທົ່ວໄປ',
                'is_active' => true,
            ],
            [
                'service_code' => 'CONS02',
                'service_name' => 'ປຶກສາທ່ານໝໍຊ່ຽວຊານ',
                'service_category' => 'Consultation',
                'price' => 80000,
                'duration_minutes' => 45,
                'notes' => 'ການປຶກສາແພດຊ່ຽວຊານ',
                'is_active' => true,
            ],

            // ບໍລິການແລັບ
            [
                'service_code' => 'LAB01',
                'service_name' => 'ກວດເລືອດທົ່ວໄປ (CBC)',
                'service_category' => 'Laboratory',
                'price' => 45000,
                'duration_minutes' => 60,
                'notes' => 'ການກວດເລືອດຄົບຊຸດ',
                'is_active' => true,
            ],
            [
                'service_code' => 'LAB02',
                'service_name' => 'ກວດນ້ຳຕານໃນເລືອດ (FBS)',
                'service_category' => 'Blood_Test',
                'price' => 25000,
                'duration_minutes' => 30,
                'notes' => 'ກວດລະດັບນ້ຳຕານເປົ່າທ້ອງ',
                'is_active' => true,
            ],
            [
                'service_code' => 'LAB03',
                'service_name' => 'ກວດປັດສະວະ (UA)',
                'service_category' => 'Urine_Test',
                'price' => 20000,
                'duration_minutes' => 45,
                'notes' => 'ການກວດປັດສະວະຄົບຊຸດ',
                'is_active' => true,
            ],
            [
                'service_code' => 'LAB04',
                'service_name' => 'ກວດໜ້າທີ່ໄຕ (BUN/Creatinine)',
                'service_category' => 'Blood_Test',
                'price' => 35000,
                'duration_minutes' => 60,
                'notes' => 'ກວດການເຮັດວຽກຂອງໄຕ',
                'is_active' => true,
            ],

            // ບໍລິການຖ່າຍພາບ
            [
                'service_code' => 'XRAY01',
                'service_name' => 'ຖ່າຍພາບ X-Ray',
                'service_category' => 'X_Ray',
                'price' => 60000,
                'duration_minutes' => 20,
                'notes' => 'ຖ່າຍພາບຮັງສີເອັກ',
                'is_active' => true,
            ],
            [
                'service_code' => 'US01',
                'service_name' => 'ກວດອັນຕາຊາວ',
                'service_category' => 'Ultrasound',
                'price' => 80000,
                'duration_minutes' => 30,
                'notes' => 'ການກວດດ້ວຍຄື່ນສຽງອັນຕາຊາວ',
                'is_active' => true,
            ],

            // ບໍລິການອື່ນໆ
            [
                'service_code' => 'ECG01',
                'service_name' => 'ກວດສຽງຫົວໃຈ (ECG)',
                'service_category' => 'ECG',
                'price' => 40000,
                'duration_minutes' => 15,
                'notes' => 'ການກວດລົບຈັງຫວະຫົວໃຈ',
                'is_active' => true,
            ],
            [
                'service_code' => 'TREAT01',
                'service_name' => 'ການຮັກສາທົ່ວໄປ',
                'service_category' => 'Treatment',
                'price' => 30000,
                'duration_minutes' => 20,
                'notes' => 'ການຮັກສາແບບບໍ່ຊັບຊ້ອນ',
                'is_active' => true,
            ],
            [
                'service_code' => 'PHARM01',
                'service_name' => 'ບໍລິການຢາ',
                'service_category' => 'Pharmacy',
                'price' => 0,
                'duration_minutes' => 10,
                'notes' => 'ການຈ່າຍແລະໃຫ້ຄຳປຶກສາເກີ່ຍວກັບຢາ',
                'is_active' => true,
            ],
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }

        $this->command->info("✅ ສ້າງບໍລິການ: " . count($services) . " ລາຍການ");
    }
}
