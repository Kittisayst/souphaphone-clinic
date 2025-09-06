<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Queue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'queue_number',
        'waiting_number',
        'queue_date',
        'initial_complaint',
        'doctor_id',
        'assigned_room_id',
        'room_assigned_at',
        'queue_status',
        'priority_level',
        'vital_checked_at',
        'doctor_start_at',
        'completed_at',
        'created_by'
    ];
    protected $casts = [
        'queue_date' => 'date',
        'vital_checked_at' => 'datetime',
        'doctor_start_at' => 'datetime',
        'lab_start_at' => 'datetime',
        'results_ready_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // =================== GENERATORS ===================
    protected static function boot()
    {
        parent::boot();

        // 🔥 Auto-fill ເມື່ອສ້າງ Queue ໃໝ່
        static::creating(function ($queue) {
            // ✅ Auto queue_number ຖ້າບໍ່ມີ
            if (empty($queue->queue_number)) {
                $queue->queue_number = self::getNextQueueNumber($queue->queue_date);
            }

            // ✅ Auto waiting_number ຖ້າບໍ່ມີ
            if (empty($queue->waiting_number)) {
                $queue->waiting_number = self::getNextWaitingNumber();
            }

            // ✅ Auto queue_date ຖ້າບໍ່ມີ
            if (empty($queue->queue_date)) {
                $queue->queue_date = now()->toDateString();
            }

            // ✅ Auto created_by ຖ້າບໍ່ມີ
            if (empty($queue->created_by)) {
                $queue->created_by = auth()->id();
            }

            // ✅ Auto queue_status ຖ້າບໍ່ມີ
            if (empty($queue->queue_status)) {
                $queue->queue_status = 'Registered';
            }

            // ✅ Auto priority_level ຖ້າບໍ່ມີ
            if (empty($queue->priority_level)) {
                $queue->priority_level = 'Normal';
            }
        });
    }

    // =================== RELATIONSHIPS ===================

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function queueServices()
    {
        return $this->hasMany(QueueService::class);
    }
    public function vitalSign()
    {
        return $this->hasOne(VitalSign::class);
    }
    public function treatments()
    {
        return $this->hasManyThrough(Treatment::class, QueueService::class);
    }
    public function labTests()
    {
        return $this->hasManyThrough(LabTest::class, QueueService::class);
    }
    public function medicationInstructions()
    {
        return $this->hasMany(MedicationInstruction::class);
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // =================== ACCESSORS ===================
    // Status Check Methods
    public function isRegistered(): bool
    {
        return $this->queue_status === 'Registered';
    }
    public function isVitalChecked(): bool
    {
        return $this->queue_status === 'Vital_Checked';
    }
    public function isWithDoctor(): bool
    {
        return $this->queue_status === 'With_Doctor';
    }
    public function isLabTesting(): bool
    {
        return $this->queue_status === 'Lab_Testing';
    }
    public function isResultsReady(): bool
    {
        return $this->queue_status === 'Results_Ready';
    }
    public function isCompleted(): bool
    {
        return $this->queue_status === 'Completed';
    }
    public function isCancelled(): bool
    {
        return $this->queue_status === 'Cancelled';
    }

    public function hasVitalSigns(): bool
    {
        return $this->vitalSign()->exists();
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
    // ====================== Helper =================================
    /**
     * ສ້າງເລກຄິວໃໝ່ສຳລັບວັນນີ້
     */
    public static function getNextQueueNumber($date = null): int
    {
        $date = $date ?? now()->toDateString();

        return DB::transaction(function () use ($date) {
            // ✅ ໃຊ້ MAX() + 1 ໃນ query ດຽວ
            $result = DB::selectOne('
            SELECT COALESCE(MAX(queue_number), 0) + 1 as next_number 
            FROM queues 
            WHERE queue_date = ? 
            FOR UPDATE
        ', [$date]);

            return $result->next_number;
        });
    }

    /**
     * ສ້າງເລກລໍຖ້າໃໝ່
     */
    public static function getNextWaitingNumber(): int
    {
        return DB::transaction(function () {
            // ✅ ໃຊ້ MAX() + 1 ໃນ query ດຽວ
            $result = DB::selectOne('
            SELECT COALESCE(MAX(waiting_number), 0) + 1 as next_number 
            FROM queues 
            WHERE waiting_number > 0 
            FOR UPDATE
        ');

            return $result->next_number;
        });
    }

    /**
     * ຂັ້ນຕອນທີ 2: ກວດເບື້ອງຕົ້ນ (Vital Signs)
     */
    public function completeVitalSigns(): bool
    {
        $this->queue_status = 'Vital_Checked';
        $this->vital_checked_at = now();
        return $this->save();
    }

    /**
     * ຂັ້ນຕອນທີ 3: ເຂົ້າພົບທ່ານໝໍ
     */
    public function startDoctorConsultation(): bool
    {
        $this->queue_status = 'With_Doctor';
        $this->doctor_start_at = now();
        return $this->save();
    }

    /**
     * ຂັ້ນຕອນທີ 4: ສົ່ງກວດແລັບ (ຖ້າຈຳເປັນ)
     */
    public function sendToLab(): bool
    {
        $this->queue_status = 'Lab_Testing';
        $this->lab_start_at = now();
        return $this->save();
    }

    /**
     * ຂັ້ນຕອນທີ 5: ຜົນກວດພ້ອມ (ຫຼື skip ຖ້າບໍ່ມີແລັບ)
     */
    public function markResultsReady(): bool
    {
        $this->queue_status = 'Results_Ready';
        $this->results_ready_at = now();
        return $this->save();
    }

    /**
     * ຂັ້ນຕອນທີ 6: ສຳເລັດການກວດ
     */
    public function completeQueue(): bool
    {
        $oldWaitingNumber = $this->waiting_number;

        $this->queue_status = 'Completed';
        $this->completed_at = now();
        $this->waiting_number = 0;

        if ($oldWaitingNumber > 0) {
            return DB::transaction(function () use ($oldWaitingNumber) {
                // ✅ ບັນທຶກການ complete
                $result = $this->save();

                // ✅ ອັບເດດຄິວອື່ນດ້ວຍ SQL ດຽວ
                DB::statement('
                UPDATE queues 
                SET waiting_number = waiting_number - 1 
                WHERE waiting_number > ? 
                AND queue_status != "Completed" 
                AND queue_status != "Cancelled"
            ', [$oldWaitingNumber]);

                return $result;
            });
        }

        return $this->save();
    }

    /**
     * Skip ແລັບ: ຈາກ With_Doctor ໄປ Results_Ready ໂດຍກົງ
     */
    public function skipLabToResults(): bool
    {
        if ($this->queue_status === 'With_Doctor') {
            return $this->markResultsReady();
        }
        return false;
    }

    /**
     * ຍົກເລີກຄິວ
     */
    public function cancelQueue(): bool
    {
        $this->queue_status = 'Cancelled';
        $oldWaitingNumber = $this->waiting_number;
        $this->waiting_number = 0;

        // ປັບ waiting_number ຂອງຄິວທີ່ຍັງລໍຖ້າ
        self::where('waiting_number', '>', $oldWaitingNumber)
            ->decrement('waiting_number');

        return $this->save();
    }

    /**
     * ຄຳນວນເວລາລໍຖ້າຄາດຄະເນ
     */
    public function getEstimatedWaitingTimeAttribute(): string
    {
        if ($this->waiting_number <= 0) {
            return 'ສຳເລັດແລ້ວ';
        }

        // ຄາດຄະເນຕາມຂັ້ນຕອນ
        $minutesPerStep = match ($this->queue_status) {
            'Registered' => 15,      // ລໍຖ້າກວດເບື້ອງຕົ້ນ
            'Vital_Checked' => 20,   // ລໍຖ້າພົບທ່ານໝໍ
            'With_Doctor' => 0,      // ກຳລັງກວດ
            'Lab_Testing' => 30,     // ລໍຖ້າຜົນແລັບ
            'Results_Ready' => 10,   // ລໍຖ້າສະຫຼຸບ
            default => 15
        };

        $totalMinutes = ($this->waiting_number - 1) * $minutesPerStep;
        $hours = intval($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        if ($hours > 0) {
            return "{$hours} ຊົ່ວໂມງ {$remainingMinutes} ນາທີ";
        }
        return "{$remainingMinutes} ນາທີ";
    }

    /**
     * ກວດສອບວ່າຈຳເປັນຕ້ອງກວດແລັບບໍ່
     */
    public function needsLabTesting(): bool
    {
        return $this->queueServices()
            ->whereHas('service', function ($query) {
                $query->where('service_category', 'Laboratory');
            })
            ->exists();
    }

    /**
     * ສ້າງຄິວໃໝ່ພ້ອມ waiting_number
     */
    public static function createNewQueue(array $data): self
    {
        return self::create($data);
    }
}