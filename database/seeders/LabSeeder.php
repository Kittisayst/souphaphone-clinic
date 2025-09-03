<?php

namespace Database\Seeders;

use App\Models\Lab;
use App\Models\QueueService;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabSeeder extends Seeder
{
     public function run(): void
    {
        $this->command->info('🔄 Creating lab results demo data...');

        // ດຶງຂໍ້ມູນທີ່ຈຳເປັນ
        $queueServices = QueueService::whereHas('service', function($query) {
                                        $query->where('has_lab_result', true);
                                    })
                                    ->with(['service', 'queue'])
                                    ->limit(4)
                                    ->get();
        
        $labTechnicians = User::where('role', 'nurse')->get(); // ພະຍາບານເຮັດໜ້າທີ່ Lab
        $doctors = User::where('role', 'doctor')->get();

        if ($queueServices->isEmpty() || $labTechnicians->isEmpty() || $doctors->isEmpty()) {
            $this->command->error('❌ ຕ້ອງມີຂໍ້ມູນ QueueService (ທີ່ມີຜົນກວດ), Nurse ແລະ Doctor ກ່ອນ!');
            return;
        }

        // ຜົນການກວດທົດລອງ 4 ແບບ
        $labResultsData = [
            // ຜົນກວດທີ 1: ກວດເລືອດທົ່ວໄປ (ປົກກະຕິ)
            [
                'lab_code' => 'LAB' . date('Ymd') . '001',
                'test_results' => [
                    'hemoglobin' => 13.5,
                    'hematocrit' => 40.2,
                    'white_blood_cells' => 7500,
                    'red_blood_cells' => 4.8,
                    'platelets' => 280000,
                    'glucose' => 95
                ],
                'result_summary' => 'ຜົນການກວດເລືອດທົ່ວໄປ ຢູ່ໃນເກນປົກກະຕິທຸກຄ່າ',
                'reference_values' => 'Hemoglobin: 12-15 g/dL, WBC: 4000-11000 /μL, Platelets: 150000-400000 /μL, Glucose: 70-110 mg/dL',
                'interpretation' => 'ບໍ່ມີອາການຜິດປົກກະຕິ, ສຸຂະພາບດີ',
                'lab_status' => 'Patient_Notified',
                'patient_notified' => true
            ],

            // ຜົນກວດທີ 2: X-Ray ໜ້າເອິກ (ປົກກະຕິ)
            [
                'lab_code' => 'LAB' . date('Ymd') . '002',
                'test_results' => [
                    'examination' => 'Chest X-Ray PA view',
                    'lung_fields' => 'ປອດສະອາດທັງສອງຂ້າງ',
                    'heart_shadow' => 'ຫົວໃຈຂະໜາດປົກກະຕິ',
                    'bony_structures' => 'ໂຄງກະດູກປົກກະຕິ',
                    'conclusion' => 'ບໍ່ພົບຄວາມຜິດປົກກະຕິ'
                ],
                'result_summary' => 'X-Ray ໜ້າເອິກ: ປອດແລະຫົວໃຈປົກກະຕິ',
                'reference_values' => 'ປອດສະອາດ, ຫົວໃຈຂະໜາດປົກກະຕິ, ບໍ່ມີເງົາຜິດປົກກະຕິ',
                'interpretation' => 'ບໍ່ພົບອາການປ່ວຍໃນປອດ ຫຼື ຫົວໃຈ',
                'images_attachments' => ['xray_chest_' . date('Ymd') . '_001.jpg'],
                'lab_status' => 'Doctor_Reviewed',
                'patient_notified' => false
            ],

            // ຜົນກວດທີ 3: ກວດເລືອດ (ມີບັນຫາເລັກນ້ອຍ)
            [
                'lab_code' => 'LAB' . date('Ymd') . '003',
                'test_results' => [
                    'hemoglobin' => 10.8,  // ຕ່ຳເລັກນ້ອຍ
                    'hematocrit' => 32.5,  // ຕ່ຳ
                    'white_blood_cells' => 12500, // ສູງເລັກນ້ອຍ
                    'red_blood_cells' => 4.2,
                    'platelets' => 320000,
                    'glucose' => 125 // ສູງເລັກນ້ອຍ
                ],
                'result_summary' => 'ຄ່າ Hemoglobin ແລະ Glucose ສູງເລັກນ້ອຍ, WBC ສູງອາດມີການຕິດເຊື້ອເລັກນ້ອຍ',
                'reference_values' => 'Hemoglobin: 12-15 g/dL, WBC: 4000-11000 /μL, Glucose: 70-110 mg/dL',
                'interpretation' => 'ພົບເລືອດຈາງເລັກນ້ອຍ ແລະ ນ້ຳຕານສູງ, ແນະນຳຕິດຕາມ',
                'lab_status' => 'Completed',
                'patient_notified' => false
            ],

            // ຜົນກວດທີ 4: Ultrasound ທ້ອງ (ລໍຖ້າທ່ານໝໍເບິ່ງ)
            [
                'lab_code' => 'LAB' . date('Ymd') . '004',
                'test_results' => [
                    'liver' => 'ຕັບໃສຂະໜາດປົກກະຕິ, ເນື້ອຕັບເປັນເອກະພາບ',
                    'gallbladder' => 'ຖົງນ້ຳດີຂະໜາດປົກກະຕິ, ບໍ່ມີກ້ອນ',
                    'kidneys' => 'ໄຕທັງສອງຂ້າງຂະໜາດແລະຮູບຊົງປົກກະຕິ',
                    'spleen' => 'ມ້າມຂະໜາດປົກກະຕິ',
                    'pancreas' => 'ຕັບອ່ອນເບິ່ງເຫັນບາງສ່ວນ ບໍ່ມີຄວາມຜິດປົກກະຕິ'
                ],
                'result_summary' => 'Ultrasound ອະໄວຍະວະພາຍໃນທ້ອງ: ທຸກຢ່າງປົກກະຕິ',
                'reference_values' => 'ຂະໜາດແລະຮູບຊົງອະໄວຍະວະພາຍໃນປົກກະຕິ',
                'interpretation' => 'ບໍ່ພົບຄວາມຜິດປົກກະຕິໃນອະໄວຍະວະພາຍໃນທ້ອງ',
                'images_attachments' => [
                    'ultrasound_abdomen_' . date('Ymd') . '_001.jpg',
                    'ultrasound_abdomen_' . date('Ymd') . '_002.jpg'
                ],
                'lab_status' => 'Completed',
                'patient_notified' => false
            ]
        ];

        foreach ($queueServices as $index => $queueService) {
            if (!isset($labResultsData[$index])) break;

            // ກວດສອບວ່າມີ Lab ແລ້ວບໍ່
            if (Lab::where('queue_service_id', $queueService->id)->exists()) {
                $this->command->warn("⚠️ QueueService #{$queueService->id} ມີ Lab Result ແລ້ວ - ຂ້າມໄປ");
                continue;
            }

            $data = $labResultsData[$index];
            
            // ກຳນົດເວລາຕາມສະຖານະ
            $performedAt = $queueService->started_at ?? now()->subMinutes(60);
            $reviewedAt = in_array($data['lab_status'], ['Doctor_Reviewed', 'Patient_Notified']) 
                         ? now()->subMinutes(30) : null;
            $notifiedAt = $data['patient_notified'] ? now()->subMinutes(10) : null;

            Lab::create([
                'queue_service_id' => $queueService->id,
                'lab_code' => $data['lab_code'],
                'test_results' => $data['test_results'],
                'result_summary' => $data['result_summary'],
                'reference_values' => $data['reference_values'],
                'interpretation' => $data['interpretation'],
                'images_attachments' => $data['images_attachments'] ?? null,
                'performed_by_id' => $labTechnicians->random()->id,
                'performed_at' => $performedAt,
                'reviewed_by_doctor_id' => $reviewedAt ? $doctors->random()->id : null,
                'reviewed_at' => $reviewedAt,
                'patient_notified' => $data['patient_notified'],
                'notified_at' => $notifiedAt,
                'lab_status' => $data['lab_status'],
            ]);

            $serviceName = $queueService->service->service_name;
            $queueNumber = $queueService->queue->queue_number;
            
            $this->command->info("🔬 Queue #{$queueNumber}: {$serviceName} - {$data['lab_status']}");
        }

        // ສະຖິຕິຜົນການກວດ
        $this->displayLabStatistics();

        $this->command->info('✅ Created lab results for queue services');
    }

    /**
     * ສະແດງສະຖິຕິຼົນການກວດ
     */
    private function displayLabStatistics(): void
    {
        $totalLabs = Lab::count();
        $completedLabs = Lab::where('lab_status', 'Completed')->count();
        $reviewedLabs = Lab::where('lab_status', 'Doctor_Reviewed')->count();
        $notifiedLabs = Lab::where('lab_status', 'Patient_Notified')->count();
        $pendingReview = Lab::where('lab_status', 'Completed')->whereNull('reviewed_by_doctor_id')->count();

        // ປະເພດການກວດທີ່ມີ
        $labTypes = Lab::join('queue_services', 'labs.queue_service_id', '=', 'queue_services.id')
                      ->join('services', 'queue_services.service_id', '=', 'services.id')
                      ->selectRaw('services.service_category, COUNT(*) as count')
                      ->groupBy('services.service_category')
                      ->get();

        $this->command->info('📈 Lab Results Statistics:');
        $this->command->info("   - ລວມຜົນກວດ: {$totalLabs} ຜົນ");
        $this->command->info("   - ກວດສຳເລັດ: {$completedLabs} ຜົນ");
        $this->command->info("   - ທ່ານໝໍເບິ່ງແລ້ວ: {$reviewedLabs} ຜົນ");
        $this->command->info("   - ແຈ້ງຄົນໄຂ້ແລ້ວ: {$notifiedLabs} ຜົນ");
        $this->command->info("   - ລໍຖ້າທ່ານໝໍເບິ່ງ: {$pendingReview} ຜົນ");

        if ($labTypes->isNotEmpty()) {
            $this->command->info('🧪 ປະເພດການກວດ:');
            foreach ($labTypes as $type) {
                $categoryName = $this->getLabCategoryName($type->service_category);
                $this->command->info("   - {$categoryName}: {$type->count} ຜົນ");
            }
        }
    }

    /**
     * ແປລະຫັດປະເພດການກວດເປັນພາສາລາວ
     */
    private function getLabCategoryName($category): string
    {
        $categories = [
            'Blood_Test' => 'ກວດເລືອດ',
            'Urine_Test' => 'ກວດປັດສະວະ',
            'X_Ray' => 'ຖ່າຍ X-Ray',
            'Ultrasound' => 'ກວດ Ultrasound',
            'ECG' => 'ກວດຫົວໃຈ',
            'Other' => 'ອື່ນໆ'
        ];

        return $categories[$category] ?? $category;
    }
}
