<?php
// database/seeders/QueueSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Queue, Patient, User, Service, QueueService, VitalSign, Treatment, Room};
use Carbon\Carbon;

class QueueSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('ກຳລັງສ້າງຂໍ້ມູນຄິວທົດສອບ...');

                // ດຶງຂໍ້ມູນທີ່ຈຳເປັນ
        $patients = Patient::limit(2)->get();
        $doctor = User::where('role', 'doctor')->first();
        $nurse = User::where('role', 'nurse')->first();

        if ($patients->isEmpty() || !$doctor) {
            $this->command->warn('ຕ້ອງມີຂໍ້ມູນ Patient ແລະ Doctor ກ່ອນ');
            return;
        }

        // ສ້າງຄິວວັນນີ້
        Queue::create([
            'patient_id' => $patients[0]->id,
            'queue_number' => 1,
            'waiting_number' => 0, // ສຳເລັດແລ້ວ
            'queue_date' => today(),
            'initial_complaint' => 'ປວດຫົວ, ໄຂ້ 2 ວັນແລ້ວ',
            'doctor_id' => $doctor->id,
            'assigned_room_id' => 1, // ສົມມຸດວ່າມີຫ້ອງ ID 1
            'room_assigned_at' => now()->subHours(2),
            'queue_status' => 'Completed',
            'created_by' => $nurse?->id ?? $doctor->id,
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHour(),
        ]);

        // ສ້າງຄິວທີ່ 2 (ກຳລັງປິ່ນປົວ)
        if ($patients->count() > 1) {
            Queue::create([
                'patient_id' => $patients[1]->id,
                'queue_number' => 2,
                'waiting_number' => 1,
                'queue_date' => today(),
                'initial_complaint' => 'ກວດສຸຂະພາບປົກກະຕິ',
                'doctor_id' => $doctor->id,
                'assigned_room_id' => 1,
                'room_assigned_at' => now()->subMinutes(30),
                'queue_status' => 'With_Doctor',
                'created_by' => $nurse?->id ?? $doctor->id,
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subMinutes(30),
            ]);
        }

        $this->command->info('✅ ສ້າງຂໍ້ມູນ Queue ສຳເລັດ: 2 ລາຍການ');
    }
}