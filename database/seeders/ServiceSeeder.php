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

        $consultationRoom1 = Room::where('room_code', 'CON001')->first();
        $consultationRoom2 = Room::where('room_code', 'CON002')->first();
        $labRoom = Room::where('room_code', 'LAB001')->first();
        $xrayRoom = Room::where('room_code', 'XRAY01')->first();
        $ultrasoundRoom = Room::where('room_code', 'US001')->first();
        $pharmacyRoom = Room::where('room_code', 'PHA001')->first();

        $services = [
            // ບໍລິການປຶກສາ
            [
                'service_code' => 'CONS01',
                'service_name' => 'ປຶກສາທ່ານໝໍທົ່ວໄປ',
                'service_category' => 'Consultation',
                'base_price' => 50000,
                'description' => 'ການປຶກສາແລະກວດຮ່າງກາຍທົ່ວໄປ',
                'duration_minutes' => 30,
                'room_id' => $consultationRoom1?->id,
                'has_lab_result' => false,
                'is_active' => true,
            ],
            [
                'service_code' => 'CONS02',
                'service_name' => 'ປຶກສາທ່ານໝໍຊ່ຽວຊານ',
                'service_category' => 'Consultation',
                'base_price' => 80000,
                'description' => 'ການປຶກສາແພດຊ່ຽວຊານ',
                'duration_minutes' => 45,
                'room_id' => $consultationRoom2?->id,
                'has_lab_result' => false,
                'is_active' => true,
            ],

            // ບໍລິການແລັບ
            [
                'service_code' => 'LAB01',
                'service_name' => 'ກວດເລືອດທົ່ວໄປ (CBC)',
                'service_category' => 'Laboratory',
                'base_price' => 45000,
                'description' => 'ການກວດເລືອດຄົບຊຸດ',
                'duration_minutes' => 60,
                'room_id' => $labRoom?->id,
                'has_lab_result' => true,
                'lab_test_types' => ['CBC', 'ເລືອດແດງ', 'ເລືອດຂາວ', 'ແຜ່ນເລືອດ'],
                'is_active' => true,
            ],
            [
                'service_code' => 'LAB02',
                'service_name' => 'ກວດນ້ຳຕານໃນເລືອດ (FBS)',
                'service_category' => 'Blood_Test',
                'base_price' => 25000,
                'description' => 'ກວດລະດັບນ້ຳຕານເປົ່າທ້ອງ',
                'duration_minutes' => 30,
                'room_id' => $labRoom?->id,
                'has_lab_result' => true,
                'lab_test_types' => ['FBS', 'ນ້ຳຕານ'],
                'is_active' => true,
            ],
            [
                'service_code' => 'LAB03',
                'service_name' => 'ກວດປັດສະວະ (UA)',
                'service_category' => 'Urine_Test',
                'base_price' => 20000,
                'description' => 'ການກວດປັດສະວະຄົບຊຸດ',
                'duration_minutes' => 45,
                'room_id' => $labRoom?->id,
                'has_lab_result' => true,
                'lab_test_types' => ['UA', 'ໂປຼຕີນ', 'ນ້ຳຕານ', 'ເມັດເລືອດ'],
                'is_active' => true,
            ],
            [
                'service_code' => 'LAB04',
                'service_name' => 'ກວດໜ້າທີ່ໄຕ (BUN/Creatinine)',
                'service_category' => 'Blood_Test',
                'base_price' => 35000,
                'description' => 'ກວດການເຮັດວຽກຂອງໄຕ',
                'duration_minutes' => 60,
                'room_id' => $labRoom?->id,
                'has_lab_result' => true,
                'lab_test_types' => ['BUN', 'Creatinine'],
                'is_active' => true,
            ],

            // ບໍລິການຖ່າຍພາບ
            [
                'service_code' => 'XRAY01',
                'service_name' => 'ຖ່າຍພາບ X-Ray',
                'service_category' => 'X_Ray',
                'base_price' => 60000,
                'description' => 'ຖ່າຍພາບຮັງສີເອັກ',
                'duration_minutes' => 20,
                'room_id' => $xrayRoom?->id,
                'has_lab_result' => true,
                'is_active' => true,
            ],
            [
                'service_code' => 'US01',
                'service_name' => 'ກວດອັນຕາຊາວ',
                'service_category' => 'Ultrasound',
                'base_price' => 80000,
                'description' => 'ການກວດດ້ວຍຄື່ນສຽງອັນຕາຊາວ',
                'duration_minutes' => 30,
                'room_id' => $ultrasoundRoom?->id,
                'has_lab_result' => true,
                'is_active' => true,
            ],

            // ບໍລິການອື່ນໆ
            [
                'service_code' => 'ECG01',
                'service_name' => 'ກວດສຽງຫົວໃຈ (ECG)',
                'service_category' => 'ECG',
                'base_price' => 40000,
                'description' => 'ການກວດລົບຈັງຫວະຫົວໃຈ',
                'duration_minutes' => 15,
                'room_id' => $consultationRoom1?->id,
                'has_lab_result' => true,
                'is_active' => true,
            ],
            [
                'service_code' => 'TREAT01',
                'service_name' => 'ການຮັກສາທົ່ວໄປ',
                'service_category' => 'Treatment',
                'base_price' => 30000,
                'description' => 'ການຮັກສາແບບບໍ່ຊັບຊ້ອນ',
                'duration_minutes' => 20,
                'room_id' => $consultationRoom1?->id,
                'has_lab_result' => false,
                'is_active' => true,
            ],
            [
                'service_code' => 'PHARM01',
                'service_name' => 'ບໍລິການຢາ',
                'service_category' => 'Pharmacy',
                'base_price' => 0,
                'description' => 'ການຈ່າຍແລະໃຫ້ຄຳປຶກສາເກີ່ຍວກັບຢາ',
                'duration_minutes' => 10,
                'room_id' => $pharmacyRoom?->id,
                'has_lab_result' => false,
                'is_active' => true,
            ],
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }

        $this->command->info("✅ ສ້າງບໍລິການ: " . count($services) . " ລາຍການ");
    }
}
