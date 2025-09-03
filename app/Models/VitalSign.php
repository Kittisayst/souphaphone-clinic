<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'recorded_by_id',
        'notes'
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'blood_pressure_sys' => 'integer',
        'blood_pressure_dia' => 'integer',
        'heart_rate' => 'integer',
    ];

    // =================== RELATIONSHIPS ===================

    // ຄິວທີ່ກ່ຽວຂ້ອງ
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    // ຄົນໄຂ້ (ຜ່ານ queue)
    public function patient()
    {
        return $this->hasOneThrough(Patient::class, Queue::class, 'id', 'id', 'queue_id', 'patient_id');
    }

    // ຜູ້ບັນທຶກ
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    // =================== ACCESSORS ===================

    // ຄວາມດັນເລືອດແບບຮູບແບບ
    public function getFormattedBloodPressureAttribute()
    {
        if ($this->blood_pressure_sys && $this->blood_pressure_dia) {
            return "{$this->blood_pressure_sys}/{$this->blood_pressure_dia} mmHg";
        }
        return null;
    }

    // BMI (ດັດຊະນີມວນກາຍ)
    public function getBmiAttribute()
    {
        if ($this->weight && $this->height) {
            $heightInMeters = $this->height / 100;
            return round($this->weight / ($heightInMeters * $heightInMeters), 1);
        }
        return null;
    }

    // ການປະເມີນຄວາມດັນເລືອດ
    public function getBloodPressureStatusAttribute()
    {
        if (!$this->blood_pressure_sys || !$this->blood_pressure_dia) return null;
        
        if ($this->blood_pressure_sys < 120 && $this->blood_pressure_dia < 80) {
            return 'ປົກກະຕິ';
        } elseif ($this->blood_pressure_sys >= 140 || $this->blood_pressure_dia >= 90) {
            return 'ສູງ';
        } else {
            return 'ກ່ອນສູງ';
        }
    }
}