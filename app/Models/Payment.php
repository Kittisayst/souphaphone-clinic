<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;


class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_id',
        'receipt_number',
        'discount_amount',
        'tax_amount',
        'total_queue_services',
        'total_medication',
        'total_amount',
        'payment_method',
        'payment_status',
        'paid_amount',
        'change_amount',
        'cashier_id',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_queue_services' => 'decimal:2',
        'total_medication' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // ======================== CONSTANTS ========================

    public const PAYMENT_METHODS = [
        'Cash' => 'ເງິນສົດ',
        'Transfer' => 'ໂອນເງິນ',
        'Card' => 'ບັດເຄຣດິດ',
        'Insurance' => 'ປະກັນໄພ',
    ];

    public const PAYMENT_STATUSES = [
        'Pending' => 'ລໍຖ້າ',
        'Paid' => 'ຈ່າຍແລ້ວ',
        'Refunded' => 'ຄືນເງິນແລ້ວ',
        'Cancelled' => 'ຍົກເລີກ',
    ];

    // ======================== RELATIONSHIPS ========================

}