<?php
// app/Models/Room.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_code',
        'room_name',
        'room_type',
        'room_status',
        'capacity',
        'equipment_list',
        'is_available',
        'current_user_id',
        'notes',
        'version',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'version' => 'integer',
    ];

    // ======================== CONSTANTS ========================

    public const ROOM_TYPES = [
        'Consultation' => 'ຫ້ອງປຶກສາ',
        'Laboratory' => 'ຫ້ອງແລັບ',
        'X_Ray' => 'ຫ້ອງ X-Ray',
        'Ultrasound' => 'ຫ້ອງອັນຕາຊາວ',
        'Procedure' => 'ຫ້ອງຜ່າຕັດນ້ອຍ',
        'Pharmacy' => 'ຫ້ອງຢາ',
        'Cashier' => 'ຫ້ອງເກັບເງິນ',
        'General' => 'ຫ້ອງທົ່ວໄປ',
    ];

    public const ROOM_STATUSES = [
        'Available' => 'ວ່າງ',
        'Occupied' => 'ມີຄົນໃຊ້',
        'Testing' => 'ກຳລັງກວດ',
        'Cleaning' => 'ກຳລັງທຳຄວາມສະອາດ',
        'Maintenance' => 'ບຳລຸງຮັກສາ',
    ];

    // ======================== RELATIONSHIPS ========================

    public function currentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'assigned_room_id');
    }

    public function queueServices(): HasMany
    {
        return $this->hasMany(QueueService::class, 'assigned_room_id');
    }

    // ======================== SCOPES ========================

    public function scopeAvailable($query)
    {
        return $query->where('room_status', 'Available')
                     ->where('is_available', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('room_type', $type);
    }

    // ======================== METHODS ========================

    public function getRoomTypeLabel(): string
    {
        return self::ROOM_TYPES[$this->room_type] ?? $this->room_type;
    }

    public function getRoomStatusLabel(): string
    {
        return self::ROOM_STATUSES[$this->room_status] ?? $this->room_status;
    }

    public function isAvailable(): bool
    {
        return $this->room_status === 'Available' && $this->is_available;
    }

    public function occupy(User $user): bool
    {
        return $this->update([
            'room_status' => 'Occupied',
            'is_available' => false,
            'current_user_id' => $user->id,
            'version' => $this->version + 1,
        ]);
    }

    public function release(): bool
    {
        return $this->update([
            'room_status' => 'Available',
            'is_available' => true,
            'current_user_id' => null,
            'version' => $this->version + 1,
        ]);
    }

    public function startTesting(): bool
    {
        return $this->update([
            'room_status' => 'Testing',
            'version' => $this->version + 1,
        ]);
    }

    public function getCurrentQueue(): ?Queue
    {
        return $this->queues()
            ->whereDate('queue_date', today())
            ->whereNotIn('queue_status', ['Completed', 'Cancelled'])
            ->first();
    }
}