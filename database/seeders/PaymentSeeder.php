<?php
// database/seeders/PaymentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Queue, Payment, User};

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('ກຳລັງສ້າງຂໍ້ມູນການຈ່າຍເງິນສຳລັບຄິວທີ 1...');

        // ຫາຄິວທີ 1 (ຄິວທີ່ສຳເລັດແລ້ວ)
        $queue = Queue::where('queue_number', 1)->first();
        
        if (!$queue) {
            $this->command->error('ບໍ່ພົບຄິວທີ 1 - ກະລຸນາ run QueueSeeder ກ່ອນ');
            return;
        }

        // ກວດວ່າມີ Payment ແລ້ວບໍ່
        if ($queue->payment) {
            $this->command->warn('ຄິວທີ 1 ມີ Payment ແລ້ວ - ຈະອັບເດດຂໍ້ມູນໃໝ່');
            $queue->payment->delete();
        }

        // ຫາ cashier ຫຼືໃຊ້ user ຄົນທຳອິດ
        $cashier = User::where('role', 'cashier')->first() 
                ?? User::where('role', 'admin')->first() 
                ?? User::first();

        if (!$cashier) {
            $this->command->error('ບໍ່ມີ User - ກະລຸນາ run UserSeeder ກ່ອນ');
            return;
        }

        // ==================================
        // ຄຳນວນຄ່າໃຊ້ຈ່າຍ
        // ==================================

        // ຄ່າບໍລິການ - ຈາກ QueueServices
        $queueServices = $queue->queueServices;
        $serviceTotal = 0;

        $servicesBreakdown = [];
        foreach ($queueServices as $qs) {
            $servicePrice = $qs->service->base_price ?? 0;
            $serviceTotal += $servicePrice;
            
            $servicesBreakdown[] = [
                'service_code' => $qs->service->service_code,
                'service_name' => $qs->service->service_name,
                'service_category' => $qs->service->service_category,
                'amount' => $servicePrice
            ];
        }

        // ຄ່າຢາ - ຈາກ Prescriptions (ຖ້າມີ)
        $medicineTotal = $queue->prescriptions()->sum('total_price') ?? 0;
        
        $medicinesBreakdown = [];
        foreach ($queue->prescriptions as $prescription) {
            $medicinesBreakdown[] = [
                'medicine_code' => $prescription->medicine->medicine_code ?? 'MED001',
                'medicine_name' => $prescription->medicine->medicine_name ?? 'ຢາລົດໄຂ້',
                'quantity' => $prescription->quantity ?? 1,
                'unit_price' => $prescription->unit_price ?? 5000,
                'total_price' => $prescription->total_price ?? 5000
            ];
        }

        // ຖ້າບໍ່ມີຢາ ໃຫ້ຈຳລອງ
        if (empty($medicinesBreakdown)) {
            $medicineTotal = 15000; // ຢາລົດໄຂ້ + ຢາບັນເທົາອາການ
            $medicinesBreakdown = [
                [
                    'medicine_code' => 'PARA500',
                    'medicine_name' => 'Paracetamol 500mg',
                    'quantity' => 10,
                    'unit_price' => 1000,
                    'total_price' => 10000
                ],
                [
                    'medicine_code' => 'AMOX250',
                    'medicine_name' => 'ຢາບັນເທົາອາການ',
                    'quantity' => 1,
                    'unit_price' => 5000,
                    'total_price' => 5000
                ]
            ];
        }

        // ຄຳນວນລວມ
        $subtotal = $serviceTotal + $medicineTotal;
        $discountAmount = 0; // ບໍ່ມີສ່ວນຫຼຸດ
        $taxAmount = 0;      // ບໍ່ມີພາສີ
        $finalAmount = $subtotal - $discountAmount + $taxAmount;

        // ==================================
        // ສ້າງ Receipt Number
        // ==================================
        $receiptNumber = 'REC' . now()->format('Ymd') . str_pad($queue->queue_number, 3, '0', STR_PAD_LEFT);

        // ==================================
        // ລາຍລະອຽດການຈ່າຍເງິນ
        // ==================================
        $paymentDetails = [
            'queue_info' => [
                'queue_number' => $queue->queue_number,
                'queue_date' => $queue->queue_date,
                'patient_name' => $queue->patient->full_name ?? 'ຄົນໄຂ້',
                'doctor_name' => $queue->doctor->name ?? 'ທ່ານໝໍ'
            ],
            'services_breakdown' => $servicesBreakdown,
            'medicines_breakdown' => $medicinesBreakdown,
            'calculation' => [
                'service_total' => $serviceTotal,
                'medicine_total' => $medicineTotal,
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'tax' => $taxAmount,
                'final_amount' => $finalAmount
            ],
            'payment_info' => [
                'receipt_number' => $receiptNumber,
                'payment_method' => 'Cash',
                'paid_at' => now()->subMinutes(5)->format('Y-m-d H:i:s'),
                'received_by' => $cashier->name
            ]
        ];

        // ==================================
        // ສ້າງ Payment Record
        // ==================================
        $payment = Payment::create([
            'queue_id' => $queue->id,
            'service_total' => $serviceTotal,
            'medicine_total' => $medicineTotal,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'final_amount' => $finalAmount,
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'paid_at' => now()->subMinutes(5),
            'received_by' => $cashier->id,
            'receipt_number' => $receiptNumber,
            'payment_details' => $paymentDetails,
            'notes' => 'ການຈ່າຍເງິນສຳລັບການກວດສຸຂະພາບທົ່ວໄປ'
        ]);

        // ==================================
        // ສະແດງຜົນ
        // ==================================
        $this->command->info('');
        $this->command->info('✅ ສ້າງຂໍ້ມູນການຈ່າຍເງິນສຳເລັດ!');
        $this->command->info('');

        // ສະແດງໃບເກັບເງິນ
        $this->command->info("📄 ໃບເກັບເງິນເລກທີ: {$receiptNumber}");
        $this->command->info("👤 ຄົນໄຂ້: {$queue->patient->full_name}");
        $this->command->info("🏥 ທ່ານໝໍ: {$queue->doctor->name}");
        $this->command->info("📅 ວັນທີ: {$queue->queue_date}");
        $this->command->info('');

        // ລາຍລະອຽດຄ່າໃຊ້ຈ່າຍ
        $this->command->table(
            ['ລາຍການ', 'ຈຳນວນ', 'ລາຄາ/ໜ່ວຍ', 'ລວມ'],
            array_merge(
                // ບໍລິການ
                array_map(function($service) {
                    return [
                        $service['service_name'],
                        '1',
                        number_format($service['amount']) . ' ກີບ',
                        number_format($service['amount']) . ' ກີບ'
                    ];
                }, $servicesBreakdown),
                // ຢາ
                array_map(function($medicine) {
                    return [
                        $medicine['medicine_name'],
                        $medicine['quantity'],
                        number_format($medicine['unit_price']) . ' ກີບ',
                        number_format($medicine['total_price']) . ' ກີບ'
                    ];
                }, $medicinesBreakdown)
            )
        );

        $this->command->info('');
        $this->command->info("💰 ລວມຄ່າບໍລິການ: " . number_format($serviceTotal) . " ກີບ");
        $this->command->info("💊 ລວມຄ່າຢາ: " . number_format($medicineTotal) . " ກີບ");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("💵 ລວມທັງໝົດ: " . number_format($finalAmount) . " ກີບ");
        $this->command->info("💳 ການຈ່າຍເງິນ: ເງິນສົດ");
        $this->command->info("👨‍💼 ຜູ້ຮັບເງິນ: {$cashier->name}");
        $this->command->info('');

        $this->command->info('🎯 ສາມາດທົດສອບ Payment module ໄດ້ແລ້ວ!');
    }
}