<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\Medicine;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('💊 ສ້າງຂໍ້ມູນການສັ່ງຢາ...');

        // ແກ້ໄຂຈາກ get(1) ເປັນ find(1) ຫຼື first()
        $queue = Queue::first(); // ຫຼື Queue::find(1)
        
        if (!$queue) {
            $this->command->error('❌ ບໍ່ພົບຂໍ້ມູນຄິວ');
            return;
        }

        $doctors = User::where('role', 'doctor')->get();
        
        if ($doctors->isEmpty()) {
            $this->command->error('❌ ບໍ່ພົບຂໍ້ມູນທ່ານໝໍ');
            return;
        }

        // ດຶງຂໍ້ມູນຢາ
        $medicine1 = Medicine::find(1);
        $medicine2 = Medicine::find(2); 
        $medicine3 = Medicine::find(3);

        $medications = [
            [
                'queue_id' => $queue->id,
                'medicine_id' => 1,
                'qty' => 2,
                'unit_price' => $medicine1->price,
                'total_price' => $medicine1->price * 2, // ແກ້ໄຂສູດການຄິດໄລ່
                'notes' => 'ກິນຫຼັງອາຫານ ເຊົ້າ-ແລງ',
                'created_by' => $doctors[0]->id,
            ],
            [
                'queue_id' => $queue->id,
                'medicine_id' => 2,
                'qty' => 3,
                'unit_price' => $medicine2->price,
                'total_price' => $medicine2->price * 3, // ແກ້ໄຂສູດການຄິດໄລ່
                'notes' => 'ກິນກ່ອນອາຫານ 30 ນາທີ',
                'created_by' => $doctors->count() > 1 ? $doctors[1]->id : $doctors[0]->id,
            ],
            [
                'queue_id' => $queue->id,
                'medicine_id' => 3,
                'qty' => 4,
                'unit_price' => $medicine3->price,
                'total_price' => $medicine3->price * 4, // ແກ້ໄຂສູດການຄິດໄລ່
                'notes' => 'ກິນຕອນນອນ',
                'created_by' => $doctors[0]->id,
            ],
        ];

        foreach ($medications as $medicationData) {
            Medication::create($medicationData);
        }

        $this->command->info("✅ ສ້າງການສັ່ງຢາ: " . count($medications) . " ລາຍການ");
    }
}