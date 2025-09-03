<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
   public function run(): void
    {
        $this->command->info('🔄 Creating payments demo data...');

        // ດຶງຂໍ້ມູນທີ່ຈຳເປັນ
        $queues = Queue::with(['queueServices.service', 'prescriptions.medicine'])
                       ->whereIn('queue_status', ['Completed'])
                       ->limit(3)
                       ->get();
        
        $cashier = User::where('role', 'cashier')->first() ?? 
                   User::where('role', 'admin')->first();

        if ($queues->isEmpty() || !$cashier) {
            $this->command->error('❌ ຕ້ອງມີຂໍ້ມູນ Queue (ສຳເລັດແລ້ວ) ແລະ ຜູ້ຮັບເງິນກ່ອນ!');
            return;
        }

        // ການຈ່າຍເງິນທົດລອງ 3 ແບບ
        $paymentData = [
            // ການຈ່າຍເງິນທີ 1: ເງິນສົດ (ບໍ່ມີສ່ວນຫຼຸດ)
            [
                'payment_method' => 'Cash',
                'discount_amount' => 0,
                'notes' => 'ຈ່າຍເງິນສົດ ຄົບຖ້ວນ',
                'description' => 'ການຈ່າຍປົກກະຕິ'
            ],

            // ການຈ່າຍເງິນທີ 2: ບັດເຄຣດິດ (ມີສ່ວນຫຼຸດ)
            [
                'payment_method' => 'Card',
                'discount_amount' => 20000,
                'notes' => 'ສ່ວນຫຼຸດພິເສດຄົນໄຂ້ເກົ່າ',
                'description' => 'ມີສ່ວນຫຼຸດ'
            ],

            // ການຈ່າຍເງິນທີ 3: ໂອນເງິນ (ບໍ່ມີສ່ວນຫຼຸດ)
            [
                'payment_method' => 'Transfer',
                'discount_amount' => 0,
                'notes' => 'ໂອນເງິນຜ່ານ BCEL One',
                'description' => 'ໂອນເງິນ'
            ]
        ];

        foreach ($queues as $index => $queue) {
            if (!isset($paymentData[$index])) break;

            // ກວດສອບວ່າມີ Payment ແລ້ວບໍ່
            if (Payment::where('queue_id', $queue->id)->exists()) {
                $this->command->warn("⚠️ Queue #{$queue->queue_number} ມີ Payment ແລ້ວ - ຂ້າມໄປ");
                continue;
            }

            $data = $paymentData[$index];

            // ຄຳນວນຄ່າບໍລິການລວມ
            $serviceTotal = $queue->queueServices->sum(function ($qs) {
                return $qs->service->base_price ?? 0;
            });

            // ຄຳນວນຄ່າຢາລວມ
            $medicineTotal = $queue->prescriptions->sum('total_price');

            // ຄຳນວນຍອດລວມ
            $subtotal = $serviceTotal + $medicineTotal;
            $discountAmount = $data['discount_amount'];
            $taxAmount = 0; // ສ່ວນຫຼາຍຄລີນິກເລັກບໍ່ເກັບພາສີ
            $finalAmount = $subtotal - $discountAmount + $taxAmount;

            // ສ້າງ Receipt Number
            $receiptNumber = 'REC' . date('Ymd') . str_pad($queue->queue_number, 3, '0', STR_PAD_LEFT);

            // ສ້າງລາຍລະອຽດການຈ່າຍເງິນ
            $paymentDetails = [
                'services_breakdown' => $queue->queueServices->map(function ($qs) {
                    return [
                        'service_code' => $qs->service->service_code,
                        'service_name' => $qs->service->service_name,
                        'amount' => $qs->service->base_price
                    ];
                })->toArray(),
                'medicines_breakdown' => $queue->prescriptions->map(function ($prescription) {
                    return [
                        'medicine_code' => $prescription->medicine->medicine_code,
                        'medicine_name' => $prescription->medicine->medicine_name,
                        'quantity' => $prescription->quantity,
                        'unit_price' => $prescription->unit_price,
                        'total_price' => $prescription->total_price
                    ];
                })->toArray(),
                'calculation' => [
                    'service_total' => $serviceTotal,
                    'medicine_total' => $medicineTotal,
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'tax' => $taxAmount,
                    'final_amount' => $finalAmount
                ]
            ];

            Payment::create([
                'queue_id' => $queue->id,
                'service_total' => $serviceTotal,
                'medicine_total' => $medicineTotal,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'final_amount' => $finalAmount,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'Paid',
                'paid_at' => now()->subMinutes(rand(10, 60)),
                'received_by_id' => $cashier->id,
                'receipt_number' => $receiptNumber,
                'payment_details' => $paymentDetails,
                'notes' => $data['notes'],
            ]);

            $formattedAmount = number_format($finalAmount, 0);
            $this->command->info("💰 Queue #{$queue->queue_number}: {$formattedAmount} ກີບ ({$data['payment_method']}) - {$data['description']}");
        }

        // ສະຖິຕິການຈ່າຍເງິນ
        $this->displayPaymentStatistics();

        $this->command->info('✅ Created payments for completed queues');
    }

    /**
     * ສະແດງສະຖິຕິການຈ່າຍເງິນ
     */
    private function displayPaymentStatistics(): void
    {
        $totalPayments = Payment::count();
        $totalRevenue = Payment::where('payment_status', 'Paid')->sum('final_amount');
        $totalDiscount = Payment::sum('discount_amount');
        $avgPayment = Payment::where('payment_status', 'Paid')->avg('final_amount');

        // ການຈ່າຍເງິນຕາມວິທີ
        $paymentMethods = Payment::selectRaw('payment_method, COUNT(*) as count, SUM(final_amount) as total')
            ->where('payment_status', 'Paid')
            ->groupBy('payment_method')
            ->get();

        // ອັດຕາສ່ວນຄ່າບໍລິການ vs ຄ່າຢາ
        $servicePercent = Payment::where('payment_status', 'Paid')->avg('service_total');
        $medicinePercent = Payment::where('payment_status', 'Paid')->avg('medicine_total');
        $totalAvg = $servicePercent + $medicinePercent;
        
        $serviceRatio = $totalAvg > 0 ? round(($servicePercent / $totalAvg) * 100, 1) : 0;
        $medicineRatio = $totalAvg > 0 ? round(($medicinePercent / $totalAvg) * 100, 1) : 0;

        $this->command->info('📈 Payment Statistics:');
        $this->command->info("   - ລວມການຈ່າຍເງິນ: {$totalPayments} ຄັ້ງ");
        $this->command->info("   - ລາຍຮັບລວມ: " . number_format($totalRevenue, 0) . " ກີບ");
        $this->command->info("   - ສ່ວນຫຼຸດລວມ: " . number_format($totalDiscount, 0) . " ກີບ");
        $this->command->info("   - ລາຍຮັບເຄີ່ຍຕໍ່ຄິວ: " . number_format($avgPayment, 0) . " ກີບ");

        $this->command->info('💳 ວິທີການຈ່າຍເງິນ:');
        foreach ($paymentMethods as $method) {
            $methodName = $this->getPaymentMethodLao($method->payment_method);
            $amount = number_format($method->total, 0);
            $this->command->info("   - {$methodName}: {$method->count} ຄັ້ງ ({$amount} ກີບ)");
        }

        $this->command->info('📊 ອັດຕາສ່ວນລາຍຮັບ:');
        $this->command->info("   - ຄ່າບໍລິການ: {$serviceRatio}%");
        $this->command->info("   - ຄ່າຢາ: {$medicineRatio}%");
    }

    /**
     * ແປວິທີການຈ່າຍເງິນເປັນພາສາລາວ
     */
    private function getPaymentMethodLao($method): string
    {
        $methods = [
            'Cash' => 'ເງິນສົດ',
            'Card' => 'ບັດເຄຣດິດ',
            'Transfer' => 'ໂອນເງິນ',
            'Insurance' => 'ປະກັນສຸຂະພາບ'
        ];

        return $methods[$method] ?? $method;
    }
}
