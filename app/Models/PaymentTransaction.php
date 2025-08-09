<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'transaction_type',
        'amount',
        'payment_method',
        'reference_number',
        'processed_by',
        'processed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Boot method ສຳຫລັບ auto-fill processed_at ແລະ processed_by
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->processed_at) {
                $transaction->processed_at = now();
            }
            if (!$transaction->processed_by) {
                $transaction->processed_by = auth()->id();
            }
        });

        // ອັບເດດໃບເກັບເງິນຫຼັງບັນທຶກ transaction
        static::created(function ($transaction) {
            $transaction->updateInvoicePaymentStatus();
        });
    }

    /**
     * ຄວາມສຳພັນກັບໃບເກັບເງິນ
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * ຄວາມສຳພັນກັບຜູ້ດຳເນີນການ
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * ກວດສອບປະເພດ transaction
     */
    public function isPayment(): bool
    {
        return $this->transaction_type === 'payment';
    }

    public function isRefund(): bool
    {
        return $this->transaction_type === 'refund';
    }

    /**
     * ປະເພດ transaction ເປັນພາສາລາວ
     */
    public function getTransactionTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'payment' => 'ຊຳລະເງິນ',
            'refund' => 'ຄືນເງິນ',
            default => $this->transaction_type,
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
     * ສີປະເພດ transaction
     */
    public function getTransactionTypeColorAttribute(): string
    {
        return match ($this->transaction_type) {
            'payment' => 'success',
            'refund' => 'warning',
            default => 'gray',
        };
    }

    /**
     * ສີວິທີການຊຳລະ
     */
    public function getPaymentMethodColorAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'success',
            'transfer' => 'info',
            'credit_card' => 'warning',
            'insurance' => 'primary',
            default => 'gray',
        };
    }

    /**
     * ຈຳນວນເງິນທີ່ມີຜົນກະທົບຕໍ່ໃບເກັບເງິນ
     * ການຊຳລະ = +, ການຄືນເງິນ = -
     */
    public function getEffectiveAmountAttribute(): float
    {
        return $this->isPayment() ? $this->amount : -$this->amount;
    }

    /**
     * ອັບເດດສະຖານະການຊຳລະຂອງໃບເກັບເງິນ
     */
    private function updateInvoicePaymentStatus(): void
    {
        $invoice = $this->invoice;
        if (!$invoice)
            return;

        // ຄິດໄລລວມການຊຳລະທັງໝົດ
        $totalPaid = $invoice->paymentTransactions()
            ->sum(\DB::raw('CASE WHEN transaction_type = "payment" THEN amount ELSE -amount END'));

        $invoice->update([
            'paid_amount' => max(0, $totalPaid), // ບໍ່ໃຫ້ເປັນລົບ
        ]);

        // ໃຫ້ Invoice model ອັບເດດ payment_status ເອງ
        $invoice->updatePaymentStatus();
        $invoice->save();
    }

    /**
     * ສ້າງການຊຳລະເງິນ
     */
    public static function createPayment(
        int $invoiceId,
        float $amount,
        string $paymentMethod,
        ?string $referenceNumber = null,
        ?string $notes = null
    ): self {
        return self::create([
            'invoice_id' => $invoiceId,
            'transaction_type' => 'payment',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'reference_number' => $referenceNumber,
            'notes' => $notes,
        ]);
    }

    /**
     * ສ້າງການຄືນເງິນ
     */
    public static function createRefund(
        int $invoiceId,
        float $amount,
        string $reason = null,
        ?string $referenceNumber = null
    ): self {
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            throw new \Exception('ບໍ່ພົບໃບເກັບເງິນ');
        }

        if ($amount > $invoice->paid_amount) {
            throw new \Exception('ຈຳນວນເງິນຄືນເກີນຈຳນວນທີ່ຊຳລະແລ້ວ');
        }

        return self::create([
            'invoice_id' => $invoiceId,
            'transaction_type' => 'refund',
            'amount' => $amount,
            'payment_method' => $invoice->payment_method,
            'reference_number' => $referenceNumber,
            'notes' => $reason ? "ເຫດຜົນຄືນເງິນ: {$reason}" : null,
        ]);
    }

    /**
     * ສ້າງການຊຳລະເງິນສົດ
     */
    public static function createCashPayment(
        int $invoiceId,
        float $amount,
        ?string $notes = null
    ): self {
        return self::createPayment(
            invoiceId: $invoiceId,
            amount: $amount,
            paymentMethod: 'cash',
            notes: $notes
        );
    }

    /**
     * ສ້າງການຊຳລະດ້ວຍການໂອນ
     */
    public static function createTransferPayment(
        int $invoiceId,
        float $amount,
        string $transferReference,
        ?string $notes = null
    ): self {
        return self::createPayment(
            invoiceId: $invoiceId,
            amount: $amount,
            paymentMethod: 'transfer',
            referenceNumber: $transferReference,
            notes: $notes
        );
    }

    /**
     * ສ້າງການຊຳລະດ້ວຍບັດເຄຣດິດ
     */
    public static function createCreditCardPayment(
        int $invoiceId,
        float $amount,
        string $cardReference,
        ?string $notes = null
    ): self {
        return self::createPayment(
            invoiceId: $invoiceId,
            amount: $amount,
            paymentMethod: 'credit_card',
            referenceNumber: $cardReference,
            notes: $notes
        );
    }

    /**
     * ສ້າງການຊຳລະດ້ວຍປະກັນ
     */
    public static function createInsurancePayment(
        int $invoiceId,
        float $amount,
        string $insuranceReference,
        ?string $notes = null
    ): self {
        return self::createPayment(
            invoiceId: $invoiceId,
            amount: $amount,
            paymentMethod: 'insurance',
            referenceNumber: $insuranceReference,
            notes: $notes
        );
    }

    /**
     * ກວດສອບວ່າສາມາດຍົກເລີກໄດ້ບໍ່
     */
    public function canBeCancelled(): bool
    {
        // ສາມາດຍົກເລີກໄດ້ພາຍໃນ 1 ວັນ ແລະ ຕ້ອງເປັນ Admin
        return $this->processed_at->diffInHours(now()) < 24 &&
            auth()->user()?->isAdmin();
    }

    /**
     * ຍົກເລີກ transaction (ສ້າງ transaction ກົງກັນຂ້າມ)
     */
    public function cancel(string $reason): self
    {
        if (!$this->canBeCancelled()) {
            throw new \Exception('ບໍ່ສາມາດຍົກເລີກ transaction ນີ້ໄດ້');
        }

        $cancelType = $this->isPayment() ? 'refund' : 'payment';
        $cancelNotes = "ຍົກເລີກ Transaction #{$this->id} - {$reason}";

        return self::create([
            'invoice_id' => $this->invoice_id,
            'transaction_type' => $cancelType,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'reference_number' => "CANCEL-{$this->id}",
            'notes' => $cancelNotes,
        ]);
    }

    /**
     * ຟອມແມດເງິນສວຍງາມ
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->amount, 2) . ' ₭';
    }

    /**
     * Scope ສຳຫລັບຄົ້ນຫາ
     */
    public function scopeByInvoice($query, $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopePayments($query)
    {
        return $query->where('transaction_type', 'payment');
    }

    public function scopeRefunds($query)
    {
        return $query->where('transaction_type', 'refund');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    public function scopeTransfer($query)
    {
        return $query->where('payment_method', 'transfer');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('processed_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('processed_at', now()->month)
            ->whereYear('processed_at', now()->year);
    }

    public function scopeBetweenDates($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('processed_at', [$from, $to]);
    }

    /**
     * ລາຍງານການຊຳລະລາຍວັນ
     */
    public static function getDailyReport(?Carbon $date = null): array
    {
        $date = $date ?? today();

        $transactions = self::whereDate('processed_at', $date)
            ->with(['invoice.patient', 'processedBy'])
            ->get();

        $payments = $transactions->where('transaction_type', 'payment');
        $refunds = $transactions->where('transaction_type', 'refund');

        return [
            'date' => $date->format('d/m/Y'),
            'total_transactions' => $transactions->count(),
            'total_payments' => $payments->sum('amount'),
            'total_refunds' => $refunds->sum('amount'),
            'net_amount' => $payments->sum('amount') - $refunds->sum('amount'),
            'by_method' => [
                'cash' => $payments->where('payment_method', 'cash')->sum('amount'),
                'transfer' => $payments->where('payment_method', 'transfer')->sum('amount'),
                'credit_card' => $payments->where('payment_method', 'credit_card')->sum('amount'),
                'insurance' => $payments->where('payment_method', 'insurance')->sum('amount'),
            ],
            'transactions' => $transactions,
        ];
    }

    /**
     * ລາຍງານການຊຳລະລາຍເດືອນ
     */
    public static function getMonthlyReport(?int $month = null, ?int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $transactions = self::whereMonth('processed_at', $month)
            ->whereYear('processed_at', $year)
            ->get();

        $payments = $transactions->where('transaction_type', 'payment');
        $refunds = $transactions->where('transaction_type', 'refund');

        return [
            'period' => "{$month}/{$year}",
            'total_transactions' => $transactions->count(),
            'total_payments' => $payments->sum('amount'),
            'total_refunds' => $refunds->sum('amount'),
            'net_amount' => $payments->sum('amount') - $refunds->sum('amount'),
            'by_method' => [
                'cash' => $payments->where('payment_method', 'cash')->sum('amount'),
                'transfer' => $payments->where('payment_method', 'transfer')->sum('amount'),
                'credit_card' => $payments->where('payment_method', 'credit_card')->sum('amount'),
                'insurance' => $payments->where('payment_method', 'insurance')->sum('amount'),
            ],
            'daily_breakdown' => $payments->groupBy(function ($transaction) {
                return $transaction->processed_at->format('d');
            })->map(function ($dayTransactions) {
                return $dayTransactions->sum('amount');
            }),
        ];
    }

    /**
     * ລາຍງານ Cashier ປະຈຳວັນ
     */
    public static function getCashierReport(int $userId, ?Carbon $date = null): array
    {
        $date = $date ?? today();

        $transactions = self::where('processed_by', $userId)
            ->whereDate('processed_at', $date)
            ->get();

        $payments = $transactions->where('transaction_type', 'payment');
        $refunds = $transactions->where('transaction_type', 'refund');

        return [
            'cashier_name' => User::find($userId)?->name,
            'date' => $date->format('d/m/Y'),
            'total_transactions' => $transactions->count(),
            'total_payments' => $payments->sum('amount'),
            'total_refunds' => $refunds->sum('amount'),
            'net_amount' => $payments->sum('amount') - $refunds->sum('amount'),
            'cash_collected' => $payments->where('payment_method', 'cash')->sum('amount'),
            'cash_refunded' => $refunds->where('payment_method', 'cash')->sum('amount'),
            'net_cash' => $payments->where('payment_method', 'cash')->sum('amount') -
                $refunds->where('payment_method', 'cash')->sum('amount'),
        ];
    }
}