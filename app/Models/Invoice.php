<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'examination_ids',
        'treatment_id',
        'items',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'tax_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'paid_amount',
        'payment_date',
        'payment_reference',
        'cashier_id',
        'approved_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'examination_ids' => 'array',
            'items' => 'array',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'payment_date' => 'datetime',
        ];
    }

    /**
     * Boot method ສຳຫລັບສ້າງເລກໃບເກັບເງິນອັດຕະໂນມັດ
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }

            // Auto calculate totals if not provided
            if (empty($invoice->subtotal) && !empty($invoice->items)) {
                $invoice->calculateTotals();
            }
        });

        static::updating(function ($invoice) {
            // Auto update payment status based on paid amount
            if ($invoice->isDirty('paid_amount')) {
                $invoice->updatePaymentStatus();
            }
        });
    }

    /**
     * ສ້າງເລກໃບເກັບເງິນອັດຕະໂນມັດ (INV-2024-001)
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');

        // ຫາໃບເກັບເງິນລ່າສຸດຂອງເດືອນນີ້
        $lastInvoice = self::whereYear('created_at', $year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('invoice_number', 'desc')
            ->first();

        if (!$lastInvoice) {
            return "INV-{$year}-{$month}-001";
        }

        // ດຶງເອົາເລກຈາກໃບເກັບເງິນຄັ້ງລ່າສຸດ
        $parts = explode('-', $lastInvoice->invoice_number);
        $lastNumber = intval(end($parts));
        $newNumber = $lastNumber + 1;

        return "INV-{$year}-{$month}-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * ຄວາມສຳພັນກັບຄົນໄຂ້
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * ຄວາມສຳພັນກັບການຮັກສາ
     */
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    /**
     * ຄວາມສຳພັນກັບເຄົາເຕີ
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * ຄວາມສຳພັນກັບຜູ້ອະນຸມັດ
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * ຄວາມສຳພັນກັບປະຫວັດການຊຳລະ
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * ການກວດທີ່ອ້າງອີງ
     */
    public function medicalExaminations()
    {
        if (empty($this->examination_ids)) {
            return collect();
        }

        return MedicalExamination::whereIn('id', $this->examination_ids)->get();
    }

    /**
     * ຄິດໄລລວມທັງໝົດ
     */
    public function calculateTotals(): void
    {
        if (empty($this->items)) {
            return;
        }

        $subtotal = 0;

        foreach ($this->items as $item) {
            $itemTotal = ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
            $subtotal += $itemTotal;
        }

        $this->subtotal = $this->asDecimal($subtotal);

        // ຄິດສ່ວນຫຼຸດ
        $discountAmount = 0;
        if ($this->discount_percent > 0) {
            $discountAmount = ($subtotal * $this->discount_percent) / 100;
        } elseif ($this->discount_amount > 0) {
            $discountAmount = $this->discount_amount;
        }

        // ຄິດພາສີ (ຖ້າມີ)
        $taxAmount = 0;
        if ($this->tax_amount > 0) {
            $taxAmount = $this->tax_amount;
        }

        $this->total_amount = $this->asDecimal($subtotal - $discountAmount + $taxAmount);
    }

    /**
     * ອັບເດດສະຖານະການຊຳລະ
     */
    public function updatePaymentStatus(): void
    {
        if ($this->paid_amount <= 0) {
            $this->payment_status = 'pending';
        } elseif ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = 'paid';
            if (!$this->payment_date) {
                $this->payment_date = now();
            }
        } else {
            $this->payment_status = 'partial';
        }
    }

    /**
     * ເພີ່ມລາຍການໃນໃບເກັບເງິນ
     */
    public function addItem(string $type, int $itemId, string $name, int $quantity, float $unitPrice): void
    {
        $items = $this->items ?? [];

        $items[] = [
            'type' => $type, // 'service' ຫຼື 'medicine'
            'id' => $itemId,
            'name' => $name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $quantity * $unitPrice,
        ];

        $this->items = $items;
        $this->calculateTotals();
    }

    /**
     * ເພີ່ມບໍລິການການກວດ
     */
    public function addService(MedicalService $service, int $quantity = 1): void
    {
        $this->addItem('service', $service->id, $service->service_name, $quantity, (float)$service->price);
    }

    /**
     * ເພີ່ມຢາ
     */
    public function addMedicine(Medicine $medicine, int $quantity): void
    {
        $this->addItem('medicine', $medicine->id, $medicine->medicine_name, $quantity, (float)$medicine->unit_price);
    }

    /**
     * ລົບລາຍການ
     */
    public function removeItem(int $index): void
    {
        $items = $this->items ?? [];

        if (isset($items[$index])) {
            unset($items[$index]);
            $this->items = array_values($items); // Re-index array
            $this->calculateTotals();
        }
    }

    /**
     * ໃສ່ສ່ວນຫຼຸດເປັນເປີເຊັນ
     */
    public function applyPercentageDiscount(float $percentage): void
    {
        $this->discount_percent = $percentage;
        $this->discount_amount = 0; // Reset fixed discount
        $this->calculateTotals();
    }

    /**
     * ໃສ່ສ່ວນຫຼຸດເປັນຈຳນວນເງິນ
     */
    public function applyFixedDiscount(float $amount): void
    {
        $this->discount_amount = $amount;
        $this->discount_percent = 0; // Reset percentage discount
        $this->calculateTotals();
    }

    /**
     * ຊຳລະເງິນ
     */
    public function makePayment(float $amount, string $method, ?string $reference = null): PaymentTransaction
    {
        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->id,
            'transaction_type' => 'payment',
            'amount' => $amount,
            'payment_method' => $method,
            'reference_number' => $reference,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        // ອັບເດດຈຳນວນເງິນທີ່ຊຳລະແລ້ວ
        $this->paid_amount += $amount;
        $this->payment_method = $method;
        $this->payment_reference = $reference;
        $this->updatePaymentStatus();
        $this->save();

        return $transaction;
    }

    /**
     * ຄືນເງິນ
     */
    public function makeRefund(float $amount, string $reason = null): PaymentTransaction
    {
        if ($amount > $this->paid_amount) {
            throw new \Exception('ຈຳນວນເງິນຄືນເກີນຈຳນວນທີ່ຊຳລະແລ້ວ');
        }

        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->id,
            'transaction_type' => 'refund',
            'amount' => $amount,
            'payment_method' => $this->payment_method,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
            'notes' => $reason,
        ]);

        // ອັບເດດຈຳນວນເງິນທີ່ຊຳລະແລ້ວ
        $this->paid_amount -= $amount;
        $this->updatePaymentStatus();
        $this->save();

        return $transaction;
    }

    /**
     * ກວດສອບສະຖານະການຊຳລະ
     */
    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === 'partial';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->payment_status === 'cancelled';
    }

    public function isRefunded(): bool
    {
        return $this->payment_status === 'refunded';
    }

    /**
     * ຈຳນວນເງິນທີ່ຍັງຄ້າງ
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * ເປີເຊັນການຊຳລະ
     */
    public function getPaymentPercentageAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return round(($this->paid_amount / $this->total_amount) * 100, 2);
    }

    /**
     * ສະຖານະການຊຳລະເປັນພາສາລາວ
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'ລໍຖ້າຊຳລະ',
            'partial' => 'ຊຳລະບາງສ່ວນ',
            'paid' => 'ຊຳລະຄົບແລ້ວ',
            'cancelled' => 'ຍົກເລີກ',
            'refunded' => 'ຄືນເງິນແລ້ວ',
            default => $this->payment_status,
        };
    }

    /**
     * ສີສະຖານະການຊຳລະ
     */
    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'warning',
            'partial' => 'info',
            'paid' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'gray',
            default => 'gray',
        };
    }

    /**
     * ວິທີການຊຳລະເປັນພາສາລາວ
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'ເງິນສົດ',
            'transfer' => 'ໂອນເງິນ',
            'credit_card' => 'ບັດເຄຣດິດ',
            'insurance' => 'ປະກັນສຸຂະພາບ',
            default => $this->payment_method,
        };
    }

    /**
     * ຈຳນວນລາຍການ
     */
    public function getItemCountAttribute(): int
    {
        return count($this->items ?? []);
    }

    /**
     * ສາມາດແກ້ໄຂໄດ້ບໍ່
     */
    public function canBeEdited(): bool
    {
        return $this->isPending() || $this->isPartiallyPaid();
    }

    /**
     * ສາມາດຊຳລະໄດ້ບໍ່
     */
    public function canMakePayment(): bool
    {
        return $this->remaining_amount > 0 && !$this->isCancelled();
    }

    /**
     * ສາມາດຄືນເງິນໄດ້ບໍ່
     */
    public function canMakeRefund(): bool
    {
        return $this->paid_amount > 0 && ($this->isPaid() || $this->isPartiallyPaid());
    }

    /**
     * ສາມາດຍົກເລີກໄດ້ບໍ່
     */
    public function canBeCancelled(): bool
    {
        return $this->isPending();
    }

    /**
     * Scope ສຳຫລັບຄົ້ນຫາ
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status', ['pending', 'partial']);
    }

    public function scopeOverdue($query, int $days = 30)
    {
        return $query->whereIn('payment_status', ['pending', 'partial'])
            ->where('created_at', '<', now()->subDays($days));
    }
}