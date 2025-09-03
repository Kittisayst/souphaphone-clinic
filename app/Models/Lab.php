<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lab extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_service_id',
        'lab_code',
        'test_results',
        'result_summary',
        'reference_values',
        'interpretation',
        'images_attachments',
        'performed_by_id',
        'performed_at',
        'reviewed_by_doctor_id',
        'reviewed_at',
        'patient_notified',
        'notified_at',
        'lab_status'
    ];

    protected $casts = [
        'test_results' => 'array',
        'images_attachments' => 'array',
        'performed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'notified_at' => 'datetime',
        'patient_notified' => 'boolean',
    ];

    // =================== RELATIONSHIPS ===================

    // ບໍລິການໃນຄິວ
    public function queueService()
    {
        return $this->belongsTo(QueueService::class);
    }

    // ຄິວ (ຜ່ານ queueService)
    public function queue()
    {
        return $this->hasOneThrough(Queue::class, QueueService::class, 'id', 'id', 'queue_service_id', 'queue_id');
    }

    // ຄົນໄຂ້
    public function patient()
    {
        return $this->hasOneThrough(
            Patient::class,
            Queue::class, 
            'id',
            'id',
            'queue_id',     // ຜ່ານ queueService
            'patient_id'
        );
    }

    // ບໍລິການ (ຜ່ານ queueService)
    public function service()
    {
        return $this->hasOneThrough(Service::class, QueueService::class, 'id', 'id', 'queue_service_id', 'service_id');
    }

    // ຜູ້ເຮັດການກວດ
    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by_id');
    }

    // ທ່ານໝໍທີ່ເບິ່ງຜົນ
    public function reviewedByDoctor()
    {
        return $this->belongsTo(User::class, 'reviewed_by_doctor_id');
    }

    // =================== SCOPES ===================

    // ຕາມສະຖານະ
    public function scopeByStatus($query, $status)
    {
        return $query->where('lab_status', $status);
    }

    // ລໍຖ້າທ່ານໝໍເບິ່ງ
    public function scopePendingDoctorReview($query)
    {
        return $query->where('lab_status', 'Completed');
    }

    // ທ່ານໝໍເບິ່ງແລ້ວ ແຕ່ຍັງບໍ່ແຈ້ງຄົນໄຂ້
    public function scopePendingPatientNotification($query)
    {
        return $query->where('lab_status', 'Doctor_Reviewed')
                    ->where('patient_notified', false);
    }

    // ຂອງທ່ານໝໍ
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('reviewed_by_doctor_id', $doctorId);
    }

    // ວັນນີ້
    public function scopeToday($query)
    {
        return $query->whereDate('performed_at', today());
    }

    // =================== ACCESSORS ===================

    // ສະຖານະເປັນພາສາລາວ
    public function getStatusLaoAttribute()
    {
        $statuses = [
            'Pending' => 'ລໍຖ້າເຮັດການກວດ',
            'In_Progress' => 'ກຳລັງກວດ',
            'Completed' => 'ການກວດສຳເລັດ',
            'Doctor_Reviewed' => 'ທ່ານໝໍເບິ່ງແລ້ວ',
            'Patient_Notified' => 'ແຈ້ງຄົນໄຂ້ແລ້ວ'
        ];
        
        return $statuses[$this->lab_status] ?? $this->lab_status;
    }

    // ກວດສອບວ່າແຈ້ງຄົນໄຂ້ແລ້ວບໍ່
    public function getIsNotifiedAttribute()
    {
        return $this->patient_notified && $this->notified_at;
    }
}
