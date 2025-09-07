<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */ 
    public function run(): void
    {
        $this->command->info('🏠 ສ້າງຂໍ້ມູນຫ້ອງຕ່າງໆ...');

        $rooms = [
            // ຫ້ອງປຶກສາ
            [
                'room_code' => 'CON001',
                'room_name' => 'ຫ້ອງປຶກສາ 1',
                'room_type' => 'Consultation',
                'room_status' => 'Available',
                'is_available' => true,
            ],
            [
                'room_code' => 'CON002',
                'room_name' => 'ຫ້ອງປຶກສາ 2',
                'room_type' => 'Consultation',
                'room_status' => 'Available',
                'is_available' => true,
            ],

            // ຫ້ອງແລັບ
            [
                'room_code' => 'LAB001',
                'room_name' => 'ຫ້ອງແລັບ',
                'room_type' => 'Laboratory',
                'room_status' => 'Available',
                'is_available' => true,
            ],

            // ຫ້ອງ X-Ray
            [
                'room_code' => 'XRAY01',
                'room_name' => 'ຫ້ອງ X-Ray',
                'room_type' => 'X_Ray',
                'room_status' => 'Available',
                'is_available' => true,
            ],

            // ຫ້ອງອັນຕາຊາວ
            [
                'room_code' => 'US001',
                'room_name' => 'ຫ້ອງອັນຕາຊາວ',
                'room_type' => 'Ultrasound',
                'room_status' => 'Available',
                'is_available' => true,
            ],

            // ຫ້ອງຢາ
            [
                'room_code' => 'PHA001',
                'room_name' => 'ຫ້ອງຢາ',
                'room_type' => 'Pharmacy',
                'room_status' => 'Available',
                'is_available' => true,
            ],

            // ຫ້ອງເກັບເງິນ
            [
                'room_code' => 'CAS001',
                'room_name' => 'ຫ້ອງເກັບເງິນ',
                'room_type' => 'Cashier',
                'room_status' => 'Available',
                'is_available' => true,
            ],

            // ຫ້ອງລໍຖ້າ
            [
                'room_code' => 'WAI001',
                'room_name' => 'ຫ້ອງລໍຖ້າ',
                'room_type' => 'General',
                'room_status' => 'Available',
                'is_available' => true,
            ],
        ];

        foreach ($rooms as $roomData) {
            Room::create($roomData);
        }

        $this->command->info("✅ ສ້າງຫ້ອງ: " . count($rooms) . " ຫ້ອງ");
    }
}
