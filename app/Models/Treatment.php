<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treatment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_service_id',
        'room_id',
        'doctor_id',
        'examination_notes',
        'findings',
        'medical_history_notes',
        'diagnosis',
        'treatment_plan',
        'follow_up_required',
        'follow_up_date',
        'follow_up_notes',
        'status',
        'updated_by',
    ];

    protected $casts = [
        'follow_up_required' => 'boolean',
        'follow_up_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ສະຖານະທີ່ອະນຸຍາດ
    public const STATUS_IN_PROGRESS = 'In_Progress';
    public const STATUS_WAITING_LAB = 'Waiting_Lab_Results';
    public const STATUS_LAB_READY = 'Lab_Results_Ready';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CANCELLED = 'Cancelled';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_IN_PROGRESS => 'ກຳລັງກວດຢູ່',
            self::STATUS_WAITING_LAB => 'ລໍຖ້າຜົນ Lab',
            self::STATUS_LAB_READY => 'ຜົນ Lab ພ້ອມແລ້ວ',
            self::STATUS_COMPLETED => 'ສຳເລັດແລ້ວ',
            self::STATUS_CANCELLED => 'ຍົກເລີກ',
        ];
    }

    public function getStatusLaoAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    // ======================== RELATIONSHIPS ========================

    /**
     * ຄິວບໍລິການທີ່ເກີ່ຍວຂ້ອງ
     */
    public function queueService(): BelongsTo
    {
        return $this->belongsTo(QueueService::class);
    }

    /**
     * ຫ້ອງທີ່ເຮັດການປິ່ນປົວ
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * ທ່ານໝໍທີ່ຮັບຜິດຊອບ
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * ຜູ້ອັບເດດຂໍ້ມູນຄັ້ງຫຼ້າສຸດ
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * ການກວດ Lab ທັງໝົດ
     */
    public function labTests(): HasMany
    {
        return $this->hasMany(LabTest::class);
    }

    /**
     * ການສັ່ງຢາທັງໝົດ
     */
    public function medicationInstructions(): HasMany
    {
        return $this->hasMany(MedicationInstruction::class);
    }

    /**
     * ການຈ່າຍເງິນ
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // ======================== SCOPES ========================

    /**
     * ການປິ່ນປົວທີ່ຍັງບໍ່ສຳເລັດ
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * ການປິ່ນປົວຂອງທ່ານໝໍສະເພາະ
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * ການປິ່ນປົວທີ່ຕ້ອງຕິດຕາມ
     */
    public function scopeNeedFollowUp($query)
    {
        return $query->where('follow_up_required', true)
                    ->where('status', self::STATUS_COMPLETED);
    }

    // ======================== ACCESSORS & MUTATORS ========================

    /**
     * ຂໍ້ມູນຄົນໄຂ້ຜ່ານ Queue Service
     */
    public function getPatientAttribute()
    {
        return $this->queueService?->queue?->patient;
    }

    /**
     * ບໍລິການທີ່ໃຊ້ຜ່ານ Queue Service
     */
    public function getServiceAttribute()
    {
        return $this->queueService?->service;
    }

    // ======================== HELPER METHODS ========================

    /**
     * ກວດສອບວ່າສາມາດສັ່ງ Lab ໄດ້ບໍ່
     */
    public function canOrderLab(): bool
    {
        return in_array($this->status, [
            self::STATUS_IN_PROGRESS,
            self::STATUS_WAITING_LAB
        ]);
    }

    /**
     * ກວດສອບວ່າສາມາດສັ່ງຢາໄດ້ບໍ່
     */
    public function canPrescribeMedication(): bool
    {
        return in_array($this->status, [
            self::STATUS_IN_PROGRESS,
            self::STATUS_LAB_READY
        ]);
    }

    /**
     * ກວດສອບວ່າສາມາດສຳເລັດການປິ່ນປົວໄດ້ບໍ່
     */
    public function canComplete(): bool
    {
        // ຕ້ອງບໍ່ມີ Lab ທີ່ຍັງບໍ່ຮຽບຮ້ອຍ
        $pendingLabs = $this->labTests()
            ->whereNotIn('status', [LabTest::STATUS_REVIEWED, LabTest::STATUS_CANCELLED])
            ->count();

        return $pendingLabs === 0 && $this->status !== self::STATUS_COMPLETED;
    }

    /**
     * ອັບເດດສະຖານະອັດຕະໂນມັດ
     */
    public function updateStatusBasedOnLabResults(): void
    {
        if ($this->status === self::STATUS_WAITING_LAB) {
            $completedLabs = $this->labTests()
                ->where('status', LabTest::STATUS_REVIEWED)
                ->count();
            
            $totalLabs = $this->labTests()
                ->whereNot('status', LabTest::STATUS_CANCELLED)
                ->count();

            if ($completedLabs === $totalLabs && $totalLabs > 0) {
                $this->update(['status' => self::STATUS_LAB_READY]);
            }
        }
    }
}