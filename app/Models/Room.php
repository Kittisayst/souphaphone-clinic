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
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

}