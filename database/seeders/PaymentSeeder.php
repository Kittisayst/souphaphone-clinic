<?php
// database/seeders/PaymentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{QueueService,Medication, Payment, User};

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💳 ສ້າງຂໍ້ມູນການຈ່າຍເງິນ...');

        $cashier = User::where('role', 'cashier')->first();
        //total by queue id = 1
        $total_queue_service = QueueService::where('queue_id', 1)->sum('service_price');
        //total medication by queue id = 1
        $total_medication = Medication::where('queue_id', 1)->sum('total_price');



        $payments = [
            [
                'queue_id' => 1,
                'receipt_number' => fake()->unique()->randomNumber(8),
                'discount_amount' => 10000,
                'tax_amount' => 0,
                'total_queue_services'=>$total_queue_service,
                'total_medication'=>$total_medication,
                'total_amount' => $total_queue_service + $total_medication,
                'payment_method' => 'Cash',
                'payment_status' => 'Paid',
                'paid_amount' => $total_queue_service + $total_medication + 30000,
                'change_amount' =>30000,
                'cashier_id' => $cashier->id,
                'paid_at' => date('Y-m-d H:i:s'),
                'notes'=>fake()->paragraph(),
            ]
        ];



        foreach ($payments as $paymentData) {
            // ລຶບ fields ທີ່ບໍ່ຈຳເປັນ
            unset($paymentData['id']);
            Payment::create($paymentData);
        }

        $this->command->info("✅ ສ້າງການຈ່າຍເງິນ: " . count($payments) . " ລາຍການ");

        // ສະແດງສະຖິຕິ
        $totalPaid = Payment::where('payment_status', 'Paid')->sum('total_amount');
        $totalPending = Payment::where('payment_status', 'Pending')->sum('total_amount');

        $this->command->table(
            ['ສະຖານະ', 'ຈຳນວນ', 'ລວມເງິນ'],
            [
                ['ຈ່າຍແລ້ວ', Payment::where('payment_status', 'Paid')->count(), number_format($totalPaid) . ' ກີບ'],
                ['ລໍຖ້າຈ່າຍ', Payment::where('payment_status', 'Pending')->count(), number_format($totalPending) . ' ກີບ'],
                ['ລວມທັງໝົດ', Payment::count(), number_format($totalPaid + $totalPending) . ' ກີບ'],
            ]
        );
    }
}