<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_id',
        'medicine_id',
        'dosage',
        'frequency',
        'duration',
        'quantity',
        'instructions',
        'prescribed_by_id',
        'dispensed_by_id',
        'dispensed_quantity',
        'dispensed_at',
        'unit_price',
        'total_price',
        'status'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'dispensed_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'dispensed_at' => 'datetime',
    ];

    // =================== RELATIONSHIPS ===================

    // ຄິວ
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    // ຄົນໄຂ້ (ຜ່ານ queue)
    public function patient()
    {
        return $this->hasOneThrough(Patient::class, Queue::class, 'id', 'id', 'queue_id', 'patient_id');
    }

    // ຢາ
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    // ທ່ານໝໍທີ່ສັ່ງຢາ
    public function prescribedBy()
    {
        return $this->belongsTo(User::class, 'prescribed_by_id');
    }

    // ຜູ້ຈ່າຍຢາ
    public function dispensedBy()
    {
        return $this->belongsTo(User::class, 'dispensed_by_id');
    }

    // =================== SCOPES ===================

    // ຕາມສະຖານະ
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // ຍັງບໍ່ຈ່າຍຢາ
    public function scopePending($query)
    {
        return $query->where('status', 'Prescribed');
    }

    // ຈ່າຍແລ້ວ
    public function scopeDispensed($query)
    {
        return $query->where('status', 'Dispensed');
    }

    // ຂອງທ່ານໝໍ
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('prescribed_by_id', $doctorId);
    }

    // ວັນນີ້
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // =================== ACCESSORS ===================

    // ວິທີການກິນຢາແບບສົມບູນ
    public function getFullInstructionAttribute()
    {
        $instruction = [];
        
        if ($this->dosage) $instruction[] = "ກິນຄັ້ງລະ: {$this->dosage}";
        if ($this->frequency) $instruction[] = "ຄວາມຖີ່: {$this->frequency}";
        if ($this->duration) $instruction[] = "ໄລຍະເວລາ: {$this->duration}";
        if ($this->instructions) $instruction[] = "ວິທີໃຊ້: {$this->instructions}";
        
        return implode(', ', $instruction);
    }

    // ສະຖານະເປັນພາສາລາວ
    public function getStatusLaoAttribute()
    {
        $statuses = [
            'Prescribed' => 'ສັ່ງແລ້ວ',
            'Dispensed' => 'ຈ່າຍແລ້ວ',
            'Cancelled' => 'ຍົກເລີກ'
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    // ຈຳນວນທີ່ຍັງບໍ່ຈ່າຍ
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - ($this->dispensed_quantity ?? 0);
    }
}
