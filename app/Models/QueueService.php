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
        'room_id',
        'doctor_id',
        'started_at',
        'completed_at',
        'service_status',
        'service_price',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'service_price' => 'decimal:2',
    ];

    // ======================== CONSTANTS ========================

    public const STATUSES = [
        'Added' => 'ເພີ່ມແລ້ວ',
        'In_Progress' => 'ກຳລັງເຮັດ',
        'Completed' => 'ສຳເລັດແລ້ວ',
        'Cancelled' => 'ຍົກເລີກ',
    ];

    // ======================== RELATIONSHIPS ==================

    //========================= Accessors ======================

    // ======================== SCOPES ========================

    // ======================== METHODS ========================

}