<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabTest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'treatment_id',
        'queue_service_id',
        'lab_test_code',
        'lab_test_name',
        'test_result_values',
        'reference_range',
        'abnormal_flag',
        'interpretation',
        'sample_type',
        'sample_collected_at',
        'sample_collected_by',
        'tested_at',
        'tested_by',
        'reviewed_by',
        'reviewed_at',
        'technician_notes',
        'doctor_notes',
        'status',
    ];

    protected $casts = [
        'test_result_values' => 'array',
        'sample_collected_at' => 'datetime',
        'tested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ສະຖານະທີ່ອະນຸຍາດ
    public const STATUS_ORDERED = 'Ordered';
    public const STATUS_SAMPLE_COLLECTED = 'Sample_Collected';
    public const STATUS_TESTING = 'Testing';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_REVIEWED = 'Reviewed';
    public const STATUS_CANCELLED = 'Cancelled';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ORDERED => 'ສັ່ງແລ້ວ',
            self::STATUS_SAMPLE_COLLECTED => 'ເກັບຕົວຢ່າງແລ້ວ',
            self::STATUS_TESTING => 'ກຳລັງກວດ',
            self::STATUS_COMPLETED => 'ສຳເລັດການກວດ',
            self::STATUS_REVIEWED => 'ກວດສອບແລ້ວ',
            self::STATUS_CANCELLED => 'ຍົກເລີກ',
        ];
    }

    public function getStatusLaoAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    // ຟັງສັນຫາ abnormal_flag
    public static function getAbnormalFlags(): array
    {
        return [
            'Normal' => 'ປົກກະຕິ',
            'High' => 'ສູງກວ່າປົກກະຕິ',
            'Low' => 'ຕ່ຳກວ່າປົກກະຕິ',
            'Critical' => 'ອັນຕະລາຍ',
        ];
    }

    public function getAbnormalFlagLaoAttribute(): ?string
    {
        return $this->abnormal_flag ? self::getAbnormalFlags()[$this->abnormal_flag] : null;
    }

    // ======================== RELATIONSHIPS ========================

    /**
     * ການປິ່ນປົວທີ່ເກີ່ຍວຂ້ອງ
     */
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    /**
     * ຄິວບໍລິການ Lab (ຖ້າມີ)
     */
    public function queueService(): BelongsTo
    {
        return $this->belongsTo(QueueService::class);
    }

    /**
     * ຜູ້ເກັບຕົວຢ່າງ
     */
    public function sampleCollectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sample_collected_by');
    }

    /**
     * ຜູ້ເຮັດການກວດ
     */
    public function testedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by');
    }

    /**
     * ຜູ້ກວດສອບຜົນ
     */
    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ======================== SCOPES ========================

    /**
     * Lab ທີ່ຍັງບໍ່ສຳເລັດ
     */
    public function scopePending($query)
    {
        return $query->whereNotIn('status', [self::STATUS_REVIEWED, self::STATUS_CANCELLED]);
    }

    /**
     * Lab ທີ່ມີຜົນຜິດປົກກະຕິ
     */
    public function scopeAbnormal($query)
    {
        return $query->whereIn('abnormal_flag', ['High', 'Low', 'Critical']);
    }

    // ======================== HELPER METHODS ========================

    /**
     * ກວດສອບວ່າສາມາດອັບເດດຜົນໄດ້ບໍ່
     */
    public function canUpdateResults(): bool
    {
        return in_array($this->status, [
            self::STATUS_SAMPLE_COLLECTED,
            self::STATUS_TESTING,
            self::STATUS_COMPLETED
        ]);
    }

    /**
     * ກວດສອບວ່າສາມາດກວດສອບໄດ້ບໍ່
     */
    public function canReview(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * ອັບເດດສະຖານະການປິ່ນປົວເມື່ອກວດສອບແລ້ວ
     */
    protected static function booted()
    {
        static::updated(function (LabTest $labTest) {
            if ($labTest->status === self::STATUS_REVIEWED) {
                $labTest->treatment->updateStatusBasedOnLabResults();
            }
        });
    }
}
