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
        $this->command->info('🔄 Creating common medicines...');

        $medicines = [
            // ຢາແກ້ໄຂ້ແກ້ປວດ
            [
                'medicine_code' => 'PARA500',
                'medicine_name' => 'Paracetamol 500mg',
                'generic_name' => 'Paracetamol',
                'medicine_type' => 'ແກ້ໄຂ້ແກ້ປວດ',
                'unit' => 'ແຜັດ',
                'strength' => '500mg',
                'manufacturer' => 'ບໍລິສັດຢາລາວ',
                'stock_quantity' => 1000,
                'min_stock_level' => 50,
                'unit_price' => 1500,
                'expiry_date' => now()->addYears(2),
                'storage_condition' => 'ເກັບໃນອຸນຫະພູມຫ້ອງ, ຫ່າງຈາກຄວາມຊື້ນ',
            ],
            [
                'medicine_code' => 'IBU400',
                'medicine_name' => 'Ibuprofen 400mg',
                'generic_name' => 'Ibuprofen',
                'medicine_type' => 'ແກ້ໄຂ້ແກ້ປວດ',
                'unit' => 'ແຜັດ',
                'strength' => '400mg',
                'manufacturer' => 'Pharma International',
                'stock_quantity' => 800,
                'min_stock_level' => 40,
                'unit_price' => 2000,
                'expiry_date' => now()->addMonths(18),
                'storage_condition' => 'ເກັບໃນອຸນຫະພູມຫ້ອງ',
            ],

            // ຢາຊີວະພາບ (ແກ້ຊ້ອນ)
            [
                'medicine_code' => 'AMOXI500',
                'medicine_name' => 'Amoxicillin 500mg',
                'generic_name' => 'Amoxicillin',
                'medicine_type' => 'ຊີວະພາບ',
                'unit' => 'ແຄັບຊູນ',
                'strength' => '500mg',
                'manufacturer' => 'ຢາໄທ',
                'stock_quantity' => 600,
                'min_stock_level' => 30,
                'unit_price' => 3000,
                'expiry_date' => now()->addYears(3),
                'storage_condition' => 'ເກັບໃນທີ່ແຫ້ງ, ອຈນຫະພູມຫ້ອງ',
            ],
            [
                'medicine_code' => 'CIPRO500',
                'medicine_name' => 'Ciprofloxacin 500mg',
                'generic_name' => 'Ciprofloxacin',
                'medicine_type' => 'ຊີວະພາບ',
                'unit' => 'ແຜັດ',
                'strength' => '500mg',
                'manufacturer' => 'ຢາອິນເດຍ',
                'stock_quantity' => 400,
                'min_stock_level' => 20,
                'unit_price' => 4000,
                'expiry_date' => now()->addYears(2),
                'storage_condition' => 'ເກັບຫ່າງຈາກແສງແດດ',
            ],

            // ຢາແກ້ອັກເສບ
            [
                'medicine_code' => 'DICLO50',
                'medicine_name' => 'Diclofenac 50mg',
                'generic_name' => 'Diclofenac Sodium',
                'medicine_type' => 'ແກ້ອັກເສບ',
                'unit' => 'ແຜັດ',
                'strength' => '50mg',
                'manufacturer' => 'ຢາຫວຽດນາມ',
                'stock_quantity' => 500,
                'min_stock_level' => 25,
                'unit_price' => 2500,
                'expiry_date' => now()->addMonths(24),
                'storage_condition' => 'ເກັບໃນອຸນຫະພູມຫ້ອງ',
            ],
            [
                'medicine_code' => 'PRED5',
                'medicine_name' => 'Prednisolone 5mg',
                'generic_name' => 'Prednisolone',
                'medicine_type' => 'ແກ້ອັກເສບ',
                'unit' => 'ແຜັດ',
                'strength' => '5mg',
                'manufacturer' => 'ບໍລິສັດຢາລາວ',
                'stock_quantity' => 300,
                'min_stock_level' => 15,
                'unit_price' => 3500,
                'expiry_date' => now()->addYears(2),
                'storage_condition' => 'ເກັບໃນທີ່ແຫ້ງ, ຫ່າງຈາກແສງ',
            ],

            // ຢາລະບົບຫົວໃຈ
            [
                'medicine_code' => 'AMLOD5',
                'medicine_name' => 'Amlodipine 5mg',
                'generic_name' => 'Amlodipine',
                'medicine_type' => 'ຄວາມດັນເລືອດ',
                'unit' => 'ແຜັດ',
                'strength' => '5mg',
                'manufacturer' => 'Pharma International',
                'stock_quantity' => 400,
                'min_stock_level' => 20,
                'unit_price' => 2800,
                'expiry_date' => now()->addYears(3),
                'storage_condition' => 'ເກັບໃນອຸນຫະພູມຫ້ອງ',
            ],

            // ຢາລະບົບຍ່ອຍອາຫານ
            [
                'medicine_code' => 'OMEP20',
                'medicine_name' => 'Omeprazole 20mg',
                'generic_name' => 'Omeprazole',
                'medicine_type' => 'ລະບົບຍ່ອຍອາຫານ',
                'unit' => 'ແຄັບຊູນ',
                'strength' => '20mg',
                'manufacturer' => 'ຢາໄທ',
                'stock_quantity' => 350,
                'min_stock_level' => 20,
                'unit_price' => 3200,
                'expiry_date' => now()->addMonths(30),
                'storage_condition' => 'ເກັບໃນທີ່ແຫ້ງ, ອຸນຫະພູມຫ້ອງ',
            ],

            // ຢາແກ້ໄອ
            [
                'medicine_code' => 'COUGH100',
                'medicine_name' => 'Cough Syrup 100ml',
                'generic_name' => 'Dextromethorphan',
                'medicine_type' => 'ແກ້ໄອ',
                'unit' => 'ຂວດ',
                'strength' => '15mg/5ml',
                'manufacturer' => 'ບໍລິສັດຢາລາວ',
                'stock_quantity' => 200,
                'min_stock_level' => 15,
                'unit_price' => 25000,
                'expiry_date' => now()->addYears(2),
                'storage_condition' => 'ເກັບໃນອຸນຫະພູມຫ້ອງ, ປິດໃສ່ແໜ້ນ',
            ],

            // ຢາທາພື້ນທີ່
            [
                'medicine_code' => 'BETA10',
                'medicine_name' => 'Betamethasone Cream 10g',
                'generic_name' => 'Betamethasone',
                'medicine_type' => 'ທາພື້ນທີ່',
                'unit' => 'ຫຼອດ',
                'strength' => '0.1%',
                'manufacturer' => 'ຢາຫວຽດນາມ',
                'stock_quantity' => 150,
                'min_stock_level' => 10,
                'unit_price' => 35000,
                'expiry_date' => now()->addMonths(36),
                'storage_condition' => 'ເກັບໃນອຸນຫະພູມຫ້ອງ, ບໍ່ໃຫ້ແຂງ',
            ],

            // ວິຕາມິນ
            [
                'medicine_code' => 'VITB100',
                'medicine_name' => 'Vitamin B-Complex',
                'generic_name' => 'B-Complex',
                'medicine_type' => 'ວິຕາມິນ',
                'unit' => 'ແຜັດ',
                'strength' => '100mg',
                'manufacturer' => 'ບໍລິສັດຢາລາວ',
                'stock_quantity' => 800,
                'min_stock_level' => 50,
                'unit_price' => 1200,
                'expiry_date' => now()->addYears(3),
                'storage_condition' => 'ເກັບໃນທີ່ແຫ້ງ, ຫ່າງຈາກແສງ',
            ],
            [
                'medicine_code' => 'VITC500',
                'medicine_name' => 'Vitamin C 500mg',
                'generic_name' => 'Ascorbic Acid',
                'medicine_type' => 'ວິຕາມິນ',
                'unit' => 'ແຜັດ',
                'strength' => '500mg',
                'manufacturer' => 'Pharma International',
                'stock_quantity' => 600,
                'min_stock_level' => 40,
                'unit_price' => 1000,
                'expiry_date' => now()->addYears(2),
                'storage_condition' => 'ເກັບໃນທີ່ແຫ້ງ, ອຸນຫະພູມຫ້ອງ',
            ]
        ];

        foreach ($medicines as $medicine) {
            Medicine::create($medicine);
        }

        $this->command->info('✅ Created ' . count($medicines) . ' medicines successfully');
    }
}
