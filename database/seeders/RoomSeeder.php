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
       $this->command->info('🔄 Creating clinic rooms...');

        $rooms = [
            // ຫ້ອງກວດທົ່ວໄປ
            [
                'room_code' => 'CONS01',
                'room_name' => 'ຫ້ອງກວດທົ່ວໄປ 1',
                'room_type' => 'Consultation',
                'capacity' => 2,
                'equipment_list' => 'ເກົ້າອີ້ກວດ, ໂຕະກວດ, ເຄື່ອງວັດຄວາມດັນ, ໂຄມໄຟກວດ, ເຄື່ອງຊ່ວຍຟັງ, ເຄື່ອງວັດອຸນຫະພູມ',
                'is_available' => true,
            ],
            [
                'room_code' => 'CONS02',
                'room_name' => 'ຫ້ອງກວດທົ່ວໄປ 2', 
                'room_type' => 'Consultation',
                'capacity' => 2,
                'equipment_list' => 'ເກົ້າອີ້ກວດ, ໂຕະກວດ, ເຄື່ອງວັດຄວາມດັນ, ໂຄມໄຟກວດ, ເຄື່ອງຊ່ວຍຟັງ',
                'is_available' => true,
            ],
            [
                'room_code' => 'CONS03',
                'room_name' => 'ຫ້ອງກວດທົ່ວໄປ 3',
                'room_type' => 'Consultation', 
                'capacity' => 2,
                'equipment_list' => 'ເກົ້າອີ້ກວດ, ໂຕະກວດ, ເຄື່ອງວັດຄວາມດັນ, ໂຄມໄຟກວດ',
                'is_available' => true,
            ],

            // ຫ້ອງ X-Ray
            [
                'room_code' => 'XRAY01',
                'room_name' => 'ຫ້ອງຖ່າຍ X-Ray',
                'room_type' => 'X_Ray',
                'capacity' => 1,
                'equipment_list' => 'ເຄື່ອງຖ່າຍ X-Ray ດິຈິຕອນ, ໂຕະກວດ, ເສື້ອນຳ, ແຜ່ນປ້ອງກັນ, ຊຸດປ້ອງກັນລັງສີ',
                'is_available' => true,
            ],

            // ຫ້ອງ Ultrasound
            [
                'room_code' => 'ULTRA01',
                'room_name' => 'ຫ້ອງ Ultrasound',
                'room_type' => 'Ultrasound',
                'capacity' => 2,
                'equipment_list' => 'ເຄື່ອງ Ultrasound ດິຈິຕອນ, ເຈວ Ultrasound, ກະດາດເຊັດ, ໂຕະກວດ',
                'is_available' => true,
            ],

            // ຫ້ອງແລັບ
            [
                'room_code' => 'LAB01',
                'room_name' => 'ຫ້ອງແລັບ', 
                'room_type' => 'Laboratory',
                'capacity' => 3,
                'equipment_list' => 'ກ້ອງຈຸລະທັດ, ເຄື່ອງປັ່ນເລືອດ, ອຸປະກອນເຈາະເລືອດ, ເຄື່ອງວັດນ້ຳຕານ, ຕູ້ເກັບຕົວຢ່າງ',
                'is_available' => true,
            ],

            // ຫ້ອງວັດຫົວໃຈ
            [
                'room_code' => 'ECG01',
                'room_name' => 'ຫ້ອງວັດຫົວໃຈ ECG',
                'room_type' => 'General',
                'capacity' => 1,
                'equipment_list' => 'ເຄື່ອງວັດຫົວໃຈ ECG, ໂຕະກວດ, ເຈວນຳໄຟຟ້າ',
                'is_available' => true,
            ],

            // ຫ້ອງລໍຖ້າ
            [
                'room_code' => 'WAIT01',
                'room_name' => 'ຫ້ອງລໍຖ້າ',
                'room_type' => 'General',
                'capacity' => 20,
                'equipment_list' => 'ເກົ້າອີ້ 20 ຕົວ, ໂທລະທັດ, ເຄື່ອງປັບອາກາດ',
                'is_available' => true,
            ]
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }

        $this->command->info('✅ Created ' . count($rooms) . ' rooms successfully');
    }
}
