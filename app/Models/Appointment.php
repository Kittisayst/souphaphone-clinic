<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'service_id',
        'appointment_date',
        'appointment_time',
        'duration_minutes',
        'type',
        'status',
        'reminder_sent',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'appointment_time' => 'datetime:H:i',
            'duration_minutes' => 'integer',
            'reminder_sent' => 'boolean',
        ];
    }

    /**
     * Boot method ສຳຫລັບ auto-generate appointment number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            // Auto-generate appointment number
            $appointment->appointment_number = self::generateAppointmentNumber();

            // Auto set created_by if not provided
            if (!$appointment->created_by) {
                $appointment->created_by = auth()->id();
            }
        });

        // Auto calculate end time when creating
        static::created(function ($appointment) {
            $appointment->updateEndTime();
        });

        // Auto update end time when duration changes
        static::updating(function ($appointment) {
            if ($appointment->isDirty(['appointment_time', 'duration_minutes'])) {
                $appointment->updateEndTime();
            }
        });
    }

    /**
     * ສ້າງເລກການນັດອັດຕະໂນມັດ (AP-2024-001)
     */
    public static function generateAppointmentNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');

        $lastAppointment = self::whereYear('created_at', $year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastAppointment) {
            return "AP-{$year}-{$month}-001";
        }

        // ດຶງເອົາເລກຈາກການນັດຄັ້ງລ່າສຸດ
        $parts = explode('-', $lastAppointment->appointment_number ?? '');
        $lastNumber = isset($parts[3]) ? intval($parts[3]) : 0;
        $newNumber = $lastNumber + 1;

        return "AP-{$year}-{$month}-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * ຄວາມສຳພັນກັບຄົນໄຂ້
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * ຄວາມສຳພັນກັບໝໍ
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * ຄວາມສຳພັນກັບບໍລິການ
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(MedicalService::class, 'service_id');
    }

    /**
     * ຄວາມສຳພັນກັບຜູ້ສ້າງການນັດ
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ອັບເດດເວລາສິ້ນສຸດ
     */
    public function updateEndTime(): void
    {
        if ($this->appointment_time && $this->duration_minutes) {
            $startTime = Carbon::parse($this->appointment_time);
            $this->end_time = $startTime->copy()->addMinutes($this->duration_minutes)->format('H:i');
        }
    }

    /**
     * ກວດສອບສະຖານະການນັດ
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isNoShow(): bool
    {
        return $this->status === 'no_show';
    }

    /**
     * ກວດສອບປະເພດການນັດ
     */
    public function isFollowUp(): bool
    {
        return $this->type === 'follow_up';
    }

    public function isNewVisit(): bool
    {
        return $this->type === 'new_visit';
    }

    public function isCheckUp(): bool
    {
        return $this->type === 'check_up';
    }

    public function isProcedure(): bool
    {
        return $this->type === 'procedure';
    }

    /**
     * ການນັດວັນນີ້
     */
    public function isToday(): bool
    {
        return Carbon::parse((string) $this->appointment_date)->isToday();
    }

    /**
     * ການນັດມື້ອື່ນ
     */
    public function isTomorrow(): bool
    {
        return  Carbon::parse((string)$this->appointment_date)->isTomorrow();
    }

    /**
     * ການນັດໃນອະນາຄົດ
     */
    public function isFuture(): bool
    {
        return Carbon::parse((string)$this->appointment_date)->isFuture();
    }

    /**
     * ການນັດໃນອະດີດ
     */
    public function isPast(): bool
    {
        return Carbon::parse((string)$this->appointment_date)->isPast();
    }

    /**
     * ເກີນເວລານັດແລ້ວບໍ
     */
    public function isOverdue(): bool
    {
        if (!$this->isScheduled() && !$this->isConfirmed()) {
            return false;
        }

        $appointmentDateTime = Carbon::parse((string)$this->appointment_date)->copy()
            ->setTimeFrom($this->appointment_time);

        return $appointmentDateTime->isPast();
    }

    /**
     * ສະຖານະການນັດເປັນພາສາລາວ
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'ນັດໝາຍແລ້ວ',
            'confirmed' => 'ຢືນຢັນແລ້ວ',
            'completed' => 'ສຳເລັດແລ້ວ',
            'cancelled' => 'ຍົກເລີກ',
            'no_show' => 'ບໍ່ມາ',
            default => $this->status,
        };
    }

    /**
     * ປະເພດການນັດເປັນພາສາລາວ
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'follow_up' => 'ຕິດຕາມ',
            'new_visit' => 'ກວດຄັ້ງທຳອິດ',
            'check_up' => 'ກວດສຸຂະພາບ',
            'procedure' => 'ການຮັກສາ',
            default => $this->type,
        };
    }

    /**
     * ສີສະຖານະ
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'warning',
            'confirmed' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'no_show' => 'gray',
            default => 'gray',
        };
    }

    /**
     * ສີປະເພດການນັດ
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'follow_up' => 'info',
            'new_visit' => 'success',
            'check_up' => 'warning',
            'procedure' => 'danger',
            default => 'gray',
        };
    }

    /**
     * ເວລາການນັດແບບເຕັມ
     */
    public function getFullAppointmentTimeAttribute(): string
    {
        $start = $this->appointment_time->format('H:i');
        $end = $this->end_time ?? $this->appointment_time->copy()->addMinutes($this->duration_minutes)->format('H:i');
        return "{$start} - {$end}";
    }

    /**
     * ວັນທີແລະເວລາແບບເຕັມ
     */
    public function getFullDateTimeAttribute(): string
    {
        return  Carbon::parse((string)$this->appointment_date)->format('d/m/Y') . ' ' . $this->full_appointment_time;
    }

    /**
     * ຈຳນວນວັນທີ່ເຫຼືອ
     */
    public function getDaysUntilAppointmentAttribute(): int
    {
        return Carbon::parse((string)$this->appointment_date)->diffInDays(today(), false);
    }

    /**
     * ຢືນຢັນການນັດ
     */
    public function confirm(): bool
    {
        if ($this->isScheduled()) {
            $this->update(['status' => 'confirmed']);
            return true;
        }
        return false;
    }

    /**
     * ສຳເລັດການນັດ
     */
    public function complete(string $notes = null): bool
    {
        if ($this->isScheduled() || $this->isConfirmed()) {
            $this->update([
                'status' => 'completed',
                'notes' => $notes ? $this->notes . "\n\n" . $notes : $this->notes,
            ]);
            return true;
        }
        return false;
    }

    /**
     * ຍົກເລີກການນັດ
     */
    public function cancel(string $reason = null): bool
    {
        if ($this->isScheduled() || $this->isConfirmed()) {
            $this->update([
                'status' => 'cancelled',
                'notes' => $reason ? $this->notes . "\n\nຍົກເລີກ: " . $reason : $this->notes,
            ]);
            return true;
        }
        return false;
    }

    /**
     * ໝາຍເປັນບໍ່ມາ
     */
    public function markAsNoShow(): bool
    {
        if ($this->isOverdue() && ($this->isScheduled() || $this->isConfirmed())) {
            $this->update(['status' => 'no_show']);
            return true;
        }
        return false;
    }

    /**
     * ສົ່ງການແຈ້ງເຕືອນ
     */
    public function sendReminder(): bool
    {
        if (!$this->reminder_sent && ($this->isScheduled() || $this->isConfirmed())) {
            // TODO: ໃຊ້ Notification system ໃນອະນາຄົດ
            $this->update(['reminder_sent' => true]);
            return true;
        }
        return false;
    }

    /**
     * ເປີ່ຍນເວລານັດ
     */
    public function reschedule(Carbon $newDate, Carbon $newTime, int $newDuration = null): bool
    {
        if ($this->isScheduled() || $this->isConfirmed()) {
            // ກວດສອບວ່າເວລາໃໝ່ວ່າງບໍ
            if ($this->isTimeSlotAvailable($newDate, $newTime, $newDuration)) {
                $this->update([
                    'appointment_date' => $newDate,
                    'appointment_time' => $newTime,
                    'duration_minutes' => $newDuration ?? $this->duration_minutes,
                    'reminder_sent' => false, // Reset reminder
                ]);
                return true;
            }
        }
        return false;
    }

    /**
     * ກວດສອບວ່າເວລານັ້ນວ່າງບໍ
     */
    public function isTimeSlotAvailable(Carbon $date, Carbon $time, int $duration = null): bool
    {
        $duration = $duration ?? $this->duration_minutes;
        $endTime = $time->copy()->addMinutes($duration);

        $conflictingAppointments = self::where('doctor_id', $this->doctor_id)
            ->where('appointment_date', $date)
            ->where('id', '!=', $this->id) // ຫຼີກເວັ້ນການນັດປະຈຸບັນ
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where(function ($query) use ($time, $endTime) {
                $query->whereBetween('appointment_time', [$time, $endTime])
                    ->orWhere(function ($q) use ($time, $endTime) {
                        $q->where('appointment_time', '<=', $time)
                            ->whereRaw('DATE_ADD(appointment_time, INTERVAL duration_minutes MINUTE) > ?', [$time]);
                    });
            })
            ->exists();

        return !$conflictingAppointments;
    }

    /**
     * ສ້າງຄິວຈາກການນັດ
     */
    public function createQueue(string $priority = 'normal'): ?Queue
    {
        if ($this->isToday() && ($this->isScheduled() || $this->isConfirmed())) {
            return Queue::create([
                'patient_id' => $this->patient_id,
                'queue_date' => $this->appointment_date,
                'priority' => $priority,
                'notes' => "ສ້າງຈາກການນັດ #{$this->appointment_number}",
                'created_by' => auth()->id() ?? $this->created_by,
            ]);
        }
        return null;
    }

    /**
     * Scope ສຳຫລັບຄົ້ນຫາ
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }

    public function scopeTomorrow($query)
    {
        return $query->whereDate('appointment_date', now()->addDay());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('appointment_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->whereBetween('appointment_date', [
            today(),
            now()->addDays($days)
        ])->whereIn('status', ['scheduled', 'confirmed']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('appointment_date', '<', today())
            ->whereIn('status', ['scheduled', 'confirmed']);
    }

    public function scopeNeedReminder($query, int $hoursBefore = 24)
    {
        return $query->where('reminder_sent', false)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('appointment_date', '>=', today())
            ->where('appointment_date', '<=', now()->addHours($hoursBefore));
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * ສາມາດແກ້ໄຂໄດ້ບໍ
     */
    public function canBeEdited(): bool
    {
        return $this->isScheduled() || $this->isConfirmed();
    }

    /**
     * ສາມາດຍົກເລີກໄດ້ບໍ
     */
    public function canBeCancelled(): bool
    {
        return $this->isScheduled() || $this->isConfirmed();
    }

    /**
     * ສາມາດຢືນຢັນໄດ້ບໍ
     */
    public function canBeConfirmed(): bool
    {
        return $this->isScheduled();
    }

    /**
     * ສາມາດສ້າງຄິວໄດ້ບໍ
     */
    public function canCreateQueue(): bool
    {
        return $this->isToday() && ($this->isScheduled() || $this->isConfirmed());
    }

    /**
     * ຕ້ອງການແຈ້ງເຕືອນບໍ
     */
    public function needsReminder(): bool
    {
        return !$this->reminder_sent &&
            ($this->isScheduled() || $this->isConfirmed()) &&
            Carbon::parse((string)$this->appointment_date)->isTomorrow();
    }
}