<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_code',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'birth_date',
        'gender',
        'id_card_number',
        'emergency_contact',
        'allergies',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'emergency_contact' => 'array',
            'allergies' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot method ສຳລັບສ້າງລະຫັດຄົນໄຂ້ອັດຕະໂນມັດ
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->patient_code)) {
                $patient->patient_code = self::generatePatientCode();
            }
        });
    }

    /**
     * ສ້າງລະຫັດຄົນໄຂ້ອັດຕະໂນມັດ (PT001, PT002...)
     */
    public static function generatePatientCode(): string
    {
        $lastPatient = self::orderBy('id', 'desc')->first();
        
        if (!$lastPatient) {
            return 'PT001';
        }

        // ດຶງເອົາເລກຈາກລະຫັດຄົນໄຂ້ຄັ້ງລ່າສຸດ
        $lastNumber = intval(substr($lastPatient->patient_code, 2));
        $newNumber = $lastNumber + 1;

        return 'PT' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * ຊື່ເຕັມຂອງຄົນໄຂ້
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * ອາຍຸຂອງຄົນໄຂ້
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        return Carbon::parse((string)$this->birth_date)->age;
    }

    /**
     * ຄວາມສຳພັນກັບຄິວ
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    /**
     * ຄິວປະຈຸບັນ (ວັນນີ້)
     */
    public function todayQueue()
    {
        return $this->queues()
            ->whereDate('queue_date', today())
            ->whereIn('status', ['waiting', 'called', 'in_progress'])
            ->first();
    }

    /**
     * ຄວາມສຳພັນກັບການກວດ
     */
    public function medicalExaminations(): HasMany
    {
        return $this->hasMany(MedicalExamination::class);
    }

    /**
     * ການກວດຄັ້ງລ່າສຸດ
     */
    public function latestExamination()
    {
        return $this->medicalExaminations()
            ->latest('examination_date')
            ->first();
    }

    /**
     * ຄວາມສຳພັນກັບການຮັກສາ
     */
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    /**
     * ການຮັກສາຄັ້ງລ່າສຸດ
     */
    public function latestTreatment()
    {
        return $this->treatments()
            ->latest('created_at')
            ->first();
    }

    /**
     * ຄວາມສຳພັນກັບໃບເກັບເງິນ
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * ໃບເກັບເງິນທີ່ຍັງບໍ່ໄດ້ຈ່າຍ
     */
    public function unpaidInvoices()
    {
        return $this->invoices()
            ->whereIn('payment_status', ['pending', 'partial']);
    }

    /**
     * ຄວາມສຳພັນກັບການນັດໝາຍ
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * ການນັດໝາຍຄັ້ງຕໍ່ໄປ
     */
    public function nextAppointment()
    {
        return $this->appointments()
            ->where('appointment_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->first();
    }

    /**
     * ກວດສອບວ່າມີການແພ້ຢາບໍ່
     */
    public function hasAllergy(string $medicine): bool
    {
        if (!$this->allergies) {
            return false;
        }

        foreach ($this->allergies as $allergy) {
            if (stripos($allergy['medicine_name'] ?? '', $medicine) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scope ສຳຫລັບຄົ້ນຫາ
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('patient_code', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('id_card_number', 'like', "%{$search}%");
        });
    }

    /**
     * Scope ສຳຫລັບຄົນໄຂ້ທີ່ເປີດໃຊ້ງານ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ສຳຫລັບຄົນໄຂ້ທີ່ມີຄິວວັນນີ້
     */
    public function scopeHasTodayQueue($query)
    {
        return $query->whereHas('queues', function ($q) {
            $q->whereDate('queue_date', today());
        });
    }
}