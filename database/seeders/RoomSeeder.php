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
            ['room_code' => 'C001', 'room_name' => 'ຫ້ອງກວດ 1', 'room_type' => 'Consultation'],
            ['room_code' => 'C002', 'room_name' => 'ຫ້ອງກວດ 2', 'room_type' => 'Consultation'],
            ['room_code' => 'LAB01', 'room_name' => 'ຫ້ອງແລັບ', 'room_type' => 'Laboratory', 'capacity' => 3],
            ['room_code' => 'XRAY01', 'room_name' => 'ຫ້ອງ X-Ray', 'room_type' => 'X_Ray'],
            ['room_code' => 'US01', 'room_name' => 'ຫ້ອງ Ultrasound', 'room_type' => 'Ultrasound'],
        ];

        foreach ($rooms as $room) {
            Room::create(array_merge($room, [
                'capacity' => $room['capacity'] ?? 1,
                'is_available' => true
            ]));
        }

        $this->command->info('✅ Created ' . count($rooms) . ' rooms successfully');
    }
}
