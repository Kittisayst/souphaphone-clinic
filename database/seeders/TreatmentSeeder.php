<?php
// database/seeders/TreatmentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Queue, QueueService, Treatment, Room, User, Service};

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏥 ສ້າງຂໍ້ມູນການປິ່ນປົວ...');

        $consultationServices = QueueService::whereHas('service', function ($q) {
            $q->where('service_category', 'Consultation');
        })->get();

        $doctors = User::where('role', 'doctor')->get();

        $treatments = [];

        foreach ($consultationServices as $queueService) {
            $queue = $queueService->queue;
            $doctor = $doctors->find($queue->doctor_id) ?? $doctors->first();

            $treatmentData = [
                'queue_service_id' => $queueService->id,
                'room_id' => $queueService->assigned_room_id,
                'doctor_id' => $doctor->id,
                'status' => $this->getTreatmentStatus($queue->queue_status),
                'updated_by' => $doctor->id,
                'created_at' => $queueService->started_at ?? $queue->created_at,
                'updated_at' => $queueService->completed_at ?? $queue->updated_at,
            ];

            // ເພີ່ມຂໍ້ມູນຕາມສະຖານະ
            if ($queue->queue_status !== 'Registered') {
                $treatmentData = array_merge($treatmentData, $this->getTreatmentDetails($queue));
            }

            $treatments[] = $treatmentData;
        }

        foreach ($treatments as $treatmentData) {
            $treatment = Treatment::create($treatmentData);

            // ອັບເດດ billing items
            if ($treatment->status === 'Completed') {
                $treatment->updateBillingItems();
            }
        }

        $this->command->info("✅ ສ້າງ Treatment: " . count($treatments) . " ລາຍການ");
    }

    private function getTreatmentStatus(string $queueStatus): string
    {
        return match ($queueStatus) {
            'Completed' => 'Completed',
            'Cancelled' => 'Cancelled',
            default => 'In_Progress'
        };
    }

    private function getTreatmentDetails(Queue $queue): array
    {
        $patientName = $queue->patient->full_name;
        $complaint = $queue->initial_complaint;

        switch ($queue->id) {
            case 1: // ສຳເລັດແລ້ວ - ໄຂ້ຫວັດ
                return [
                    'examination_notes' => 'ກວດພົບ: ອຸນຫະພູມ 38.2°C, ຄໍແດງ, ປວດຫົວ ເລັກນ້ອຍ',
                    'findings' => 'ອາການຄ້າຍກັບໄຂ້ຫວັດທົ່ວໄປ, ບໍ່ມີອາການຮ້າຍແຮງ',
                    'medical_history_notes' => 'ປະຫວັດຄວາມດັນສູງ, ກິນຢາ Amlodipine ເປັນປົກກະຕິ',
                    'diagnosis' => 'ໄຂ້ຫວັດທົ່ວໄປ (Common Cold)',
                    'treatment_plan' => 'ພັກຜ່ອນ, ດື່ມນ້ຳຫຼາຍໆ, ກິນຢາລົດໄຂ້',
                    'follow_up_required' => false,
                ];

            case 2: // ລໍຖ້າຜົນ Lab
                return [
                    'examination_notes' => 'ກວດຮ່າງກາຍທົ່ວໄປປົກກະຕິ, ຄົນໄຂ້ຮູ້ສຶກເມື່ອຍ',
                    'findings' => 'ບໍ່ພົບອາການຜິດປົກກະຕິທາງກາຍະພາບ',
                    'medical_history_notes' => 'ມີປະຫວັດຄອບຄົວເປັນເບົາຫວານ',
                    'diagnosis' => null, // ຍັງບໍ່ມີການວິນິໄຈ
                    'treatment_plan' => null, // ລໍຖ້າຜົນ Lab ກ່ອນ
                    'follow_up_required' => true,
                    'follow_up_date' => today()->addDays(3),
                    'follow_up_notes' => 'ມາເບິ່ງຜົນກວດເລືອດ',
                ];

            case 3: // ກຳລັງກວດ - ເດັກ
                return [
                    'examination_notes' => 'ເດັກມີອາການໄອ, ຈາມ, ໄຂ້ເລັກນ້ອຍ',
                    'findings' => 'ຄໍເປັນສີແດງ, ມີນ້ຳມູກ',
                    'medical_history_notes' => 'ເດັກມີສຸຂະພາບແຂງແຮງ, ໄດ້ຮັບວັກຊີນຄົບຖ້ວນ',
                    'diagnosis' => null, // ກຳລັງກວດ
                    'treatment_plan' => null,
                    'follow_up_required' => false,
                ];

            case 4: // ກວດ vital ແລ້ວ
                return [
                    'examination_notes' => null, // ຍັງບໍ່ໄດ້ກວດ
                    'findings' => null,
                    'medical_history_notes' => 'ມີປະຫວັດເບົາຫວານ, ຄວາມດັນສູງ',
                    'diagnosis' => null,
                    'treatment_plan' => null,
                    'follow_up_required' => false,
                ];

            default:
                return [
                    'examination_notes' => null,
                    'findings' => null,
                    'medical_history_notes' => null,
                    'diagnosis' => null,
                    'treatment_plan' => null,
                    'follow_up_required' => false,
                ];
        }
    }
}