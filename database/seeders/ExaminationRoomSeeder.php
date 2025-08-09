<?php

namespace Database\Seeders;

use App\Models\ExaminationRoom;
use Illuminate\Database\Seeder;

class ExaminationRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            [
                'room_name' => 'ຫ້ອງກວດທົ່ວໄປ A',
                'room_code' => 'R001',
                'room_type' => 'general',
                'status' => 'available',
                'capacity' => 1,
                'equipment' => ['ເຄື່ອງວັດຄວາມດັນ', 'ເທີໂມມີເຕີ', 'ຟັງຫົວໃຈ', 'ເຄື່ອງຊັ່ງ'],
                'notes' => 'ຫ້ອງກວດທົ່ວໄປສຳລັບການກວດສຸຂະພາບປົກກະຕິ',
               
            ],
            [
                'room_name' => 'ຫ້ອງກວດທົ່ວໄປ B',
                'room_code' => 'R002',
                'room_type' => 'general',
                'status' => 'available',
                'capacity' => 1,
                'equipment' => ['ເຄື່ອງວັດຄວາມດັນ', 'ເທີໂມມີເຕີ', 'ຟັງຫົວໃຈ', 'ເຄື່ອງຊັ່ງ'],
                'notes' => 'ຫ້ອງກວດທົ່ວໄປສຳລັບການກວດສຸຂະພາບປົກກະຕິ',
                
            ],
            [
                'room_name' => 'ຫ້ອງກວດເລືອດ',
                'room_code' => 'R003',
                'room_type' => 'general',
                'status' => 'available',
                'capacity' => 2,
                'equipment' => ['ເຄື່ອງກວດເລືອດ', 'ກ້ອງຈຸລະທັດ', 'ເຄື່ອງປັ່ນເລືອດ', 'ຕູ້ແຊ່'],
                'notes' => 'ຫ້ອງກວດເລືອດ ແລະ ປັດສະວະ',
                
            ],
            [
                'room_name' => 'ຫ້ອງ Ultrasound',
                'room_code' => 'R004',
                'room_type' => 'general',
                'status' => 'available',
                'capacity' => 1,
                'equipment' => ['ເຄື່ອງ Ultrasound', 'Gel', 'ເຄື່ອງພິມຮູບ'],
                'notes' => 'ຫ້ອງກວດດ້ວຍຄື່ນສຽງ ultrasound',
                
            ],
            [
                'room_name' => 'ຫ້ອງກວດຟັນ',
                'room_code' => 'R005',
                'room_type' => 'general',
                'status' => 'available',
                'capacity' => 1,
                'equipment' => ['ເກ້າອີ້ທັນຕະແພດ', 'ເຄື່ອງດູດນ້ຳລາຍ', 'ໄຟສ່ອງ', 'ເຄື່ອງມືທັນຕະແພດ'],
                'notes' => 'ຫ້ອງກວດ ແລະ ປິ່ນປົວຟັນ',
                
            ],
            [
                'room_name' => 'ຫ້ອງກວດພິເສດ',
                'room_code' => 'R006',
                'room_type' => 'general',
                'status' => 'available',
                'capacity' => 1,
                'equipment' => ['ເຄື່ອງກວດພິເສດ', 'ເຄື່ອງວັດຄວາມດັນ', 'ECG', 'ເຄື່ອງວັດລົມຫາຍໃຈ'],
                'notes' => 'ຫ້ອງກວດພິເສດສຳລັບໝໍຜູ້ຊ່ຽວຊານ',
                
            ],
        ];

        foreach ($rooms as $room) {
            ExaminationRoom::create($room);
        }
    }
}