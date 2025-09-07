<?php
// app/Models/MedicationInstruction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;


class Medication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_id',
        'medicine_id',
        'qty',
        'unit_price',
        'total_price',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // ======================== CONSTANTS ========================

    public const DISPENSING_STATUSES = [
        'Prescribed' => 'ສັ່ງແລ້ວ',
        'Dispensed' => 'ຈ່າຍແລ້ວ',
        'Cancelled' => 'ຍົກເລີກ',
    ];

    public const ADMINISTRATION_ROUTES = [
        'ກິນ' => 'ກິນ (ທາງປາກ)',
        'ທາ' => 'ທາ (ທາງຜິວ)',
        'ສັກ' => 'ສັກ (ທາງເສັ້ນເລືອດ)',
        'ຫຍົດ' => 'ຫຍົດ (ຕາ/ຫູ/ດັງ)',
        'ສູດ' => 'ສູດ (ທາງຫາຍໃຈ)',
        'ອື່ນໆ' => 'ອື່ນໆ',
    ];

    // ======================== RELATIONSHIPS ========================

}