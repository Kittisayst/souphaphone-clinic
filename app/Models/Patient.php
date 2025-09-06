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
        'email',
        'address',
        'village',
        'district',
        'province',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'medical_history',
        'allergies',
        'chronic_conditions',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = ['full_name', 'age'];

    // ======================== ATTRIBUTES ========================

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getAgeAttribute(): ?int
    {
        return Carbon::parse($this->birth_date)?->age;
    }

    // ======================== RELATIONSHIPS ========================

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    // ======================== SCOPES ========================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('patient_code', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // ======================== METHODS ========================

    public static function generatePatientCode(): string
    {
        $lastPatient = static::withTrashed()->latest('id')->first();
        $nextNumber = ($lastPatient?->id ?? 0) + 1;
        return 'P' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getTodayQueue(): ?Queue
    {
        return $this->queues()->whereDate('queue_date', today())->first();
    }

    public function hasActiveQueue(): bool
    {
        return $this->queues()
            ->whereDate('queue_date', today())
            ->whereNotIn('queue_status', ['Completed', 'Cancelled'])
            ->exists();
    }
}