<?php
// app/Models/Medicine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medicine_code',
        'medicine_name',
        'generic_name',
        'brand_name',
        'medicine_type',
        'strength',
        'unit',
        'unit_price',
        'wholesale_price',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'expiry_date',
        'batch_number',
        'dosage_instructions',
        'side_effects',
        'contraindications',
        'manufacturer',
        'supplier',
        'requires_prescription',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'current_stock' => 'integer',
        'minimum_stock' => 'integer',
        'maximum_stock' => 'integer',
        'expiry_date' => 'date',
        'requires_prescription' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ======================== CONSTANTS ========================

    public const MEDICINE_TYPES = [
        'Tablet' => 'ເມັດ',
        'Capsule' => 'ແຄັບຊູນ',
        'Syrup' => 'ນ້ຳຢາ',
        'Injection' => 'ຢາສັກ',
        'Cream' => 'ຄຣີມ',
        'Drops' => 'ຢາຫຍົດ',
        'Spray' => 'ສະເປ',
        'Other' => 'ອື່ນໆ',
    ];

    public const UNITS = [
        'mg' => 'ມິນລິກຣາມ',
        'ml' => 'ມິນລິລິດ',
        'tablet' => 'ເມັດ',
        'capsule' => 'ແຄັບຊູນ',
        'bottle' => 'ຂວດ',
        'tube' => 'ຫລອດ',
        'box' => 'ກ່ອງ',
        'piece' => 'ຊີ້ນ',
    ];

    // ======================== RELATIONSHIPS ========================

    public function medicationInstructions(): HasMany
    {
        return $this->hasMany(MedicationInstruction::class);
    }

    // ======================== SCOPES ========================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('current_stock', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'minimum_stock');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', today());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', today()->addDays($days));
    }

    public function scopeByType($query, $type)
    {
        return $query->where('medicine_type', $type);
    }

    public function scopeRequiresPrescription($query)
    {
        return $query->where('requires_prescription', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('medicine_name', 'like', "%{$search}%")
              ->orWhere('generic_name', 'like', "%{$search}%")
              ->orWhere('brand_name', 'like', "%{$search}%")
              ->orWhere('medicine_code', 'like', "%{$search}%");
        });
    }

    // ======================== ATTRIBUTES ========================

    public function getDisplayNameAttribute(): string
    {
        $name = $this->medicine_name;
        
        if ($this->strength) {
            $name .= ' ' . $this->strength;
        }
        
        if ($this->medicine_type) {
            $name .= ' (' . $this->getMedicineTypeLabel() . ')';
        }
        
        return $name;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'ຫມົດສາງ';
        }
        
        if ($this->current_stock <= $this->minimum_stock) {
            return 'ສາງຕ່ຳ';
        }
        
        if ($this->current_stock >= $this->maximum_stock) {
            return 'ສາງເຕັມ';
        }
        
        return 'ປົກກະຕິ';
    }

    public function getExpiryStatusAttribute(): string
    {
        if (!$this->expiry_date) {
            return 'ບໍ່ລະບຸ';
        }
        
        if ($this->expiry_date->isPast()) {
            return 'ໝົດອາຍຸແລ້ວ';
        }
        
        if ($this->expiry_date->diffInDays() <= 30) {
            return 'ໃກ້ໝົດອາຍຸ';
        }
        
        return 'ຍັງໃຊ້ໄດ້';
    }

    // ======================== METHODS ========================

    public function getMedicineTypeLabel(): string
    {
        return self::MEDICINE_TYPES[$this->medicine_type] ?? $this->medicine_type;
    }

    public function getUnitLabel(): string
    {
        return self::UNITS[$this->unit] ?? $this->unit;
    }

    public function getUnitPriceFormatted(): string
    {
        return number_format($this->unit_price) . ' ກີບ/' . $this->getUnitLabel();
    }

    public function getWholesalePriceFormatted(): string
    {
        return number_format($this->wholesale_price) . ' ກີບ/' . $this->getUnitLabel();
    }

    public function getCurrentStockFormatted(): string
    {
        return number_format($this->current_stock) . ' ' . $this->getUnitLabel();
    }

    public function getExpiryDateFormatted(): string
    {
        return $this->expiry_date ? $this->expiry_date->format('d/m/Y') : '-';
    }

    public static function generateMedicineCode(): string
    {
        $lastMedicine = static::withTrashed()->latest('id')->first();
        $nextNumber = ($lastMedicine?->id ?? 0) + 1;
        return 'MED' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // ======================== STOCK MANAGEMENT ========================

    public function isInStock(int $quantity = 1): bool
    {
        return $this->current_stock >= $quantity;
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date->diffInDays() <= $days;
    }

    public function canDispense(int $quantity): bool
    {
        return $this->is_active && 
               !$this->isExpired() && 
               $this->isInStock($quantity);
    }

    public function addStock(int $quantity, string $notes = null): bool
    {
        return $this->update([
            'current_stock' => $this->current_stock + $quantity,
        ]);
    }

    public function reduceStock(int $quantity, string $notes = null): bool
    {
        if (!$this->isInStock($quantity)) {
            return false;
        }
        
        return $this->update([
            'current_stock' => $this->current_stock - $quantity,
        ]);
    }

    public function getStockValue(): float
    {
        return $this->current_stock * $this->wholesale_price;
    }

    public function getReorderQuantity(): int
    {
        return max(0, $this->maximum_stock - $this->current_stock);
    }

    // ======================== BUSINESS LOGIC ========================

    public function calculatePrice(int $quantity): float
    {
        return $quantity * $this->unit_price;
    }

    public function getMonthlyUsage(): int
    {
        // Calculate based on medication instructions from last 30 days
        return $this->medicationInstructions()
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('total_quantity');
    }

    public function getAverageMonthlyUsage($months = 3): float
    {
        $totalUsage = $this->medicationInstructions()
            ->where('created_at', '>=', now()->subMonths($months))
            ->sum('total_quantity');
            
        return $totalUsage / $months;
    }

    public function getDaysOfStockRemaining(): int
    {
        $avgDailyUsage = $this->getAverageMonthlyUsage() / 30;
        
        if ($avgDailyUsage <= 0) {
            return 999; // No usage data
        }
        
        return intval($this->current_stock / $avgDailyUsage);
    }

    public function getStockAlerts(): array
    {
        $alerts = [];
        
        if ($this->isExpired()) {
            $alerts[] = [
                'type' => 'error',
                'message' => 'ຢາໝົດອາຍຸແລ້ວ'
            ];
        } elseif ($this->isExpiringSoon()) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'ໃກ້ໝົດອາຍຸ (' . $this->expiry_date->diffInDays() . ' ວັນ)'
            ];
        }
        
        if ($this->current_stock <= 0) {
            $alerts[] = [
                'type' => 'error',
                'message' => 'ຫມົດສາງ'
            ];
        } elseif ($this->isLowStock()) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'ສາງຕ່ຳ (' . $this->getCurrentStockFormatted() . ')'
            ];
        }
        
        return $alerts;
    }

    // ======================== PRESCRIPTION HELPERS ========================

    public function getCommonDosages(): array
    {
        // Return common dosages for this medicine type
        return match($this->medicine_type) {
            'Tablet', 'Capsule' => ['1 ເມັດ', '2 ເມັດ', '1/2 ເມັດ'],
            'Syrup' => ['5 ml', '10 ml', '15 ml'],
            'Injection' => ['1 ແອມພູນ', '2 ແອມພູນ'],
            'Drops' => ['1-2 ຫຍົດ', '3-4 ຫຍົດ'],
            default => ['ຕາມແພດສັ່ງ']
        };
    }

    public function getCommonFrequencies(): array
    {
        return [
            'ວັນລະ 1 ເທື່ອ',
            'ວັນລະ 2 ເທື່ອ',
            'ວັນລະ 3 ເທື່ອ',
            'ວັນລະ 4 ເທື່ອ',
            'ທຸກ 6 ຊົ່ວໂມງ',
            'ທຸກ 8 ຊົ່ວໂມງ',
            'ທຸກ 12 ຊົ່ວໂມງ',
            'ເມື່ອຕ້ອງການ'
        ];
    }

    public function getCommonDurations(): array
    {
        return [
            '3 ວັນ',
            '5 ວັນ',
            '7 ວັນ',
            '10 ວັນ',
            '2 ອາທິດ',
            '1 ເດືອນ',
            'ຈົນກວ່າໝົດ'
        ];
    }
}