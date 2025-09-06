<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $treatment_id
 * @property array $payment_items
 * @property float $subtotal_amount
 * @property float $discount_amount
 * @property float $tax_amount
 * @property float $total_amount
 * @property string $payment_method
 * @property float $paid_amount
 * @property float $change_amount
 * @property string $payment_status
 * @property int $cashier_id
 * @property Carbon|null $paid_at
 * @property string $receipt_number
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read Treatment $treatment
 * @property-read User $cashier
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'treatment_id',
        'payment_items',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'payment_method',
        'paid_amount',
        'change_amount',
        'payment_status',
        'cashier_id',
        'paid_at',
        'receipt_number',
        'notes',
    ];

    protected $casts = [
        'payment_items' => 'json',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // ======================== CONSTANTS ========================

    public const PAYMENT_METHODS = [
        'Cash' => 'ເງິນສົດ',
        'Transfer' => 'ໂອນເງິນ',
        'Card' => 'ບັດເຄຣດິດ',
        'Insurance' => 'ປະກັນໄພ',
    ];

    public const PAYMENT_STATUSES = [
        'Pending' => 'ລໍຖ້າ',
        'Paid' => 'ຈ່າຍແລ້ວ',
        'Refunded' => 'ຄືນເງິນແລ້ວ',
        'Cancelled' => 'ຍົກເລີກ',
    ];

    // ======================== RELATIONSHIPS ========================

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    // ======================== SCOPES ========================

    public function scopeByStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'Pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'Paid');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByCashier($query, $cashierId)
    {
        return $query->where('cashier_id', $cashierId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('paid_at', [$startDate, $endDate]);
    }

    // ======================== ATTRIBUTES ========================

    public function getPaymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function getPaymentStatusLabel(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    public function getSubtotalFormatted(): string
    {
        return number_format((int)$this->subtotal_amount) . ' ກີບ';
    }

    public function getDiscountFormatted(): string
    {
        return number_format((int)$this->discount_amount) . ' ກີບ';
    }

    public function getTaxFormatted(): string
    {
        return number_format((int)$this->tax_amount) . ' ກີບ';
    }

    public function getTotalFormatted(): string
    {
        return number_format((int)$this->total_amount) . ' ກີບ';
    }

    public function getPaidFormatted(): string
    {
        return number_format((int)$this->paid_amount) . ' ກີບ';
    }

    public function getChangeFormatted(): string
    {
        return number_format((int)$this->change_amount) . ' ກີບ';
    }

    public function getPaidAtFormatted(): string
    {
        return $this->paid_at ? $this->paid_at->format('d/m/Y H:i') : '-';
    }

    // ======================== STATIC METHODS ========================

    public static function generateReceiptNumber(): string
    {
        $today = today()->format('Ymd');
        $lastPayment = static::whereDate('created_at', today())
            ->where('receipt_number', 'like', "REC{$today}%")
            ->latest('id')
            ->first();

        if ($lastPayment) {
            $lastNumber = intval(substr($lastPayment->receipt_number, -4));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'REC' . $today . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // ======================== STATUS CHECKS ========================

    public function isPending(): bool
    {
        return $this->payment_status === 'Pending';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'Paid';
    }

    public function isRefunded(): bool
    {
        return $this->payment_status === 'Refunded';
    }

    public function isCancelled(): bool
    {
        return $this->payment_status === 'Cancelled';
    }

    public function canPay(): bool
    {
        return $this->isPending();
    }

    public function canRefund(): bool
    {
        return $this->isPaid();
    }

    public function canCancel(): bool
    {
        return $this->isPending();
    }

    // ======================== PAYMENT ITEMS MANAGEMENT ========================

    public function getPaymentItems(): array
    {
        return $this->payment_items ?? [];
    }

    public function setPaymentItems(array $items): void
    {
        $this->payment_items = $items;
        $this->calculateAmounts();
    }

    public function addPaymentItem(array $item): void
    {
        $items = $this->getPaymentItems();
        $items[] = $item;
        $this->setPaymentItems($items);
    }

    public function removePaymentItem(int $index): void
    {
        $items = $this->getPaymentItems();
        if (isset($items[$index])) {
            unset($items[$index]);
            $this->setPaymentItems(array_values($items));
        }
    }

    public function updatePaymentItem(int $index, array $item): void
    {
        $items = $this->getPaymentItems();
        if (isset($items[$index])) {
            $items[$index] = $item;
            $this->setPaymentItems($items);
        }
    }

    // ======================== AMOUNT CALCULATIONS ========================

    public function calculateAmounts(): void
    {
        $items = $this->getPaymentItems();
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['total_price'] ?? $item['price'] ?? 0;
        }

        $this->subtotal_amount = $subtotal;
        $this->total_amount = $subtotal - $this->discount_amount + $this->tax_amount;
        $this->change_amount = max(0, $this->paid_amount - $this->total_amount);
    }

    public function applyDiscount(float $amount, string $reason = null): void
    {
        $this->discount_amount = $amount;
        $this->calculateAmounts();

        if ($reason) {
            $this->notes = ($this->notes ?? '') . "\nDiscount: {$reason}";
        }
    }

    public function applyDiscountPercent(float $percent, string $reason = null): void
    {
        $discountAmount = $this->subtotal_amount * ($percent / 100);
        $this->applyDiscount($discountAmount, $reason ?? "Discount {$percent}%");
    }

    public function applyTax(float $amount): void
    {
        $this->tax_amount = $amount;
        $this->calculateAmounts();
    }

    public function applyTaxPercent(float $percent): void
    {
        $taxAmount = $this->subtotal_amount * ($percent / 100);
        $this->applyTax($taxAmount);
    }

    // ======================== PAYMENT PROCESSING ========================

    public function processPayment(User $cashier, float $paidAmount, string $paymentMethod, array $options = []): bool
    {
        if (!$this->canPay()) {
            return false;
        }

        // Validate paid amount
        if ($paidAmount < $this->total_amount && $paymentMethod !== 'Insurance') {
            return false;
        }

        $this->paid_amount = $paidAmount;
        $this->payment_method = $paymentMethod;
        $this->cashier_id = $cashier->id;
        $this->paid_at = now();
        $this->payment_status = 'Paid';
        
        // Calculate change for cash payments
        if ($paymentMethod === 'Cash') {
            $this->change_amount = max(0, $paidAmount - $this->total_amount);
        } else {
            $this->change_amount = 0;
        }

        // Add payment method specific notes
        if (!empty($options['reference_number'])) {
            $this->notes = ($this->notes ?? '') . "\nRef: " . $options['reference_number'];
        }

        if (!empty($options['notes'])) {
            $this->notes = ($this->notes ?? '') . "\n" . $options['notes'];
        }

        $saved = $this->save();

        if ($saved) {
            // Update treatment status
            $this->treatment->getQueue()->complete();
            
            // Update treatment
            $this->treatment->update(['status' => 'Completed']);
        }

        return $saved;
    }

    public function processRefund(User $cashier, float $refundAmount, string $reason): bool
    {
        if (!$this->canRefund()) {
            return false;
        }

        if ($refundAmount > $this->paid_amount) {
            return false;
        }

        $this->payment_status = 'Refunded';
        $this->notes = ($this->notes ?? '') . "\nRefunded: {$this->getRefundFormatted($refundAmount)} - {$reason}";

        return $this->save();
    }

    public function cancel(string $reason = null): bool
    {
        if (!$this->canCancel()) {
            return false;
        }

        $this->payment_status = 'Cancelled';
        
        if ($reason) {
            $this->notes = ($this->notes ?? '') . "\nCancelled: {$reason}";
        }

        return $this->save();
    }

    // ======================== BUSINESS LOGIC ========================

    public function initializeFromTreatment(Treatment $treatment): void
    {
        $this->treatment_id = $treatment->id;
        
        // Get billing items from treatment
        $billingItems = $treatment->billing_items ?? [];
        $paymentItems = [];

        // Convert services to payment items
        foreach ($billingItems['services'] ?? [] as $service) {
            $paymentItems[] = [
                'type' => 'service',
                'id' => $service['queue_service_id'],
                'name' => $service['service_name'],
                'price' => $service['price'],
                'quantity' => 1,
                'total_price' => $service['price'],
            ];
        }

        // Convert medications to payment items
        foreach ($billingItems['medications'] ?? [] as $medication) {
            $paymentItems[] = [
                'type' => 'medication',
                'id' => $medication['medication_id'],
                'name' => $medication['medicine_name'],
                'price' => $medication['unit_price'],
                'quantity' => $medication['quantity'],
                'total_price' => $medication['total_price'],
            ];
        }

        $this->setPaymentItems($paymentItems);
        $this->receipt_number = static::generateReceiptNumber();
    }

    public function getItemsByType(string $type): array
    {
        $items = $this->getPaymentItems();
        return array_filter($items, fn($item) => ($item['type'] ?? '') === $type);
    }

    public function getServiceItems(): array
    {
        return $this->getItemsByType('service');
    }

    public function getMedicationItems(): array
    {
        return $this->getItemsByType('medication');
    }

    public function getTotalByType(string $type): float
    {
        $items = $this->getItemsByType($type);
        return array_sum(array_column($items, 'total_price'));
    }

    public function getServiceTotal(): float
    {
        return $this->getTotalByType('service');
    }

    public function getMedicationTotal(): float
    {
        return $this->getTotalByType('medication');
    }

    // ======================== RECEIPT GENERATION ========================

    public function getReceiptData(): array
    {
        return [
            'receipt_number' => $this->receipt_number,
            'date' => $this->paid_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
            'patient' => [
                'name' => $this->treatment->getPatient()->full_name,
                'code' => $this->treatment->getPatient()->patient_code,
            ],
            'queue' => [
                'number' => $this->treatment->getQueue()->getQueueNumberFormatted(),
                'date' => Carbon::parse($this->treatment->getQueue()->queue_date)->format('d/m/Y'),
            ],
            'doctor' => $this->treatment->doctor->name,
            'cashier' => $this->cashier->name ?? '',
            'items' => $this->getFormattedItems(),
            'amounts' => [
                'subtotal' => $this->getSubtotalFormatted(),
                'discount' => $this->getDiscountFormatted(),
                'tax' => $this->getTaxFormatted(),
                'total' => $this->getTotalFormatted(),
                'paid' => $this->getPaidFormatted(),
                'change' => $this->getChangeFormatted(),
            ],
            'payment_method' => $this->getPaymentMethodLabel(),
            'notes' => $this->notes,
        ];
    }

    public function getFormattedItems(): array
    {
        $formatted = [];
        
        foreach ($this->getPaymentItems() as $item) {
            $formatted[] = [
                'name' => $item['name'],
                'quantity' => $item['quantity'] ?? 1,
                'price' => number_format($item['price'] ?? 0) . ' ກີບ',
                'total' => number_format($item['total_price'] ?? 0) . ' ກີບ',
                'type' => $item['type'] === 'service' ? 'ບໍລິການ' : 'ຢາ',
            ];
        }
        
        return $formatted;
    }

    // ======================== REPORTING HELPERS ========================

    public function getRefundFormatted(float $amount): string
    {
        return number_format($amount) . ' ກີບ';
    }

    public static function getDailyTotal($date = null): float
    {
        $date = $date ?? today();
        
        return static::whereDate('paid_at', $date)
            ->where('payment_status', 'Paid')
            ->sum('total_amount');
    }

    public static function getMethodTotals($date = null): array
    {
        $date = $date ?? today();
        
        $payments = static::whereDate('paid_at', $date)
            ->where('payment_status', 'Paid')
            ->get();

        $totals = [];
        foreach (self::PAYMENT_METHODS as $method => $label) {
            $total = $payments->where('payment_method', $method)->sum('total_amount');
            $totals[$method] = [
                'label' => $label,
                'total' => $total,
                'formatted' => number_format($total) . ' ກີບ',
                'count' => $payments->where('payment_method', $method)->count(),
            ];
        }

        return $totals;
    }

    public static function getCashierTotals($date = null): array
    {
        $date = $date ?? today();
        
        return static::whereDate('paid_at', $date)
            ->where('payment_status', 'Paid')
            ->with('cashier')
            ->get()
            ->groupBy('cashier_id')
            ->map(function ($payments) {
                return [
                    'cashier' => $payments->first()->cashier->name,
                    'total' => $payments->sum('total_amount'),
                    'count' => $payments->count(),
                    'formatted' => number_format($payments->sum('total_amount')) . ' ກີບ',
                ];
            })
            ->values()
            ->toArray();
    }

    // ======================== VALIDATION ========================

    public function validatePayment(): array
    {
        $errors = [];

        if ($this->total_amount <= 0) {
            $errors[] = 'ຈຳນວນເງິນຕ້ອງຫຼາຍກວ່າ 0';
        }

        if (empty($this->getPaymentItems())) {
            $errors[] = 'ບໍ່ມີລາຍການສິນຄ້າ';
        }

        if ($this->paid_amount < $this->total_amount && $this->payment_method !== 'Insurance') {
            $errors[] = 'ຈຳນວນເງິນທີ່ຈ່າຍບໍ່ພໍ';
        }

        return $errors;
    }

    public function isValid(): bool
    {
        return empty($this->validatePayment());
    }

    // ======================== HELPER METHODS ========================

    public function getPatient(): Patient
    {
        return $this->treatment->getPatient();
    }

    public function getQueue(): Queue
    {
        return $this->treatment->getQueue();
    }

    public function hasDiscount(): bool
    {
        return $this->discount_amount > 0;
    }

    public function hasTax(): bool
    {
        return $this->tax_amount > 0;
    }

    public function hasChange(): bool
    {
        return $this->change_amount > 0;
    }

    public function getDiscountPercent(): float
    {
        if ($this->subtotal_amount <= 0) return 0;
        
        return ($this->discount_amount / $this->subtotal_amount) * 100;
    }

    public function getTaxPercent(): float
    {
        if ($this->subtotal_amount <= 0) return 0;
        
        return ($this->tax_amount / $this->subtotal_amount) * 100;
    }
}