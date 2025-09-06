<?php
// app/Models/MedicationInstruction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
/**
 * Summary of MedicationInstruction
 * @property int $id
 * @property int $treatment_id
 * @property int $medicine_id
 * @property string $dosage
 * @property string $frequency
 * @property string $duration
 * @property int $total_quantity
 * @property string $administration_route
 * @property string $special_instructions
 * @property string $warnings
 * @property float $unit_price
 * @property float $total_price
 * @property string $dispensing_status
 * @property int $prescribed_by
 * @property int $dispensed_by
 * @property Carbon|null $dispensed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read Treatment $treatment
 * @property-read Medicine $medicine
 * @property-read User $prescribed_by
 * @property-read User $dispensed_by
 */
class MedicationInstruction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'treatment_id',
        'medicine_id',
        'dosage',
        'frequency',
        'duration',
        'total_quantity',
        'administration_route',
        'special_instructions',
        'warnings',
        'unit_price',
        'total_price',
        'dispensing_status',
        'prescribed_by',
        'dispensed_by',
        'dispensed_at',
    ];

    protected $casts = [
        'total_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'dispensed_at' => 'datetime',
    ];

    // ======================== CONSTANTS ========================

    public const DISPENSING_STATUSES = [
        'Prescribed' => 'ສັ່ງແລ້ວ',
        'Dispensed' => 'ຈ່າຍແລ້ວ',
        'Cancelled' => 'ຍົກເລີກ',
    ];

    public const ADMINISTRATION_ROUTES = [
        'ກິນ' => 'ກິນ (ທາງປາກ)',
        'ທາ' => 'ທາ (ທາງຜິວ)',
        'ສັກ' => 'ສັກ (ທາງເສັ້ນເລືອດ)',
        'ຫຍົດ' => 'ຫຍົດ (ຕາ/ຫູ/ດັງ)',
        'ສູດ' => 'ສູດ (ທາງຫາຍໃຈ)',
        'ອື່ນໆ' => 'ອື່ນໆ',
    ];

    // ======================== RELATIONSHIPS ========================

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function prescribedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    // ======================== SCOPES ========================

    public function scopeByStatus($query, $status)
    {
        return $query->where('dispensing_status', $status);
    }

    public function scopePrescribed($query)
    {
        return $query->where('dispensing_status', 'Prescribed');
    }

    public function scopeDispensed($query)
    {
        return $query->where('dispensing_status', 'Dispensed');
    }

    public function scopePending($query)
    {
        return $query->where('dispensing_status', 'Prescribed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByMedicine($query, $medicineId)
    {
        return $query->where('medicine_id', $medicineId);
    }

    // ======================== ATTRIBUTES ========================

    public function getInstructionSummaryAttribute(): string
    {
        $parts = [];
        
        if ($this->dosage) {
            $parts[] = $this->dosage;
        }
        
        if ($this->frequency) {
            $parts[] = $this->frequency;
        }
        
        if ($this->duration) {
            $parts[] = $this->duration;
        }
        
        return implode(', ', $parts);
    }

    public function getFullInstructionAttribute(): string
    {
        $instruction = $this->medicine->medicine_name;
        
        if ($this->dosage) {
            $instruction .= ' - ' . $this->dosage;
        }
        
        if ($this->frequency) {
            $instruction .= ', ' . $this->frequency;
        }
        
        if ($this->duration) {
            $instruction .= ', ' . $this->duration;
        }
        
        if ($this->administration_route) {
            $instruction .= ' (' . $this->administration_route . ')';
        }
        
        return $instruction;
    }

    // ======================== METHODS ========================

    public function getDispensingStatusLabel(): string
    {
        return self::DISPENSING_STATUSES[$this->dispensing_status] ?? $this->dispensing_status;
    }

    public function getAdministrationRouteLabel(): string
    {
        return self::ADMINISTRATION_ROUTES[$this->administration_route] ?? $this->administration_route;
    }

    public function getTotalPriceFormatted(): string
    {
        return number_format((int)$this->total_price) . ' ກີບ';
    }

    public function getUnitPriceFormatted(): string
    {
        return number_format((int)$this->unit_price) . ' ກີບ/' . $this->medicine->getUnitLabel();
    }

    public function getQuantityFormatted(): string
    {
        return number_format($this->total_quantity) . ' ' . $this->medicine->getUnitLabel();
    }

    // ======================== STATUS CHECKS ========================

    public function canDispense(): bool
    {
        return $this->dispensing_status === 'Prescribed' &&
               $this->medicine->canDispense($this->total_quantity);
    }

    public function canCancel(): bool
    {
        return $this->dispensing_status === 'Prescribed';
    }

    public function isDispensed(): bool
    {
        return $this->dispensing_status === 'Dispensed';
    }

    public function isPending(): bool
    {
        return $this->dispensing_status === 'Prescribed';
    }

    // ======================== STATUS TRANSITIONS ========================

    public function dispense(User $pharmacist): bool
    {
        if (!$this->canDispense()) {
            return false;
        }
        
        // Check stock availability
        if (!$this->medicine->isInStock($this->total_quantity)) {
            return false;
        }
        
        // Reduce medicine stock
        $stockReduced = $this->medicine->reduceStock($this->total_quantity);
        if (!$stockReduced) {
            return false;
        }
        
        // Update dispensing status
        return $this->update([
            'dispensing_status' => 'Dispensed',
            'dispensed_by' => $pharmacist->id,
            'dispensed_at' => now(),
        ]);
    }

    public function cancel(string $reason = null): bool
    {
        if (!$this->canCancel()) {
            return false;
        }
        
        return $this->update([
            'dispensing_status' => 'Cancelled',
        ]);
    }

    // ======================== BUSINESS LOGIC ========================

    public function calculateTotalPrice(): float
    {
        return $this->total_quantity * $this->unit_price;
    }

    public function updatePricing(): bool
    {
        $this->unit_price = $this->medicine->unit_price;
        $this->total_price = $this->calculateTotalPrice();
        
        return $this->save();
    }

    public function getDaysSupply(): ?int
    {
        // Try to extract days from duration string
        if (!$this->duration) return null;
        
        // Simple extraction - could be improved with regex
        if (str_contains($this->duration, 'ວັນ')) {
            preg_match('/(\d+)\s*ວັນ/', $this->duration, $matches);
            return isset($matches[1]) ? intval($matches[1]) : null;
        }
        
        if (str_contains($this->duration, 'ອາທິດ')) {
            preg_match('/(\d+)\s*ອາທິດ/', $this->duration, $matches);
            return isset($matches[1]) ? intval($matches[1]) * 7 : null;
        }
        
        if (str_contains($this->duration, 'ເດືອນ')) {
            preg_match('/(\d+)\s*ເດືອນ/', $this->duration, $matches);
            return isset($matches[1]) ? intval($matches[1]) * 30 : null;
        }
        
        return null;
    }

    public function getDailyDose(): ?int
    {
        // Try to extract daily frequency from frequency string
        if (!$this->frequency) return null;
        
        if (str_contains($this->frequency, 'ວັນລະ')) {
            preg_match('/ວັນລະ\s*(\d+)\s*ເທື່ອ/', $this->frequency, $matches);
            return isset($matches[1]) ? intval($matches[1]) : null;
        }
        
        if (str_contains($this->frequency, 'ຊົ່ວໂມງ')) {
            preg_match('/(\d+)\s*ຊົ່ວໂມງ/', $this->frequency, $matches);
            if (isset($matches[1])) {
                $hours = intval($matches[1]);
                return $hours > 0 ? intval(24 / $hours) : null;
            }
        }
        
        return null;
    }

    public function getUsageInstructions(): array
    {
        $instructions = [];
        
        if ($this->dosage) {
            $instructions[] = 'ຂະໜາດ: ' . $this->dosage;
        }
        
        if ($this->frequency) {
            $instructions[] = 'ຄວາມຖີ່: ' . $this->frequency;
        }
        
        if ($this->duration) {
            $instructions[] = 'ໄລຍະເວລາ: ' . $this->duration;
        }
        
        if ($this->administration_route) {
            $instructions[] = 'ວິທີໃຊ້: ' . $this->getAdministrationRouteLabel();
        }
        
        if ($this->special_instructions) {
            $instructions[] = 'ຄຳແນະນຳພິເສດ: ' . $this->special_instructions;
        }
        
        if ($this->warnings) {
            $instructions[] = 'ຄຳເຕືອນ: ' . $this->warnings;
        }
        
        return $instructions;
    }

    public function getPatient(): Patient
    {
        return $this->treatment->getPatient();
    }

    public function getQueue(): Queue
    {
        return $this->treatment->getQueue();
    }

    // ======================== VALIDATION HELPERS ========================

    public function validateQuantity(): array
    {
        $errors = [];
        
        // Check if medicine exists and is active
        if (!$this->medicine || !$this->medicine->is_active) {
            $errors[] = 'ຢາບໍ່ມີຢູ່ ຫຼື ບໍ່ສາມາດໃຊ້ໄດ້';
        }
        
        // Check stock availability
        if ($this->medicine && !$this->medicine->isInStock($this->total_quantity)) {
            $errors[] = 'ສາງບໍ່ພໍ (ມີ ' . $this->medicine->getCurrentStockFormatted() . ')';
        }
        
        // Check expiry
        if ($this->medicine && $this->medicine->isExpired()) {
            $errors[] = 'ຢາໝົດອາຍຸແລ້ວ';
        }
        
        // Check if requires prescription
        if ($this->medicine && $this->medicine->requires_prescription && !$this->prescribed_by) {
            $errors[] = 'ຢານີ້ຕ້ອງມີໃບສັ່ງແພດ';
        }
        
        return $errors;
    }

    public function isValidForDispensing(): bool
    {
        return empty($this->validateQuantity());
    }
}