<?php
// app/Models/VitalSign.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VitalSign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_id',
        'temperature',
        'weight',
        'height',
        'blood_pressure',
        'heart_rate',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'heart_rate' => 'integer',
    ];

    // ======================== RELATIONSHIPS ========================

}