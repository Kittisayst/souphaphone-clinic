<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medicine_code',
        'medicine_name',
        'generic_name',
        'medicine_type',
        'unit',
        'strength',
        'manufacturer',
        'stock_quantity',
        'min_stock_level',
        'unit_price',
        'expiry_date',
        'storage_condition'
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:2',
        'min_stock_level' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    // =================== RELATIONSHIPS ===================

    // ໃບສັ່ງຢາທີ່ໃຊ້ຢາຊະນິດນີ້
    public function medicationInstructions()
    {
        return $this->hasMany(MedicationInstruction::class);
    }

    // =================== SCOPES ===================

    // ຢາທີ່ມີສະຕ໋ອກ
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // ຢາທີ່ສະຕ໋ອກຕ່ຳ
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
    }

    // ຢາທີ່ໃກ້ໝົດອາຍຸ (ໃນ 3 ເດືອນ)
    public function scopeNearExpiry($query)
    {
        return $query->where('expiry_date', '<=', now()->addMonths(3));
    }

    // ຢາໝົດອາຍຸ
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    // ຢາທີ່ມີຢູ່ (ບໍ່ໝົດອາຍຸ ແລະ ມີສະຕ໋ອກ)
    public function scopeAvailable($query)
    {
        return $query->where('expiry_date', '>=', now())
            ->where('stock_quantity', '>', 0)
            ->whereNull('deleted_at');
    }

    // =================== ACCESSORS ===================

    // ຊື່ເຕັມຂອງຢາ
    public function getFullNameAttribute()
    {
        return "{$this->medicine_name} {$this->strength}";
    }

    // ສະຖານະສະຕ໋ອກ
    public function getStockStatusAttribute()
    {
        if ($this->stock_quantity <= 0)
            return 'Out_of_Stock';
        if ($this->stock_quantity <= $this->min_stock_level)
            return 'Low_Stock';
        return 'In_Stock';
    }

    // ກວດສອບໝົດອາຍຸ
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date < now();
    }

    // ວັນທີ່ໝົດອາຍຸທີ່ຈັດຮູບແບບ
    public function getFormattedExpiryDateAttribute()
    {
        return $this->expiry_date ? Carbon::parse($this->expiry_date)->format('d/m/Y') : null;
    }

    // ຈຳນວນວັນທີ່ເຫຼືອກ່ອນໝົດອາຍຸ
    public function getDaysToExpiryAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }
        
        $days = (int) now()->diffInDays($this->expiry_date, false);
        return $days >= 0 ? $days : 0; // ຖ້າໝົດອາຍຸແລ້ວ ໃຫ້ສະແດງ 0
    }

    // ສະຖານະອາຍຸ (ໃໝ່, ໃກ້ໝົດ, ໝົດແລ້ວ)
    public function getExpiryStatusAttribute()
    {
        $daysToExpiry = $this->days_to_expiry;

        if ($daysToExpiry === null) {
            return 'unknown';
        }

        if ($this->is_expired) {
            return 'expired';
        }

        if ($daysToExpiry <= 30) {
            return 'critical'; // ໃກ້ໝົດອາຍຸຫຼາຍ (30 ວັນ)
        }

        if ($daysToExpiry <= 90) {
            return 'warning'; // ໃກ້ໝົດອາຍຸ (3 ເດືອນ)
        }

        return 'safe'; // ຍັງໃຊ້ໄດ້ດີ
    }
}
