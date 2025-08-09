<?php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class QueueSeeder extends Seeder
{
    public function run(): void
    {
        // ສ້າງຄິວສຳຫລັບວັນນີ້
        $this->createTodayQueues();
        
        // ສ້າງຄິວສຳຫລັບເມື່ອວານ (ເພື່ອທົດສອບປະຫວັດ)
        $this->createYesterdayQueues();
    }

    private function createTodayQueues(): void
    {
        $patients = Patient::active()->limit(15)->get();
        
        foreach ($patients as $index => $patient) {
            $status = match (true) {
                $index < 8 => 'completed',
                $index < 10 => 'in_progress', 
                $index < 12 => 'called',
                default => 'waiting',
            };

            $priority = ($index === 9 || $index === 13) ? 'urgent' : 'normal';

            $queue = Queue::create([
                'patient_id' => $patient->id,
                'queue_date' => today(),
                'priority' => $priority,
                'status' => $status,
                'notes' => $priority === 'urgent' ? 'ຄິວດ່ວນ - ຄົນໄຂ້ມີອາການຮຸນແຮງ' : null,
                'created_by' => 1, // Admin user
                'created_at' => today()->addMinutes(rand(30, 480)), // ລະຫວ່າງ 08:30 - 16:00
            ]);

            // ອັບເດດເວລາຕາມສະຖານະ
            if ($status === 'called') {
                $queue->update([
                    'called_at' => $queue->created_at->addMinutes(rand(5, 30))
                ]);
            } elseif ($status === 'in_progress') {
                $queue->update([
                    'called_at' => $queue->created_at->addMinutes(rand(5, 15)),
                    // 'started_at' => $queue->created_at->addMinutes(rand(15, 45))
                ]);
            } elseif ($status === 'completed') {
                $queue->update([
                    'called_at' => $queue->created_at->addMinutes(rand(5, 15)),
                    // 'started_at' => $queue->created_at->addMinutes(rand(15, 30)),
                    'completed_at' => $queue->created_at->addMinutes(rand(30, 90))
                ]);
            }
        }
    }

    private function createYesterdayQueues(): void
    {
        $patients = Patient::active()->skip(15)->limit(20)->get();
        
        foreach ($patients as $index => $patient) {
            $status = match (true) {
                $index < 17 => 'completed',
                $index < 19 => 'cancelled',
                default => 'completed',
            };

            $priority = ($index === 5 || $index === 12) ? 'urgent' : 'normal';

            $queue = Queue::create([
                'patient_id' => $patient->id,
                'queue_date' => Carbon::yesterday(),
                'priority' => $priority,
                'status' => $status,
                'notes' => $status === 'cancelled' ? 'ຍົກເລີກ - ຄົນໄຂ້ບໍ່ມາ' : null,
                'created_by' => rand(1, 4), // Random user
                'created_at' => Carbon::yesterday()->addMinutes(rand(30, 480)),
            ]);

            // ອັບເດດເວລາສຳຫລັບຄິວທີ່ສຳເລັດ
            if ($status === 'completed') {
                $queue->update([
                    'called_at' => $queue->created_at->addMinutes(rand(5, 15)),
                    'started_at' => $queue->created_at->addMinutes(rand(15, 30)),
                    'completed_at' => $queue->created_at->addMinutes(rand(30, 90))
                ]);
            }
        }
    }
}