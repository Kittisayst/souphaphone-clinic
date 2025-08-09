<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Treatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'examination_ids',
        'chief_complaint',
        'diagnosis',
        'diagnosis_notes',
        'treatment_plan',
        'treatment_notes',
        'prescribed_medicines',
        'follow_up_date',
        'follow_up_notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'examination_ids' => 'array',
            'diagnosis' => 'array',
            'treatment_plan' => 'array',
            'prescribed_medicines' => 'array',
            'follow_up_date' => 'date',
        ];
    }

    /**
     * Boot method ສຳຫລັບ auto-generate treatment number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($treatment) {
            // Auto-generate treatment number
            $treatment->treatment_number = self::generateTreatmentNumber();
        });

        // Auto update examination status when treatment is created
        static::created(function ($treatment) {
            if (!empty($treatment->examination_ids)) {
                MedicalExamination::whereIn('id', $treatment->examination_ids)
                    ->update(['status' => 'completed']);
            }
        });
    }

    /**
     * ສ້າງເລກການຮັກສາອັດຕະໂນມັດ (TR-2024-001)
     */
    public static function generateTreatmentNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');

        $lastTreatment = self::whereYear('created_at', $year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastTreatment) {
            return "TR-{$year}-{$month}-001";
        }

        // ດຶງເອົາເລກຈາກການຮັກສາຄັ້ງລ່າສຸດ
        $parts = explode('-', $lastTreatment->treatment_number ?? '');
        $lastNumber = isset($parts[3]) ? intval($parts[3]) : 0;
        $newNumber = $lastNumber + 1;

        return "TR-{$year}-{$month}-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
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
     * ຄວາມສຳພັນກັບໃບເກັບເງິນ
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * ການກວດທີ່ອ້າງອີງ
     */
    public function medicalExaminations()
    {
        if (empty($this->examination_ids)) {
            return collect();
        }

        return MedicalExamination::whereIn('id', $this->examination_ids)->get();
    }

    /**
     * ການນັດຕິດຕາມ
     */
    public function followUpAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class)
            ->where('type', 'follow_up')
            ->orderBy('appointment_date');
    }

    /**
     * ການນັດຕິດຕາມຄັ້ງຕໍ່ໄປ
     */
    public function nextFollowUp()
    {
        return $this->followUpAppointments()
            ->where('appointment_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->first();
    }

    /**
     * ກວດສອບສະຖານະການຮັກສາ
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
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
     * ຕ້ອງການຕິດຕາມບໍ
     */
    public function needsFollowUp(): bool
    {
        return $this->follow_up_date && $this->follow_up_date >= today();
    }

    /**
     * ເກີນກຳນົດນັດຕິດຕາມແລ້ວບໍ
     */
    public function isFollowUpOverdue(): bool
    {
        return $this->follow_up_date && $this->follow_up_date < today() && $this->isActive();
    }

    /**
     * ສະຖານະການຮັກສາເປັນພາສາລາວ
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'ກຳລັງຮັກສາ',
            'completed' => 'ສຳເລັດການຮັກສາ',
            'cancelled' => 'ຍົກເລີກການຮັກສາ',
            default => $this->status,
        };
    }

    /**
     * ສີສະຖານະ
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    /**
     * ການວິນິໄສຫຼັກ
     */
    public function getPrimaryDiagnosisAttribute(): ?string
    {
        if (empty($this->diagnosis)) {
            return null;
        }

        $diagnoses = $this->diagnosis;
        return isset($diagnoses[0]['name']) ? $diagnoses[0]['name'] : 
               (is_string($diagnoses[0]) ? $diagnoses[0] : null);
    }

    /**
     * ຈຳນວນຢາທີ່ສັ່ງ
     */
    public function getPrescribedMedicineCountAttribute(): int
    {
        return count($this->prescribed_medicines ?? []);
    }

    /**
     * ເພີ່ມການວິນິໄສ
     */
    public function addDiagnosis(string $name, ?string $icd10Code = null, string $type = 'primary'): void
    {
        $diagnoses = $this->diagnosis ?? [];
        
        $diagnoses[] = [
            'name' => $name,
            'icd10_code' => $icd10Code,
            'type' => $type,
            'added_at' => now()->toISOString(),
        ];

        $this->diagnosis = $diagnoses;
        $this->save();
    }

    /**
     * ເພີ່ມຢາ
     */
    public function addMedicine(int $medicineId, string $medicineName, string $dosage, string $frequency, int $duration, string $instructions = null): void
    {
        $medicines = $this->prescribed_medicines ?? [];
        
        $medicines[] = [
            'medicine_id' => $medicineId,
            'medicine_name' => $medicineName,
            'dosage' => $dosage,
            'frequency' => $frequency,
            'duration_days' => $duration,
            'instructions' => $instructions,
            'prescribed_at' => now()->toISOString(),
        ];

        $this->prescribed_medicines = $medicines;
        $this->save();
    }

    /**
     * ລົບຢາ
     */
    public function removeMedicine(int $index): void
    {
        $medicines = $this->prescribed_medicines ?? [];
        
        if (isset($medicines[$index])) {
            unset($medicines[$index]);
            $this->prescribed_medicines = array_values($medicines);
            $this->save();
        }
    }

    /**
     * ກຳນົດການນັດຕິດຕາມ
     */
    public function scheduleFollowUp(Carbon $date, string $notes = null): void
    {
        $this->update([
            'follow_up_date' => $date,
            'follow_up_notes' => $notes,
        ]);
    }

    /**
     * ສຳເລັດການຮັກສາ
     */
    public function complete(string $notes = null): bool
    {
        if ($this->isActive()) {
            $this->update([
                'status' => 'completed',
                'treatment_notes' => $notes ? $this->treatment_notes . "\n\n" . $notes : $this->treatment_notes,
            ]);
            return true;
        }
        return false;
    }

    /**
     * ຍົກເລີກການຮັກສາ
     */
    public function cancel(string $reason = null): bool
    {
        if ($this->isActive()) {
            $this->update([
                'status' => 'cancelled',
                'treatment_notes' => $reason ? $this->treatment_notes . "\n\nຍົກເລີກ: " . $reason : $this->treatment_notes,
            ]);
            return true;
        }
        return false;
    }

    /**
     * ສ້າງໃບເກັບເງິນຈາກການຮັກສານີ້
     */
    public function createInvoice(): Invoice
    {
        // ສ້າງໃບເກັບເງິນພ້ອມກັບລາຍການຢາ
        $invoice = Invoice::create([
            'patient_id' => $this->patient_id,
            'treatment_id' => $this->id,
            'examination_ids' => $this->examination_ids,
            'items' => $this->buildInvoiceItems(),
            'cashier_id' => auth()->id(),
        ]);

        $invoice->calculateTotals();
        return $invoice;
    }

    /**
     * ສ້າງລາຍການສິນຄ້າສຳລັບໃບເກັບເງິນ
     */
    private function buildInvoiceItems(): array
    {
        $items = [];

        // ເພີ່ມຄ່າການກວດ
        $examinations = $this->medicalExaminations();
        foreach ($examinations as $exam) {
            $items[] = [
                'type' => 'service',
                'id' => $exam->service->id,
                'name' => $exam->service->service_name,
                'quantity' => 1,
                'unit_price' => $exam->service->price,
                'total' => $exam->service->price,
            ];
        }

        // ເພີ່ມຄ່າຢາ
        if (!empty($this->prescribed_medicines)) {
            foreach ($this->prescribed_medicines as $medicine) {
                $medicineModel = Medicine::find($medicine['medicine_id']);
                if ($medicineModel) {
                    $quantity = $medicine['duration_days'] ?? 1;
                    $items[] = [
                        'type' => 'medicine',
                        'id' => $medicineModel->id,
                        'name' => $medicineModel->medicine_name,
                        'quantity' => $quantity,
                        'unit_price' => $medicineModel->unit_price,
                        'total' => $quantity * $medicineModel->unit_price,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * ຄ່າໃຊ້ຈ່າຍທັງໝົດຂອງການຮັກສາ
     */
    public function getTotalCostAttribute(): float
    {
        $total = 0;

        // ຄ່າການກວດ
        $examinations = $this->medicalExaminations();
        foreach ($examinations as $exam) {
            $total += $exam->service->price ?? 0;
        }

        // ຄ່າຢາ
        if (!empty($this->prescribed_medicines)) {
            foreach ($this->prescribed_medicines as $medicine) {
                $medicineModel = Medicine::find($medicine['medicine_id']);
                if ($medicineModel) {
                    $quantity = $medicine['duration_days'] ?? 1;
                    $total += $quantity * $medicineModel->unit_price;
                }
            }
        }

        return $total;
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeNeedsFollowUp($query)
    {
        return $query->where('follow_up_date', '>=', today())
                    ->where('status', 'active');
    }

    public function scopeOverdueFollowUp($query)
    {
        return $query->where('follow_up_date', '<', today())
                    ->where('status', 'active');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * ການສະແດງຂໍ້ມູນສຳລັບ UI
     */
    public function getDiagnosisListAttribute(): string
    {
        if (empty($this->diagnosis)) {
            return 'ຍັງບໍ່ໄດ້ວິນິໄສ';
        }

        $names = [];
        foreach ($this->diagnosis as $diagnosis) {
            if (is_array($diagnosis) && isset($diagnosis['name'])) {
                $names[] = $diagnosis['name'];
            } elseif (is_string($diagnosis)) {
                $names[] = $diagnosis;
            }
        }

        return implode(', ', $names);
    }

    public function getMedicineListAttribute(): string
    {
        if (empty($this->prescribed_medicines)) {
            return 'ບໍ່ໄດ້ສັ່ງຢາ';
        }

        $medicines = [];
        foreach ($this->prescribed_medicines as $medicine) {
            $name = $medicine['medicine_name'] ?? 'ບໍ່ລະບຸຊື່';
            $dosage = $medicine['dosage'] ?? '';
            $medicines[] = "{$name} {$dosage}";
        }

        return implode(', ', $medicines);
    }

    /**
     * ສາມາດແກ້ໄຂໄດ້ບໍ
     */
    public function canBeEdited(): bool
    {
        return $this->isActive();
    }

    /**
     * ສາມາດສ້າງໃບເກັບເງິນໄດ້ບໍ
     */
    public function canCreateInvoice(): bool
    {
        return $this->isActive() && !$this->invoice;
    }

    /**
     * ສາມາດກຳນົດການນັດໄດ້ບໍ
     */
    public function canScheduleFollowUp(): bool
    {
        return $this->isActive();
    }
}