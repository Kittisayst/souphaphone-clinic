<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'current_stage',
        'assigned_room_id',
        'called_at',
        'basic_check_at',
        'examination_started_at',
        'results_available_at',
        'consultation_started_at',
        'completed_at',
        'basic_vitals',
        'notes',
        'created_by',
        'basic_check_by',
        'examination_by',
        'consultation_by',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'called_at' => 'datetime',
            'basic_check_at' => 'datetime',
            'examination_started_at' => 'datetime',
            'results_available_at' => 'datetime',
            'consultation_started_at' => 'datetime',
            'completed_at' => 'datetime',
            'basic_vitals' => 'array',
        ];
    }

    // === Relationships ===
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function assignedRoom(): BelongsTo
    {
        return $this->belongsTo(ExaminationRoom::class, 'assigned_room_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function basicCheckBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'basic_check_by');
    }

    public function examinationBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'examination_by');
    }

    public function consultationBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultation_by');
    }

    // === Workflow Status Checks ===
    public function isAtRegistration(): bool
    {
        return $this->current_stage === 'registration';
    }

    public function isAtBasicCheck(): bool
    {
        return $this->current_stage === 'basic_check';
    }

    public function isAtWaitingRoom(): bool
    {
        return $this->current_stage === 'waiting_room';
    }

    public function isAtExamination(): bool
    {
        return $this->current_stage === 'examination';
    }

    public function isWaitingResults(): bool
    {
        return $this->current_stage === 'waiting_results';
    }

    public function isAtConsultation(): bool
    {
        return $this->current_stage === 'consultation';
    }

    public function isAtTreatment(): bool
    {
        return $this->current_stage === 'treatment';
    }

    public function isAtPayment(): bool
    {
        return $this->current_stage === 'payment';
    }

    // === Workflow Actions ===
    
    /**
     * ເອີ້ນຄິວເພື່ອກວດພື້ນຖານ (Counter Staff)
     */
    public function callForBasicCheck(): bool
    {
        if ($this->current_stage === 'registration' && $this->status === 'waiting') {
            return $this->update([
                'status' => 'called',
                'current_stage' => 'basic_check',
                'called_at' => now(),
            ]);
        }
        return false;
    }

    /**
     * ເລີ່ມການກວດພື້ນຖານ (Counter Staff)
     */
    public function startBasicCheck(int $staffId, array $vitals = []): bool
    {
        if ($this->current_stage === 'basic_check' && $this->status === 'called') {
            return $this->update([
                'status' => 'in_progress',
                'basic_check_at' => now(),
                'basic_check_by' => $staffId,
                'basic_vitals' => $vitals,
            ]);
        }
        return false;
    }

    /**
     * ສຳເລັດການກວດພື້ນຖານ ແລະ ມອບໝາຍຫ້ອງ (Counter Staff)
     */
    public function completeBasicCheckAndAssignRoom(int $roomId): bool
    {
        if ($this->current_stage === 'basic_check' && $this->status === 'in_progress') {
            // ກວດສອບວ່າຫ້ອງວ່າງບໍ່
            $room = ExaminationRoom::find($roomId);
            if (!$room || $room->status !== 'available') {
                return false;
            }

            // ອັບເດດຫ້ອງ
            $room->update([
                'status' => 'occupied',
                'current_patient_id' => $this->patient_id,
            ]);

            return $this->update([
                'status' => 'waiting',
                'current_stage' => 'waiting_room',
                'assigned_room_id' => $roomId,
            ]);
        }
        return false;
    }

    /**
     * ເອີ້ນເຂົ້າຫ້ອງກວດ (Doctor/Nurse)
     */
    public function callToExaminationRoom(): bool
    {
        if ($this->current_stage === 'waiting_room' && $this->status === 'waiting') {
            return $this->update([
                'status' => 'called',
                'current_stage' => 'examination',
            ]);
        }
        return false;
    }

    /**
     * ເລີ່ມການກວດພິເສດ (Doctor)
     */
    public function startExamination(int $doctorId): bool
    {
        if ($this->current_stage === 'examination' && $this->status === 'called') {
            return $this->update([
                'status' => 'in_progress',
                'examination_started_at' => now(),
                'examination_by' => $doctorId,
            ]);
        }
        return false;
    }

    /**
     * ສົ່ງລໍຖ້າຜົນກວດ (Doctor)
     */
    public function sendToWaitingResults(): bool
    {
        if ($this->current_stage === 'examination' && $this->status === 'in_progress') {
            return $this->update([
                'status' => 'waiting',
                'current_stage' => 'waiting_results',
            ]);
        }
        return false;
    }

    /**
     * ຜົນກວດພ້ອມແລ້ວ - ເອີ້ນພົບໝໍ (Counter Staff)
     */
    public function callForConsultation(): bool
    {
        if ($this->current_stage === 'waiting_results' && $this->status === 'waiting') {
            return $this->update([
                'status' => 'called',
                'current_stage' => 'consultation',
                'results_available_at' => now(),
            ]);
        }
        return false;
    }

    /**
     * ເລີ່ມການປຶກສາ/ວິນິໄສ (Doctor)
     */
    public function startConsultation(int $doctorId): bool
    {
        if ($this->current_stage === 'consultation' && $this->status === 'called') {
            return $this->update([
                'status' => 'in_progress',
                'current_stage' => 'treatment',
                'consultation_started_at' => now(),
                'consultation_by' => $doctorId,
            ]);
        }
        return false;
    }

    /**
     * ສົ່ງຈ່າຍເງິນ (Doctor → Cashier)
     */
    public function sendToPayment(): bool
    {
        if ($this->current_stage === 'treatment' && $this->status === 'in_progress') {
            return $this->update([
                'status' => 'waiting',
                'current_stage' => 'payment',
            ]);
        }
        return false;
    }

    /**
     * ສຳເລັດການຈ່າຍເງິນ (Cashier)
     */
    public function completePayment(): bool
    {
        if ($this->current_stage === 'payment' && $this->status === 'waiting') {
            // ປ່ອຍຫ້ອງ
            if ($this->assigned_room_id) {
                $this->assignedRoom->update([
                    'status' => 'available',
                    'current_patient_id' => null,
                ]);
            }

            return $this->update([
                'status' => 'completed',
                'current_stage' => 'completed',
                'completed_at' => now(),
            ]);
        }
        return false;
    }

    // === Helper Methods ===

    /**
     * ສະແດງຂັ້ນຕອນເປັນພາສາລາວ
     */
    public function getCurrentStageLabel(): string
    {
        return match ($this->current_stage) {
            'registration' => 'ລົງທະບຽນ',
            'basic_check' => 'ກວດພື້ນຖານ',
            'waiting_room' => 'ລໍຖ້າເຂົ້າຫ້ອງ',
            'examination' => 'ການກວດພິເສດ',
            'waiting_results' => 'ລໍຖ້າຜົນກວດ',
            'consultation' => 'ພົບໝໍ',
            'treatment' => 'ການຮັກສາ',
            'payment' => 'ຈ່າຍເງິນ',
            'completed' => 'ສຳເລັດ',
            default => $this->current_stage,
        };
    }

    /**
     * ກວດສອບວ່າ Counter Staff ສາມາດດຳເນີນການໄດ້ບໍ່
     */
    public function canCounterStaffHandle(): bool
    {
        return in_array($this->current_stage, [
            'basic_check',
            'waiting_results' // ສາມາດເອີ້ນຄົນໄຂ້ພົບໝໍໄດ້
        ]);
    }

    /**
     * ກວດສອບວ່າ Doctor ສາມາດດຳເນີນການໄດ້ບໍ່
     */
    public function canDoctorHandle(): bool
    {
        return in_array($this->current_stage, [
            'examination',
            'consultation',
            'treatment'
        ]);
    }

    /**
     * ກວດສອບວ່າ Cashier ສາມາດດຳເນີນການໄດ້ບໍ່
     */
    public function canCashierHandle(): bool
    {
        return $this->current_stage === 'payment';
    }

    // === Scopes ===
    
    public function scopeAtStage($query, string $stage)
    {
        return $query->where('current_stage', $stage);
    }

    public function scopeForCounterStaff($query)
    {
        return $query->whereIn('current_stage', ['registration', 'basic_check', 'waiting_results'])
                    ->whereIn('status', ['waiting', 'called', 'in_progress']);
    }

    public function scopeForDoctor($query)
    {
        return $query->whereIn('current_stage', ['examination', 'consultation', 'treatment'])
                    ->whereIn('status', ['waiting', 'called', 'in_progress']);
    }

    public function scopeForCashier($query)
    {
        return $query->where('current_stage', 'payment')
                    ->where('status', 'waiting');
    }

    // === Auto-generation ===
    
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
            if (empty($queue->current_stage)) {
                $queue->current_stage = 'registration';
            }
        });
    }

    public static function generateQueueNumber(?Carbon $date = null): string
    {
        $date = $date ?? today();
        $lastQueue = self::whereDate('queue_date', $date)
            ->orderBy('queue_number', 'desc')
            ->first();

        if (!$lastQueue) {
            return 'A001';
        }

        $lastNumber = intval(substr($lastQueue->queue_number, 1));
        $newNumber = $lastNumber + 1;
        return 'A' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}