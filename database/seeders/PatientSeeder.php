<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $patients = [
            [
                'first_name' => 'ສົມຊາຍ',
                'last_name' => 'ວົງດາລາ',
                'phone' => '020 55555555',
                'email' => 'somchai@email.com',
                'address' => 'ບ້ານ ຫ້ວຍຫີນ, ເມືອງ ຈັນທະບູລີ, ວຽງຈັນ',
                'birth_date' => Carbon::parse('1985-03-15'),
                'gender' => 'male',
                'id_card_number' => '1234567890123',
                'emergency_contact' => [
                    [
                        'name' => 'ນາງ ສົມຍິງ ວົງດາລາ',
                        'phone' => '020 66666666',
                        'relationship' => 'ພັນລະຍາ'
                    ]
                ],
                'allergies' => [
                    [
                        'medicine_name' => 'Penicillin',
                        'reaction' => 'ຜື່ນແດງ, ຄັນ',
                        'severity' => 'moderate'
                    ]
                ],
                'notes' => 'ຄົນໄຂ້ປະຈຳ, ມີປະຫວັດເຈັບເບົາຫວານ',
                'is_active' => true,
            ],
            [
                'first_name' => 'ນາງ ສົມຍິງ',
                'last_name' => 'ພາວົງສາ',
                'phone' => '030 77777777',
                'email' => 'somying@email.com',
                'address' => 'ບ້ານ ນາຄໍ, ເມືອງ ໄຊເສດຖາ, ວຽງຈັນ',
                'birth_date' => Carbon::parse('1990-07-22'),
                'gender' => 'female',
                'id_card_number' => '2345678901234',
                'emergency_contact' => [
                    [
                        'name' => 'ທ້າວ ບຸນມີ ພາວົງສາ',
                        'phone' => '030 88888888',
                        'relationship' => 'ສາມີ'
                    ]
                ],
                'allergies' => [],
                'notes' => 'ມີລູກນ້ອຍ 2 ຄົນ',
                'is_active' => true,
            ],
            [
                'first_name' => 'ເດັກຊາຍ ນ້ອງໂຕ',
                'last_name' => 'ຈັນທະວົງ',
                'phone' => '020 99999999',
                'email' => null,
                'address' => 'ບ້ານ ດົງດອກ, ເມືອງ ສີໂຄດຕະບອງ, ວຽງຈັນ',
                'birth_date' => Carbon::parse('2015-12-10'),
                'gender' => 'male',
                'id_card_number' => null,
                'emergency_contact' => [
                    [
                        'name' => 'ນາງ ມານີ ຈັນທະວົງ',
                        'phone' => '020 99999999',
                        'relationship' => 'ແມ່'
                    ]
                ],
                'allergies' => [
                    [
                        'medicine_name' => 'ຢາປວດຫົວ',
                        'reaction' => 'ຄື່ນ, ອ້ວກ',
                        'severity' => 'mild'
                    ]
                ],
                'notes' => 'ເດັກນ້ອຍ ອາຍຸ 8 ປີ, ມາກວດປະຈຳ',
                'is_active' => true,
            ],
            [
                'first_name' => 'ທ້າວ ບຸນຊູ',
                'last_name' => 'ສີວິລາຍ',
                'phone' => '020 11111111',
                'email' => 'bounchu@email.com',
                'address' => 'ບ້ານ ວັດຈັນ, ເມືອງ ວັດຈັນ, ວຽງຈັນ',
                'birth_date' => Carbon::parse('1955-01-30'),
                'gender' => 'male',
                'id_card_number' => '5555555555555',
                'emergency_contact' => [
                    [
                        'name' => 'ທ້າວ ສົມພຽງ ສີວິລາຍ',
                        'phone' => '020 22222222',
                        'relationship' => 'ລູກຊາຍ'
                    ]
                ],
                'allergies' => [
                    [
                        'medicine_name' => 'Aspirin',
                        'reaction' => 'ບັງເອີນເລືອດ',
                        'severity' => 'severe'
                    ],
                    [
                        'medicine_name' => 'Ibuprofen',
                        'reaction' => 'ເຈັບຄໍ',
                        'severity' => 'moderate'
                    ]
                ],
                'notes' => 'ຜູ້ສູງອາຍຸ, ມີປະຫວັດຄວາມດັນເລືອດສູງ, ຕ້ອງລະວັງການໃຊ້ຢາ',
                'is_active' => true,
            ],
            [
                'first_name' => 'ນາງ ສົມສີ',
                'last_name' => 'ຄຳພະເນົາ',
                'phone' => '030 33333333',
                'email' => 'somsee@email.com',
                'address' => 'ບ້ານ ທ່າເດື່ອ, ເມືອງ ຫາດຊາຍຟອງ, ວຽງຈັນ',
                'birth_date' => Carbon::parse('1988-11-05'),
                'gender' => 'female',
                'id_card_number' => '3456789012345',
                'emergency_contact' => [
                    [
                        'name' => 'ທ້າວ ວິໄລ ຄຳພະເນົາ',
                        'phone' => '030 44444444',
                        'relationship' => 'ສາມີ'
                    ]
                ],
                'allergies' => [],
                'notes' => 'ກຳລັງຖືລູກ ເດືອນທີ 6',
                'is_active' => true,
            ],
            [
                'first_name' => 'ເດັກຍິງ ນ້ອງນາງ',
                'last_name' => 'ບົວຄຳ',
                'phone' => '020 55555666',
                'email' => null,
                'address' => 'ບ້ານ ນາຊາຍ, ເມືອງ ທຸ່ລະຄົມ, ວຽງຈັນ',
                'birth_date' => Carbon::parse('2018-05-18'),
                'gender' => 'female',
                'id_card_number' => null,
                'emergency_contact' => [
                    [
                        'name' => 'ນາງ ສົມທິດ ບົວຄຳ',
                        'phone' => '020 55555666',
                        'relationship' => 'ແມ່'
                    ]
                ],
                'allergies' => [],
                'notes' => 'ເດັກນ້ອຍ ອາຍຸ 5 ປີ, ມາກວດລຳຕົວ',
                'is_active' => true,
            ],
            [
                'first_name' => 'ທ້າວ ວິລາພອນ',
                'last_name' => 'ແສງດາວ',
                'phone' => '030 77788899',
                'email' => 'wilaphon@email.com',
                'address' => 'ບ້ານ ບໍ່ແກ້ວ, ເມືອງ ປາກງື່ມ, ວຽງຈັນ',
                'birth_date' => Carbon::parse('1975-09-12'),
                'gender' => 'male',
                'id_card_number' => '7777777777777',
                'emergency_contact' => [
                    [
                        'name' => 'ນາງ ມະລິ ແສງດາວ',
                        'phone' => '030 99988877',
                        'relationship' => 'ພັນລະຍາ'
                    ]
                ],
                'allergies' => [
                    [
                        'medicine_name' => 'ຢາຕ້ານການອັກເສບ',
                        'reaction' => 'ຄັນຜື່ນ',
                        'severity' => 'mild'
                    ]
                ],
                'notes' => 'ກິດຈະກອນໜັກ, ມີບັນຫາກະດູກ',
                'is_active' => true,
            ],
            [
                'first_name' => 'ນາງ ວັນເພັນ',
                'last_name' => 'ສຸກສະໜ່າຍ',
                'phone' => '020 11122233',
                'email' => 'wanpen@email.com',
                'address' => 'ບ້ານ ແຂວງໃໝ່, ເມືອງ ຊົນນະບູລີ, ວຽງຈັນ',
                'birth_date' => Carbon::parse('1992-02-28'),
                'gender' => 'female',
                'id_card_number' => '9999999999999',
                'emergency_contact' => [
                    [
                        'name' => 'ທ້າວ ພຸດສະດີ ສຸກສະໜ່າຍ',
                        'phone' => '020 33322211',
                        'relationship' => 'ສາມີ'
                    ]
                ],
                'allergies' => [],
                'notes' => 'ມີແພ້ອາກາດ, ໄອເລື້ອຍໆ',
                'is_active' => true,
            ]
        ];

        foreach ($patients as $patientData) {
            Patient::create($patientData);
        }

        // ສ້າງຄົນໄຂ້ເພີ່ມອີກ 20 ຄົນແບບ random
        for ($i = 1; $i <= 20; $i++) {
            Patient::create([
                'first_name' => 'ຄົນໄຂ້ທົດສອບ ' . $i,
                'last_name' => 'ນາມສະກຸນ ' . $i,
                'phone' => '020 ' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'email' => 'patient' . $i . '@test.com',
                'address' => 'ທີ່ຢູ່ທົດສອບ ' . $i . ', ວຽງຈັນ',
                'birth_date' => Carbon::now()->subYears(rand(5, 80))->subDays(rand(1, 365)),
                'gender' => ['male', 'female'][rand(0, 1)],
                'id_card_number' => str_pad(rand(1000000000000, 9999999999999), 13, '0', STR_PAD_LEFT),
                'emergency_contact' => [
                    [
                        'name' => 'ຜູ້ຕິດຕໍ່ສຸກເສີນ ' . $i,
                        'phone' => '020 ' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                        'relationship' => ['ພໍ່', 'ແມ່', 'ສາມີ', 'ພັນລະຍາ', 'ລູກ'][rand(0, 4)]
                    ]
                ],
                'allergies' => rand(0, 1) ? [
                    [
                        'medicine_name' => ['Penicillin', 'Aspirin', 'Paracetamol'][rand(0, 2)],
                        'reaction' => ['ຜື່ນແດງ', 'ຄັນ', 'ຄື່ນ'][rand(0, 2)],
                        'severity' => ['mild', 'moderate', 'severe'][rand(0, 2)]
                    ]
                ] : [],
                'notes' => 'ຄົນໄຂ້ທົດສອບ ເລກທີ ' . $i,
                'is_active' => true,
            ]);
        }
    }
}