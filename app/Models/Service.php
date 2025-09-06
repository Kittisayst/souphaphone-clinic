<?php
// app/Models/Service.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_code',
        'service_name',
        'service_category',
        'base_price',
        'description',
        'duration_minutes',
        'room_id',
        'has_lab_result',
        'lab_test_types',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'has_lab_result' => 'boolean',
        'lab_test_types' => 'json',
        'is_active' => 'boolean',
    ];

    // ======================== CONSTANTS ========================

    public const CATEGORIES = [
        'Consultation' => 'ປຶກສາ',
        'Laboratory' => 'ແລັບ',
        'X_Ray' => 'X-Ray',
        'Ultrasound' => 'ອັນຕາຊາວ',
        'Blood_Test' => 'ກວດເລືອດ',
        'Urine_Test' => 'ກວດປັດສະວະ',
        'ECG' => 'ເອັກຊີຈີ',
        'Treatment' => 'ການຮັກສາ',
        'Imaging' => 'ຖ່າຍພາບ',
        'Pharmacy' => 'ຢາ',
        'Other' => 'ອື່ນໆ',
    ];

    // ======================== RELATIONSHIPS ========================

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function queueServices(): HasMany
    {
        return $this->hasMany(QueueService::class);
    }

    // ======================== SCOPES ========================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('service_category', $category);
    }

    public function scopeLabServices($query)
    {
        return $query->where('has_lab_result', true);
    }

    // ======================== METHODS ========================

    public function getCategoryLabel(): string
    {
        return self::CATEGORIES[$this->service_category] ?? $this->service_category;
    }

    public function requiresRoom(): bool
    {
        return !is_null($this->room_id);
    }

    public function isLabService(): bool
    {
        return $this->has_lab_result === true;
    }

    public function getLabTestTypes(): array
    {
        return $this->lab_test_types ?? [];
    }

    public function getPriceFormatted(): string
    {
        return number_format((int)$this->base_price) . ' ກີບ';
    }

    public function getDurationFormatted(): string
    {
        if (!$this->duration_minutes) return '-';
        
        $hours = intval($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return $hours . ' ຊົ່ວໂມງ ' . $minutes . ' ນາທີ';
        }
        
        return $minutes . ' ນາທີ';
    }
}