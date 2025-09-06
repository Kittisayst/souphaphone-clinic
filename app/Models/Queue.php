<?php
// app/Models/Queue.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Queue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'queue_number',
        'waiting_number',
        'queue_date',
        'initial_complaint',
        'queue_status',
        'doctor_id',
        'assigned_room_id',
        'room_assigned_at',
        'doctor_start_at',
        'tests_completed_at',
        'payment_completed_at',
        'priority_level',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'queue_date' => 'date',
        'room_assigned_at' => 'datetime',
        'doctor_start_at' => 'datetime',
        'tests_completed_at' => 'datetime',
        'payment_completed_at' => 'datetime',
    ];

    // ======================== CONSTANTS ========================

    public const STATUSES = [
        'Registered' => '1. ລົງທະບຽນແລ້ວ',
        'Vital_Checked' => '2. ກວດ vital signs ແລ້ວ',
        'With_Doctor' => '3. ຢູ່ກັບທ່ານໝໍ',
        'Waiting_Test_Results' => '4. ລໍຖ້າຜົນກວດ',
        'Results_Ready' => '5a. ຜົນກວດພ້ອມ',
        'Ready_For_Payment' => '5b. ພ້ອມຈ່າຍເງິນ',
        'Completed' => '6. ສຳເລັດ',
        'Cancelled' => 'ຍົກເລີກ',
    ];

    public const PRIORITIES = [
        'Normal' => 'ປົກກະຕິ',
        'Urgent' => 'ຮີບ',
        'Emergency' => 'ສຸກເສີນ',
    ];

    // ======================== RELATIONSHIPS ========================

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function assignedRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'assigned_room_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function vitalSigns(): HasOne
    {
        return $this->hasOne(VitalSign::class);
    }

    public function queueServices(): HasMany
    {
        return $this->hasMany(QueueService::class);
    }

    // ======================== SCOPES ========================

    public function scopeToday($query)
    {
        return $query->whereDate('queue_date', today());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('queue_status', $status);
    }

    public function scopeWaiting($query)
    {
        return $query->where('waiting_number', '>', 0);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }

    // ======================== METHODS ========================

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->queue_status] ?? $this->queue_status;
    }

    public function getPriorityLabel(): string
    {
        return self::PRIORITIES[$this->priority_level] ?? $this->priority_level;
    }

    public function getQueueNumberFormatted(): string
    {
        return str_pad($this->queue_number, 3, '0', STR_PAD_LEFT);
    }

    public static function generateQueueNumber($date = null): int
    {
        $date = $date ?? today();
        $lastQueue = static::whereDate('queue_date', $date)->max('queue_number');
        return ($lastQueue ?? 0) + 1;
    }

    public function generateWaitingNumber(): int
    {
        $maxWaiting = static::whereDate('queue_date', $this->queue_date)
            ->where('waiting_number', '>', 0)
            ->max('waiting_number');
        return ($maxWaiting ?? 0) + 1;
    }

    // ======================== STATUS CHECKS ========================

    public function canRecordVitalSigns(): bool
    {
        return $this->queue_status === 'Registered';
    }

    public function canCallToDoctor(): bool
    {
        return $this->queue_status === 'Vital_Checked' && $this->vitalSigns()->exists();
    }

    public function canStartTesting(): bool
    {
        return $this->queue_status === 'With_Doctor';
    }

    public function canCompleteTests(): bool
    {
        return $this->queue_status === 'Waiting_Test_Results';
    }

    public function canProcessPayment(): bool
    {
        return $this->queue_status === 'Ready_For_Payment';
    }

    // ======================== STATUS TRANSITIONS ========================

    public function markVitalChecked(): bool
    {
        if (!$this->canRecordVitalSigns()) return false;
        
        return $this->update([
            'queue_status' => 'Vital_Checked',
            'updated_by' => auth()->id(),
        ]);
    }

    public function assignToDoctor(User $doctor, Room $room): bool
    {
        if (!$this->canCallToDoctor()) return false;
        
        return $this->update([
            'queue_status' => 'With_Doctor',
            'doctor_id' => $doctor->id,
            'assigned_room_id' => $room->id,
            'room_assigned_at' => now(),
            'doctor_start_at' => now(),
            'updated_by' => auth()->id(),
        ]);
    }

    public function startTesting(): bool
    {
        if (!$this->canStartTesting()) return false;
        
        return $this->update([
            'queue_status' => 'Waiting_Test_Results',
            'updated_by' => auth()->id(),
        ]);
    }

    public function markTestsCompleted(): bool
    {
        if (!$this->canCompleteTests()) return false;
        
        return $this->update([
            'queue_status' => 'Results_Ready',
            'tests_completed_at' => now(),
            'updated_by' => auth()->id(),
        ]);
    }

    public function readyForPayment(): bool
    {
        return $this->update([
            'queue_status' => 'Ready_For_Payment',
            'updated_by' => auth()->id(),
        ]);
    }

    public function complete(): bool
    {
        if (!$this->canProcessPayment()) return false;
        
        return $this->update([
            'queue_status' => 'Completed',
            'payment_completed_at' => now(),
            'waiting_number' => 0, // ຍົກເລີກເລກລໍຖ້າ
            'updated_by' => auth()->id(),
        ]);
    }

    public function cancel(string $reason = null): bool
    {
        return $this->update([
            'queue_status' => 'Cancelled',
            'waiting_number' => 0,
            'updated_by' => auth()->id(),
        ]);
    }

    // ======================== HELPER METHODS ========================

    public function hasLabServices(): bool
    {
        return $this->queueServices()
            ->whereHas('service', fn($q) => $q->where('has_lab_result', true))
            ->exists();
    }

    public function getAllServicesCompleted(): bool
    {
        $totalServices = $this->queueServices()->count();
        $completedServices = $this->queueServices()->where('service_status', 'Completed')->count();
        
        return $totalServices > 0 && $totalServices === $completedServices;
    }

    public function getTotalEstimatedAmount(): float
    {
        $serviceTotal = $this->queueServices()
            ->whereNotNull('service_price')
            ->sum('service_price');
            
        // TODO: Add medication costs when implementing
        
        return $serviceTotal;
    }

    public function getWaitingTime(): ?string
    {
        if (!$this->doctor_start_at) return null;
        
        return $this->created_at->diffForHumans($this->doctor_start_at);
    }
}