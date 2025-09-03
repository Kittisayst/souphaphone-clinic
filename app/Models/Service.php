<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_code',
        'service_name',
        'service_category',
        'base_price',
        'description',
        'duration_minutes',
        'requires_room',
        'room_type_required',
        'template_lab',
        'has_lab_result'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'requires_room' => 'boolean',
        'has_lab_result' => 'boolean',
        'template_lab' => 'array',
    ];

    // =================== RELATIONSHIPS ===================

    // การเลือกบริการในคิว
    public function queueServices()
    {
        return $this->hasMany(QueueService::class);
    }

    // คิวที่เลือกบริการนี้
    public function queues()
    {
        return $this->belongsToMany(Queue::class, 'queue_services')
            ->withPivot(['service_status', 'priority_order', 'added_by_id'])
            ->withTimestamps();
    }

    // =================== SCOPES ===================

    // บริการที่ใช้งานอยู่
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    // บริการตามประเภท
    public function scopeByCategory($query, $category)
    {
        return $query->where('service_category', $category);
    }

    // บริการที่ต้องการห้อง
    public function scopeRequiresRoom($query)
    {
        return $query->where('requires_room', true);
    }

    // บริการที่มีผลตรวจ
    public function scopeWithLabResult($query)
    {
        return $query->where('has_lab_result', true);
    }

    // =================== ACCESSORS ===================

    // แสดงชื่อพร้อมรหัส
    public function getDisplayNameAttribute()
    {
        return "{$this->service_code} - {$this->service_name}";
    }

    // ราคาที่จัดรูปแบบ
    public function getFormattedPriceAttribute()
    {
        return number_format((int) $this->base_price, 0) . ' ກີບ';
    }
}