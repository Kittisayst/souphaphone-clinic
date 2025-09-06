<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treatment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_service_id',
        'room_id',
        'performed_by',
        'treatment_started_at',
        'treatment_ended_at',
        'medical_history_notes',
        'current_symptoms',
        'physical_examination',
        'examination_notes',
        'initial_assessment',
        'diagnosis',
        'treatment_plan',
        'follow_up_required',
        'follow_up_date',
        'follow_up_notes',
        'status',
        'updated_by'

    ];

    protected $casts = [
        'treatment_started_at' => 'datetime',
        'treatment_ended_at' => 'datetime',
        'follow_up_required' => 'boolean',
        'follow_up_date' => 'date',
        'updated_by' => 'integer'
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

    // ຄົນໄຂ້ (ຜ່ານ queue)
    public function patient()
    {
        return $this->hasOneThrough(
            Patient::class, 
            Queue::class,
            'id',           // queues.id
            'id',           // patients.id  
            'queue_id',     // treatments.queue_id (ຜ່ານ queueService)
            'patient_id'    // queues.patient_id
        );
    }

    // ບໍລິການ (ຜ່ານ queueService)
    public function service()
    {
        return $this->hasOneThrough(Service::class, QueueService::class, 'id', 'id', 'queue_service_id', 'service_id');
    }

    // ຫ້ອງທີ່ໃຊ້
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // ຜູ້ເຮັດການປິ່ນປົວ
    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // =================== SCOPES ===================

    // ຕາມສະຖານະ
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // ທີ່ກຳລັງດຳເນີນການ
    public function scopeInProgress($query)
    {
        return $query->where('status', 'In_Progress');
    }

    // ຂອງທ່ານໝໍ
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('performed_by_id', $doctorId);
    }

    // ວັນນີ້
    public function scopeToday($query)
    {
        return $query->whereDate('treatment_started_at', today());
    }

    // =================== ACCESSORS ===================

    // ໄລຍະເວລາການປິ່ນປົວ
    public function getTreatmentDurationAttribute()
    {
        if ($this->treatment_started_at && $this->treatment_ended_at) {
            return $this->treatment_started_at->diffInMinutes($this->treatment_ended_at);
        }
        return null;
    }

    // ສະຖານະເປັນພາສາລາວ
    public function getStatusLaoAttribute()
    {
        $statuses = [
            'In_Progress' => 'ກຳລັງເຮັດ',
            'Completed' => 'ສຳເລັດ',
            'Cancelled' => 'ຍົກເລີກ'
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }
}