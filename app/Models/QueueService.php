<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class QueueService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_id',
        'service_id',
        'added_by_id',
        'assigned_to_id',
        'service_status',
        'started_at',
        'completed_at',
        'actual_duration',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ສະຖານະທີ່ອະນຸຍາດ (ງ່າຍກວ່າເກົ່າ)
    public const STATUS_ADDED = 'Added';
    public const STATUS_IN_PROGRESS = 'In_Progress';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CANCELLED = 'Cancelled';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ADDED => 'ເພີ່ມແລ້ວ',
            self::STATUS_IN_PROGRESS => 'ກຳລັງເຮັດ',
            self::STATUS_COMPLETED => 'ສຳເລັດ',
            self::STATUS_CANCELLED => 'ຍົກເລີກ',
        ];
    }

    public function getServiceStatusLaoAttribute(): string
    {
        return self::getStatuses()[$this->service_status] ?? $this->service_status;
    }

    // ======================== RELATIONSHIPS ========================

    /**
     * ຄິວທີ່ເກີ່ຍວຂ້ອງ
     */
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    /**
     * ບໍລິການທີ່ເລືອກ
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * ຜູ້ເພີ່ມບໍລິການ
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_id');
    }

    /**
     * ຜູ້ຮັບມອບໝາຍ
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * ການປິ່ນປົວທີ່ເກີ່ຍວຂ້ອງ (ຖ້າເປັນບໍລິການກວດທ່ານໝໍ)
     */
    public function treatment(): HasOne
    {
        return $this->hasOne(Treatment::class);
    }

    // ======================== SCOPES ========================

    /**
     * ບໍລິການທີ່ຍັງບໍ່ສຳເລັດ
     */
    public function scopePending($query)
    {
        return $query->whereNotIn('service_status', [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED
        ]);
    }

    /**
     * ບໍລິການທີ່ກຳລັງເຮັດ
     */
    public function scopeInProgress($query)
    {
        return $query->where('service_status', self::STATUS_IN_PROGRESS);
    }

    /**
     * ບໍລິການຂອງຄິວສະເພາະ
     */
    public function scopeForQueue($query, $queueId)
    {
        return $query->where('queue_id', $queueId)
                    ->orderBy('created_at'); // ຈັດລຳດັບຕາມເວລາເພີ່ມ
    }

    /**
     * ບໍລິການທີ່ມອບໝາຍໃຫ້ຜູ້ໃຊ້ສະເພາະ
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to_id', $userId);
    }

    /**
     * ບໍລິການທີ່ເພີ່ມວັນນີ້
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ======================== ACCESSORS & MUTATORS ========================

    /**
     * ຄິດເວລາທີ່ໃຊ້ຈິງອັດຕະໂນມັດ
     */
    protected static function booted()
    {
        static::updating(function (QueueService $queueService) {
            // ຄິດເວລາທີ່ໃຊ້ຈິງເມື່ອສຳເລັດ
            if ($queueService->service_status === self::STATUS_COMPLETED && 
                $queueService->started_at && 
                $queueService->completed_at &&
                !$queueService->actual_duration) {
                
                $queueService->actual_duration = $queueService->started_at
                    ->diffInMinutes($queueService->completed_at);
            }
        });
    }

    /**
     * ສະຖານະການດຳເນີນງານ
     */
    public function getProgressStatusAttribute(): string
    {
        return match($this->service_status) {
            self::STATUS_ADDED => 'ລໍຖ້າເລີ່ມ',
            self::STATUS_IN_PROGRESS => 'ກຳລັງດຳເນີນການ',
            self::STATUS_COMPLETED => "ສຳເລັດ: {$this->completed_at?->format('H:i')}",
            self::STATUS_CANCELLED => 'ຍົກເລີກ',
            default => 'ບໍ່ຊັດເຈນ'
        };
    }

    /**
     * ເວລາທີ່ໃຊ້ໃນການເຮັດບໍລິການ (ຖ້າຍັງບໍ່ສຳເລັດ)
     */
    public function getCurrentDurationAttribute(): ?int
    {
        if (!$this->started_at) return null;
        
        $endTime = $this->completed_at ?? now();
        return $this->started_at->diffInMinutes($endTime);
    }

    // ======================== HELPER METHODS ========================

    /**
     * ກວດສອບວ່າສາມາດເລີ່ມໄດ້ບໍ່
     */
    public function canStart(): bool
    {
        return $this->service_status === self::STATUS_ADDED && $this->assigned_to_id;
    }

    /**
     * ກວດສອບວ່າສາມາດສຳເລັດໄດ້ບໍ່
     */
    public function canComplete(): bool
    {
        return $this->service_status === self::STATUS_IN_PROGRESS;
    }

    /**
     * ເລີ່ມບໍລິການ
     */
    public function start(): bool
    {
        if (!$this->canStart()) {
            return false;
        }

        $this->update([
            'service_status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        return true;
    }

    /**
     * ສຳເລັດບໍລິການ
     */
    public function complete(): bool
    {
        if (!$this->canComplete()) {
            return false;
        }

        $this->update([
            'service_status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return true;
    }

    /**
     * ຍົກເລີກບໍລິການ
     */
    public function cancel(string $reason = null): bool
    {
        if ($this->service_status === self::STATUS_COMPLETED) {
            return false;
        }

        $notes = $this->notes;
        if ($reason) {
            $notes .= ($notes ? "\n" : "") . "ຍົກເລີກ: {$reason}";
        }

        $this->update([
            'service_status' => self::STATUS_CANCELLED,
            'notes' => $notes,
        ]);

        return true;
    }

    /**
     * ມອບໝາຍໃຫ້ຜູ້ໃຊ້
     */
    public function assignTo(int $userId): bool
    {
        $this->update([
            'assigned_to_id' => $userId,
        ]);

        return true;
    }

    // ======================== STATIC HELPER METHODS ========================

    /**
     * ສ້າງບໍລິການໃໝ່ໃນຄິວ
     */
    public static function addToQueue(int $queueId, int $serviceId, int $addedById, int $assignedToId = null): static
    {
        return static::create([
            'queue_id' => $queueId,
            'service_id' => $serviceId,
            'added_by_id' => $addedById,
            'assigned_to_id' => $assignedToId,
            'service_status' => self::STATUS_ADDED,
        ]);
    }

    /**
     * ດຶງບໍລິການທີ່ຮີບດ່ວນ (ຕາມເວລາສ້າງ - ອັນເກົ່າກ່ອນ)
     */
    public static function getUrgentServices()
    {
        return static::pending()
            ->with(['queue.patient', 'service', 'assignedTo'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * ດຶງສະຖິຕິການເຮັດວຽກວັນນີ້
     */
    public static function getTodayStats(): array
    {
        $today = static::today();
        
        return [
            'total' => $today->count(),
            'completed' => $today->where('service_status', self::STATUS_COMPLETED)->count(),
            'in_progress' => $today->where('service_status', self::STATUS_IN_PROGRESS)->count(),
            'pending' => $today->where('service_status', self::STATUS_ADDED)->count(),
            'cancelled' => $today->where('service_status', self::STATUS_CANCELLED)->count(),
        ];
    }

    /**
     * ດຶງບໍລິການທີ່ມອບໝາຍໃຫ້ຜູ້ໃຊ້ ແລະ ຍັງບໍ່ສຳເລັດ
     */
    public static function getMyPendingWork(int $userId)
    {
        return static::assignedTo($userId)
            ->pending()
            ->with(['queue.patient', 'service'])
            ->orderBy('created_at')
            ->get();
    }
}