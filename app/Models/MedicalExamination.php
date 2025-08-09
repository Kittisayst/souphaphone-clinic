<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class MedicalExamination extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'queue_id',
        'service_id',
        'room_id',
        'examination_date',
        'examination_time',
        'vital_signs',
        'examination_results',
        'status',
        'conducted_by',
        'verified_by',
        'started_at',
        'completed_at',
        'verified_at',
        'notes',
        'technician_notes',
        'doctor_notes',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'examination_date' => 'date',
            'examination_time' => 'datetime:H:i',
            'vital_signs' => 'array',
            'examination_results' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'verified_at' => 'datetime',
            'attachments' => 'array',
        ];
    }

    /**
     * Boot method ສຳຫລັບ auto-fill examination_date ແລະ examination_time
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($examination) {
            if (empty($examination->examination_date)) {
                $examination->examination_date = now()->toDateString();
            }
            if (empty($examination->examination_time)) {
                $examination->examination_time = now()->format('H:i');
            }
        });

        // Auto update timestamps based on status changes
        static::updating(function ($examination) {
            if ($examination->isDirty('status')) {
                switch ($examination->status) {
                    case 'in_progress':
                        if (!$examination->started_at) {
                            $examination->started_at = now();
                        }
                        break;
                    case 'completed':
                        if (!$examination->completed_at) {
                            $examination->completed_at = now();
                        }
                        break;
                }
            }

            // Auto set verified_at when verified_by is set
            if ($examination->isDirty('verified_by') && $examination->verified_by && !$examination->verified_at) {
                $examination->verified_at = now();
            }
        });
    }

    /**
     * ຄວາມສຳພັນກັບຄົນໄຂ້
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * ຄວາມສຳພັນກັບຄິວ
     */
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    /**
     * ຄວາມສຳພັນກັບບໍລິການການກວດ
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(MedicalService::class, 'service_id');
    }

    /**
     * ຄວາມສຳພັນກັບຫ້ອງກວດ
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(ExaminationRoom::class, 'room_id');
    }

    /**
     * ຄວາມສຳພັນກັບຜູ້ເຮັດການກວດ (ພະຍາບານ/ເທັກນິກ)
     */
    public function conductor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    /**
     * ຄວາມສຳພັນກັບຜູ້ຢືນຢັນ (ໝໍ)
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * ຄວາມສຳພັນກັບການຮັກສາ
     */
    public function treatment(): HasOne
    {
        return $this->hasOne(Treatment::class);
    }

    /**
     * ໄດ້ຮັບການຢືນຢັນຈາກໝໍແລ້ວ
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_by) && !is_null($this->verified_at);
    }

    /**
     * ສຳເລັດການກວດແລ້ວ
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * ກຳລັງດຳເນີນການກວດ
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * ລໍຖ້າເຮັດການກວດ
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * ຍົກເລີກການກວດ
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * ໄດ້ຮັບ Vital Signs ແລ້ວ
     */
    public function hasVitalSigns(): bool
    {
        return !empty($this->vital_signs);
    }

    /**
     * ມີຜົນການກວດ
     */
    public function hasResults(): bool
    {
        return !empty($this->examination_results);
    }

    /**
     * ມີໄຟລ์ແນບ
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * ເວລາທີ່ໃຊ້ໃນການກວດ (ນາທີ)
     */
    public function getDurationInMinutesAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }

    /**
     * ສະຖານະການກວດເປັນພາສາລາວ
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'ລໍຖ້າການກວດ',
            'in_progress' => 'ກຳລັງກວດ',
            'completed' => 'ສຳເລັດການກວດ',
            'cancelled' => 'ຍົກເລີກ',
            default => $this->status,
        };
    }

    /**
     * ສີສະຖານະ
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    /**
     * ຄ່າ Vital Signs ສຳຄັນ
     */
    public function getWeightAttribute(): ?float
    {
        return $this->vital_signs['weight'] ?? null;
    }

    public function getBloodPressureAttribute(): ?string
    {
        return $this->vital_signs['blood_pressure'] ?? null;
    }

    public function getTemperatureAttribute(): ?float
    {
        return $this->vital_signs['temperature'] ?? null;
    }

    public function getHeartRateAttribute(): ?int
    {
        return $this->vital_signs['heart_rate'] ?? null;
    }

    /**
     * ຜູ້ບັນທຶກ Vital Signs
     */
    public function getVitalSignsRecordedByAttribute(): ?string
    {
        return $this->vital_signs['recorded_by'] ?? null;
    }

    /**
     * ເວລາບັນທຶກ Vital Signs
     */
    public function getVitalSignsRecordedAtAttribute(): ?Carbon
    {
        $recorded_at = $this->vital_signs['recorded_at'] ?? null;
        return $recorded_at ? Carbon::parse($recorded_at) : null;
    }

    /**
     * ຄ່າປົກກະຕິຂອງ Vital Signs
     */
    public function isVitalSignsNormal(): array
    {
        $normal = [];
        
        // ອຸນຫະພູມ (36-37.5°C)
        if ($this->temperature) {
            $normal['temperature'] = $this->temperature >= 36 && $this->temperature <= 37.5;
        }

        // ໃຈເຕັ້ນ (60-100 ຄັ້ງ/ນາທີ)
        if ($this->heart_rate) {
            $normal['heart_rate'] = $this->heart_rate >= 60 && $this->heart_rate <= 100;
        }

        // ນ້ຳໜັກ (ບໍ່ມີຄ່າມາດຕະຖານຄົງທີ່)
        if ($this->weight) {
            $normal['weight'] = true; // ຕ້ອງເປຣຽບທຽບກັບປະຫວັດ
        }

        // ຄວາມດັນເລືອດ (ຕ້ອງ parse ຄ່າ systolic/diastolic)
        if ($this->blood_pressure) {
            // ຄາດວ່າຄ່າຈະເປັນ "120/80" format
            if (preg_match('/(\d+)\/(\d+)/', $this->blood_pressure, $matches)) {
                $systolic = (int)$matches[1];
                $diastolic = (int)$matches[2];
                $normal['blood_pressure'] = ($systolic >= 90 && $systolic <= 140) && 
                                          ($diastolic >= 60 && $diastolic <= 90);
            }
        }

        return $normal;
    }

    /**
     * Scope ສຳຫລັບຄົ້ນຫາ
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('examination_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('examination_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('examination_date', now()->month)
                    ->whereYear('examination_date', now()->year);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_by')->whereNotNull('verified_at');
    }

    public function scopeUnverified($query)
    {
        return $query->where('status', 'completed')
                    ->where(function ($q) {
                        $q->whereNull('verified_by')
                          ->orWhereNull('verified_at');
                    });
    }

    /**
     * ການແຈ້ງເຕືອນອັດຕະໂນມັດ
     */
    public function shouldNotifyDoctor(): bool
    {
        // ແຈ້ງເຕືອນໝໍເມື່ອການກວດສຳເລັດ ແຕ່ຍັງບໍ່ໄດ້ຢືນຢັນ
        return $this->status === 'completed' && !$this->isVerified();
    }

    public function shouldNotifyPatient(): bool
    {
        // ແຈ້ງເຕືອນຄົນໄຂ້ເມື່ອຜົນການກວດພ້ອມແລ້ວ
        return $this->isVerified() && $this->hasResults();
    }

    /**
     * ສ້າງ Treatment ຈາກການກວດນີ້
     */
    public function createTreatment(array $data = []): Treatment
    {
        $treatment = new Treatment($data);
        $treatment->patient_id = $this->patient_id;
        $treatment->examination_ids = [$this->id];
        $treatment->save();

        return $treatment;
    }
}