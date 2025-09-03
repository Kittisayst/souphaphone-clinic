<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Payment::class;
    public function definition(): array
    {
        $serviceTotal = $this->faker->numberBetween(50000, 400000);
        $medicineTotal = $this->faker->numberBetween(20000, 150000);
        $subtotal = $serviceTotal + $medicineTotal;
        $discountAmount = $this->faker->optional(0.3)->numberBetween(5000, 20000) ?? 0;
        $taxAmount = 0; // ສ່ວນຫຼາຍບໍ່ມີພາສີໃນຄລີນິກເລັກ
        $finalAmount = $subtotal - $discountAmount + $taxAmount;

        $paymentMethods = ['Cash', 'Card', 'Transfer', 'Insurance'];
        $status = $this->faker->randomElement(['Pending', 'Paid']);
        $paidTime = $status === 'Paid' ? $this->faker->dateTimeBetween('-1 day', 'now') : null;

        return [
            'queue_id' => Queue::factory(),
            'service_total' => $serviceTotal,
            'medicine_total' => $medicineTotal,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'final_amount' => $finalAmount,
            'payment_method' => $status === 'Paid' ? $this->faker->randomElement($paymentMethods) : null,
            'payment_status' => $status,
            'paid_at' => $paidTime,
            'received_by_id' => $paidTime ? User::factory()->state(['user_type' => $this->faker->randomElement(['Admin', 'Receptionist'])]) : null,
            'receipt_number' => $paidTime ? 'REC' . date('Ymd') . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT) : null,
            'payment_details' => $paidTime ? [
                'services_breakdown' => [
                    ['service' => 'ກວດທົ່ວໄປ', 'amount' => 80000],
                    ['service' => 'ກວດເລືອດ', 'amount' => $serviceTotal - 80000],
                ],
                'medicines_breakdown' => [
                    ['medicine' => 'Paracetamol', 'quantity' => 10, 'unit_price' => 1500, 'total' => 15000],
                    ['medicine' => 'Amoxicillin', 'quantity' => 6, 'unit_price' => ($medicineTotal - 15000) / 6],
                ]
            ] : null,
            'notes' => $this->faker->optional(0.2)->sentence(),
        ];
    }

    // State: ຈ່າຍເງິນແລ້ວ
    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_status' => 'Paid',
            'paid_at' => now(),
            'payment_method' => $this->faker->randomElement(['Cash', 'Card', 'Transfer']),
            'receipt_number' => 'REC' . date('Ymd') . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
        ]);
    }

    // State: ລໍຖ້າຈ່າຍ
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_status' => 'Pending',
            'payment_method' => null,
            'paid_at' => null,
            'received_by_id' => null,
            'receipt_number' => null,
        ]);
    }

    // State: ຈ່າຍດ້ວຍເງິນສົດ
    public function cash(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'paid_at' => now(),
        ]);
    }
}
