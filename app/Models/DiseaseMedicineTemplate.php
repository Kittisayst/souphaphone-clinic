<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiseaseMedicineTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'disease_name',
        'icd10_code',
        'template_medicines',
        'instructions',
        'created_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'template_medicines' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot method ສຳຫລັບ auto-fill created_by
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (!$template->created_by) {
                $template->created_by = auth()->id();
            }
        });
    }

    /**
     * ຄວາມສຳພັນກັບຜູ້ສ້າງ
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ຄວາມສຳພັນກັບການຮັກສາທີ່ໃຊ້ template ນີ້
     */
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    /**
     * ກວດສອບວ່າ template ເປີດໃຊ້ງານຫຍັງ
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * ຊື່ໂລກພ້ອມລະຫັດ ICD10
     */
    public function getFullDiseaseNameAttribute(): string
    {
        return $this->icd10_code 
            ? "{$this->disease_name} ({$this->icd10_code})"
            : $this->disease_name;
    }

    /**
     * ຈຳນວນຢາໃນ template
     */
    public function getMedicineCountAttribute(): int
    {
        return count($this->template_medicines ?? []);
    }

    /**
     * ເພີ່ມຢາເຂົ້າໃນ template
     */
    public function addMedicine(
        int $medicineId,
        string $medicineName,
        int $quantity,
        string $dosage,
        int $duration,
        string $instructions = '',
        string $frequency = 'ມື້ລະ 3 ເທື່ອ'
    ): void {
        $medicines = $this->template_medicines ?? [];

        // ກວດສອບວ່າມີຢານີ້ແລ້ວບໍ່
        $existingIndex = collect($medicines)->search(function ($medicine) use ($medicineId) {
            return $medicine['medicine_id'] === $medicineId;
        });

        $medicineData = [
            'medicine_id' => $medicineId,
            'medicine_name' => $medicineName,
            'quantity' => $quantity,
            'dosage' => $dosage,
            'frequency' => $frequency,
            'duration' => $duration,
            'instructions' => $instructions,
            'created_at' => now()->toDateTimeString(),
        ];

        if ($existingIndex !== false) {
            // ອັບເດດຢາທີ່ມີແລ້ວ
            $medicines[$existingIndex] = $medicineData;
        } else {
            // ເພີ່ມຢາໃໝ່
            $medicines[] = $medicineData;
        }

        $this->update(['template_medicines' => $medicines]);
    }

    /**
     * ລົບຢາອອກຈາກ template
     */
    public function removeMedicine(int $medicineId): void
    {
        $medicines = $this->template_medicines ?? [];

        $medicines = collect($medicines)->filter(function ($medicine) use ($medicineId) {
            return $medicine['medicine_id'] !== $medicineId;
        })->values()->toArray();

        $this->update(['template_medicines' => $medicines]);
    }

    /**
     * ອັບເດດຢາໃນ template
     */
    public function updateMedicine(
        int $medicineId,
        array $updates
    ): void {
        $medicines = $this->template_medicines ?? [];

        $medicines = collect($medicines)->map(function ($medicine) use ($medicineId, $updates) {
            if ($medicine['medicine_id'] === $medicineId) {
                return array_merge($medicine, $updates, [
                    'updated_at' => now()->toDateTimeString()
                ]);
            }
            return $medicine;
        })->toArray();

        $this->update(['template_medicines' => $medicines]);
    }

    /**
     * ໃຊ້ template ສຳຫລັບການຮັກສາ
     */
    public function applyToTreatment(Treatment $treatment): array
    {
        $prescribedMedicines = [];
        
        foreach ($this->template_medicines ?? [] as $templateMedicine) {
            $prescribedMedicines[] = [
                'medicine_id' => $templateMedicine['medicine_id'],
                'medicine_name' => $templateMedicine['medicine_name'],
                'quantity' => $templateMedicine['quantity'],
                'dosage' => $templateMedicine['dosage'],
                'frequency' => $templateMedicine['frequency'],
                'duration' => $templateMedicine['duration'],
                'instructions' => $templateMedicine['instructions'],
                'prescribed_at' => now()->toDateTimeString(),
                'prescribed_by' => auth()->id(),
            ];
        }

        $treatment->update([
            'prescribed_medicines' => $prescribedMedicines,
            'template_used_id' => $this->id,
        ]);

        return $prescribedMedicines;
    }

    /**
     * ສ້າງ template ຈາກການຮັກສາທີ່ມີຢູ່
     */
    public static function createFromTreatment(
        Treatment $treatment,
        string $diseaseName,
        ?string $icd10Code = null,
        ?string $instructions = null
    ): self {
        return self::create([
            'disease_name' => $diseaseName,
            'icd10_code' => $icd10Code,
            'template_medicines' => $treatment->prescribed_medicines ?? [],
            'instructions' => $instructions,
            'is_active' => true,
        ]);
    }

    /**
     * ສ້າງ template ເປົ່າ
     */
    public static function createEmpty(
        string $diseaseName,
        ?string $icd10Code = null,
        ?string $instructions = null
    ): self {
        return self::create([
            'disease_name' => $diseaseName,
            'icd10_code' => $icd10Code,
            'template_medicines' => [],
            'instructions' => $instructions,
            'is_active' => true,
        ]);
    }

    /**
     * ກ໋ອບປີ້ template
     */
    public function duplicate(string $newName = null): self
    {
        $newName = $newName ?? "Copy of {$this->disease_name}";

        return self::create([
            'disease_name' => $newName,
            'icd10_code' => $this->icd10_code,
            'template_medicines' => $this->template_medicines,
            'instructions' => $this->instructions,
            'is_active' => false, // ຕ້ອງເປີດໃຊ້ງານເອງ
        ]);
    }

    /**
     * ລາຄາປະມານທັງໝົດຂອງຢາໃນ template
     */
    public function getEstimatedCostAttribute(): float
    {
        $totalCost = 0;

        foreach ($this->template_medicines ?? [] as $templateMedicine) {
            $medicine = Medicine::find($templateMedicine['medicine_id']);
            if ($medicine) {
                $totalCost += $medicine->unit_price * $templateMedicine['quantity'];
            }
        }

        return $totalCost;
    }

    /**
     * ກວດສອບວ່າຢາໃນ template ພ້ອມໃຊ້ງານບໍ່ (ມີສາງພໍ)
     */
    public function checkMedicineAvailability(): array
    {
        $availability = [];

        foreach ($this->template_medicines ?? [] as $templateMedicine) {
            $medicine = Medicine::find($templateMedicine['medicine_id']);
            $isAvailable = $medicine && 
                          $medicine->is_active && 
                          $medicine->stock_quantity >= $templateMedicine['quantity'];

            $availability[] = [
                'medicine_id' => $templateMedicine['medicine_id'],
                'medicine_name' => $templateMedicine['medicine_name'],
                'required_quantity' => $templateMedicine['quantity'],
                'available_quantity' => $medicine?->stock_quantity ?? 0,
                'is_available' => $isAvailable,
                'is_active' => $medicine?->is_active ?? false,
            ];
        }

        return $availability;
    }

    /**
     * ສາມາດໃຊ້ template ໄດ້ບໍ່ (ຢາພ້ອມໝົດ)
     */
    public function canBeUsed(): bool
    {
        $availability = $this->checkMedicineAvailability();
        
        return collect($availability)->every('is_available');
    }

    /**
     * ຢາທີ່ບໍ່ພ້ອມໃຊ້ງານ
     */
    public function getUnavailableMedicines(): array
    {
        $availability = $this->checkMedicineAvailability();
        
        return collect($availability)->filter(function ($item) {
            return !$item['is_available'];
        })->values()->toArray();
    }

    /**
     * ລາຍການຢາແບບສະຫຼຸບ
     */
    public function getMedicineSummary(): array
    {
        return collect($this->template_medicines ?? [])->map(function ($medicine) {
            return [
                'name' => $medicine['medicine_name'],
                'display' => "{$medicine['medicine_name']} - {$medicine['quantity']} {$medicine['dosage']} ({$medicine['frequency']})",
                'duration' => $medicine['duration'] . ' ວັນ',
            ];
        })->toArray();
    }

    /**
     * Scope ສຳຫລັບຄົ້ນຫາ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDisease($query, $diseaseName)
    {
        return $query->where('disease_name', 'like', "%{$diseaseName}%");
    }

    public function scopeByIcd10($query, $icd10Code)
    {
        return $query->where('icd10_code', $icd10Code);
    }

    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopePopular($query)
    {
        return $query->withCount('treatments')
                    ->orderBy('treatments_count', 'desc');
    }

    public function scopeRecentlyUsed($query)
    {
        return $query->whereHas('treatments', function ($q) {
            $q->where('created_at', '>=', now()->subDays(30));
        });
    }

    /**
     * ລາຍງານການໃຊ້ template
     */
    public function getUsageStats(?int $days = 30): array
    {
        $treatments = $this->treatments()
            ->when($days, function ($query) use ($days) {
                return $query->where('created_at', '>=', now()->subDays($days));
            })
            ->get();

        return [
            'total_uses' => $treatments->count(),
            'unique_patients' => $treatments->pluck('patient_id')->unique()->count(),
            'unique_doctors' => $treatments->pluck('doctor_id')->unique()->count(),
            'last_used' => $treatments->max('created_at'),
            'average_uses_per_week' => $days ? ($treatments->count() / ($days / 7)) : 0,
        ];
    }

    /**
     * ແມ່ແບບທີ່ນິຍົມໃຊ້
     */
    public static function getPopularTemplates(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::active()
            ->withCount('treatments')
            ->orderBy('treatments_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * ແມ່ແບບທີ່ໃຊ້ເມື່ອໄວໆນີ້
     */
    public static function getRecentlyUsedTemplates(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::active()
            ->whereHas('treatments', function ($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            })
            ->withCount(['treatments' => function ($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            }])
            ->orderBy('treatments_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * ຄົ້ນຫາ template ຕາມຊື່ໂລກ
     */
    public static function searchByDisease(string $search): \Illuminate\Database\Eloquent\Collection
    {
        return self::active()
            ->where(function ($query) use ($search) {
                $query->where('disease_name', 'like', "%{$search}%")
                      ->orWhere('icd10_code', 'like', "%{$search}%");
            })
            ->orderBy('disease_name')
            ->get();
    }

    /**
     * ສ້າງ template ພື້ນຖານສຳຫລັບໂລກທົ່ວໄປ
     */
    public static function createCommonTemplates(): void
    {
        $commonDiseases = [
            [
                'disease_name' => 'ໄຂ້ຫວັດ',
                'icd10_code' => 'J00',
                'instructions' => 'ພັກຜ່ອນ, ດື່ມນ້ຳຫຼາຍໆ, ຫຼີກເວັ້ນອາຫານເຜັດ',
                'medicines' => [
                    ['medicine_name' => 'Paracetamol 500mg', 'quantity' => 10, 'dosage' => '500mg', 'frequency' => 'ມື້ລະ 3 ເທື່ອ', 'duration' => 5],
                    ['medicine_name' => 'Vitamin C', 'quantity' => 30, 'dosage' => '1000mg', 'frequency' => 'ມື້ລະ 1 ເທື່ອ', 'duration' => 7],
                ]
            ],
            [
                'disease_name' => 'ປວດຫົວ',
                'icd10_code' => 'R51',
                'instructions' => 'ພັກຜ່ອນໃຫ້ພໍ, ຫຼີກເວັ້ນຄວາມເຄັ່ງຕຶງ',
                'medicines' => [
                    ['medicine_name' => 'Paracetamol 500mg', 'quantity' => 10, 'dosage' => '500mg', 'frequency' => 'ເມື່ອປວດ (ຫ່າງ 4-6 ຊົ່ວໂມງ)', 'duration' => 3],
                ]
            ],
            [
                'disease_name' => 'ອາການເຈັບຄໍ',
                'icd10_code' => 'J02.9',
                'instructions' => 'ກິນນ້ຳອຸ່ນໆ, ຄັ່ວນ້ຳເກືອ',
                'medicines' => [
                    ['medicine_name' => 'Amoxicillin 500mg', 'quantity' => 15, 'dosage' => '500mg', 'frequency' => 'ມື້ລະ 3 ເທື່ອ', 'duration' => 5],
                    ['medicine_name' => 'Paracetamol 500mg', 'quantity' => 15, 'dosage' => '500mg', 'frequency' => 'ມື້ລະ 3 ເທື່ອ', 'duration' => 5],
                ]
            ],
        ];

        foreach ($commonDiseases as $disease) {
            $template = self::createEmpty(
                $disease['disease_name'],
                $disease['icd10_code'],
                $disease['instructions']
            );

            foreach ($disease['medicines'] as $medicine) {
                $template->addMedicine(
                    medicineId: 0, // ຕ້ອງປ່ຽນເປັນ ID ຈິງ
                    medicineName: $medicine['medicine_name'],
                    quantity: $medicine['quantity'],
                    dosage: $medicine['dosage'],
                    duration: $medicine['duration'],
                    frequency: $medicine['frequency']
                );
            }
        }
    }
}