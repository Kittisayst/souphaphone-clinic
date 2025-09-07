<?php
// database/seeders/QueueServiceSeeder.php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\QueueService;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class QueueServiceSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('🏥 ສ້າງຂໍ້ມູນ Queue Services...');

        $queues = Queue::all();
        $services = Service::all();
        $doctor = User::where('role', 'doctor')->first();
        $nurse = User::where('role', 'nurse')->first();
        $room = Room::all();

        // ທຸກຄິວມີການປຶກສາ
        $queueServices = [
            [
                'queue_id' => $queues[0]->id,
                'service_id' => $services[0]->id,
                'room_id' => $room[0]->id,
                'doctor_id' => $doctor->id,
                'started_at' => Carbon::now()->subMinutes(30),
                'completed_at' => Carbon::now()->subMinutes(30)->addMinutes(30),
                'notes' => 'ການປຶກສາທ່ານໝໍ',
                'service_price' => $services[0]->price,
                'created_by' => $nurse->id,
            ],
            [
                'queue_id' => $queues[0]->id,
                'service_id' => $services[1]->id,
                'room_id' => $room[1]->id,
                'doctor_id' => $doctor->id,
                'started_at' => Carbon::now()->subMinutes(30),
                'completed_at' => Carbon::now()->subMinutes(30)->addMinutes(30),
                'notes' => 'ການປຶກສາທ່ານໝໍ',
                'service_price' => $services[1]->price,
                'created_by' => $nurse->id,
            ],
            [
                'queue_id' => $queues[0]->id,
                'service_id' => $services[2]->id,
                'room_id' => $room[0]->id,
                'doctor_id' => $doctor->id,
                'started_at' => Carbon::now()->subMinutes(30),
                'completed_at' => Carbon::now()->subMinutes(30)->addMinutes(30),
                'notes' => 'ການປຶກສາທ່ານໝໍ',
                'service_price' => $services[2]->price,
                'created_by' => $nurse->id,
            ]
        ];


        foreach ($queueServices as $qsData) {
            QueueService::create($qsData);
        }

        $this->command->info("✅ ສ້າງ Queue Services: " . count($queueServices) . " ລາຍການ");
    }

}