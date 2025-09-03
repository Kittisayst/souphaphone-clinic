<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
        'phone',
        'address',
        'license_number',
        'specializations',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'permissions' => 'array',
        'specializations' => 'array',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    // =================== RELATIONSHIPS ===================

    // ຄິວທີ່ສ້າງໂດຍຜູ້ໃຊ້ຄົນນີ້
    public function createdQueues()
    {
        return $this->hasMany(Queue::class, 'created_by_id');
    }

    // ຄິວທີ່ຖືກມອບໝາຍໃຫ້ (ສຳລັບທ່ານໝໍ)
    public function assignedQueues()
    {
        return $this->hasMany(Queue::class, 'assigned_doctor_id');
    }

    // ການກວດເບື້ອງຕົ້ນທີ່ບັນທຶກ
    public function recordedVitalSigns()
    {
        return $this->hasMany(VitalSign::class, 'recorded_by_id');
    }

    // ບໍລິການທີ່ເພີ່ມ
    public function addedQueueServices()
    {
        return $this->hasMany(QueueService::class, 'added_by_id');
    }

    // ບໍລິການທີ່ຖືກມອບໝາຍ
    public function assignedQueueServices()
    {
        return $this->hasMany(QueueService::class, 'assigned_to_id');
    }

    // ການປິ່ນປົວທີ່ເຮັດ
    public function performedTreatments()
    {
        return $this->hasMany(Treatment::class, 'performed_by_id');
    }

    // ການກວດ Lab ທີ່ເຮັດ
    public function performedLabs()
    {
        return $this->hasMany(Lab::class, 'performed_by_id');
    }

    // ຜົນ Lab ທີ່ທ່ານໝໍເບິ່ງ
    public function reviewedLabs()
    {
        return $this->hasMany(Lab::class, 'reviewed_by_doctor_id');
    }

    // ໃບສັ່ງຢາທີ່ສັ່ງ
    public function prescribedMedicines()
    {
        return $this->hasMany(Prescription::class, 'prescribed_by_id');
    }

    // ຢາທີ່ຈ່າຍໃຫ້ຄົນໄຂ້
    public function dispensedMedicines()
    {
        return $this->hasMany(Prescription::class, 'dispensed_by_id');
    }

    // ການຈ່າຍເງິນທີ່ຮັບ
    public function receivedPayments()
    {
        return $this->hasMany(Payment::class, 'received_by_id');
    }

    // ຫ້ອງທີ່ກຳລັງໃຊ້
    public function currentRoom()
    {
        return $this->hasOne(Room::class, 'current_user_id');
    }

    // =================== ROLE SCOPES ===================

    // ສະເພາະແອັດມິນ
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // ສະເພາະທ່ານໝໍ
    public function scopeDoctors($query)
    {
        return $query->where('role', 'doctor');
    }

    // ສະເພາະພະຍາບານ
    public function scopeNurses($query)
    {
        return $query->where('role', 'nurse');
    }

    // ສະເພາະຜູ້ຮັບເງິນ
    public function scopeCashiers($query)
    {
        return $query->where('role', 'cashier');
    }

    // ສະເພາະຊ່າງເທັກນິກ
    public function scopeTechnicians($query)
    {
        return $query->where('role', 'technician');
    }

    // ຜູ້ໃຊ້ທີ່ເຮັດວຽກຢູ່
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ຜູ້ໃຊ້ທີ່ສາມາດເຂົ້າເຖິງຂໍ້ມູນການແພດ
    public function scopeMedicalStaff($query)
    {
        return $query->whereIn('role', ['doctor', 'nurse']);
    }

    // ຜູ້ໃຊ້ທີ່ສາມາດຮັບຄິວ
    public function scopeCanManageQueue($query)
    {
        return $query->whereIn('role', ['admin', 'nurse', 'cashier']);
    }

    // =================== ROLE CHECK METHODS ===================

    /**
     * ກວດສອບບົດບາດ
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * ກວດສອບບົດບາດຫຼາຍອັນ
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * ກວດສອບສິດທິ
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) return false;
        
        return in_array($permission, $this->permissions);
    }

    /**
     * ເພີ່ມສິດທິ
     */
    public function grantPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    /**
     * ຖອນສິດທິ
     */
    public function revokePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * ກວດສອບວ່າມີຄວາມຊ່ຽວຊານບໍ່
     */
    public function hasSpecialization(string $specialization): bool
    {
        if (!$this->specializations) return false;
        
        return in_array($specialization, $this->specializations);
    }

    // =================== ACCESSORS ===================

    /**
     * ຊື່ບົດບາດເປັນພາສາລາວ
     */
    public function getRoleLaoAttribute(): string
    {
        $roles = [
            'admin' => 'ແອັດມິນ',
            'doctor' => 'ທ່ານໝໍ',
            'nurse' => 'ພະຍາບານ',
            'cashier' => 'ພະນັກງານການເງິນ',
            'technician' => 'ຊ່າງເທັກນິກ'
        ];
        
        return $roles[$this->role] ?? $this->role;
    }

    /**
     * ຊື່ພ້ອມບົດບາດ
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->role_lao})";
    }

    /**
     * ກວດສອບວ່າເປັນທ່ານໝໍບໍ່
     */
    public function getIsDoctorAttribute(): bool
    {
        return $this->role === 'doctor';
    }

    /**
     * ກວດສອບວ່າເປັນພະຍາບານບໍ່
     */
    public function getIsNurseAttribute(): bool
    {
        return $this->role === 'nurse';
    }

    /**
     * ກວດສອບວ່າເປັນແອັດມິນບໍ່
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * ກວດສອບວ່າສາມາດເຮັດການແພດໄດ້ບໍ່
     */
    public function getCanTreatPatientsAttribute(): bool
    {
        return in_array($this->role, ['doctor', 'nurse']);
    }

    /**
     * ກວດສອບວ່າສາມາດຈັດການຄິວໄດ້ບໍ່
     */
    public function getCanManageQueueAttribute(): bool
    {
        return in_array($this->role, ['admin', 'nurse', 'cashier']);
    }

    /**
     * ກວດສອບວ່າສາມາດເຂົ້າເຖິງລາຍງານການເງິນໄດ້ບໍ່
     */
    public function getCanAccessFinancialReportsAttribute(): bool
    {
        return in_array($this->role, ['admin', 'cashier']) || 
               $this->hasPermission('view_financial_reports');
    }

    /**
     * ລາຍການຄວາມຊ່ຽວຊານທີ່ຈັດຮູບແບບ
     */
    public function getFormattedSpecializationsAttribute(): string
    {
        if (!$this->specializations || empty($this->specializations)) {
            return 'ບໍ່ມີຄວາມຊ່ຽວຊານສະເພາະ';
        }
        
        return implode(', ', $this->specializations);
    }

    /**
     * ສະຖານະການເຮັດວຽກ
     */
    public function getWorkStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'ບໍ່ເຮັດວຽກ';
        }

        // ກວດສອບວ່າກຳລັງໃຊ້ຫ້ອງຢູ່ບໍ່
        if ($this->currentRoom) {
            return "ກຳລັງໃຊ້ຫ້ອງ {$this->currentRoom->room_name}";
        }

        return 'ພ້ອມເຮັດວຽກ';
    }

    // =================== PERMISSION CONSTANTS ===================

    const PERMISSIONS = [
        // ການຈັດການຄິວ
        'manage_queues' => 'ຈັດການຄິວ',
        'create_queue' => 'ສ້າງຄິວ',
        'assign_doctor' => 'ມອບໝາຍທ່ານໝໍ',
        
        // ການແພດ
        'perform_treatment' => 'ເຮັດການປິ່ນປົວ',
        'prescribe_medicine' => 'ສັ່ງຢາ',
        'review_lab_results' => 'ເບິ່ງຜົນກວດ',
        'dispense_medicine' => 'ຈ່າຍຢາ',
        
        // ການເງິນ
        'process_payments' => 'ດໍາເນີນການຈ່າຍເງິນ',
        'view_financial_reports' => 'ເບິ່ງລາຍງານການເງິນ',
        'manage_discounts' => 'ຈັດການສ່ວນຫຼຸດ',
        
        // ການຄຸ້ມຄອງ
        'manage_users' => 'ຈັດການຜູ້ໃຊ້',
        'manage_services' => 'ຈັດການບໍລິການ',
        'manage_medicines' => 'ຈັດການຢາ',
        'manage_rooms' => 'ຈັດການຫ້ອງ',
        
        // ລາຍງານ
        'view_reports' => 'ເບິ່ງລາຍງານ',
        'export_data' => 'ສົ່ງອອກຂໍ້ມູນ',
    ];

    const SPECIALIZATIONS = [
        // ຄວາມຊ່ຽວຊານທ່ານໝໍ
        'ແພດທົ່ວໄປ',
        'ແພດເດັກ', 
        'ແພດສູດຕິກ',
        'ແພດໂລກພາຍໃນ',
        'ແພດກະດູກ',
        'ແພດຜິວໜັງ',
        'ແພດຈັກສູ',
        'ແພດຫົວໃຈ',
        
        // ຄວາມຊ່ຽວຊານພະຍາບານ
        'ພະຍາບານທົ່ວໄປ',
        'ພະຍາບານເດັກ',
        'ພະຍາບານຜ່າຕັດ',
        'ພະຍາບານຫ້ອງສຸກເສີນ',
        
        // ຄວາມຊ່ຽວຊານຊ່າງເທັກນິກ
        'ຊ່າງ X-Ray',
        'ຊ່າງ Ultrasound',
        'ຊ່າງແລັບ',
        'ຊ່າງ ECG',
    ];

    // =================== ROLE-BASED DEFAULT PERMISSIONS ===================

    /**
     * ສິດທິເບື້ອງຕົ້ນຕາມບົດບາດ
     */
    public static function getDefaultPermissions(string $role): array
    {
        return match($role) {
            'admin' => [
                'manage_queues', 'create_queue', 'assign_doctor',
                'manage_users', 'manage_services', 'manage_medicines', 'manage_rooms',
                'view_financial_reports', 'view_reports', 'export_data',
                'process_payments', 'manage_discounts'
            ],
            
            'doctor' => [
                'perform_treatment', 'prescribe_medicine', 'review_lab_results',
                'create_queue', 'assign_doctor', 'view_reports'
            ],
            
            'nurse' => [
                'manage_queues', 'create_queue', 
                'perform_treatment', 'dispense_medicine',
                'view_reports'
            ],
            
            'cashier' => [
                'manage_queues', 'create_queue',
                'process_payments', 'view_financial_reports',
                'manage_discounts'
            ],
            
            'technician' => [
                'perform_treatment', // ສຳລັບການກວດພິເສດ
                'view_reports'
            ],
            
            default => []
        };
    }

    /**
     * ມອບສິດທິເບື້ອງຕົ້ນຕາມບົດບາດ
     */
    public function assignDefaultPermissions(): void
    {
        $defaultPermissions = self::getDefaultPermissions($this->role);
        $this->update(['permissions' => $defaultPermissions]);
    }

    // =================== SPECIALIZATION METHODS ===================

    /**
     * ເພີ່ມຄວາມຊ່ຽວຊານ
     */
    public function addSpecialization(string $specialization): void
    {
        $specializations = $this->specializations ?? [];
        
        if (!in_array($specialization, $specializations)) {
            $specializations[] = $specialization;
            $this->update(['specializations' => $specializations]);
        }
    }

    /**
     * ຖອນຄວາມຊ່ຽວຊານ
     */
    public function removeSpecialization(string $specialization): void
    {
        $specializations = $this->specializations ?? [];
        
        $specializations = array_filter($specializations, fn($s) => $s !== $specialization);
        $this->update(['specializations' => array_values($specializations)]);
    }

    /**
     * ດຶງຄວາມຊ່ຽວຊານຫຼັກ (ອັນທຳອິດ)
     */
    public function getPrimarySpecializationAttribute(): ?string
    {
        return $this->specializations[0] ?? null;
    }

    // =================== BUSINESS LOGIC METHODS ===================

    /**
     * ກວດສອບວ່າສາມາດປິ່ນປົວຄົນໄຂ້ປະເພດນີ້ໄດ້ບໍ່
     */
    public function canTreatPatientType(string $patientType): bool
    {
        if (!$this->is_doctor) return false;
        
        // ທ່ານໝໍທົ່ວໄປສາມາດກວດທຸກຄົນ
        if ($this->hasSpecialization('ແພດທົ່ວໄປ')) return true;
        
        // ແພດເດັກສາມາດກວດເດັກເທົ່ານັ້ນ
        if ($patientType === 'child') {
            return $this->hasSpecialization('ແພດເດັກ');
        }
        
        return true;
    }

    /**
     * ກວດສອບວ່າສາມາດເຮັດບໍລິການປະເພດນີ້ໄດ້ບໍ່
     */
    public function canPerformService(string $serviceCategory): bool
    {
        return match($serviceCategory) {
            'Consultation' => $this->hasRole('doctor'),
            'X_Ray' => $this->hasAnyRole(['doctor', 'technician']) && 
                      ($this->hasSpecialization('ຊ່າງ X-Ray') || $this->hasRole('doctor')),
            'Ultrasound' => $this->hasAnyRole(['doctor', 'technician']) && 
                           ($this->hasSpecialization('ຊ່າງ Ultrasound') || $this->hasRole('doctor')),
            'Blood_Test', 'Urine_Test' => $this->hasAnyRole(['nurse', 'technician']) && 
                                         ($this->hasSpecialization('ຊ່າງແລັບ') || $this->hasRole('nurse')),
            'ECG' => $this->hasAnyRole(['doctor', 'nurse', 'technician']),
            default => $this->hasRole('admin')
        };
    }

    /**
     * ກວດສອບວ່າກຳລັງເຮັດວຽກຢູ່ບໍ່
     */
    public function isCurrentlyWorking(): bool
    {
        // ກວດສອບຫ້ອງທີ່ກຳລັງໃຊ້
        if ($this->currentRoom) return true;
        
        // ກວດສອບການປິ່ນປົວທີ່ຍັງບໍ່ສຳເລັດ
        $activeTreatments = $this->performedTreatments()
            ->where('status', 'In_Progress')
            ->exists();
            
        return $activeTreatments;
    }

    /**
     * ດຶງລາຍການຄິວວັນນີ້
     */
    public function getTodayQueuesAttribute()
    {
        if (!$this->is_doctor) return collect();
        
        return $this->assignedQueues()
            ->whereDate('queue_date', today())
            ->orderBy('queue_number')
            ->get();
    }

    /**
     * ດຶງຈຳນວນຄິວທີ່ລໍຖ້າ
     */
    public function getPendingQueuesCountAttribute(): int
    {
        if (!$this->is_doctor) return 0;
        
        return $this->assignedQueues()
            ->whereDate('queue_date', today())
            ->whereNotIn('queue_status', ['Completed', 'Cancelled'])
            ->count();
    }

    // =================== UTILITY METHODS ===================

    /**
     * Reset ສິດທິເປັນຄ່າເບື້ອງຕົ້ນ
     */
    public function resetToDefaultPermissions(): void
    {
        $this->assignDefaultPermissions();
    }

    /**
     * ອັບເດດເວລາ Login ຫຼ້າສຸດ
     */
    public function updateLastLogin(): void
    {
        $this->timestamps = false; // ບໍ່ອັບເດດ updated_at
        $this->update(['last_login' => now()]);
        $this->timestamps = true;
    }

    /**
     * ສ້າງ API Token (ຖ້າໃຊ້ Sanctum)
     */
    public function createApiToken(string $name = 'api-token'): string
    {
        return $this->createToken($name)->plainTextToken;
    }

    // =================== QUERY HELPERS ===================

    /**
     * ດຶງທ່ານໝໍທີ່ວ່າງໃນເວລານີ້
     */
    public static function getAvailableDoctors()
    {
        return static::doctors()
            ->active()
            ->whereDoesntHave('currentRoom')
            ->get();
    }

    /**
     * ດຶງພະຍາບານທີ່ເຮັດວຽກຢູ່
     */
    public static function getActiveNurses()
    {
        return static::nurses()
            ->active()
            ->get();
    }

    /**
     * ດຶງຜູ້ໃຊ້ທີ່ສາມາດເຮັດບໍລິການນີ້ໄດ້
     */
    public static function getStaffForService(string $serviceCategory)
    {
        return static::active()
            ->get()
            ->filter(fn($user) => $user->canPerformService($serviceCategory));
    }
}