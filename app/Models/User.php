<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * ກວດສອບວ່າສາມາດເຂົ້າເຖິງ Filament ໄດ້ບໍ່
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && in_array($this->role, ['admin', 'doctor', 'nurse', 'cashier']);
    }

    /**
     * ກວດສອບບົດບາດ
     */
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

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    /**
     * ກວດສອບສິດທິ
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin ມີສິດທັງໝົດ
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * ຄວາມສຳພັນກັບຕາຕະລາງອື່ນ
     */
    // public function medicalExaminations()
    // {
    //     return $this->hasMany(MedicalExamination::class, 'conducted_by');
    // }

    // public function verifiedExaminations()
    // {
    //     return $this->hasMany(MedicalExamination::class, 'verified_by');
    // }

    // public function treatments()
    // {
    //     return $this->hasMany(Treatment::class, 'doctor_id');
    // }

    // public function createdServices()
    // {
    //     return $this->hasMany(MedicalService::class, 'created_by');
    // }
}