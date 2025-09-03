<?php

namespace Database\Seeders;

use App\Models\QueueService;
use App\Models\Room;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creating treatments demo data...');

        // ດຶງຂໍ້ມູນທີ່ຈຳເປັນ
        $queueServices = QueueService::whereIn('service_status', ['In_Progress', 'Completed'])
                                    ->with(['queue', 'service'])
                                    ->limit(4)
                                    ->get();
        $rooms = Room::where('room_type', 'Consultation')->limit(3)->get();
        $doctors = User::where('role', 'doctor')->limit(2)->get();

        if ($queueServices->isEmpty() || $rooms->isEmpty() || $doctors->isEmpty()) {
            $this->command->error('❌ ຕ້ອງມີຂໍ້ມູນ QueueService, Room ແລະ Doctor ກ່ອນ!');
            return;
        }

        // ການປິ່ນປົວທົດລອງ 4 ແບບ
        $treatmentData = [
            // ການປິ່ນປົວທີ 1: ກວດທົ່ວໄປ (ສຳເລັດ)
            [
                'examination_notes' => 'ຄົນໄຂ້ມາດ້ວຍອາການປວດຫົວ ແລະ ໄຂ້ມາ 2 ວັນ. ກວດພົບອຸນຫະພູມ 38.2°C, ຄວາມດັນເລືອດປົກກະຕິ. ກວດຄໍບໍ່ມີການອັກເສບ, ຫູ-ດັງປົກກະຕິ.',
                'findings' => 'ມີໄຂ້ເລັກນ້ອຍ, ບໍ່ມີອາການຮ້າຍແຮງ, ຄໍແດງເລັກນ້ອຍ',
                'recommendations' => 'ພັກຜ່ອນຢ່າງພຽງພໍ, ດື່ມນ້ຳຫຼາຍໆ, ກິນຢາແກ້ໄຂ້ຕາມເວລາ',
                'status' => 'Completed'
            ],

            // ການປິ່ນປົວທີ 2: ກວດເລືອດ (ສຳເລັດ)
            [
                'examination_notes' => 'ເຈາະເລືອດເພື່ອກວດ CBC ແລະ ນ້ຳຕານ. ຄົນໄຂ້ໃຫ້ການຮ່ວມມືດີ, ບໍ່ມີບັນຫາໃນການເຈາະເລືອດ.',
                'findings' => 'ເຈາະເລືອດສຳເລັດ, ຕົວຢ່າງເລືອດມີຄຸນນະພາບດີ',
                'recommendations' => 'ລໍຖ້າຜົນກວດ, ຈະແຈ້ງໃຫ້ຊາບພາຍໃນ 2 ຊົ່ວໂມງ',
                'status' => 'Completed'
            ],

            // ການປິ່ນປົວທີ 3: X-Ray (ກຳລັງເຮັດ)
            [
                'examination_notes' => 'ຖ່າຍ X-Ray ໜ້າເອິກເພື່ອກວດສອບປອດ ແລະ ຫົວໃຈ. ຄົນໄຂ້ມີອາການໄອແລະເຈັບໜ້າເອິກ.',
                'findings' => 'ກຳລັງຖ່າຍຮູບ X-Ray, ຄາດວ່າຈະສຳເລັດໃນອີກ 10 ນາທີ',
                'recommendations' => 'ລໍຖ້າຜົນ X-Ray ເພື່ອປະເມີນສະພາບປອດ',
                'status' => 'In_Progress'
            ],

            // ການປິ່ນປົວທີ 4: Ultrasound (ນັດເວລາແລ້ວ)
            [
                'examination_notes' => 'ນັດເວລາກວດ Ultrasound ທ້ອງເພື່ອກວດສອບອະໄວຍະວະພາຍໃນ. ຄົນໄຂ້ມີອາການປວດທ້ອງດ້ານຂວາ.',
                'findings' => 'ຍັງບໍ່ໄດ້ເລີ່ມກວດ, ລໍຖ້າເວລານັດໝາຍ',
                'recommendations' => 'ກຽມພ້ອມສຳລັບການກວດ Ultrasound, ດື່ມນ້ຳ 500ml ກ່ອນກວດ',
                'status' => 'In_Progress'
            ]
        ];

        foreach ($queueServices as $index => $queueService) {
            if (!isset($treatmentData[$index])) break;

            $data = $treatmentData[$index];
            
            // ກວດສອບວ່າມີ Treatment ແລ້ວບໍ່
            if (Treatment::where('queue_service_id', $queueService->id)->exists()) {
                $this->command->warn("⚠️ QueueService #{$queueService->id} ມີ Treatment ແລ້ວ - ຂ້າມໄປ");
                continue;
            }

            // ເລືອກຫ້ອງຕາມປະເພດບໍລິການ
            $room = $this->selectAppropriateRoom($queueService->service->service_category, $rooms);
            
            // ເລືອກທ່ານໝໍ
            $doctor = $queueService->assigned_to_id ? 
                     User::find($queueService->assigned_to_id) : 
                     $doctors->random();

            // ກຳນົດເວລາຕາມສະຖານະ
            $treatmentStart = $queueService->started_at ?? now()->subMinutes(30);
            $treatmentEnd = $data['status'] === 'Completed' ? 
                           ($queueService->completed_at ?? now()->subMinutes(5)) : 
                           null;

            Treatment::create([
                'queue_service_id' => $queueService->id,
                'room_id' => $room->id,
                'performed_by' => $doctor->id,
                'treatment_started_at' => $treatmentStart,
                'treatment_ended_at' => $treatmentEnd,
                'examination_notes' => $data['examination_notes'],
                'findings' => $data['findings'],
                'recommendations' => $data['recommendations'],
                'status' => $data['status'],
            ]);

            $serviceName = $queueService->service->service_name;
            $queueNumber = $queueService->queue->queue_number;
            
            $this->command->info("✅ Queue #{$queueNumber}: {$serviceName} - {$data['status']}");
        }

        // ສະຖິຕິການປິ່ນປົວ
        $this->displayTreatmentStatistics();

        $this->command->info('✅ Created treatments for queue services');
    }

    /**
     * ເລືອກຫ້ອງທີ່ເໝາະສົມຕາມປະເພດບໍລິການ
     */
    private function selectAppropriateRoom($serviceCategory, $rooms)
    {
        // ຖ້າເປັນການກວດທົ່ວໄປ ໃຊ້ຫ້ອງ Consultation
        if ($serviceCategory === 'Consultation') {
            return $rooms->where('room_type', 'Consultation')->first() ?? $rooms->first();
        }
        
        // ບໍລິການອື່ນໆ ໃຊ້ຫ້ອງທົ່ວໄປ
        return $rooms->first();
    }

    /**
     * ສະແດງສະຖິຕິການປິ່ນປົວ
     */
    private function displayTreatmentStatistics(): void
    {
        $totalTreatments = Treatment::count();
        $completedTreatments = Treatment::where('status', 'Completed')->count();
        $inProgressTreatments = Treatment::where('status', 'In_Progress')->count();
        
        // ເວລາປົກກະຕິໃນການປິ່ນປົວ
        $avgDuration = Treatment::whereNotNull('treatment_ended_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, treatment_started_at, treatment_ended_at)) as avg_duration')
            ->first()
            ->avg_duration ?? 0;

        // ທ່ານໝໍທີ່ເຮັດວຽກ
        $activeDoctors = Treatment::distinct('performed_by')->count('performed_by');

        $this->command->info('📈 Treatment Statistics:');
        $this->command->info("   - ລວມການປິ່ນປົວ: {$totalTreatments} ຄັ້ງ");
        $this->command->info("   - ສຳເລັດແລ້ວ: {$completedTreatments} ຄັ້ງ");
        $this->command->info("   - ກຳລັງເຮັດ: {$inProgressTreatments} ຄັ້ງ");
        $this->command->info("   - ເວລາເຄີ່ຍ: " . round($avgDuration, 0) . " ນາທີ");
        $this->command->info("   - ທ່ານໝໍທີ່ເຮັດວຽກ: {$activeDoctors} ຄົນ");
    }
}
