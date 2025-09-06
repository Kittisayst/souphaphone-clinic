<?php
// app/Models/QueueService.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class QueueService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_id',
        'service_id',
        'added_by_id',
        'assigned_to_id',
        'service_status',
        'assigned_room_id',
        'started_at',
        'completed_at',
        'actual_duration',
        'notes',
        'service_details',
        'service_price',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'actual_duration' => 'integer',
        'service_details' => 'json',
        'service_price' => 'decimal:2',
    ];

    // ======================== CONSTANTS ========================

    public const STATUSES = [
        'Added' => 'ເພີ່ມແລ້ວ',
        'In_Progress' => 'ກຳລັງເຮັດ',
        'Completed' => 'ສຳເລັດແລ້ວ',
        'Cancelled' => 'ຍົກເລີກ',
    ];

    // ======================== RELATIONSHIPS ========================

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function assignedRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'assigned_room_id');
    }

    public function treatment(): HasOne
    {
        return $this->hasOne(Treatment::class);
    }

    // ======================== SCOPES ========================

    public function scopeByStatus($query, $status)
    {
        return $query->where('service_status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('service_status', 'Added');
    }

    public function scopeInProgress($query)
    {
        return $query->where('service_status', 'In_Progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('service_status', 'Completed');
    }

    public function scopeLabServices($query)
    {
        return $query->whereHas('service', fn($q) => $q->where('has_lab_result', true));
    }

    // ======================== METHODS ========================

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->service_status] ?? $this->service_status;
    }

    public function getServicePrice(): float
    {
        return $this->service_price ?? $this->service->base_price ?? 0;
    }

    // ======================== STATUS CHECKS ========================

    public function canStart(): bool
    {
        return $this->service_status === 'Added';
    }

    public function canComplete(): bool
    {
        return $this->service_status === 'In_Progress';
    }

    public function isLabService(): bool
    {
        return $this->service?->has_lab_result === true;
    }

    // ======================== STATUS TRANSITIONS ========================

    public function start(User $user = null): bool
    {
        if (!$this->canStart()) return false;
        
        $updateData = [
            'service_status' => 'In_Progress',
            'started_at' => now(),
        ];
        
        if ($user) {
            $updateData['assigned_to_id'] = $user->id;
        }
        
        // Auto-assign room from service
        if (!$this->assigned_room_id && $this->service->room_id) {
            $updateData['assigned_room_id'] = $this->service->room_id;
            
            // Occupy the room
            $room = Room::find($this->service->room_id);
            if ($room && $room->isAvailable()) {
                $room->startTesting();
            }
        }
        
        return $this->update($updateData);
    }

    public function complete(array $completionData = []): bool
    {
        if (!$this->canComplete()) return false;
        
        $updateData = [
            'service_status' => 'Completed',
            'completed_at' => now(),
            'actual_duration' => $this->started_at ? 
                $this->started_at->diffInMinutes(now()) : null,
        ];
        
        // Add completion notes/details
        if (!empty($completionData['notes'])) {
            $updateData['notes'] = $this->notes . "\n" . $completionData['notes'];
        }
        
        if (!empty($completionData['service_details'])) {
            $updateData['service_details'] = array_merge(
                $this->service_details ?? [],
                $completionData['service_details']
            );
        }
        
        $result = $this->update($updateData);
        
        // Release room if this was the last service using it
        if ($result && $this->assigned_room_id) {
            $room = $this->assignedRoom;
            if ($room && !$this->hasOtherActiveServices()) {
                $room->release();
            }
        }
        
        return $result;
    }

    public function cancel(string $reason = null): bool
    {
        $updateData = [
            'service_status' => 'Cancelled',
            'completed_at' => now(),
        ];
        
        if ($reason) {
            $updateData['notes'] = $this->notes . "\nCancelled: " . $reason;
        }
        
        $result = $this->update($updateData);
        
        // Release room
        if ($result && $this->assigned_room_id) {
            $room = $this->assignedRoom;
            if ($room && !$this->hasOtherActiveServices()) {
                $room->release();
            }
        }
        
        return $result;
    }

    // ======================== HELPER METHODS ========================

    public function getDurationFormatted(): string
    {
        if (!$this->actual_duration) return '-';
        
        $hours = intval($this->actual_duration / 60);
        $minutes = $this->actual_duration % 60;
        
        if ($hours > 0) {
            return $hours . ' ຊົ່ວໂມງ ' . $minutes . ' ນາທີ';
        }
        
        return $minutes . ' ນາທີ';
    }

    public function getLabTestDetails(): array
    {
        if (!$this->isLabService()) return [];
        
        return $this->service_details['lab_tests'] ?? [];
    }

    public function addLabTestDetail(array $testData): bool
    {
        if (!$this->isLabService()) return false;
        
        $details = $this->service_details ?? [];
        $details['lab_tests'] = $details['lab_tests'] ?? [];
        $details['lab_tests'][] = $testData;
        
        return $this->update(['service_details' => $details]);
    }

    private function hasOtherActiveServices(): bool
    {
        return QueueService::where('assigned_room_id', $this->assigned_room_id)
            ->where('id', '!=', $this->id)
            ->whereIn('service_status', ['Added', 'In_Progress'])
            ->exists();
    }
}