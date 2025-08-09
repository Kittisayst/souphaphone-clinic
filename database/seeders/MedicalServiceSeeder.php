<?php

namespace Database\Seeders;

use App\Models\MedicalService;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicalServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ຫາ Admin ຫຼື ໝໍຄົນທຳອິດ
        $createdBy = User::where('role', 'admin')->first()?->id ?? 
                     User::where('role', 'doctor')->first()?->id ?? 
                     User::first()?->id ?? 1;

        $services = [
            // ການກວດທົ່ວໄປ
            [
                'service_name' => 'ກວດສຸຂະພາບທົ່ວໄປ',
                'service_code' => 'CHECK01',
                'service_category' => 'examination',
                'description' => 'ການກວດສຸຂະພາບພື້ນຖານ ວັດນ້ຳໜັກ ສ່ວນສູງ ຄວາມດັນເລືອດ',
                'price' => 50000,
                'estimated_duration' => 30,
                'requires_preparation' => false,
                'preparation_instructions' => null,
                'template_fields' => [
                    [
                        'field_name' => 'ນ້ຳໜັກ',
                        'field_type' => 'number',
                        'unit' => 'kg',
                        'normal_range' => '50-80',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ສ່ວນສູງ',
                        'field_type' => 'number',
                        'unit' => 'cm',
                        'normal_range' => '150-180',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ຄວາມດັນເລືອດ',
                        'field_type' => 'text',
                        'unit' => 'mmHg',
                        'normal_range' => '<140/90',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ອຸນຫະພູມ',
                        'field_type' => 'number',
                        'unit' => '°C',
                        'normal_range' => '36-37.5',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ການເຕັ້ນຫົວໃຈ',
                        'field_type' => 'number',
                        'unit' => 'ຄັ້ງ/ນາທີ',
                        'normal_range' => '60-100',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ສະຖານະທົ່ວໄປ',
                        'field_type' => 'select',
                        'options' => "ປົກກະຕິ\nຜິດປົກກະຕິ\nຕ້ອງກວດເພີ່ມ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ໝາຍເຫດ',
                        'field_type' => 'textarea',
                        'is_required' => false,
                    ],
                ],
                'is_active' => true,
                'created_by' => $createdBy,
            ],

            // ການກວດເລືອດ
            [
                'service_name' => 'ກວດເລືອດ CBC',
                'service_code' => 'BLOOD01',
                'service_category' => 'laboratory',
                'description' => 'ການກວດນັບເຊວເລືອດສົມບູນ',
                'price' => 80000,
                'estimated_duration' => 15,
                'requires_preparation' => true,
                'preparation_instructions' => 'ອົດອາຫານ 8-12 ຊົ່ວໂມງກ່ອນເຈາະເລືອດ',
                'template_fields' => [
                    [
                        'field_name' => 'WBC (ເມັດເລືອດຂາວ)',
                        'field_type' => 'number',
                        'unit' => 'cells/μL',
                        'normal_range' => '4,000-11,000',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'RBC (ເມັດເລືອດແດງ)',
                        'field_type' => 'number',
                        'unit' => 'cells/μL',
                        'normal_range' => '4.5-5.5 ລ້ານ',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'Hemoglobin',
                        'field_type' => 'number',
                        'unit' => 'g/dL',
                        'normal_range' => '12-16',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'Hematocrit',
                        'field_type' => 'number',
                        'unit' => '%',
                        'normal_range' => '36-48',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'Platelet (ເກล็ດເລືອດ)',
                        'field_type' => 'number',
                        'unit' => 'cells/μL',
                        'normal_range' => '150,000-450,000',
                        'is_required' => true,
                    ],
                ],
                'is_active' => true,
                'created_by' => $createdBy,
            ],

            [
                'service_name' => 'ກວດນ້ຳຕານ',
                'service_code' => 'BLOOD02',
                'service_category' => 'laboratory',
                'description' => 'ການກວດລະດັບນ້ຳຕານໃນເລືອດ',
                'price' => 25000,
                'estimated_duration' => 10,
                'requires_preparation' => true,
                'preparation_instructions' => 'ອົດອາຫານ 8 ຊົ່ວໂມງກ່ອນເຈາະເລືອດ',
                'template_fields' => [
                    [
                        'field_name' => 'FBS (ນ້ຳຕານກ່ອນອາຫານ)',
                        'field_type' => 'number',
                        'unit' => 'mg/dL',
                        'normal_range' => '70-110',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ຜົນການວິເຄາະ',
                        'field_type' => 'select',
                        'options' => "ປົກກະຕິ\nນ້ຳຕານສູງ\nນ້ຳຕານຕ່ຳ\nຕ້ອງກວດຊໍ້າ",
                        'is_required' => true,
                    ],
                ],
                'is_active' => true,
                'created_by' => $createdBy,
            ],

            // ການກວດດ້ວຍເຄື່ອງ
            [
                'service_name' => 'Ultrasound ທ້ອງ',
                'service_code' => 'ULTRA01',
                'service_category' => 'imaging',
                'description' => 'ການກວດ Ultrasound ອະໄວຍະວະໃນຊ່ອງທ້ອງ',
                'price' => 150000,
                'estimated_duration' => 45,
                'requires_preparation' => true,
                'preparation_instructions' => 'ດື່ມນ້ຳ 4-6 ແກ້ວ 1 ຊົ່ວໂມງກ່ອນກວດ ແລະ ຫ້າມເຂົ້າຫ້ອງນ້ຳ',
                'template_fields' => [
                    [
                        'field_name' => 'ຕັບ',
                        'field_type' => 'select',
                        'options' => "ປົກກະຕິ\nມີຂະໜາດໃຫຍ່\nມີຈຸດຜິດປົກກະຕິ\nອື່ນໆ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ໄຕ',
                        'field_type' => 'select',
                        'options' => "ປົກກະຕິ\nມີກ້ອນ\nມີນ້ຳ\nອື່ນໆ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ຖົງນ້ຳດີ',
                        'field_type' => 'select',
                        'options' => "ປົກກະຕິ\nມີນິ່ວ\nຫນາ\nອື່ນໆ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ລຳໄສ້',
                        'field_type' => 'select',
                        'options' => "ປົກກະຕິ\nຟູມ\nມີນ້ຳ\nອື່ນໆ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ສະຫລຸບຜົນ',
                        'field_type' => 'textarea',
                        'is_required' => true,
                    ],
                ],
                'is_active' => true,
                'created_by' => $createdBy,
            ],

            [
                'service_name' => 'X-Ray ໜ້າເອິກ',
                'service_code' => 'XRAY01',
                'service_category' => 'imaging',
                'description' => 'ການຖ່າຍ X-Ray ບາງຊ່ອງໜ້າເອິກ',
                'price' => 120000,
                'estimated_duration' => 20,
                'requires_preparation' => false,
                'preparation_instructions' => null,
                'template_fields' => [
                    [
                        'field_name' => 'ປອດ',
                        'field_type' => 'select',
                        'options' => "ປົກກະຕິ\nມີເງົາ\nມີຂົວໃສ\nມີນ້ຳ\nອື່ນໆ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ຫົວໃຈ',
                        'field_type' => 'select',
                        'options' => "ປົກກະຕິ\nໃຫຍ່\nຮູບຮ່າງຜິດປົກກະຕິ\nອື່ນໆ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ກະດູກຊີໂຄງ',
                        'field_type' => 'select',
                        'options' => "ປົກກະຕິ\nຫັກ\nຜິດປົກກະຕິ\nອື່ນໆ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ການວິເຄາະ',
                        'field_type' => 'textarea',
                        'is_required' => true,
                    ],
                ],
                'is_active' => true,
                'created_by' => $createdBy,
            ],

            // ການຮັກສາພິເສດ
            [
                'service_name' => 'ຕັດແຜ',
                'service_code' => 'PROC01',
                'service_category' => 'procedure',
                'description' => 'ການຕັດແຜນ້ອຍ ແລະ ການຮັກສາບາດແຜ',
                'price' => 75000,
                'estimated_duration' => 30,
                'requires_preparation' => false,
                'preparation_instructions' => null,
                'template_fields' => [
                    [
                        'field_name' => 'ຕຳແໜ່ງບາດແຜ',
                        'field_type' => 'text',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ຂະໜາດບາດແຜ',
                        'field_type' => 'text',
                        'unit' => 'cm',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ລັກສະນະບາດແຜ',
                        'field_type' => 'select',
                        'options' => "ຕື້ນ\nເລິກ\nໜ້າຍານ\nສະອາດ\nເປື້ອນ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ການຮັກສາ',
                        'field_type' => 'select',
                        'options' => "ລ້າງທຳຄວາມສະອາດ\nຍິບ\nພັນຜ້າ\nໃສ່ຢາຊີວະນາດ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ການນັດຕິດຕາມ',
                        'field_type' => 'select',
                        'options' => "ບໍ່ຕ້ອງ\n3 ວັນ\n1 ອາທິດ\n2 ອາທິດ",
                        'is_required' => true,
                    ],
                ],
                'is_active' => true,
                'created_by' => $createdBy,
            ],

            [
                'service_name' => 'ສັກຢາ',
                'service_code' => 'PROC02',
                'service_category' => 'procedure',
                'description' => 'ການສັກຢາໂດຍການສັກເຂົ້າກ້າມເນື້ອ ຫຼື ເສັ້ນເລືອດ',
                'price' => 35000,
                'estimated_duration' => 15,
                'requires_preparation' => false,
                'preparation_instructions' => null,
                'template_fields' => [
                    [
                        'field_name' => 'ຊື່ຢາ',
                        'field_type' => 'text',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ປະລິມານ',
                        'field_type' => 'text',
                        'unit' => 'ml',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ວິທີການສັກ',
                        'field_type' => 'select',
                        'options' => "ສັກກ້າມເນື້ອ (IM)\nສັກເສັ້ນເລືອດ (IV)\nສັກໃຕ້ຜິວໜັງ (SC)",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ຕຳແໜ່ງທີ່ສັກ',
                        'field_type' => 'select',
                        'options' => "ແຂນຊ້າຍ\nແຂນຂວາ\nຂາຊ້າຍ\nຂາຂວາ\nກົ້ນ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ຜົນຂ້າງຄຽງ',
                        'field_type' => 'select',
                        'options' => "ບໍ່ມີ\nເຈັບ\nບວມ\nແດງ\nອື່ນໆ",
                        'is_required' => true,
                    ],
                ],
                'is_active' => true,
                'created_by' => $createdBy,
            ],

            // ເພີ່ມບໍລິການອື່ນໆ
            [
                'service_name' => 'ກວດຕາ',
                'service_code' => 'EYE01',
                'service_category' => 'examination',
                'description' => 'ການກວດສາຍຕາ ແລະ ສຸຂະພາບຂອງຕາ',
                'price' => 60000,
                'estimated_duration' => 25,
                'requires_preparation' => false,
                'preparation_instructions' => null,
                'template_fields' => [
                    [
                        'field_name' => 'ສາຍຕາຊ້າຍ',
                        'field_type' => 'text',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ສາຍຕາຂວາ',
                        'field_type' => 'text',
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ຄວາມດັນຕາ',
                        'field_type' => 'number',
                        'unit' => 'mmHg',
                        'normal_range' => '10-21',
                        'is_required' => true,
                    ],
                ],
                'is_active' => true,
                'created_by' => $createdBy,
            ],

            [
                'service_name' => 'ກວດຟັນ',
                'service_code' => 'DENTAL01',
                'service_category' => 'examination',
                'description' => 'ການກວດສຸຂະພາບຊ່ອງປາກ ແລະ ຟັນ',
                'price' => 40000,
                'estimated_duration' => 20,
                'requires_preparation' => false,
                'preparation_instructions' => null,
                'template_fields' => [
                    [
                        'field_name' => 'ສະຖານະຟັນ',
                        'field_type' => 'select',
                        'options' => "ດີ\nມີຜຸ\nສົ່ຍ\nຫຼຸດ\nຜິດປົກກະຕິ",
                        'is_required' => true,
                    ],
                    [
                        'field_name' => 'ເໜືອຍ',
                        'field_type' => 'select',
                        'options' => "ປົກກະຕິ\nອັກເສບ\nເລືອດອອກ\nບວມ",
                        'is_required' => true,
                    ],
                ],
                'is_active' => true,
                'created_by' => $createdBy,
            ],
        ];

        foreach ($services as $service) {
            MedicalService::create($service);
        }

        $this->command->info('✅ ສ້າງຂໍ້ມູນບໍລິການການກວດສຳເລັດ: ' . count($services) . ' ລາຍການ');
    }
}
