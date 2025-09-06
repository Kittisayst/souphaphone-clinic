<?php
// app/Models/User.php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'gender',
        'birth_date',
        'address',
        'license_number',
        'specializations',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'specializations' => 'json',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    // ======================== CONSTANTS ========================

    public const ROLES = [
        'admin' => 'ຜູ້ດູແລລະບົບ',
        'doctor' => 'ທ່ານໝໍ',
        'nurse' => 'ພະຍາບານ',
        'technician' => 'ເຕັກນິກ',
        'pharmacist' => 'ເ຀ມີ',
        'cashier' => 'ເກັບເງິນ',
        'receptionist' => 'ຮັບບ້ານ',
    ];

    public const GENDERS = [
        'Male' => 'ຊາຍ',
        'Female' => 'ຍິງ',
        'Other' => 'ອື່ນໆ',
    ];

    // ======================== RELATIONSHIPS ========================

    public function createdPatients(): HasMany
    {
        return $this->hasMany(Patient::class, 'created_by');
    }

    public function createdQueues(): HasMany
    {
        return $this->hasMany(Queue::class, 'created_by');
    }

    public function assignedQueues(): HasMany
    {
        return $this->hasMany(Queue::class, 'doctor_id');
    }

    public function currentRoom(): HasMany
    {
        return $this->hasMany(Room::class, 'current_user_id');
    }

    public function addedQueueServices(): HasMany
    {
        return $this->hasMany(QueueService::class, 'added_by_id');
    }

    public function assignedQueueServices(): HasMany
    {
        return $this->hasMany(QueueService::class, 'assigned_to_id');
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class, 'doctor_id');
    }

    public function vitalSignsRecords(): HasMany
    {
        return $this->hasMany(VitalSign::class, 'recorded_by');
    }

    // ======================== SCOPES ========================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeDoctors($query)
    {
        return $query->where('role', 'doctor');
    }

    public function scopeNurses($query)
    {
        return $query->where('role', 'nurse');
    }

    // ======================== METHODS ========================

    public function getRoleLabel(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function getGenderLabel(): string
    {
        return self::GENDERS[$this->gender] ?? $this->gender;
    }

    public function getAge(): ?int
    {
        return Carbon::parse($this->birth_date)?->age;
    }

    // ======================== ROLE CHECKS ========================

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isNurse(): bool
    {
        return $this->role === 'nurse';
    }

    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }

    public function isPharmacist(): bool
    {
        return $this->role === 'pharmacist';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    // ======================== PERMISSIONS ========================

    public function canCreatePatients(): bool
    {
        return in_array($this->role, ['admin', 'receptionist', 'nurse']);
    }

    public function canManageQueues(): bool
    {
        return in_array($this->role, ['admin', 'receptionist', 'nurse']);
    }

    public function canRecordVitalSigns(): bool
    {
        return in_array($this->role, ['admin', 'nurse']);
    }

    public function canConsultPatients(): bool
    {
        return in_array($this->role, ['admin', 'doctor']);
    }

    public function canPerformLabTests(): bool
    {
        return in_array($this->role, ['admin', 'technician', 'nurse']);
    }

    public function canDispenseMedications(): bool
    {
        return in_array($this->role, ['admin', 'pharmacist']);
    }

    public function canProcessPayments(): bool
    {
        return in_array($this->role, ['admin', 'cashier']);
    }

    public function canManageRooms(): bool
    {
        return in_array($this->role, ['admin', 'nurse']);
    }

    // ======================== HELPER METHODS ========================

    public function getTodayQueues()
    {
        return $this->assignedQueues()->today();
    }

    public function getActiveQueues()
    {
        return $this->assignedQueues()
            ->whereNotIn('queue_status', ['Completed', 'Cancelled']);
    }

    public function getCurrentRoom(): ?Room
    {
        return $this->currentRoom()->first();
    }

    public function getSpecializations(): array
    {
        return $this->specializations ?? [];
    }

    public function hasSpecialization(string $specialization): bool
    {
        return in_array($specialization, $this->getSpecializations());
    }

    public static function generateEmployeeCode(string $role): string
    {
        $prefix = match ($role) {
            'doctor' => 'DOC',
            'nurse' => 'NUR',
            'technician' => 'TEC',
            'pharmacist' => 'PHA',
            'cashier' => 'CAS',
            'receptionist' => 'REC',
            default => 'EMP'
        };

        $lastUser = static::withTrashed()
            ->where('employee_code', 'like', $prefix . '%')
            ->latest('id')
            ->first();

        if ($lastUser) {
            $lastNumber = intval(substr($lastUser->employee_code, 3));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function getWorkload(): array
    {
        $today = today();

        return [
            'total_queues' => $this->assignedQueues()->whereDate('queue_date', $today)->count(),
            'completed_queues' => $this->assignedQueues()
                ->whereDate('queue_date', $today)
                ->where('queue_status', 'Completed')
                ->count(),
            'pending_queues' => $this->assignedQueues()
                ->whereDate('queue_date', $today)
                ->whereNotIn('queue_status', ['Completed', 'Cancelled'])
                ->count(),
        ];
    }
}