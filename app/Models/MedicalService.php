<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class MedicalService extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_name',
        'service_code',
        'service_category',
        'description',
        'price',
        'template_fields',
        'document_template',
        'estimated_duration',
        'requires_preparation',
        'preparation_instructions',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'template_fields' => 'array',
            'document_template' => 'array',
            'price' => 'decimal:2',
            'requires_preparation' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot method ສຳຫລັບສ້າງລະຫັດບໍລິການອັດຕະໂນມັດ
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->service_code)) {
                $service->service_code = self::generateServiceCode($service->service_category);
            }
        });
    }

    /**
     * ສ້າງລະຫັດບໍລິການອັດຕະໂນມັດ (BLOOD01, ULTRA01, EXAM01)
     */
    public static function generateServiceCode(string $category): string
    {
        $prefix = match ($category) {
            'examination' => 'EXAM',
            'laboratory' => 'LAB',
            'imaging' => 'IMG',
            'procedure' => 'PROC',
            default => 'SRV',
        };

        $lastService = self::where('service_category', $category)
            ->where('service_code', 'like', $prefix . '%')
            ->orderBy('service_code', 'desc')
            ->first();

        if (!$lastService) {
            return $prefix . '01';
        }

        // ດຶງເອົາເລກຈາກລະຫັດຄັ້ງລ່າສຸດ
        $lastNumber = intval(substr($lastService->service_code, strlen($prefix)));
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * ຄວາມສຳພັນກັບຜູ້ສ້າງ
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ຄວາມສຳພັນກັບການກວດທັງໝົດ
     */
    public function medicalExaminations(): HasMany
    {
        return $this->hasMany(MedicalExamination::class, 'service_id');
    }

    /**
     * ການກວດຈຳນວນຫຼາຍໃນປະຈຸບັນ
     */
    public function activeExaminations()
    {
        return $this->medicalExaminations()
            ->whereIn('status', ['pending', 'in_progress'])
            ->with(['patient', 'room']);
    }

    /**
     * ກວດສອບປະເພດບໍລິການ
     */
    public function isExaminationType(): bool
    {
        return $this->service_category === 'examination';
    }

    public function isLaboratoryType(): bool
    {
        return $this->service_category === 'laboratory';
    }

    public function isImagingType(): bool
    {
        return $this->service_category === 'imaging';
    }

    public function isProcedureType(): bool
    {
        return $this->service_category === 'procedure';
    }

    /**
     * ກວດສອບວ່າບໍລິການເປີດໃຊ້ງານ
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * ກວດສອບວ່າຕ້ອງການການກະກຽມ
     */
    public function requiresPreparation(): bool
    {
        return $this->requires_preparation;
    }

    /**
     * ປະເພດບໍລິການເປັນພາສາລາວ
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->service_category) {
            'examination' => 'ການກວດທົ່ວໄປ',
            'laboratory' => 'ກວດເລືອດ/ຫ້ອງທົດລອງ',
            'imaging' => 'ຖ່າຍຮູບ/ວິທະຍຸ',
            'procedure' => 'ການຮັກສາພິເສດ',
            default => $this->service_category,
        };
    }

    /**
     * ສີປະເພດບໍລິການ
     */
    public function getCategoryColorAttribute(): string
    {
        return match ($this->service_category) {
            'examination' => 'primary',
            'laboratory' => 'success',
            'imaging' => 'warning',
            'procedure' => 'danger',
            default => 'gray',
        };
    }

    /**
     * ຮູບແບບລາຄາ
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0) . ' ກີບ';
    }

    /**
     * ເວລາປະມານການກວດ
     */
    public function getEstimatedDurationTextAttribute(): string
    {
        if (!$this->estimated_duration) {
            return 'ບໍ່ລະບຸ';
        }

        if ($this->estimated_duration < 60) {
            return $this->estimated_duration . ' ນາທີ';
        }

        $hours = floor($this->estimated_duration / 60);
        $minutes = $this->estimated_duration % 60;

        $text = $hours . ' ຊົ່ວໂມງ';
        if ($minutes > 0) {
            $text .= ' ' . $minutes . ' ນາທີ';
        }

        return $text;
    }

    /**
     * ສ້າງແບບຟອມການກວດ dynamic
     */
    public function createExaminationForm(): array
    {
        $defaultFields = [
            // Vital Signs ພື້ນຖານ
            [
                'type' => 'section',
                'label' => 'ຂໍ້ມູນພື້ນຖານ (Vital Signs)',
                'fields' => [
                    ['type' => 'number', 'name' => 'weight', 'label' => 'ນ້ຳໜັກ (kg)', 'min' => 0, 'max' => 300, 'step' => 0.1],
                    ['type' => 'text', 'name' => 'blood_pressure', 'label' => 'ຄວາມດັນເລືອດ', 'placeholder' => '120/80'],
                    ['type' => 'number', 'name' => 'temperature', 'label' => 'ອຸນຫະພູມ (°C)', 'min' => 30, 'max' => 45, 'step' => 0.1],
                    ['type' => 'number', 'name' => 'heart_rate', 'label' => 'ຫົວໃຈເຕັ້ນ (ຄັ້ງ/ນາທີ)', 'min' => 30, 'max' => 200],
                ],
            ],
        ];

        // ເພີ່ມ template fields ສະເພາະ
        if (!empty($this->template_fields)) {
            $customFields = [
                'type' => 'section',
                'label' => 'ການກວດສະເພາະ - ' . $this->service_name,
                'fields' => $this->template_fields,
            ];
            $defaultFields[] = $customFields;
        }

        return $defaultFields;
    }

    /**
     * ວິເຄາະຜົນການກວດ
     */
    public function analyzeExaminationResults(array $results): array
    {
        $analysis = [];

        // ວິເຄາະ Vital Signs
        if (isset($results['vital_signs'])) {
            $vital = $results['vital_signs'];

            // ອຸນຫະພູມ
            if (isset($vital['temperature'])) {
                $temp = (float) $vital['temperature'];
                if ($temp < 36.0) {
                    $analysis[] = ['type' => 'warning', 'message' => 'ອຸນຫະພູມຕ່ຳກວ່າປົກກະຕິ'];
                } elseif ($temp > 37.5) {
                    $analysis[] = ['type' => 'danger', 'message' => 'ມີໄຂ້ - ອຸນຫະພູມສູງກວ່າປົກກະຕິ'];
                }
            }

            // ຫົວໃຈເຕັ້ນ
            if (isset($vital['heart_rate'])) {
                $hr = (int) $vital['heart_rate'];
                if ($hr < 60) {
                    $analysis[] = ['type' => 'warning', 'message' => 'ຫົວໃຈເຕັ້ນຊ້າກວ່າປົກກະຕິ'];
                } elseif ($hr > 100) {
                    $analysis[] = ['type' => 'warning', 'message' => 'ຫົວໃຈເຕັ້ນໄວກວ່າປົກກະຕິ'];
                }
            }

            // ຄວາມດັນເລືອດ
            if (isset($vital['blood_pressure'])) {
                $bp = $vital['blood_pressure'];
                if (preg_match('/(\d+)\/(\d+)/', $bp, $matches)) {
                    $systolic = (int) $matches[1];
                    $diastolic = (int) $matches[2];

                    if ($systolic >= 140 || $diastolic >= 90) {
                        $analysis[] = ['type' => 'danger', 'message' => 'ຄວາມດັນເລືອດສູງ'];
                    } elseif ($systolic < 90 || $diastolic < 60) {
                        $analysis[] = ['type' => 'warning', 'message' => 'ຄວາມດັນເລືອດຕ່ຳ'];
                    }
                }
            }
        }

        // ຖ້າບໍ່ມີບັນຫາ
        if (empty($analysis)) {
            $analysis[] = ['type' => 'success', 'message' => 'ຜົນການກວດຢູ່ໃນເກນປົກກະຕິ'];
        }

        return $analysis;
    }

    /**
     * ຄິດລາຄາບໍລິການລວມ
     */
    public function calculateTotalPrice(int $quantity = 1, float $discount = 0): float
    {
        $total = $this->price * $quantity;

        if ($discount > 0) {
            $total = $total * (1 - ($discount / 100));
        }

        return round($total, 2);
    }

    /**
     * ສະຖິຕິການໃຊ້ບໍລິການ
     */
    public function getUsageStats(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = $this->medicalExaminations();

        if ($startDate) {
            $query->whereDate('examination_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('examination_date', '<=', $endDate);
        }

        $examinations = $query->get();

        return [
            'total_examinations' => $examinations->count(),
            'completed_examinations' => $examinations->where('status', 'completed')->count(),
            'total_revenue' => $examinations->where('status', 'completed')->count() * $this->price,
            'average_duration' => $examinations->where('status', 'completed')->avg('duration_in_minutes'),
            'completion_rate' => $examinations->count() > 0 
                ? round(($examinations->where('status', 'completed')->count() / $examinations->count()) * 100, 1)
                : 0,
        ];
    }

    /**
     * Scope ສຳຫລັບຄົ້ນຫາ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('service_category', $category);
    }

    public function scopeExamination($query)
    {
        return $query->where('service_category', 'examination');
    }

    public function scopeLaboratory($query)
    {
        return $query->where('service_category', 'laboratory');
    }

    public function scopeImaging($query)
    {
        return $query->where('service_category', 'imaging');
    }

    public function scopeProcedure($query)
    {
        return $query->where('service_category', 'procedure');
    }

    public function scopeAffordable($query, $maxPrice)
    {
        return $query->where('price', '<=', $maxPrice);
    }

    public function scopeQuickService($query, $maxDuration = 30)
    {
        return $query->where('estimated_duration', '<=', $maxDuration);
    }

    /**
     * ຄົ້ນຫາຄໍາສັບ
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('service_name', 'like', "%{$search}%")
                ->orWhere('service_code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * ສ້າງ template fields ເບື້ອງຕົ້ນຕາມປະເພດ
     */
    public static function getDefaultTemplateFields(string $category): array
    {
        return match ($category) {
            'examination' => [
                ['type' => 'textarea', 'name' => 'chief_complaint', 'label' => 'ອາການຫຼັກ', 'required' => true],
                ['type' => 'textarea', 'name' => 'physical_examination', 'label' => 'ການກວດຮ່າງກາຍ'],
                ['type' => 'textarea', 'name' => 'assessment', 'label' => 'ການປະເມີນ'],
                ['type' => 'textarea', 'name' => 'plan', 'label' => 'ແຜນການຮັກສາ'],
            ],
            'laboratory' => [
                ['type' => 'select', 'name' => 'sample_type', 'label' => 'ປະເພດຕົວຢ່າງ', 'options' => ['blood' => 'ເລືອດ', 'urine' => 'ປັດສະວະ', 'stool' => 'ອາຈົມ']],
                ['type' => 'text', 'name' => 'sample_collection_time', 'label' => 'ເວລາເກັບຕົວຢ່າງ'],
                ['type' => 'textarea', 'name' => 'special_instructions', 'label' => 'ຄຳແນະນຳພິເສດ'],
            ],
            'imaging' => [
                ['type' => 'select', 'name' => 'body_part', 'label' => 'ບາງສ່ວນຂອງຮ່າງກາຍ', 'required' => true],
                ['type' => 'text', 'name' => 'contrast_used', 'label' => 'ສານຕັດກັນ'],
                ['type' => 'textarea', 'name' => 'indication', 'label' => 'ເຫດຜົນການກວດ'],
                ['type' => 'textarea', 'name' => 'findings', 'label' => 'ຜົນການກວດພົບ'],
            ],
            'procedure' => [
                ['type' => 'text', 'name' => 'procedure_type', 'label' => 'ປະເພດການຮັກສາ', 'required' => true],
                ['type' => 'text', 'name' => 'anesthesia', 'label' => 'ການຊາ'],
                ['type' => 'textarea', 'name' => 'procedure_notes', 'label' => 'ໝາຍເຫດການຮັກສາ'],
                ['type' => 'textarea', 'name' => 'post_procedure_care', 'label' => 'ການດູແລຫຼັງການຮັກສາ'],
            ],
            default => [
                ['type' => 'textarea', 'name' => 'general_notes', 'label' => 'ໝາຍເຫດທົ່ວໄປ'],
            ],
        };
    }
}