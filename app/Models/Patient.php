<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_code',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'phone_number',
        'address',
        'emergency_contact',
        'emergency_phone',
        'blood_type',
        'allergies',
        'medical_history'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    // =================== RELATIONSHIPS ===================

    // ຄິວທັງໝົດຂອງຄົນໄຂ້ຄົນນີ້
    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    // ຄິວລ່າສຸດ
    public function latestQueue()
    {
        return $this->hasOne(Queue::class)->latest();
    }

    // ຄິວທີ່ຍັງບໍ່ສຳເລັດ
    public function activeQueues()
    {
        return $this->hasMany(Queue::class)->whereNotIn('queue_status', ['Completed', 'Cancelled']);
    }

    // ການກວດເບື້ອງຕົ້ນທັງໝົດ (ຜ່ານ queues)
    public function vitalSigns()
    {
        return $this->hasManyThrough(VitalSign::class, Queue::class);
    }

    // ໃບສັ່ງຢາທັງໝົດ (ຜ່ານ queues)
    public function prescriptions()
    {
        return $this->hasManyThrough(Prescription::class, Queue::class);
    }

    // ການຈ່າຍເງິນທັງໝົດ (ຜ່ານ queues)
    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Queue::class);
    }

    // =================== ACCESSORS ===================

    // ຊື່ເຕັມ
    public function getFullNameAttribute()
    {
        $prefix = match ($this->gender) {
            'M' => 'ທ້າວ',
            'F' => 'ນາງ',
            'Other' => '',
            default => ''
        };

        return trim("{$prefix} {$this->first_name} {$this->last_name}");
    }

    // ອາຍຸ
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null;
    }

    // ຊື່ສະແດງກັບລະຫັດ
    public function getDisplayNameAttribute()
    {
        return "{$this->patient_code} - {$this->full_name}";
    }

    // ============= Scopes ===================

    // ຄົນໄຂ້ຕາມເພດ
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    // ຄົນໄຂ້ທີ່ມີອາຍຸຫຼາຍກວ່າ
    public function scopeAgeGreaterThan($query, $age)
    {
        return $query->whereYear('date_of_birth', '<=', now()->subYears($age)->year);
    }

    // ຄົນໄຂ້ທີ່ມີອາຍຸໜ້ອຍກວ່າ
    public function scopeAgeLessThan($query, $age)
    {
        return $query->whereYear('date_of_birth', '>=', now()->subYears($age)->year);
    }

    // ຄົນໄຂ້ທີ່ລົງທະບຽນໃນມື້ນີ້
    public function scopeRegisteredToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
