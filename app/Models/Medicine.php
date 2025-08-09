<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_name',
        'medicine_code',
        'generic_name',
        'brand_name',
        'strength',
        'dosage_form',
        'unit_price',
        'cost_price',
        'stock_quantity',
        'minimum_stock',
        'maximum_stock',
        'expiry_date',
        'batch_number',
        'supplier',
        'supplier_contact',
        'category',
        'therapeutic_class',
        'description',
        'usage_instructions',
        'side_effects',
        'contraindications',
        'is_active',
        'requires_prescription',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'minimum_stock' => 'integer',
            'maximum_stock' => 'integer',
            'expiry_date' => 'date',
            'is_active' => 'boolean',
            'requires_prescription' => 'boolean',
        ];
    }

    /**
     * Boot method ສຳຫລັບ auto-generate medicine code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($medicine) {
            // Auto-generate medicine code if not provided
            if (empty($medicine->medicine_code)) {
                $medicine->medicine_code = self::generateMedicineCode();
            }

            // Set default values
            if ($medicine->minimum_stock === null) {
                $medicine->minimum_stock = 10;
            }
        });

        // Auto create stock movement when stock changes
        static::updated(function ($medicine) {
            if ($medicine->isDirty('stock_quantity')) {
                $medicine->recordStockMovement();
            }
        });
    }

    /**
     * ສ້າງລະຫັດຢາອັດຕະໂນມັດ (MED001, MED002...)
     */
    public static function generateMedicineCode(): string
    {
        $lastMedicine = self::orderBy('id', 'desc')->first();
        
        if (!$lastMedicine) {
            return 'MED001';
        }

        // ດຶງເອົາເລກຈາກລະຫັດຢາຄັ້ງລ່າສຸດ
        if (preg_match('/MED(\d+)/', $lastMedicine->medicine_code, $matches)) {
            $lastNumber = intval($matches[1]);
            $newNumber = $lastNumber + 1;
            return 'MED' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        }

        return 'MED001';
    }

    /**
     * ຄວາມສຳພັນກັບການເຄື່ອນໄຫວສະຕ໋ອກ
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(MedicineStockMovement::class);
    }

    /**
     * ບັນທຶກການເຄື່ອນໄຫວສະຕ໋ອກ
     */
    private function recordStockMovement(): void
    {
        $oldStock = $this->getOriginal('stock_quantity');
        $newStock = $this->stock_quantity;
        $change = $newStock - $oldStock;

        if ($change != 0) {
            MedicineStockMovement::create([
                'medicine_id' => $this->id,
                'movement_type' => $change > 0 ? 'in' : 'out',
                'quantity' => abs($change),
                'remaining_stock' => $newStock,
                'reference_type' => 'adjustment',
                'notes' => 'ປັບຍອດສະຕ໋ອກ',
                'moved_by' => auth()->id() ?? 1,
                'moved_at' => now(),
            ]);
        }
    }

    /**
     * ກວດສອບສະຖານະຢາ
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isInactive(): bool
    {
        return !$this->is_active;
    }

    public function requiresPrescription(): bool
    {
        return $this->requires_prescription;
    }

    public function isOverTheCounter(): bool
    {
        return !$this->requires_prescription;
    }

    /**
     * ກວດສອບສະຕ໋ອກ
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->minimum_stock;
    }

    public function isOverStock(): bool
    {
        return $this->maximum_stock && $this->stock_quantity > $this->maximum_stock;
    }

    /**
     * ກວດສອບວັນໝົດອາຍຸ
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && Carbon::parse((string)$this->expiry_date)->isPast();
    }

    public function isNearExpiry(int $days = 30): bool
    {
        return $this->expiry_date && 
               Carbon::parse((string)$this->expiry_date)->isFuture() && 
               Carbon::parse((string)$this->expiry_date)->diffInDays(today()) <= $days;
    }

    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    /**
     * ຄວາມສຳຄັນຂອງການແຈ້ງເຕືອນ
     */
    public function getAlertLevelAttribute(): string
    {
        if ($this->isExpired()) {
            return 'danger';
        }
        
        if ($this->isOutOfStock()) {
            return 'danger';
        }
        
        if ($this->isNearExpiry(7)) {
            return 'danger';
        }
        
        if ($this->isLowStock() || $this->isNearExpiry(30)) {
            return 'warning';
        }
        
        if ($this->isOverStock()) {
            return 'info';
        }
        
        return 'success';
    }

    /**
     * ສະຖານະສະຕ໋ອກເປັນພາສາລາວ
     */
    public function getStockStatusLabelAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'ໝົດສະຕ໋ອກ';
        }
        
        if ($this->isLowStock()) {
            return 'ສະຕ໋ອກຕ່ຳ';
        }
        
        if ($this->isOverStock()) {
            return 'ສະຕ໋ອກເກີນ';
        }
        
        return 'ສະຕ໋ອກປົກກະຕິ';
    }

    /**
     * ສະຖານະວັນໝົດອາຍຸເປັນພາສາລາວ
     */
    public function getExpiryStatusLabelAttribute(): string
    {
        if ($this->isExpired()) {
            return 'ໝົດອາຍຸແລ້ວ';
        }
        
        if ($this->isNearExpiry(7)) {
            return 'ໃກ້ໝົດອາຍຸ (7 ວັນ)';
        }
        
        if ($this->isNearExpiry(30)) {
            return 'ໃກ້ໝົດອາຍຸ (30 ວັນ)';
        }
        
        return 'ຍັງບໍ່ໝົດອາຍຸ';
    }

    /**
     * ປະເພດຢາເປັນພາສາລາວ
     */
    public function getDosageFormLabelAttribute(): string
    {
        return match ($this->dosage_form) {
            'tablet' => 'ເມັດ',
            'capsule' => 'ແຄບຊູນ',
            'syrup' => 'ນ້ຳຢາ',
            'injection' => 'ຢາຉີດ',
            'cream' => 'ຄຣີມ',
            'drops' => 'ຢານາດ',
            'other' => 'ອື່ນໆ',
            default => $this->dosage_form,
        };
    }

    /**
     * ຊື່ຢາເຕັມ (ລວມຄວາມແຮງ)
     */
    public function getFullMedicineNameAttribute(): string
    {
        $name = $this->medicine_name;
        
        if ($this->strength) {
            $name .= ' ' . $this->strength;
        }
        
        if ($this->dosage_form) {
            $name .= ' (' . $this->dosage_form_label . ')';
        }
        
        return $name;
    }

    /**
     * ວັນທີ່ເຫຼືອກ່ອນໝົດອາຍຸ
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        
        return Carbon::parse((string)$this->expiry_date)->diffInDays(today(), false);
    }

    /**
     * ຄ່າກຳໄລ
     */
    public function getProfitMarginAttribute(): float
    {
        if (!$this->cost_price || $this->cost_price <= 0) {
            return 0;
        }
        
        return (($this->unit_price - $this->cost_price) / $this->cost_price) * 100;
    }

    /**
     * ມູນຄ່າສະຕ໋ອກ
     */
    public function getStockValueAttribute(): float
    {
        return $this->stock_quantity * $this->unit_price;
    }

    /**
     * ເພີ່ມສະຕ໋ອກ
     */
    public function addStock(int $quantity, string $referenceType = 'purchase', ?int $referenceId = null, string $notes = null): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $this->increment('stock_quantity', $quantity);

        MedicineStockMovement::create([
            'medicine_id' => $this->id,
            'movement_type' => 'in',
            'quantity' => $quantity,
            'remaining_stock' => $this->stock_quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'moved_by' => auth()->id() ?? 1,
            'moved_at' => now(),
        ]);

        return true;
    }

    /**
     * ຫຼຸດສະຕ໋ອກ
     */
    public function reduceStock(int $quantity, string $referenceType = 'sale', ?int $referenceId = null, string $notes = null): bool
    {
        if ($quantity <= 0 || $quantity > $this->stock_quantity) {
            return false;
        }

        $this->decrement('stock_quantity', $quantity);

        MedicineStockMovement::create([
            'medicine_id' => $this->id,
            'movement_type' => 'out',
            'quantity' => $quantity,
            'remaining_stock' => $this->stock_quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'moved_by' => auth()->id() ?? 1,
            'moved_at' => now(),
        ]);

        return true;
    }

    /**
     * ປັບສະຕ໋ອກ (adjustment)
     */
    public function adjustStock(int $newQuantity, string $reason = null): bool
    {
        $oldQuantity = $this->stock_quantity;
        $difference = $newQuantity - $oldQuantity;

        if ($difference == 0) {
            return true;
        }

        $this->update(['stock_quantity' => $newQuantity]);

        MedicineStockMovement::create([
            'medicine_id' => $this->id,
            'movement_type' => 'adjustment',
            'quantity' => abs($difference),
            'remaining_stock' => $newQuantity,
            'reference_type' => 'adjustment',
            'notes' => $reason ?? 'ປັບຍອດສະຕ໋ອກ',
            'moved_by' => auth()->id() ?? 1,
            'moved_at' => now(),
        ]);

        return true;
    }

    /**
     * ໝາຍຢາໝົດອາຍຸ
     */
    public function markAsExpired(string $reason = null): bool
    {
        if ($this->stock_quantity > 0) {
            MedicineStockMovement::create([
                'medicine_id' => $this->id,
                'movement_type' => 'expired',
                'quantity' => $this->stock_quantity,
                'remaining_stock' => 0,
                'reference_type' => 'disposal',
                'notes' => $reason ?? 'ຢາໝົດອາຍຸ',
                'moved_by' => auth()->id() ?? 1,
                'moved_at' => now(),
            ]);

            $this->update(['stock_quantity' => 0]);
        }

        return true;
    }

    /**
     * ຄິດລາຄາສຳລັບຈຳນວນທີ່ກຳນົດ
     */
    public function calculatePrice(int $quantity): float
    {
        return $this->unit_price * $quantity;
    }

    /**
     * ກວດສອບວ່າມີສະຕ໋ອກພຽງພໍບໍ
     */
    public function hasEnoughStock(int $requestedQuantity): bool
    {
        return $this->stock_quantity >= $requestedQuantity;
    }

    /**
     * ຈຳນວນທີ່ຕ້ອງສັ່ງຊື້
     */
    public function getQuantityToOrderAttribute(): int
    {
        if ($this->stock_quantity >= $this->minimum_stock) {
            return 0;
        }

        $targetStock = $this->maximum_stock ?? ($this->minimum_stock * 3);
        return $targetStock - $this->stock_quantity;
    }

    /**
     * Scope ສຳຫລັບຄົ້ນຫາ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity > 0 AND stock_quantity <= minimum_stock');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', today());
    }

    public function scopeNearExpiry($query, int $days = 30)
    {
        return $query->where('expiry_date', '>', today())
                    ->where('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeValid($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', today());
        });
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDosageForm($query, $form)
    {
        return $query->where('dosage_form', $form);
    }

    public function scopePrescriptionOnly($query)
    {
        return $query->where('requires_prescription', true);
    }

    public function scopeOverTheCounter($query)
    {
        return $query->where('requires_prescription', false);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('medicine_name', 'like', "%{$search}%")
              ->orWhere('medicine_code', 'like', "%{$search}%")
              ->orWhere('generic_name', 'like', "%{$search}%")
              ->orWhere('brand_name', 'like', "%{$search}%");
        });
    }

    /**
     * ການແຈ້ງເຕືອນ
     */
    public function needsLowStockAlert(): bool
    {
        return $this->isLowStock() && $this->isActive();
    }

    public function needsExpiryAlert(): bool
    {
        return ($this->isExpired() || $this->isNearExpiry()) && $this->stock_quantity > 0;
    }

    public function needsReorderAlert(): bool
    {
        return $this->stock_quantity <= $this->minimum_stock && $this->isActive();
    }

    /**
     * ປະຫວັດການເຄື່ອນໄຫວລ່າສຸດ
     */
    public function getLatestMovements(int $limit = 10)
    {
        return $this->stockMovements()
                   ->latest('moved_at')
                   ->limit($limit)
                   ->get();
    }

    /**
     * ສະຖິຕິການໃຊ້ຢາ
     */
    public function getUsageStats(Carbon $from = null, Carbon $to = null): array
    {
        $from = $from ?? now()->subMonth();
        $to = $to ?? now();

        $movements = $this->stockMovements()
                         ->where('movement_type', 'out')
                         ->whereBetween('moved_at', [$from, $to])
                         ->get();

        return [
            'total_quantity_used' => $movements->sum('quantity'),
            'number_of_transactions' => $movements->count(),
            'average_per_transaction' => $movements->count() > 0 
                ? round($movements->sum('quantity') / $movements->count(), 2) 
                : 0,
            'usage_period_days' => $from->diffInDays($to),
        ];
    }
}