<?php
// app/Models/Medicine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medicine_code',
        'medicine_name',
        'generic_name',
        'brand_name',
        'medicine_type',
        'strength',
        'unit',
        'price',
        'wholesale_price',
        'current_stock',
        'minimum_stock',
        'expiry_date',
        'batch_number',
        'dosage_instructions',
        'side_effects',
        'contraindications',
        'manufacturer',
        'supplier',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'current_stock' => 'integer',
        'minimum_stock' => 'integer',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    // ======================== CONSTANTS ========================

    public const MEDICINE_TYPES = [
        'Tablet' => 'ເມັດ',
        'Capsule' => 'ແຄັບຊູນ',
        'Syrup' => 'ນ້ຳຢາ',
        'Injection' => 'ຢາສັກ',
        'Cream' => 'ຄຣີມ',
        'Drops' => 'ຢາຫຍົດ',
        'Spray' => 'ສະເປ',
        'Other' => 'ອື່ນໆ',
    ];

    public const UNITS = [
        'mg' => 'ມິນລິກຣາມ',
        'ml' => 'ມິນລິລິດ',
        'tablet' => 'ເມັດ',
        'capsule' => 'ແຄັບຊູນ',
        'bottle' => 'ຂວດ',
        'tube' => 'ຫລອດ',
        'box' => 'ກ່ອງ',
        'piece' => 'ຊີ້ນ',
    ];

    
}