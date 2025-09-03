<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Queue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'queue_number',
        'queue_date',
        'initial_complaint',
        'assigned_doctor_id',
        'queue_status',
        'vital_checked_at',
        'doctor_start_at',
        'completed_at',
        'priority_level',
        'created_by_id'
    ];

    protected $casts = [
        'queue_date' => 'date',
        'vital_checked_at' => 'datetime',
        'doctor_start_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // =================== RELATIONSHIPS ===================

    // ຄົນໄຂ້
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // ທ່ານໝໍທີ່ຮັບຄິວ
    public function assignedDoctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    // ຜູ້ສ້າງຄິວ
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    // ການກວດເບື້ອງຕົ້ນ
    public function vitalSign()
    {
        return $this->hasOne(VitalSign::class);
    }

    // ບໍລິການທີ່ເລືອກ
    public function queueServices()
    {
        return $this->hasMany(QueueService::class);
    }

    // ບໍລິການທີ່ເລືອກ (ພ້ອມຂໍ້ມູນບໍລິການ)
    public function services()
    {
        return $this->belongsToMany(Service::class, 'queue_services')
            ->withPivot([
                'service_status',
                'priority_order',
                'added_by_id',
                'assigned_to_id',
                'scheduled_at',
                'started_at',
                'completed_at',
                'notes'
            ])
            ->withTimestamps();
    }

    // ການປິ່ນປົວ (ຜ່ານ queueServices)
    public function treatments()
    {
        return $this->hasManyThrough(Treatment::class, QueueService::class);
    }

    // ຼົນການກວດ (ຜ່ານ queueServices)
    public function labs()
    {
        return $this->hasManyThrough(Lab::class, QueueService::class);
    }

    // ໃບສັ່ງຢາ
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    // ການຈ່າຍເງິນ
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // =================== SCOPES ===================

    // ຄິວຂອງວັນນີ້
    public function scopeToday($query)
    {
        return $query->whereDate('queue_date', today());
    }

    // ຄິວຕາມສະຖານະ
    public function scopeByStatus($query, $status)
    {
        return $query->where('queue_status', $status);
    }

    // ຄິວທີ່ລໍຖ້າ
    public function scopeWaiting($query)
    {
        return $query->where('queue_status', 'Registered');
    }

    // ຄິວທີ່ສຳເລັດ
    public function scopeCompleted($query)
    {
        return $query->where('queue_status', 'Completed');
    }

    // ຄິວຂອງທ່ານໝໍ
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('assigned_doctor_id', $doctorId);
    }

    // =================== ACCESSORS ===================

    // ເລກຄິວທີ່ຈັດຮູບແບບ
    public function getFormattedQueueNumberAttribute()
    {
        return str_pad($this->queue_number, 3, '0', STR_PAD_LEFT);
    }

    // ສະຖານະເປັນພາສາລາວ
    public function getStatusLaoAttribute()
    {
        $statuses = [
            'Registered' => 'ລົງທະບຽນແລ້ວ',
            'Vital_Checked' => 'ກວດເບື້ອງຕົ້ນແລ້ວ',
            'With_Doctor' => 'ຢູ່ກັບທ່ານໝໍ',
            'Lab_Testing' => 'ກວດແລັບ',
            'Results_Ready' => 'ຜົນກວດພ້ອມ',
            'Completed' => 'ສຳເລັດ',
            'Cancelled' => 'ຍົກເລີກ'
        ];

        return $statuses[$this->queue_status] ?? $this->queue_status;
    }

    public function statusColor()
    {
        return match ($this->queue_status) {
            'Registered' => 'info',
            'Vital_Checked' => 'warning',
            'With_Doctor' => 'info',
            'Lab_Testing' => 'warning',
            'Results_Ready' => 'success',
            'Completed' => 'success',
            'Cancelled' => 'danger',
            default => 'gray',
        };
    }
}