<?php
// app/Models/VitalSign.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VitalSign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_id',
        'temperature',
        'weight',
        'height',
        'blood_pressure_sys',
        'blood_pressure_dia',
        'heart_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'blood_pressure_sys' => 'integer',
        'blood_pressure_dia' => 'integer',
        'heart_rate' => 'integer',
        'respiratory_rate' => 'integer',
        'oxygen_saturation' => 'decimal:2',
    ];

    // ======================== RELATIONSHIPS ========================

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ======================== METHODS ========================

    public function getBloodPressureFormatted(): string
    {
        if (!$this->blood_pressure_sys || !$this->blood_pressure_dia) {
            return '-';
        }
        
        return $this->blood_pressure_sys . '/' . $this->blood_pressure_dia . ' mmHg';
    }

    public function getTemperatureFormatted(): string
    {
        return $this->temperature ? $this->temperature . '°C' : '-';
    }

    public function getWeightFormatted(): string
    {
        return $this->weight ? $this->weight . ' kg' : '-';
    }

    public function getHeightFormatted(): string
    {
        return $this->height ? $this->height . ' cm' : '-';
    }

    public function getBMI(): ?float
    {
        if (!$this->weight || !$this->height) return null;
        
        $heightInMeters = $this->height / 100;
        return round($this->weight / ($heightInMeters * $heightInMeters), 2);
    }

    public function getBMICategory(): ?string
    {
        $bmi = $this->getBMI();
        if (!$bmi) return null;
        
        if ($bmi < 18.5) return 'ຜອມເກີນໄປ';
        if ($bmi < 25) return 'ປົກກະຕິ';
        if ($bmi < 30) return 'ອ້ວນ';
        return 'ອ້ວນຫຼາຍ';
    }

    public function hasAbnormalValues(): bool
    {
        // ກວດວ່າມີຄ່າຜິດປົກກະຕິບໍ່
        $abnormal = false;
        
        // Temperature (ປົກກະຕິ: 36-37.5°C)
        if ($this->temperature && ($this->temperature < 36 || $this->temperature > 37.5)) {
            $abnormal = true;
        }
        
        // Blood Pressure (ປົກກະຕິ: SYS < 140, DIA < 90)
        if ($this->blood_pressure_sys && $this->blood_pressure_sys > 140) {
            $abnormal = true;
        }
        if ($this->blood_pressure_dia && $this->blood_pressure_dia > 90) {
            $abnormal = true;
        }
        
        // Heart Rate (ປົກກະຕິ: 60-100 bpm)
        if ($this->heart_rate && ($this->heart_rate < 60 || $this->heart_rate > 100)) {
            $abnormal = true;
        }
        
        return $abnormal;
    }

    public function getAbnormalFlags(): array
    {
        $flags = [];
        
        if ($this->temperature) {
            if ($this->temperature > 37.5) $flags[] = 'ອຸນຫະພູມສູງ';
            if ($this->temperature < 36) $flags[] = 'ອຸນຫະພູມຕ່ຳ';
        }
        
        if ($this->blood_pressure_sys > 140) $flags[] = 'ຄວາມດັນເລືອດສູງ';
        if ($this->heart_rate && $this->heart_rate > 100) $flags[] = 'ໃຈເຕັ້ນໄວ';
        if ($this->heart_rate && $this->heart_rate < 60) $flags[] = 'ໃຈເຕັ້ນຊ້າ';
        
        return $flags;
    }
}