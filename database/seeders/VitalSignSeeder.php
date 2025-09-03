<?php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VitalSignSeeder extends Seeder
{
   public function run(): void
    {
        $this->command->info('🔄 Creating vital signs demo data...');

        // ດຶງຂໍ້ມູນທີ່ຈຳເປັນ
        $queues = Queue::whereNotNull('vital_checked_at')->limit(4)->get();
        $nurses = User::where('role', 'nurse')->get();

        if ($queues->isEmpty() || $nurses->isEmpty()) {
            $this->command->error('❌ ຕ້ອງມີຂໍ້ມູນ Queue (ທີ່ກວດແລ້ວ) ແລະ Nurse ກ່ອນ!');
            return;
        }

        // ການກວດເບື້ອງຕົ້ນທົດລອງ 4 ແບບ
        $vitalSignsData = [
            // ກໍລະນີ 1: ຄ່າປົກກະຕິ
            [
                'temperature' => 36.5,
                'weight' => 65.5,
                'height' => 165.0,
                'blood_pressure_sys' => 120,
                'blood_pressure_dia' => 80,
                'heart_rate' => 75,
                'notes' => 'ທຸກຄ່າປົກກະຕິ',
                'condition' => 'ປົກກະຕິ'
            ],

            // ກໍລະນີ 2: ມີໄຂ້
            [
                'temperature' => 38.2,
                'weight' => 58.0,
                'height' => 160.0,
                'blood_pressure_sys' => 125,
                'blood_pressure_dia' => 85,
                'heart_rate' => 88,
                'notes' => 'ຄົນໄຂ້ມີໄຂ້ສູງ, ຮູ້ສຶກເມື່ອຍ',
                'condition' => 'ມີໄຂ້'
            ],

            // ກໍລະນີ 3: ຄວາມດັນສູງ
            [
                'temperature' => 36.8,
                'weight' => 78.5,
                'height' => 172.0,
                'blood_pressure_sys' => 150,
                'blood_pressure_dia' => 95,
                'heart_rate' => 82,
                'notes' => 'ຄວາມດັນເລືອດສູງ, ແນະນຳຕິດຕາມ',
                'condition' => 'ຄວາມດັນສູງ'
            ],

            // ກໍລະນີ 4: ເດັກນ້ອຍ
            [
                'temperature' => 37.1,
                'weight' => 25.0,
                'height' => 120.0,
                'blood_pressure_sys' => 95,
                'blood_pressure_dia' => 60,
                'heart_rate' => 95,
                'notes' => 'ເດັກອາຍຸ 8 ປີ, ໄຂ້ເລັກນ້ອຍ',
                'condition' => 'ເດັກມີໄຂ້ເບົາໆ'
            ]
        ];

        foreach ($queues as $index => $queue) {
            if (!isset($vitalSignsData[$index])) break;
            
            $data = $vitalSignsData[$index];

            // ກວດສອບວ່າມີ VitalSign ແລ້ວບໍ່
            if (VitalSign::where('queue_id', $queue->id)->exists()) {
                $this->command->warn("⚠️ Queue #{$queue->queue_number} ມີ VitalSign ແລ້ວ - ຂ້າມໄປ");
                continue;
            }

            VitalSign::create([
                'queue_id' => $queue->id,
                'temperature' => $data['temperature'],
                'weight' => $data['weight'],
                'height' => $data['height'],
                'blood_pressure_sys' => $data['blood_pressure_sys'],
                'blood_pressure_dia' => $data['blood_pressure_dia'],
                'heart_rate' => $data['heart_rate'],
                'recorded_by_id' => $nurses->random()->id,
                'notes' => $data['notes'],
            ]);

            $this->command->info("📊 Queue #{$queue->queue_number}: {$data['condition']} - {$data['blood_pressure_sys']}/{$data['blood_pressure_dia']} mmHg, {$data['temperature']}°C");
        }

        // ສະຖິຕິການກວດ
        $this->displayVitalSignsStatistics();

        $this->command->info('✅ Created vital signs for demo queues');
    }

    /**
     * ສະແດງສະຖິຕິການກວດເບື້ອງຕົ້ນ
     */
    private function displayVitalSignsStatistics(): void
    {
        $totalVitalSigns = VitalSign::count();
        $feverCases = VitalSign::where('temperature', '>', 37.5)->count();
        $highBpCases = VitalSign::where('blood_pressure_sys', '>', 140)->count();
        $avgTemperature = VitalSign::avg('temperature');
        $avgHeartRate = VitalSign::avg('heart_rate');

        $this->command->info('📈 Vital Signs Statistics:');
        $this->command->info("   - ລວມການກວດ: {$totalVitalSigns} ຄັ້ງ");
        $this->command->info("   - ກໍລະນີມີໄຂ້: {$feverCases} ຄົນ");
        $this->command->info("   - ກໍລະນີຄວາມດັນສູງ: {$highBpCases} ຄົນ");
        $this->command->info("   - ອຸນຫະພູມເຄີ່ຍ: " . round($avgTemperature, 1) . "°C");
        $this->command->info("   - ການເຕັ້ນຫົວໃຈເຄີ່ຍ: " . round($avgHeartRate, 0) . " bpm");
    }
}
