<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_id',
        'service_total',
        'medicine_total',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'final_amount',
        'payment_method',
        'payment_status',
        'paid_at',
        'received_by_id',
        'receipt_number',
        'payment_details',
        'notes'
    ];

    protected $casts = [
        'service_total' => 'decimal:2',
        'medicine_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_details' => 'array',
    ];

    // =================== RELATIONSHIPS ===================

    // ຄິວ
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    // ຄົນໄຂ້ (ຜ່ານ queue)
    public function patient()
    {
        return $this->hasOneThrough(Patient::class, Queue::class, 'id', 'id', 'queue_id', 'patient_id');
    }

    // ຜູ້ຮັບເງິນ
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_id');
    }

    // ລາຍລະອຽດບໍລິການ (ຜ່ານ queue)
    public function serviceDetails()
    {
        return $this->hasManyThrough(
            QueueService::class,
            Queue::class,
            'id',           // queues.id
            'queue_id',     // queue_services.queue_id
            'queue_id',     // payments.queue_id  
            'id'            // queues.id
        )->with('service');
    }

    // ລາຍລະອຽດຢາ (ຜ່ານ queue)
    public function medicineDetails()
    {
        return $this->hasManyThrough(
            Prescription::class,
            Queue::class,
            'id',           // queues.id
            'queue_id',     // prescriptions.queue_id
            'queue_id',     // payments.queue_id
            'id'            // queues.id
        )->with('medicine');
    }

    // =================== SCOPES ===================

    // ຕາມສະຖານະ
    public function scopeByStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    // ທີ່ຈ່າຍແລ້ວ
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'Paid');
    }

    // ລໍຖ້າຈ່າຍ
    public function scopePending($query)
    {
        return $query->where('payment_status', 'Pending');
    }

    // ວັນນີ້
    public function scopeToday($query)
    {
        return $query->whereDate('paid_at', today());
    }

    // ໃນຊ່ວງເວລາ
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('paid_at', [$startDate, $endDate]);
    }

    // =================== ACCESSORS ===================

    // ສະຖານະເປັນພາສາລາວ
    public function getStatusLaoAttribute()
    {
        $statuses = [
            'Pending' => 'ລໍຖ້າຈ່າຍ',
            'Paid' => 'ຈ່າຍແລ້ວ',
            'Refunded' => 'ຄືນເງິນແລ້ວ'
        ];

        return $statuses[$this->payment_status] ?? $this->payment_status;
    }

    // ວິທີຈ່າຍເງິນເປັນພາສາລາວ
    public function getPaymentMethodLaoAttribute()
    {
        $methods = [
            'Cash' => 'ເງິນສົດ',
            'Card' => 'ບັດເຄຣດິດ',
            'Transfer' => 'ໂອນເງິນ',
            'Insurance' => 'ປະກັນສຸຂະພາບ'
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    // ຈຳນວນເງິນທີ່ຈັດຮູບແບບ
    public function getFormattedFinalAmountAttribute()
    {
        return number_format((int) $this->final_amount, 0) . ' ກີບ';
    }

    // ການຄິດໄລ່ສ່ວນຫຼຸດ %
    public function getDiscountPercentageAttribute()
    {
        if ($this->subtotal > 0) {
            return round(($this->discount_amount / $this->subtotal) * 100, 2);
        }
        return 0;
    }
}
