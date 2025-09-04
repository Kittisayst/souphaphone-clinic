<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QueueService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_id',
        'service_id',
        'added_by',
        'service_status',
        'priority_order',
        'assigned_to',
        'scheduled_at',
        'started_at',
        'completed_at',
        'notes'
    ];

    protected $casts = [
        'priority_order' => 'integer',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($record) {
            \Log::info('Boot method triggered', [
                'added_by_before' => $record->added_by,
                'auth_id' => auth()->id(),
                'auth_check' => auth()->check()
            ]);

            if (empty($record->added_by) && auth()->check()) {
                $record->added_by = auth()->id();
                \Log::info('Set added_by in boot', ['added_by' => $record->added_by]);
            }

            if (empty($record->priority_order)) {
                $lastPriority = static::where('queue_id', $record->queue_id)
                    ->max('priority_order') ?? 0;
                $record->priority_order = $lastPriority + 1;
            }
        });
    }

    // =================== RELATIONSHIPS ===================

    // ຄິວ
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    // ບໍລິການ
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // ຜູ້ເພີ່ມບໍລິການ
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    // ຜູ້ຖືກມອບໝາຍ
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // ການປິ່ນປົວ
    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    // ການປິ່ນປົວປັດຈຸບັນ
    public function activeTreatment()
    {
        return $this->hasOne(Treatment::class)->where('status', 'In_Progress');
    }

    // ຜົນການກວດ Lab
    public function labs()
    {
        return $this->hasMany(Lab::class);
    }

    // ຼົນການກວດລ່າສຸດ
    public function latestLab()
    {
        return $this->hasOne(Lab::class)->latest();
    }

    // =================== SCOPES ===================

    // ຕາມສະຖານະ
    public function scopeByStatus($query, $status)
    {
        return $query->where('service_status', $status);
    }

    // ທີ່ຍັງບໍ່ສຳເລັດ
    public function scopePending($query)
    {
        return $query->whereIn('service_status', ['Added', 'Scheduled', 'In_Progress']);
    }

    // ທີ່ສຳເລັດແລ້ວ
    public function scopeCompleted($query)
    {
        return $query->where('service_status', 'Completed');
    }

    // ຈັດລຳດັບຕາມຄວາມສຳຄັນ
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority_order');
    }

    // =================== ACCESSORS ===================

    // ສະຖານະເປັນພາສາລາວ
    public function getStatusLaoAttribute()
    {
        $statuses = [
            'Added' => 'ເພີ່ມແລ້ວ',
            'Scheduled' => 'ນັດເວລາແລ້ວ',
            'In_Progress' => 'ກຳລັງເຮັດ',
            'Completed' => 'ສຳເລັດ',
            'Cancelled' => 'ຍົກເລີກ'
        ];

        return $statuses[$this->service_status] ?? $this->service_status;
    }

    // ໄລຍະເວລາທີ່ໃຊ້
    public function getDurationAttribute()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }
        return null;
    }
}