<?php
// database/seeders/PaymentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Treatment, Payment, User};

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💳 ສ້າງຂໍ້ມູນການຈ່າຍເງິນ...');

        $treatments = Treatment::with(['medicationInstructions'])->get();
        $cashier = User::where('role', 'cashier')->first();

        $payments = [];

        foreach ($treatments as $treatment) {
            $queue = $treatment->getQueue();

            // ສ້າງການຈ່າຍເງິນສຳລັບຄິວທີ່ສຳເລັດແລ້ວ
            if ($queue->queue_status === 'Completed') {
                $payment = new Payment();
                $payment->initializeFromTreatment($treatment);

                // ກໍລະນີຄິວທີ 1 - ຈ່າຍເງິນສົດ
                if ($queue->id === 1) {
                    $payment->payment_method = 'Cash';
                    $payment->paid_amount = 130000; // ຈ່າຍເກີນ
                    $payment->payment_status = 'Paid';
                    $payment->cashier_id = $cashier->id;
                    $payment->paid_at = now()->subMinutes(30);
                    $payment->calculateAmounts();
                    $payment->notes = 'ຈ່າຍເງິນສົດ';
                }

                $payments[] = $payment->toArray();
            }

            // ສ້າງການຈ່າຍເງິນລໍຖ້າສຳລັບຄິວອື່ນ
            elseif (in_array($queue->queue_status, ['Results_Ready', 'Ready_For_Payment'])) {
                $payment = new Payment();
                $payment->initializeFromTreatment($treatment);
                $payment->payment_status = 'Pending';

                $payments[] = $payment->toArray();
            }
        }

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