<?php
// app/Models/Patient.php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_code',
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'phone',
        'address',
        'allergies',
        'note',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

}