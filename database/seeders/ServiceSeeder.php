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
            [
                'service_code' => 'CONS01',
                'service_name' => 'ກວດສຸຂະພາບທົ່ວໄປ',
                'service_category' => 'Consultation',
                'base_price' => 50000,
                'requires_room' => true,
                'room_type_required' => 'Consultation',
                'duration_minutes' => 30
            ],
            [
                'service_code' => 'BLOOD01',
                'service_name' => 'ກວດເລືອດຄົບຊຸດ',
                'service_category' => 'Blood_Test',
                'base_price' => 80000,
                'requires_room' => true,
                'room_type_required' => 'Laboratory',
                'duration_minutes' => 15,
                'has_lab_result' => true
            ],
            [
                'service_code' => 'XRAY01',
                'service_name' => 'ຖ່າຍ X-Ray ໜ້າເອິກ',
                'service_category' => 'X_Ray',
                'base_price' => 120000,
                'requires_room' => true,
                'room_type_required' => 'X_Ray',
                'duration_minutes' => 20,
                'has_lab_result' => true
            ],
            [
                'service_code' => 'ECG01',
                'service_name' => 'ກວດຫົວໃຈ',
                'service_category' => 'ECG',
                'base_price' => 60000,
                'requires_room' => true,
                'room_type_required' => 'Consultation',
                'duration_minutes' => 20
            ],
            [
                'service_code' => 'US01',
                'service_name' => 'ອຸນຕາຊາວ',
                'service_category' => 'Ultrasound',
                'base_price' => 100000,
                'requires_room' => true,
                'room_type_required' => 'Ultrasound',
                'duration_minutes' => 25,
                'has_lab_result' => true
            ]
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        $this->command->info('✅ Created ' . count($services) . ' services successfully');
    }
}
