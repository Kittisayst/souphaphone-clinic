<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'queue_number',
        'queue_date',
        'priority',
        'status',
        'called_at',
        'completed_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'called_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Boot method ສຳຫລັບສ້າງເລກຄິວອັດຕະໂນມັດ
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($queue) {
            if (empty($queue->queue_number)) {
                $queue->queue_number = self::generateQueueNumber($queue->queue_date);
            }
            if (empty($queue->queue_date)) {
                $queue->queue_date = today();
            }
        });

        // Auto update timestamps based on status changes
        static::updating(function ($queue) {
            if ($queue->isDirty('status')) {
                switch ($queue->status) {
                    case 'called':
                        if (!$queue->called_at) {
                            $queue->called_at = now();
                        }
                        break;
                    case 'completed':
                        if (!$queue->completed_at) {
                            $queue->completed_at = now();
                        }
                        break;
                }
            }
        });
    }

    /**
     * ສ້າງເລກຄິວອັດຕະໂນມັດ (A001, A002...)
     */
    public static function generateQueueNumber(?Carbon $date = null): string
    {
        $date = $date ?? today();

        // ຫາຄິວລ່າສຸດຂອງວັນນັ້ນ
        $lastQueue = self::whereDate('queue_date', $date)
            ->orderBy('queue_number', 'desc')
            ->first();

        if (!$lastQueue) {
            return 'A001';
        }

        // ດຶງເອົາເລກຈາກເລກຄິວຄັ້ງລ່າສຸດ
        $lastNumber = intval(substr($lastQueue->queue_number, 1));
        $newNumber = $lastNumber + 1;

        return 'A' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Reset ເລກຄິວປະຈຳວັນ (ໃຊ້ໃນ Scheduler)
     */
    public static function resetDailyQueue(): void
    {
        // ລຶບຄິວເກົ່າທີ່ສຳເລັດແລ້ວ (ສາມາດເກັບໄວ້ໃນປະຫວັດ)
        // ບໍ່ລຶບ record ຈິງໆ, ເພື່ອເກັບປະຫວັດ
    }

    /**
     * ຄວາມສຳພັນກັບຄົນໄຂ້
     */

    public function room(): BelongsTo
    {
        return $this->belongsTo(ExaminationRoom::class, 'room_id');
    }
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * ຄວາມສຳພັນກັບຜູ້ສ້າງຄິວ
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ຄວາມສຳພັນກັບການກວດ
     */
    public function medicalExaminations(): HasMany
    {
        return $this->hasMany(MedicalExamination::class);
    }

    /**
     * ການກວດປະຈຸບັນ
     */
    public function currentExamination(): HasOne
    {
        return $this->hasOne(MedicalExamination::class)
            ->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * ກວດສອບສະຖານະຄິວ
     */
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    public function isCalled(): bool
    {
        return $this->status === 'called';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * ຄິວມີຄວາມສຳຄັນສູງ
     */
    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }

    /**
     * ຄິວປົກກະຕິ
     */
    public function isNormal(): bool
    {
        return $this->priority === 'normal';
    }

    /**
     * ສະຖານະຄິວເປັນພາສາລາວ
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'ລໍຖ້າ',
            'called' => 'ເອີ້ນແລ້ວ',
            'in_progress' => 'ກຳລັງດຳເນີນການ',
            'completed' => 'ສຳເລັດ',
            'cancelled' => 'ຍົກເລີກ',
            default => $this->status,
        };
    }

    /**
     * ຄວາມສຳຄັນເປັນພາສາລາວ
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'normal' => 'ປົກກະຕິ',
            'urgent' => 'ດ່ວນ',
            default => $this->priority,
        };
    }

    /**
     * ສີສະຖານະ
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'warning',
            'called' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    /**
     * ສີຄວາມສຳຄັນ
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'normal' => 'primary',
            'urgent' => 'danger',
            default => 'gray',
        };
    }

    /**
     * ເວລາລໍຖ້າ (ນາທີ)
     */
    public function getWaitingTimeInMinutesAttribute(): ?int
    {
        if ($this->isWaiting()) {
            return $this->created_at->diffInMinutes(now());
        }

        if ($this->called_at) {
            return $this->created_at->diffInMinutes($this->called_at);
        }

        return null;
    }

    /**
     * ເວລາໃຊ້ທັງໝົດ (ນາທີ)
     */
    public function getTotalTimeInMinutesAttribute(): ?int
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->completed_at);
    }

    /**
     * ຄິວທີ່ຢູ່ໃນວັນດຽວກັນ
     */
    public function sameDayQueues()
    {
        return self::whereDate('queue_date', $this->queue_date);
    }

    /**
     * ຄິວທີ່ຢູ່ຂ້າງໜ້າ
     */
    public function getQueuesAheadAttribute(): int
    {
        return self::whereDate('queue_date', $this->queue_date)
            ->where('queue_number', '<', $this->queue_number)
            ->whereIn('status', ['waiting', 'called', 'in_progress'])
            ->count();
    }

    /**
     * ປະມານເວລາລໍຖ້າ (ນາທີ)
     */
    public function getEstimatedWaitTimeAttribute(): int
    {
        $queuesAhead = $this->queues_ahead;
        $averageTimePerQueue = 15; // ສົມມຸດວ່າແຕ່ລະຄິວໃຊ້ເວລາ 15 ນາທີ

        return $queuesAhead * $averageTimePerQueue;
    }

    /**
     * ສາມາດຍົກເລີກໄດ້ບໍ່
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['waiting', 'called']);
    }

    /**
     * ສາມາດເອີ້ນໄດ້ບໍ່
     */
    public function canBeCalled(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * ສາມາດເລີ່ມການກວດໄດ້ບໍ່
     */
    public function canStartExamination(): bool
    {
        return in_array($this->status, ['called', 'in_progress']);
    }

    /**
     * Scope ສຳຫລັບຄົ້ນຫາ
     */
    public function scopeToday($query)
    {
        return $query->whereDate('queue_date', Carbon::today());
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('queue_date', $date);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeCalled($query)
    {
        return $query->where('status', 'called');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'called', 'in_progress']);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeOrderByQueue($query)
    {
        return $query->orderBy('priority', 'desc') // urgent ກ່ອນ
            ->orderBy('queue_number', 'asc'); // ແລ້ວຕາມລຳດັບເລກ
    }

    /**
     * ການແຈ້ງເຕືອນ
     */
    public function shouldNotifyPatient(): bool
    {
        // ແຈ້ງເຕືອນຄົນໄຂ້ເມື່ອໃກ້ຖືງຄິວ (2-3 ຄິວກ່ອນ)
        return $this->isWaiting() && $this->queues_ahead <= 2;
    }

    public function shouldNotifyStaff(): bool
    {
        // ແຈ້ງເຕືອນພະນັກງານເມື່ອຄິວລໍຖ້ານານເກີນໄປ
        return $this->isWaiting() && $this->waiting_time_in_minutes > 60;
    }

    /**
     * ເອີ້ນຄິວ
     */
    public function call(): bool
    {
        if ($this->canBeCalled()) {
            $this->update([
                'status' => 'called',
                'called_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * ເລີ່ມການກວດ
     */
    public function startExamination(): bool
    {
        if ($this->canStartExamination()) {
            $this->update(['status' => 'in_progress']);
            return true;
        }
        return false;
    }

    /**
     * ສຳເລັດຄິວ
     */
    public function complete(): bool
    {
        if ($this->isInProgress()) {
            $this->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * ຍົກເລີກຄິວ
     */
    public function cancel(string $reason = null): bool
    {
        if ($this->canBeCancelled()) {
            $this->update([
                'status' => 'cancelled',
                'notes' => $reason ? "ຍົກເລີກ: {$reason}" : $this->notes,
            ]);
            return true;
        }
        return false;
    }

    /**
     * ສ້າງການກວດຈາກຄິວນີ້
     */
    public function createExamination(int $serviceId, ?int $roomId = null): MedicalExamination
    {
        return MedicalExamination::create([
            'patient_id' => $this->patient_id,
            'queue_id' => $this->id,
            'service_id' => $serviceId,
            'room_id' => $roomId,
            'examination_date' => $this->queue_date,
            'examination_time' => now()->format('H:i'),
            'status' => 'pending',
        ]);
    }
}