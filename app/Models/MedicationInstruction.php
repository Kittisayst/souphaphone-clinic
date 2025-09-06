<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationInstruction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'treatment_id',
        'medicine_id',
        'dosage',
        'frequency',
        'duration',
        'quantity',
        'instructions',
        'unit_price',
        'total_price',
        'prescribed_by',
        'prescribed_at',
        'dispensed_by',
        'dispensed_quantity',
        'dispensed_at',
        'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'dispensed_quantity' => 'decimal:2',
        'prescribed_at' => 'datetime',
        'dispensed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ສະຖານະທີ່ອະນຸຍາດ
    public const STATUS_PRESCRIBED = 'Prescribed';
    public const STATUS_DISPENSED = 'Dispensed';
    public const STATUS_CANCELLED = 'Cancelled';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PRESCRIBED => 'ສັ່ງແລ້ວ',
            self::STATUS_DISPENSED => 'ຈ່າຍແລ້ວ',
            self::STATUS_CANCELLED => 'ຍົກເລີກ',
        ];
    }

    public function getStatusLaoAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    // ======================== RELATIONSHIPS ========================

    /**
     * ການປິ່ນປົວທີ່ເກີ່ຍວຂ້ອງ
     */
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    /**
     * ຂໍ້ມູນຢາ
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * ທ່ານໝໍທີ່ສັ່ງຢາ
     */
    public function prescribedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }

    /**
     * ຜູ້ຈ່າຍຢາ
     */
    public function dispensedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    // ======================== ACCESSORS & MUTATORS ========================

    /**
     * ຄຳສັ່ງການໃຊ້ຢາແບບເຕັມ
     */
    public function getFullInstructionAttribute(): string
    {
        $parts = array_filter([
            $this->dosage,
            $this->frequency,
            $this->duration,
            $this->instructions
        ]);

        return implode(' | ', $parts);
    }

    /**
     * ຈຳນວນທີ່ຍັງເຫຼືອ
     */
    public function getRemainingQuantityAttribute(): float
    {
        return $this->quantity - ($this->dispensed_quantity ?? 0);
    }

    /**
     * ອັດຕະໂນມັດຄິດໄລຄາລວມ
     */
    protected static function booted()
    {
        static::saving(function (MedicationInstruction $instruction) {
            if ($instruction->unit_price && $instruction->quantity) {
                $instruction->total_price = $instruction->unit_price * $instruction->quantity;
            }
        });
    }

    // ======================== SCOPES ========================

    /**
     * ຢາທີ່ຍັງບໍ່ໄດ້ຈ່າຍ
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PRESCRIBED);
    }

    /**
     * ຢາທີ່ຈ່າຍແລ້ວ
     */
    public function scopeDispensed($query)
    {
        return $query->where('status', self::STATUS_DISPENSED);
    }

    // ======================== HELPER METHODS ========================

    /**
     * ກວດສອບວ່າສາມາດຈ່າຍຢາໄດ້ບໍ່
     */
    public function canDispense(): bool
    {
        return $this->status === self::STATUS_PRESCRIBED
            && $this->remaining_quantity > 0;
    }

    /**
     * ຈ່າຍຢາ
     */
    public function dispense(float $quantity, int $dispensedBy): bool
    {
        if (!$this->canDispense() || $quantity > $this->remaining_quantity) {
            return false;
        }

        $this->update([
            'dispensed_quantity' => $quantity,
            'dispensed_by' => $dispensedBy,
            'dispensed_at' => now(),
            'status' => self::STATUS_DISPENSED,
        ]);

        return true;
    }
}

// ==================================================================================
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'treatment_id',
        'consultation_fee',
        'lab_fees',
        'medication_fees',
        'other_fees',
        'subtotal',
        'discount_amount',
        'total_amount',
        'payment_method',
        'paid_amount',
        'change_amount',
        'cashier_id',
        'paid_at',
        'receipt_number',
        'notes',
    ];

    protected $casts = [
        'consultation_fee' => 'decimal:2',
        'lab_fees' => 'decimal:2',
        'medication_fees' => 'decimal:2',
        'other_fees' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ວິທີການຈ່າຍເງິນ
    public static function getPaymentMethods(): array
    {
        return [
            'Cash' => 'ເງິນສົດ',
            'Transfer' => 'ໂອນເງິນ',
            'Card' => 'ບັດເຄຣດິດ',
            'Insurance' => 'ປະກັນໄພ',
        ];
    }

    public function getPaymentMethodLaoAttribute(): string
    {
        return self::getPaymentMethods()[$this->payment_method] ?? $this->payment_method;
    }

    // ======================== RELATIONSHIPS ========================

    /**
     * ການປິ່ນປົວທີ່ເກີ່ຍວຂ້ອງ
     */
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    /**
     * ພະນັກງານເກັບເງິນ
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    // ======================== HELPER METHODS ========================

    /**
     * ສ້າງເລກທີ່ໃບເກັບເງິນອັດຕະໂນມັດ
     */
    public static function generateReceiptNumber(): string
    {
        $prefix = 'RC';
        $date = now()->format('Ymd');
        $lastPayment = static::whereDate('paid_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastPayment ?
            (int) substr($lastPayment->receipt_number, -4) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * ຄິດໄລຄາອັດຕະໂນມັດຈາກການປິ່ນປົວ
     */
    public static function calculateFromTreatment(Treatment $treatment): array
    {
        $medicationFees = $treatment->medicationInstructions()
            ->where('status', MedicationInstruction::STATUS_PRESCRIBED)
            ->sum('total_price');

        $labFees = 0; // ຈະຕ້ອງເພີ່ມລາຄາ Lab ຖ້າມີ

        $consultationFee = 50000; // ຄ່າກວດມາດຕະຖານ (ສາມາດປັບແຕ່ງໄດ້)

        $subtotal = $consultationFee + $labFees + $medicationFees;

        return [
            'consultation_fee' => $consultationFee,
            'lab_fees' => $labFees,
            'medication_fees' => $medicationFees,
            'other_fees' => 0,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'total_amount' => $subtotal,
        ];
    }

    /**
     * ອັບເດດສະຖານະການປິ່ນປົວເມື່ອຈ່າຍເງິນແລ້ວ
     */
    protected static function booted()
    {
        static::created(function (Payment $payment) {
            // ອັບເດດສະຖານະການປິ່ນປົວເປັນສຳເລັດ
            if ($payment->treatment->canComplete()) {
                $payment->treatment->update([
                    'status' => Treatment::STATUS_COMPLETED
                ]);
            }
        });

        static::creating(function (Payment $payment) {
            // ສ້າງເລກທີ່ໃບເກັບເງິນອັດຕະໂນມັດ
            if (!$payment->receipt_number) {
                $payment->receipt_number = self::generateReceiptNumber();
            }

            // ຄິດເງິນທອນ
            if ($payment->paid_amount > $payment->total_amount) {
                $payment->change_amount = $payment->paid_amount - $payment->total_amount;
            }
        });
    }
}