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

    /**
     * ການປິ່ນປົວທີ່ເຮັດ (ປັບປຸງ field name)
     */
    public function performedTreatments()
    {
        return $this->hasMany(Treatment::class, 'doctor_id');
    }

    /**
     * ການປິ່ນປົວທີ່ອັບເດດ
     */
    public function updatedTreatments()
    {
        return $this->hasMany(Treatment::class, 'updated_by');
    }

    /**
     * ການກວດ Lab ທີ່ເກັບຕົວຢ່າງ
     */
    public function collectedLabSamples()
    {
        return $this->hasMany(LabTest::class, 'sample_collected_by');
    }

    /**
     * ການກວດ Lab ທີ່ເຮັດ
     */
    public function performedLabTests()
    {
        return $this->hasMany(LabTest::class, 'tested_by');
    }

    /**
     * ຜົນ Lab ທີ່ທ່ານໝໍເບິ່ງ
     */
    public function reviewedLabTests()
    {
        return $this->hasMany(LabTest::class, 'reviewed_by');
    }

    /**
     * ໃບສັ່ງຢາທີ່ສັ່ງ (ປັບປຸງ model name)
     */
    public function prescribedMedications()
    {
        return $this->hasMany(MedicationInstruction::class, 'prescribed_by');
    }

    /**
     * ຢາທີ່ຈ່າຍໃຫ້ຄົນໄຂ້
     */
    public function dispensedMedications()
    {
        return $this->hasMany(MedicationInstruction::class, 'dispensed_by');
    }

    /**
     * ການຈ່າຍເງິນທີ່ຮັບ (ປັບປຸງ field name)
     */
    public function processedPayments()
    {
        return $this->hasMany(Payment::class, 'cashier_id');
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
        if (!$this->permissions)
            return false;

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
        if (!$this->specializations)
            return false;

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
        return match ($role) {
            'admin' => [
                'manage_queues',
                'create_queue',
                'assign_doctor',
                'manage_users',
                'manage_services',
                'manage_medicines',
                'manage_rooms',
                'view_financial_reports',
                'view_reports',
                'export_data',
                'process_payments',
                'manage_discounts',
                'manage_treatments',
                'manage_lab_tests',
                'manage_medications'
            ],

            'doctor' => [
                'perform_treatment',
                'prescribe_medicine',
                'review_lab_results',
                'create_queue',
                'assign_doctor',
                'view_reports',
                'order_lab_tests',
                'update_treatment_plan',
                'schedule_follow_up'
            ],

            'nurse' => [
                'manage_queues',
                'create_queue',
                'perform_treatment',
                'dispense_medicine',
                'collect_lab_samples',
                'record_vital_signs',
                'view_reports'
            ],

            'cashier' => [
                'manage_queues',
                'create_queue',
                'process_payments',
                'view_financial_reports',
                'manage_discounts',
                'generate_receipts'
            ],

            'technician' => [
                'perform_lab_tests',
                'update_lab_results',
                'collect_lab_samples',
                'operate_lab_equipment',
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
        if (!$this->is_doctor)
            return false;

        // ທ່ານໝໍທົ່ວໄປສາມາດກວດທຸກຄົນ
        if ($this->hasSpecialization('ແພດທົ່ວໄປ'))
            return true;

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
        return match ($serviceCategory) {
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
        if ($this->currentRoom)
            return true;

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
        if (!$this->is_doctor)
            return collect();

        return $this->assignedQueues()
            ->whereDate('queue_date', today())
            ->orderBy('queue_number')
            ->with(['patient', 'queueServices.service'])
            ->get();
    }

    /**
     * ດຶງຈຳນວນຄິວທີ່ລໍຖ້າ
     */
    public function getPendingQueuesCountAttribute(): int
    {
        if (!$this->is_doctor)
            return 0;

        return $this->assignedQueues()
            ->whereDate('queue_date', today())
            ->whereNotIn('queue_status', ['Completed', 'Cancelled'])
            ->count();
    }

    /**
     * ດຶງການປິ່ນປົວທີ່ຕ້ອງຕິດຕາມ
     */
    public function getTreatmentsNeedingFollowUpAttribute()
    {
        if (!$this->is_doctor)
            return collect();

        return $this->performedTreatments()
            ->where('follow_up_required', true)
            ->where('status', Treatment::STATUS_COMPLETED)
            ->whereDate('follow_up_date', '<=', today())
            ->with(['queueService.queue.patient'])
            ->get();
    }

    /**
     * ດຶງ Lab Tests ທີ່ຕ້ອງກວດສອບ
     */
    public function getLabTestsNeedingReviewAttribute()
    {
        if (!$this->is_doctor)
            return collect();

        return LabTest::whereHas('treatment', function ($query) {
            $query->where('doctor_id', $this->id);
        })
            ->where('status', LabTest::STATUS_COMPLETED)
            ->with(['treatment.queueService.queue.patient'])
            ->get();
    }

    /**
     * ດຶງສະຖິຕິການເຮັດວຽກວັນນີ້
     */
    public function getTodayWorkStatsAttribute(): array
    {
        $today = today();

        return [
            'treatments_completed' => $this->performedTreatments()
                ->whereDate('created_at', $today)
                ->where('status', Treatment::STATUS_COMPLETED)
                ->count(),

            'lab_tests_reviewed' => $this->reviewedLabTests()
                ->whereDate('reviewed_at', $today)
                ->count(),

            'medications_prescribed' => $this->prescribedMedications()
                ->whereDate('prescribed_at', $today)
                ->count(),

            'medications_dispensed' => $this->dispensedMedications()
                ->whereDate('dispensed_at', $today)
                ->count(),

            'payments_processed' => $this->processedPayments()
                ->whereDate('paid_at', $today)
                ->count(),
        ];
    }

    /**
     * ດຶງລາຍໄດ້ທີ່ສ້າງວັນນີ້ (ສຳລັບທ່ານໝໍ)
     */
    public function getTodayRevenueAttribute(): float
    {
        if (!$this->is_doctor)
            return 0;

        return Payment::whereHas('treatment', function ($query) {
            $query->where('doctor_id', $this->id);
        })
            ->whereDate('paid_at', today())
            ->sum('total_amount');
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

    /**
     * ດຶງການປິ່ນປົວທີ່ຍັງບໍ່ສຳເລັດ
     */
    public function getActiveTreatmentsAttribute()
    {
        return $this->performedTreatments()
            ->whereNotIn('status', [
                Treatment::STATUS_COMPLETED,
                Treatment::STATUS_CANCELLED
            ])
            ->with(['queueService.queue.patient', 'room'])
            ->get();
    }

    /**
     * ດຶງ Lab Tests ທີ່ຍັງບໍ່ສຳເລັດ
     */
    public function getPendingLabTestsAttribute()
    {
        return $this->performedLabTests()
            ->whereNotIn('status', [
                LabTest::STATUS_REVIEWED,
                LabTest::STATUS_CANCELLED
            ])
            ->with(['treatment.queueService.queue.patient'])
            ->get();
    }

    /**
     * ດຶງຢາທີ່ຍັງບໍ່ໄດ້ຈ່າຍ
     */
    public function getPendingMedicationsAttribute()
    {
        return $this->prescribedMedications()
            ->where('status', MedicationInstruction::STATUS_PRESCRIBED)
            ->with(['treatment.queueService.queue.patient', 'medicine'])
            ->get();
    }

    /**
     * ຄິດຈຳນວນຄົນໄຂ້ທີ່ປິ່ນປົວໃນຊ່ວງເວລາ
     */
    public function getPatientsCountInPeriod(\DateTime $startDate, \DateTime $endDate): int
    {
        if (!$this->is_doctor)
            return 0;

        return $this->performedTreatments()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->distinct('queue_service_id')
            ->count();
    }

    /**
     * ຄິດລາຍໄດ້ໃນຊ່ວງເວລາ
     */
    public function getRevenueInPeriod(\DateTime $startDate, \DateTime $endDate): float
    {
        if ($this->is_doctor) {
            return Payment::whereHas('treatment', function ($query) {
                $query->where('doctor_id', $this->id);
            })
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->sum('total_amount');
        }

        if ($this->hasRole('cashier')) {
            return $this->processedPayments()
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->sum('total_amount');
        }

        return 0;
    }

    // =================== QUERY OPTIMIZATION METHODS ===================

    /**
     * ດຶງທ່ານໝໍພ້ອມຂໍ້ມູນການເຮັດວຽກ
     */
    public static function getDoctorsWithWorkload()
    {
        return static::doctors()
            ->active()
            ->withCount([
                'performedTreatments as active_treatments_count' => function ($query) {
                    $query->whereNotIn('status', [
                        Treatment::STATUS_COMPLETED,
                        Treatment::STATUS_CANCELLED
                    ]);
                },
                'assignedQueues as today_queues_count' => function ($query) {
                    $query->whereDate('queue_date', today())
                        ->whereNotIn('queue_status', ['Completed', 'Cancelled']);
                }
            ])
            ->get();
    }

    /**
     * ດຶງພະນັກງານທີ່ມີປະສົບການກັບບໍລິການສະເພາະ
     */
    public static function getExperiencedStaffForService(string $serviceCategory)
    {
        return static::active()
            ->whereJsonContains('specializations', $serviceCategory)
            ->orWhere(function ($query) use ($serviceCategory) {
                // ຖ້າບໍ່ມີຄວາມຊ່ຽວຊານສະເພາະ ແຕ່ມີບົດບາດທີ່ເໝາະສົມ
                switch ($serviceCategory) {
                    case 'ຊ່າງແລັບ':
                        $query->whereIn('role', ['nurse', 'technician']);
                        break;
                    case 'ແພດທົ່ວໄປ':
                        $query->where('role', 'doctor');
                        break;
                }
            })
            ->get();
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