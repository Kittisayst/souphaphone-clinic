<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('💊 ສ້າງຂໍ້ມູນຢາ...');

        $medicines = [
            // ຢາແກ້ໄຂ້
            [
                'medicine_code' => 'MED0001',
                'medicine_name' => 'Paracetamol 500mg',
                'generic_name' => 'Paracetamol',
                'brand_name' => 'Tylenol',
                'medicine_type' => 'Tablet',
                'strength' => '500mg',
                'unit' => 'tablet',
                'unit_price' => 500,
                'wholesale_price' => 300,
                'current_stock' => 1000,
                'minimum_stock' => 100,
                'maximum_stock' => 2000,
                'expiry_date' => now()->addYears(2),
                'batch_number' => 'PAR2024001',
                'dosage_instructions' => '1-2 ເມັດ ທຸກ 4-6 ຊົ່ວໂມງ',
                'side_effects' => 'ອາດມີອາການວຽນຫົວ, ຄື່ນເຫຍື່ອ',
                'manufacturer' => 'ບໍລິສັດຢາ ABC',
                'supplier' => 'ຜູ້ສະໜອງຢາ XYZ',
                'requires_prescription' => false,
                'is_active' => true,
            ],

            // ຢາປະຕິຊີວະນະ
            [
                'medicine_code' => 'MED0002',
                'medicine_name' => 'Amoxicillin 250mg',
                'generic_name' => 'Amoxicillin',
                'brand_name' => 'Amoxil',
                'medicine_type' => 'Capsule',
                'strength' => '250mg',
                'unit' => 'capsule',
                'unit_price' => 1200,
                'wholesale_price' => 800,
                'current_stock' => 500,
                'minimum_stock' => 50,
                'maximum_stock' => 1000,
                'expiry_date' => now()->addYears(1),
                'batch_number' => 'AMX2024001',
                'dosage_instructions' => '1 ແຄັບຊູນ ວັນລະ 3 ເທື່ອ',
                'side_effects' => 'ອາດມີອາການແພ້, ທ້ອງເສຍ',
                'contraindications' => 'ຫ້າມໃຊ້ຖ້າແພ້ Penicillin',
                'manufacturer' => 'ບໍລິສັດຢາ DEF',
                'supplier' => 'ຜູ້ສະໜອງຢາ ABC',
                'requires_prescription' => true,
                'is_active' => true,
            ],

            // ຢາແກ້ເຈັບ
            [
                'medicine_code' => 'MED0003',
                'medicine_name' => 'Ibuprofen 400mg',
                'generic_name' => 'Ibuprofen',
                'brand_name' => 'Brufen',
                'medicine_type' => 'Tablet',
                'strength' => '400mg',
                'unit' => 'tablet',
                'unit_price' => 800,
                'wholesale_price' => 500,
                'current_stock' => 800,
                'minimum_stock' => 80,
                'maximum_stock' => 1500,
                'expiry_date' => now()->addMonths(18),
                'batch_number' => 'IBU2024001',
                'dosage_instructions' => '1 ເມັດ ວັນລະ 2-3 ເທື່ອ',
                'side_effects' => 'ອາດມີອາການເຈັບກະເພາະ',
                'contraindications' => 'ຫ້າມໃຊ້ຖ້າມີແຜນກະເພາະ',
                'manufacturer' => 'ບໍລິສັດຢາ GHI',
                'requires_prescription' => false,
                'is_active' => true,
            ],

            // ຢາແກ້ໄອ
            [
                'medicine_code' => 'MED0004',
                'medicine_name' => 'Cough Syrup',
                'generic_name' => 'Dextromethorphan',
                'brand_name' => 'Benadryl DM',
                'medicine_type' => 'Syrup',
                'strength' => '15mg/5ml',
                'unit' => 'ml',
                'unit_price' => 150,
                'wholesale_price' => 100,
                'current_stock' => 2000,
                'minimum_stock' => 200,
                'maximum_stock' => 5000,
                'expiry_date' => now()->addMonths(12),
                'batch_number' => 'CSY2024001',
                'dosage_instructions' => '5-10ml ທຸກ 4 ຊົ່ວໂມງ',
                'side_effects' => 'ອາດມີອາການງ່ວງນອນ',
                'manufacturer' => 'ບໍລິສັດຢາ JKL',
                'requires_prescription' => false,
                'is_active' => true,
            ],

            // ຢາລົດຄວາມດັນ
            [
                'medicine_code' => 'MED0005',
                'medicine_name' => 'Amlodipine 5mg',
                'generic_name' => 'Amlodipine',
                'brand_name' => 'Norvasc',
                'medicine_type' => 'Tablet',
                'strength' => '5mg',
                'unit' => 'tablet',
                'unit_price' => 1500,
                'wholesale_price' => 1000,
                'current_stock' => 300,
                'minimum_stock' => 30,
                'maximum_stock' => 600,
                'expiry_date' => now()->addYears(3),
                'batch_number' => 'AML2024001',
                'dosage_instructions' => '1 ເມັດ ວັນລະ 1 ເທື່ອ ຕອນເຊົ້າ',
                'side_effects' => 'ອາດມີອາການຂາບວມ, ວຽນຫົວ',
                'contraindications' => 'ຫ້າມໃຊ້ຖ້າມີການແພ້',
                'manufacturer' => 'ບໍລິສັດຢາ MNO',
                'requires_prescription' => true,
                'is_active' => true,
            ],

            // ຢາລົດນ້ຳຕານ
            [
                'medicine_code' => 'MED0006',
                'medicine_name' => 'Metformin 500mg',
                'generic_name' => 'Metformin',
                'brand_name' => 'Glucophage',
                'medicine_type' => 'Tablet',
                'strength' => '500mg',
                'unit' => 'tablet',
                'unit_price' => 1000,
                'wholesale_price' => 700,
                'current_stock' => 400,
                'minimum_stock' => 40,
                'maximum_stock' => 800,
                'expiry_date' => now()->addYears(2),
                'batch_number' => 'MET2024001',
                'dosage_instructions' => '1 ເມັດ ວັນລະ 2 ເທື່ອ ກັບອາຫານ',
                'side_effects' => 'ອາດມີອາການທ້ອງເສຍ, ຄື່ນເຫຍື່ອ',
                'contraindications' => 'ຫ້າມໃຊ້ຖ້າມີບັນຫາໄຕ',
                'manufacturer' => 'ບໍລິສັດຢາ PQR',
                'requires_prescription' => true,
                'is_active' => true,
            ],

            // ຢາວິຕາມິນ
            [
                'medicine_code' => 'MED0007',
                'medicine_name' => 'Vitamin B Complex',
                'generic_name' => 'B-Complex',
                'brand_name' => 'Berocca',
                'medicine_type' => 'Tablet',
                'strength' => '1 tablet',
                'unit' => 'tablet',
                'unit_price' => 2000,
                'wholesale_price' => 1500,
                'current_stock' => 200,
                'minimum_stock' => 20,
                'maximum_stock' => 500,
                'expiry_date' => now()->addYears(2),
                'batch_number' => 'VIT2024001',
                'dosage_instructions' => '1 ເມັດ ວັນລະ 1 ເທື່ອ',
                'side_effects' => 'ປົກກະຕິບໍ່ມີຜົນຂ້າງຄຽງ',
                'manufacturer' => 'ບໍລິສັດຢາ STU',
                'requires_prescription' => false,
                'is_active' => true,
            ],

            // ຢາທາ
            [
                'medicine_code' => 'MED0008',
                'medicine_name' => 'Betadine Solution',
                'generic_name' => 'Povidone Iodine',
                'brand_name' => 'Betadine',
                'medicine_type' => 'Other',
                'strength' => '10%',
                'unit' => 'ml',
                'unit_price' => 200,
                'wholesale_price' => 150,
                'current_stock' => 1000,
                'minimum_stock' => 100,
                'maximum_stock' => 2000,
                'expiry_date' => now()->addYears(3),
                'batch_number' => 'BET2024001',
                'dosage_instructions' => 'ທາພາຍນອກຕາມຄວາມຈຳເປັນ',
                'side_effects' => 'ອາດມີອາການແພ້ທ້ອງຖິ່ນ',
                'manufacturer' => 'ບໍລິສັດຢາ VWX',
                'requires_prescription' => false,
                'is_active' => true,
            ],
        ];

        foreach ($medicines as $medicineData) {
            Medicine::create($medicineData);
        }

        $this->command->info("✅ ສ້າງຢາ: " . count($medicines) . " ຊະນິດ");
    }
}
