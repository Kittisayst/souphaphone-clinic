<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MedicineStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'movement_type',
        'quantity',
        'remaining_stock',
        'reference_type',
        'reference_id',
        'notes',
        'moved_by',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'remaining_stock' => 'integer',
            'moved_at' => 'datetime',
        ];
    }

    /**
     * Boot method ສຳຫລັບ auto-fill moved_at ແລະ moved_by
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($movement) {
            if (!$movement->moved_at) {
                $movement->moved_at = now();
            }
            if (!$movement->moved_by) {
                $movement->moved_by = auth()->id();
            }

            // ອັບເດດສະຕ໋ອກຢາອັດຕະໂນມັດ
            $movement->updateMedicineStock();
        });
    }

    /**
     * ຄວາມສຳພັນກັບຢາ
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * ຄວາມສຳພັນກັບຜູ້ບັນທຶກ
     */
    public function movedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }

    /**
     * ຄວາມສຳພັນກັບການອ້າງອີງ (Treatment, Invoice, etc.)
     */
    public function reference()
    {
        switch ($this->reference_type) {
            case 'treatment':
                return $this->belongsTo(Treatment::class, 'reference_id');
            case 'sale':
                return $this->belongsTo(Invoice::class, 'reference_id');
            default:
                return null;
        }
    }

    /**
     * ກວດສອບປະເພດການເຄື່ອນໄຫວ
     */
    public function isIncoming(): bool
    {
        return $this->movement_type === 'in';
    }

    public function isOutgoing(): bool
    {
        return $this->movement_type === 'out';
    }

    public function isAdjustment(): bool
    {
        return $this->movement_type === 'adjustment';
    }

    public function isExpired(): bool
    {
        return $this->movement_type === 'expired';
    }

    /**
     * ປະເພດການເຄື່ອນໄຫວເປັນພາສາລາວ
     */
    public function getMovementTypeLabelAttribute(): string
    {
        return match ($this->movement_type) {
            'in' => 'ເຂົ້າສາງ',
            'out' => 'ອອກສາງ',
            'adjustment' => 'ປັບປຸງ',
            'expired' => 'ໝົດອາຍຸ',
            default => $this->movement_type,
        };
    }

    /**
     * ປະເພດການອ້າງອີງເປັນພາສາລາວ
     */
    public function getReferenceTypeLabelAttribute(): string
    {
        return match ($this->reference_type) {
            'purchase' => 'ຊື້ເຂົ້າ',
            'sale' => 'ຂາຍ',
            'adjustment' => 'ປັບປຸງສາງ',
            'disposal' => 'ທິ້ງ/ຈຳຫນ່າຍ',
            'treatment' => 'ການຮັກສາ',
            default => $this->reference_type,
        };
    }

    /**
     * ສີປະເພດການເຄື່ອນໄຫວ
     */
    public function getMovementTypeColorAttribute(): string
    {
        return match ($this->movement_type) {
            'in' => 'success',
            'out' => 'warning',
            'adjustment' => 'info',
            'expired' => 'danger',
            default => 'gray',
        };
    }

    /**
     * ອັບເດດສະຕ໋ອກຢາອັດຕະໂນມັດ
     */
    private function updateMedicineStock(): void
    {
        $medicine = Medicine::find($this->medicine_id);
        if (!$medicine) return;

        $currentStock = $medicine->stock_quantity;

        switch ($this->movement_type) {
            case 'in':
                $newStock = $currentStock + $this->quantity;
                break;
            case 'out':
            case 'expired':
                $newStock = $currentStock - $this->quantity;
                break;
            case 'adjustment':
                // ສຳຫລັບ adjustment, quantity ອາດເປັນລົບໄດ້
                $newStock = $currentStock + $this->quantity;
                break;
            default:
                $newStock = $currentStock;
        }

        // ຫ້າມໃຫ້ສະຕ໋ອກເປັນລົບ
        $newStock = max(0, $newStock);

        $this->remaining_stock = $newStock;
        $medicine->update(['stock_quantity' => $newStock]);
    }

    /**
     * ສ້າງການເຄື່ອນໄຫວ "ເຂົ້າສາງ"
     */
    public static function createIncoming(
        int $medicineId,
        int $quantity,
        string $referenceType = 'purchase',
        ?int $referenceId = null,
        ?string $notes = null
    ): self {
        return self::create([
            'medicine_id' => $medicineId,
            'movement_type' => 'in',
            'quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
        ]);
    }

    /**
     * ສ້າງການເຄື່ອນໄຫວ "ອອກສາງ"
     */
    public static function createOutgoing(
        int $medicineId,
        int $quantity,
        string $referenceType = 'treatment',
        ?int $referenceId = null,
        ?string $notes = null
    ): self {
        return self::create([
            'medicine_id' => $medicineId,
            'movement_type' => 'out',
            'quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
        ]);
    }

    /**
     * ສ້າງການເຄື່ອນໄຫວ "ປັບປຸງສາງ"
     */
    public static function createAdjustment(
        int $medicineId,
        int $adjustmentQuantity, // ອາດເປັນລົບໄດ້
        ?string $notes = null
    ): self {
        return self::create([
            'medicine_id' => $medicineId,
            'movement_type' => 'adjustment',
            'quantity' => $adjustmentQuantity,
            'reference_type' => 'adjustment',
            'notes' => $notes,
        ]);
    }

    /**
     * ສ້າງການເຄື່ອນໄຫວ "ໝົດອາຍຸ"
     */
    public static function createExpired(
        int $medicineId,
        int $quantity,
        ?string $notes = null
    ): self {
        return self::create([
            'medicine_id' => $medicineId,
            'movement_type' => 'expired',
            'quantity' => $quantity,
            'reference_type' => 'disposal',
            'notes' => $notes ?: 'ຢາໝົດອາຍຸ - ຖືກທິ້ງ',
        ]);
    }

    /**
     * ຄິດລາຄາທີ່ສູນເສຍ (ສຳຫລັບການທິ້ງ/ໝົດອາຍຸ)
     */
    public function getLostValueAttribute(): float
    {
        if (!in_array($this->movement_type, ['expired', 'disposal'])) {
            return 0;
        }

        return $this->quantity * ($this->medicine->cost_price ?? $this->medicine->unit_price);
    }

    /**
     * ມູນຄ່າການເຄື່ອນໄຫວ
     */
    public function getMovementValueAttribute(): float
    {
        return $this->quantity * ($this->medicine->cost_price ?? $this->medicine->unit_price);
    }

    /**
     * Scope ສຳຫລັບຄົ້ນຫາ
     */
    public function scopeByMedicine($query, $medicineId)
    {
        return $query->where('medicine_id', $medicineId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeIncoming($query)
    {
        return $query->where('movement_type', 'in');
    }

    public function scopeOutgoing($query)
    {
        return $query->where('movement_type', 'out');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('moved_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('moved_at', now()->month)
                    ->whereYear('moved_at', now()->year);
    }

    public function scopeByReference($query, string $type, ?int $id = null)
    {
        $query->where('reference_type', $type);
        if ($id) {
            $query->where('reference_id', $id);
        }
        return $query;
    }

    /**
     * ລາຍງານສາງຂອງຢາ
     */
    public static function getStockReport(int $medicineId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = self::where('medicine_id', $medicineId);

        if ($from) {
            $query->where('moved_at', '>=', $from);
        }
        if ($to) {
            $query->where('moved_at', '<=', $to);
        }

        $movements = $query->orderBy('moved_at')->get();

        $report = [
            'total_in' => $movements->where('movement_type', 'in')->sum('quantity'),
            'total_out' => $movements->where('movement_type', 'out')->sum('quantity'),
            'total_expired' => $movements->where('movement_type', 'expired')->sum('quantity'),
            'adjustments' => $movements->where('movement_type', 'adjustment')->sum('quantity'),
            'movements' => $movements,
        ];

        $report['net_movement'] = $report['total_in'] - $report['total_out'] - $report['total_expired'] + $report['adjustments'];

        return $report;
    }

    /**
     * ລາຍງານມູນຄ່າທີ່ສູນເສຍ
     */
    public static function getLossReport(?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = self::whereIn('movement_type', ['expired', 'disposal']);

        if ($from) {
            $query->where('moved_at', '>=', $from);
        }
        if ($to) {
            $query->where('moved_at', '<=', $to);
        }

        $losses = $query->with('medicine')->get();

        return [
            'total_quantity_lost' => $losses->sum('quantity'),
            'total_value_lost' => $losses->sum('lost_value'),
            'by_medicine' => $losses->groupBy('medicine_id')->map(function ($group) {
                return [
                    'medicine_name' => $group->first()->medicine->medicine_name,
                    'quantity_lost' => $group->sum('quantity'),
                    'value_lost' => $group->sum('lost_value'),
                ];
            }),
        ];
    }
}