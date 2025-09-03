<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PatientSeeder::class,
            RoomSeeder::class,
            ServiceSeeder::class,
            QueueSeeder::class,
            MedicineSeeder::class,
            VitalSignSeeder::class,
            QueueServiceSeeder::class,
            LabSeeder::class,
            PrescriptionSeeder::class,
            TreatmentSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
