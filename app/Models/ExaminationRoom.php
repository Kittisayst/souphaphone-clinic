<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExaminationRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_name',
        'room_code',
        'room_type',
        'status',
        'current_patient_id',
        'capacity',
        'equipment',
        'description',
        'notes',
        'is_active',
        'hourly_rate',
    ];

    protected function casts(): array
    {
        return [
            'equipment' => 'array',
            'is_active' => 'boolean',
            'hourly_rate' => 'decimal:2',
        ];
    }

    /**
     * Boot method ສຳຫລັບສ້າງລະຫັດຫ້ອງອັດຕະໂນມັດ
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($room) {
            if (empty($room->room_code)) {
                $room->room_code = self::generateRoomCode();
            }
        });

        // Auto update status when patient changes
        static::updating(function ($room) {
            if ($room->isDirty('current_patient_id')) {
                if ($room->current_patient_id) {
                    $room->status = 'occupied';
                } else {
                    $room->status = 'available';
                }
            }
        });
    }

    /**
     * ສ້າງລະຫັດຫ້ອງອັດຕະໂນມັດ (R001, R002...)
     */
    public static function generateRoomCode(): string
    {
        $lastRoom = self::orderBy('id', 'desc')->first();
        
        if (!$lastRoom) {
            return 'R001';
        }

        // ດຶງເອົາເລກຈາກລະຫັດຫ້ອງຄັ້ງລ່າສຸດ
        $lastNumber = intval(substr($lastRoom->room_code, 1));
        $newNumber = $lastNumber + 1;

        return 'R' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * ຄວາມສຳພັນກັບຄົນໄຂ້ປະຈຸບັນ
     */
    public function currentPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'current_patient_id');
    }

    /**
     * ຄວາມສຳພັນກັບການກວດທັງໝົດ
     */
    public function medicalExaminations(): HasMany
    {
        return $this->hasMany(MedicalExamination::class, 'room_id');
    }

    /**
     * ຄວາມສຳພັນກັບຄິວທັງໝົດ
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'room_id');
    }

    /**
     * ການກວດປະຈຸບັນ
     */
    public function currentExamination()
    {
        return $this->medicalExaminations()
            ->whereIn('status', ['pending', 'in_progress'])
            ->with(['patient', 'service'])
            ->latest()
            ->first();
    }

    /**
     * ຄິວປະຈຸບັນ
     */
    public function currentQueue()
    {
        return $this->queues()
            ->whereIn('status', ['called', 'in_progress'])
            ->with(['patient'])
            ->latest()
            ->first();
    }

    /**
     * ກວດສອບສະຖານະ
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    public function isUnderMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * ກວດສອບປະເພດຫ້ອງ
     */
    public function isGeneralRoom(): bool
    {
        return $this->room_type === 'general';
    }

    public function isSpecialistRoom(): bool
    {
        return $this->room_type === 'specialist';
    }

    public function isLaboratory(): bool
    {
        return $this->room_type === 'laboratory';
    }

    public function isUltrasoundRoom(): bool
    {
        return $this->room_type === 'ultrasound';
    }

    public function isXRayRoom(): bool
    {
        return $this->room_type === 'x_ray';
    }

    public function isDentalRoom(): bool
    {
        return $this->room_type === 'dental';
    }

    /**
     * ສະຖານະຫ້ອງເປັນພາສາລາວ
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available' => 'ວ່າງ',
            'occupied' => 'ມີຄົນໄຂ້',
            'maintenance' => 'ບຳລຸງຮັກສາ',
            'closed' => 'ປິດ',
            default => $this->status,
        };
    }

    /**
     * ປະເພດຫ້ອງເປັນພາສາລາວ
     */
    public function getRoomTypeLabelAttribute(): string
    {
        return match ($this->room_type) {
            'general' => 'ຫ້ອງກວດທົ່ວໄປ',
            'specialist' => 'ຫ້ອງກວດພິເສດ',
            'laboratory' => 'ຫ້ອງກວດເລືອດ',
            'ultrasound' => 'ຫ້ອງ Ultrasound',
            'x_ray' => 'ຫ້ອງ X-Ray',
            'dental' => 'ຫ້ອງກວດຟັນ',
            default => $this->room_type,
        };
    }

    /**
     * ສີສະຖານະ
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'available' => 'success',
            'occupied' => 'warning',
            'maintenance' => 'info',
            'closed' => 'danger',
            default => 'gray',
        };
    }

    /**
     * ສີປະເພດຫ້ອງ
     */
    public function getRoomTypeColorAttribute(): string
    {
        return match ($this->room_type) {
            'general' => 'primary',
            'specialist' => 'warning',
            'laboratory' => 'info',
            'ultrasound' => 'success',
            'x_ray' => 'danger',
            'dental' => 'purple',
            default => 'gray',
        };
    }

    /**
     * ມີອຸປະກອນຫຍັງແດ່
     */
    public function hasEquipment(string $equipment): bool
    {
        return in_array($equipment, $this->equipment ?? []);
    }

    /**
     * ເພີ່ມອຸປະກອນ
     */
    public function addEquipment(string $equipment): void
    {
        $equipments = $this->equipment ?? [];
        if (!in_array($equipment, $equipments)) {
            $equipments[] = $equipment;
            $this->equipment = $equipments;
            $this->save();
        }
    }

    /**
     * ລົບອຸປະກອນ
     */
    public function removeEquipment(string $equipment): void
    {
        $equipments = $this->equipment ?? [];
        $key = array_search($equipment, $equipments);
        if ($key !== false) {
            unset($equipments[$key]);
            $this->equipment = array_values($equipments);
            $this->save();
        }
    }

    /**
     * ມອບໝາຍຄົນໄຂ້ເຂົ້າຫ້ອງ
     */
    public function assignPatient(Patient $patient): bool
    {
        if ($this->isAvailable()) {
            $this->update([
                'current_patient_id' => $patient->id,
                'status' => 'occupied'
            ]);
            return true;
        }
        return false;
    }

    /**
     * ປ່ອຍຄົນໄຂ້ອອກຈາກຫ້ອງ
     */
    public function releasePatient(): bool
    {
        if ($this->isOccupied()) {
            $this->update([
                'current_patient_id' => null,
                'status' => 'available'
            ]);
            return true;
        }
        return false;
    }

    /**
     * ເວລາທີ່ຄົນໄຂ້ຢູ່ໃນຫ້ອງ
     */
    public function getOccupiedTimeAttribute(): ?int
    {
        if (!$this->currentExamination) {
            return null;
        }

        return $this->currentExamination->started_at
            ? $this->currentExamination->started_at->diffInMinutes(now())
            : null;
    }

    /**
     * ສະຖິຕິການໃຊ້ງານຫ້ອງ
     */
    public function getUsageStatsToday(): array
    {
        $examinations = $this->medicalExaminations()
            ->whereDate('examination_date', today())
            ->get();

        return [
            'total_examinations' => $examinations->count(),
            'completed_examinations' => $examinations->where('status', 'completed')->count(),
            'total_time_used' => $examinations->sum('duration_in_minutes'),
            'average_time_per_exam' => $examinations->count() > 0 
                ? round($examinations->avg('duration_in_minutes'), 1) 
                : 0,
        ];
    }

    /**
     * Scope ສຳຫລັບຄົ້ນຫາ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('room_type', $type);
    }

    public function scopeGeneral($query)
    {
        return $query->where('room_type', 'general');
    }

    public function scopeSpecialist($query)
    {
        return $query->where('room_type', 'specialist');
    }

    public function scopeLaboratory($query)
    {
        return $query->where('room_type', 'laboratory');
    }

    public function scopeWithEquipment($query, $equipment)
    {
        return $query->whereJsonContains('equipment', $equipment);
    }

    /**
     * ຄ່າໃຊ້ຈ່າຍຫ້ອງຕໍ່ເວລາ
     */
    public function calculateRoomCost(int $minutes): float
    {
        if (!$this->hourly_rate) {
            return 0;
        }

        $hours = $minutes / 60;
        return round($this->hourly_rate * $hours, 2);
    }
}