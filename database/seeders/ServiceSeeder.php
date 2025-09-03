<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $this->command->info('🔄 Creating clinic services...');

        $services = [
            // ການກວດທົ່ວໄປ
            [
                'service_code' => 'CONS001',
                'service_name' => 'ກວດທົ່ວໄປ',
                'service_category' => 'Consultation',
                'base_price' => 80000,
                'description' => 'ການກວດສຸຂະພາບທົ່ວໄປ ແລະ ປຶກສາທ່ານໝໍ',
                'duration_minutes' => 30,
                'requires_room' => true,
                'room_type_required' => 'Consultation',
                'has_lab_result' => false,
            ],
            [
                'service_code' => 'CONS002', 
                'service_name' => 'ປຶກສາທ່ານໝໍຊ່ຽວຊານ',
                'service_category' => 'Consultation',
                'base_price' => 120000,
                'description' => 'ການປຶກສາທ່ານໝໍຊ່ຽວຊານດ້ານຕ່າງໆ',
                'duration_minutes' => 45,
                'requires_room' => true,
                'room_type_required' => 'Consultation',
                'has_lab_result' => false,
            ],

            // ການກວດ X-Ray
            [
                'service_code' => 'XRAY001',
                'service_name' => 'ຖ່າຍ X-Ray ໜ້າອົກ',
                'service_category' => 'X_Ray',
                'base_price' => 150000,
                'description' => 'ຖ່າຍ X-Ray ໜ້າອົກ ກວດປອດ ແລະ ຫົວໃຈ',
                'duration_minutes' => 15,
                'requires_room' => true,
                'room_type_required' => 'X_Ray',
                'has_lab_result' => true,
                'template_lab' => json_encode([
                    'fields' => [
                        'examination' => ['type' => 'text', 'label' => 'ການກວດ', 'required' => true],
                        'findings' => ['type' => 'textarea', 'label' => 'ສິ່ງທີ່ພົບ', 'required' => true],
                        'impression' => ['type' => 'textarea', 'label' => 'ຄວາມຄິດເຫັນ', 'required' => true],
                        'recommendation' => ['type' => 'textarea', 'label' => 'ຂໍ້ແນະນຳ', 'required' => false]
                    ]
                ])
            ],
            [
                'service_code' => 'XRAY002',
                'service_name' => 'ຖ່າຍ X-Ray ກະດູກ',
                'service_category' => 'X_Ray', 
                'base_price' => 180000,
                'description' => 'ຖ່າຍ X-Ray ກະດູກ ກວດການແຜກຫັກ ຫຼື ບາດເຈັບ',
                'duration_minutes' => 20,
                'requires_room' => true,
                'room_type_required' => 'X_Ray',
                'has_lab_result' => true,
                'template_lab' => json_encode([
                    'fields' => [
                        'bone_examined' => ['type' => 'text', 'label' => 'ກະດູກທີ່ກວດ', 'required' => true],
                        'findings' => ['type' => 'textarea', 'label' => 'ຜົນການກວດ', 'required' => true],
                        'fracture_detected' => ['type' => 'boolean', 'label' => 'ພົບການແຜກຫັກ', 'required' => true]
                    ]
                ])
            ],

            // ການກວດ Ultrasound
            [
                'service_code' => 'ULTRA001',
                'service_name' => 'Ultrasound ທ້ອງ',
                'service_category' => 'Ultrasound',
                'base_price' => 200000,
                'description' => 'ກວດ Ultrasound ອະໄວຍະວະໃນທ້ອງ',
                'duration_minutes' => 30,
                'requires_room' => true,
                'room_type_required' => 'Ultrasound',
                'has_lab_result' => true,
                'template_lab' => json_encode([
                    'fields' => [
                        'organs_examined' => ['type' => 'text', 'label' => 'ອະໄວຍະວະທີ່ກວດ', 'required' => true],
                        'findings' => ['type' => 'textarea', 'label' => 'ຜົນການກວດ', 'required' => true],
                        'measurements' => ['type' => 'text', 'label' => 'ການວັດແທກ', 'required' => false]
                    ]
                ])
            ],
            [
                'service_code' => 'ULTRA002',
                'service_name' => 'Ultrasound ຄໍ/ຕັບ',
                'service_category' => 'Ultrasound',
                'base_price' => 250000,
                'description' => 'ກວດ Ultrasound ຕັບໃສ ແລະ ຖົງນ້ຳດີ',
                'duration_minutes' => 25,
                'requires_room' => true,
                'room_type_required' => 'Ultrasound', 
                'has_lab_result' => true,
                'template_lab' => json_encode([
                    'fields' => [
                        'liver_condition' => ['type' => 'textarea', 'label' => 'ສະພາບຕັບໃສ', 'required' => true],
                        'gallbladder_condition' => ['type' => 'textarea', 'label' => 'ສະພາບຖົງນ້ຳດີ', 'required' => true],
                        'abnormalities' => ['type' => 'textarea', 'label' => 'ຄວາມຜິດປົກກະຕິ', 'required' => false]
                    ]
                ])
            ],

            // ການກວດເລືອດ
            [
                'service_code' => 'BLOOD001',
                'service_name' => 'ກວດເລືອດທົ່ວໄປ (CBC)',
                'service_category' => 'Blood_Test',
                'base_price' => 50000,
                'description' => 'ກວດເລືອດນັບຈຳນວນເຊນ Complete Blood Count',
                'duration_minutes' => 10,
                'requires_room' => true,
                'room_type_required' => 'Laboratory',
                'has_lab_result' => true,
                'template_lab' => json_encode([
                    'fields' => [
                        'hemoglobin' => ['type' => 'number', 'label' => 'Hemoglobin (g/dL)', 'normal_range' => '12-15', 'required' => true],
                        'hematocrit' => ['type' => 'number', 'label' => 'Hematocrit (%)', 'normal_range' => '36-46', 'required' => true],
                        'white_blood_cells' => ['type' => 'number', 'label' => 'WBC (cells/μL)', 'normal_range' => '4000-11000', 'required' => true],
                        'platelets' => ['type' => 'number', 'label' => 'Platelets (cells/μL)', 'normal_range' => '150000-400000', 'required' => true]
                    ]
                ])
            ],
            [
                'service_code' => 'BLOOD002',
                'service_name' => 'ກວດນ້ຳຕານໃນເລືອດ',
                'service_category' => 'Blood_Test',
                'base_price' => 30000,
                'description' => 'ກວດລະດັບນ້ຳຕານໃນເລືອດ',
                'duration_minutes' => 10,
                'requires_room' => true,
                'room_type_required' => 'Laboratory',
                'has_lab_result' => true,
                'template_lab' => json_encode([
                    'fields' => [
                        'glucose_level' => ['type' => 'number', 'label' => 'Glucose (mg/dL)', 'normal_range' => '70-110', 'required' => true],
                        'test_type' => ['type' => 'select', 'label' => 'ປະເພດການກວດ', 'options' => ['Fasting', 'Random'], 'required' => true]
                    ]
                ])
            ],
            [
                'service_code' => 'BLOOD003',
                'service_name' => 'ກວດໄຂມັນໃນເລືອດ',
                'service_category' => 'Blood_Test',
                'base_price' => 80000,
                'description' => 'ກວດລະດັບໄຂມັນ ແລະ Cholesterol',
                'duration_minutes' => 15,
                'requires_room' => true,
                'room_type_required' => 'Laboratory',
                'has_lab_result' => true,
                'template_lab' => json_encode([
                    'fields' => [
                        'total_cholesterol' => ['type' => 'number', 'label' => 'Total Cholesterol (mg/dL)', 'normal_range' => '<200', 'required' => true],
                        'hdl_cholesterol' => ['type' => 'number', 'label' => 'HDL (mg/dL)', 'normal_range' => '>40', 'required' => true],
                        'ldl_cholesterol' => ['type' => 'number', 'label' => 'LDL (mg/dL)', 'normal_range' => '<130', 'required' => true],
                        'triglycerides' => ['type' => 'number', 'label' => 'Triglycerides (mg/dL)', 'normal_range' => '<150', 'required' => true]
                    ]
                ])
            ],

            // ການກວດປັດສະວະ
            [
                'service_code' => 'URINE001',
                'service_name' => 'ກວດປັດສະວະທົ່ວໄປ',
                'service_category' => 'Urine_Test',
                'base_price' => 40000,
                'description' => 'ກວດປັດສະວະທົ່ວໄປ ແລະ ກວດການຕິດເຊື້ອ',
                'duration_minutes' => 15,
                'requires_room' => true,
                'room_type_required' => 'Laboratory',
                'has_lab_result' => true,
                'template_lab' => json_encode([
                    'fields' => [
                        'color' => ['type' => 'text', 'label' => 'ສີ', 'required' => true],
                        'clarity' => ['type' => 'text', 'label' => 'ຄວາມໃສ', 'required' => true],
                        'protein' => ['type' => 'select', 'label' => 'Protein', 'options' => ['Negative', 'Trace', '+', '++', '+++'], 'required' => true],
                        'glucose' => ['type' => 'select', 'label' => 'Glucose', 'options' => ['Negative', 'Trace', '+', '++'], 'required' => true],
                        'white_blood_cells' => ['type' => 'text', 'label' => 'WBC (/hpf)', 'required' => true],
                        'red_blood_cells' => ['type' => 'text', 'label' => 'RBC (/hpf)', 'required' => true]
                    ]
                ])
            ],

            // ການກວດຫົວໃຈ
            [
                'service_code' => 'ECG001',
                'service_name' => 'ກວດຫົວໃຈ ECG',
                'service_category' => 'ECG',
                'base_price' => 80000,
                'description' => 'ກວດການເຕັ້ນ ແລະ ລິດຍະຂອງຫົວໃຈ',
                'duration_minutes' => 20,
                'requires_room' => true,
                'room_type_required' => 'General',
                'has_lab_result' => true,
                'template_lab' => json_encode([
                    'fields' => [
                        'heart_rate' => ['type' => 'number', 'label' => 'Heart Rate (bpm)', 'normal_range' => '60-100', 'required' => true],
                        'rhythm' => ['type' => 'text', 'label' => 'ລິດຍະຫົວໃຈ', 'required' => true],
                        'interpretation' => ['type' => 'textarea', 'label' => 'ການຕີຄວາມ', 'required' => true],
                        'abnormalities' => ['type' => 'textarea', 'label' => 'ຄວາມຜິດປົກກະຕິ', 'required' => false]
                    ]
                ])
            ],

            // ການກວດພິເສດອື່ນໆ
            [
                'service_code' => 'OTHER001',
                'service_name' => 'ການປິ່ນປົວບາດແຜ',
                'service_category' => 'Other',
                'base_price' => 60000,
                'description' => 'ການທຳຄວາມສະອາດ ແລະ ປິ່ນປົວບາດແຜ',
                'duration_minutes' => 20,
                'requires_room' => true,
                'room_type_required' => 'General',
                'has_lab_result' => false,
            ],
            [
                'service_code' => 'OTHER002',
                'service_name' => 'ການສັກຢາປ້ອງກັນ',
                'service_category' => 'Other',
                'base_price' => 40000,
                'description' => 'ການສັກຢາປ້ອງກັນໂລກຕ່າງໆ',
                'duration_minutes' => 10,
                'requires_room' => true,
                'room_type_required' => 'General',
                'has_lab_result' => false,
            ]
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        $this->command->info('✅ Created ' . count($services) . ' services successfully');
    }
}
