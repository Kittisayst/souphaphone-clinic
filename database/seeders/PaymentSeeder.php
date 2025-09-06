<?php
// database/seeders/PaymentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Treatment, Payment, User};

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('ກຳລັງສ້າງຂໍ້ມູນການຈ່າຍເງິນສຳລັບຄິວທີ 1...');

        // ດຶງ Treatment ທີ່ສຳເລັດແລ້ວ
        $treatment = Treatment::where('status', 'Completed')->first();
        $cashier = User::where('role', 'cashier')->first();
        $admin = User::where('role', 'admin')->first();

        if (!$treatment) {
            $this->command->warn('ຕ້ອງມີຂໍ້ມູນ Treatment (Completed) ກ່ອນ');
            return;
        }

        // ຄິດໄລຄາຈາກການປິ່ນປົວ
        $medicationFees = $treatment->medicationInstructions()
            ->where('status', 'Dispensed')
            ->sum('total_price');

        $consultationFee = 50000; // ຄ່າກວດມາດຕະຖານ
        $labFees = 25000; // ຄ່າກວດ Lab
        $subtotal = $consultationFee + $labFees + $medicationFees;

        // ສ້າງການຈ່າຍເງິນ
        Payment::create([
            'treatment_id' => $treatment->id,
            'consultation_fee' => $consultationFee,
            'lab_fees' => $labFees,
            'medication_fees' => $medicationFees,
            'other_fees' => 0,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'total_amount' => $subtotal,
            'payment_method' => 'Cash',
            'paid_amount' => 100000, // ຈ່າຍ 100,000 ກີບ
            'change_amount' => 100000 - $subtotal, // ເງິນທອນ
            'cashier_id' => $cashier?->id ?? $admin?->id ?? 1,
            'paid_at' => now()->subMinutes(15),
            'receipt_number' => 'RC' . now()->format('Ymd') . '0001',
            'notes' => 'ການຈ່າຍເງິນເປັນເງິນສົດ',
            'created_at' => now()->subMinutes(15),
            'updated_at' => now()->subMinutes(15),
        ]);

        $this->command->info('✅ ສ້າງຂໍ້ມູນ Payment ສຳເລັດ: 1 ລາຍການ');
        $this->command->info("💰 ຍອດລວມ: " . number_format($subtotal) . " ກີບ");
    }
}