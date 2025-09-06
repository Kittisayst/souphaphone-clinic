<?php
// app/Models/Treatment.php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treatment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_service_id',
        'room_id',
        'doctor_id',
        'examination_notes',
        'findings',
        'medical_history_notes',
        'diagnosis',
        'treatment_plan',
        'follow_up_required',
        'follow_up_date',
        'follow_up_notes',
        'status',
        'billing_items',
        'total_amount',
        'updated_by',
    ];

    protected $casts = [
        'follow_up_required' => 'boolean',
        'follow_up_date' => 'date',
        'billing_items' => 'json',
        'total_amount' => 'decimal:2',
    ];

    // ======================== CONSTANTS ========================

    public const STATUSES = [
        'In_Progress' => 'ກຳລັງກວດຢູ່',
        'Completed' => 'ສຳເລັດແລ້ວ',
        'Cancelled' => 'ຍົກເລີກ',
    ];

    // ======================== RELATIONSHIPS ========================

    public function queueService(): BelongsTo
    {
        return $this->belongsTo(QueueService::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function medicationInstructions(): HasMany
    {
        return $this->hasMany(MedicationInstruction::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // ======================== SCOPES ========================

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'In_Progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeRequiringFollowUp($query)
    {
        return $query->where('follow_up_required', true)
            ->whereNotNull('follow_up_date');
    }

    // ======================== METHODS ========================

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getQueue(): Queue
    {
        return $this->queueService->queue;
    }

    public function getPatient(): Patient
    {
        return $this->getQueue()->patient;
    }

    // ======================== BILLING METHODS ========================

    public function calculateBillingItems(): array
    {
        $items = [];

        // Add queue services
        $queueServices = $this->getQueue()->queueServices;
        foreach ($queueServices as $qs) {
            $items['services'][] = [
                'queue_service_id' => $qs->id,
                'service_name' => $qs->service->service_name,
                'price' => $qs->getServicePrice(),
                'type' => 'service'
            ];
        }

        // Add medications
        foreach ($this->medicationInstructions as $medication) {
            $items['medications'][] = [
                'medication_id' => $medication->id,
                'medicine_name' => $medication->medicine->medicine_name,
                'quantity' => $medication->total_quantity,
                'unit_price' => $medication->unit_price,
                'total_price' => $medication->total_price,
                'type' => 'medication'
            ];
        }

        return $items;
    }

    public function updateBillingItems(): bool
    {
        $billingItems = $this->calculateBillingItems();

        $totalAmount = 0;

        // Calculate services total
        foreach ($billingItems['services'] ?? [] as $service) {
            $totalAmount += $service['price'];
        }

        // Calculate medications total
        foreach ($billingItems['medications'] ?? [] as $medication) {
            $totalAmount += $medication['total_price'];
        }

        return $this->update([
            'billing_items' => $billingItems,
            'total_amount' => $totalAmount,
        ]);
    }

    public function getBillingItemsFormatted(): array
    {
        $items = $this->billing_items ?? [];
        $formatted = [];

        foreach ($items['services'] ?? [] as $service) {
            $formatted[] = [
                'name' => $service['service_name'],
                'price' => number_format($service['price']) . ' ກີບ',
                'type' => 'ບໍລິການ'
            ];
        }

        foreach ($items['medications'] ?? [] as $medication) {
            $formatted[] = [
                'name' => $medication['medicine_name'] . ' x' . $medication['quantity'],
                'price' => number_format($medication['total_price']) . ' ກີບ',
                'type' => 'ຢາ'
            ];
        }

        return $formatted;
    }

    public function getTotalAmountFormatted(): string
    {
        return number_format((int) $this->total_amount) . ' ກີບ';
    }

    // ======================== STATUS TRANSITIONS ========================

    public function complete(): bool
    {
        $this->updateBillingItems();

        return $this->update([
            'status' => 'Completed',
            'updated_by' => auth()->id(),
        ]);
    }

    public function cancel(string $reason = null): bool
    {
        return $this->update([
            'status' => 'Cancelled',
            'updated_by' => auth()->id(),
        ]);
    }

    // ======================== HELPER METHODS ========================

    public function hasMedications(): bool
    {
        return $this->medicationInstructions()->exists();
    }

    public function isPaid(): bool
    {
        return $this->payment && $this->payment->payment_status === 'Paid';
    }

    public function canPrescribeMedication(): bool
    {
        return $this->status === 'In_Progress';
    }

    public function needsFollowUp(): bool
    {
        return $this->follow_up_required && $this->follow_up_date;
    }

    public function getFollowUpStatus(): string
    {
        if (!$this->needsFollowUp())
            return 'ບໍ່ຕ້ອງຕິດຕາມ';

        if (Carbon::parse($this->follow_up_date)->isFuture()) {
            return 'ນັດວັນທີ ' . Carbon::parse($this->follow_up_date)->format('d/m/Y');
        }

        if (Carbon::parse($this->follow_up_date)->isToday()) {
            return 'ນັດວັນນີ້';
        }

        return 'ເລີຍກຳນົດແລ້ວ';
    }
}