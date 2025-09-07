<?php
// app/Models/Service.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_code',
        'service_name',
        'service_category',
        'price',
        'duration_minutes',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];
}