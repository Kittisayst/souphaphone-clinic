<?php
// app/Models/Queue.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Queue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'queue_number',
        'waiting_number',
        'queue_date',
        'queue_status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'queue_date' => 'date',
    ];

    // ======================== CONSTANTS ========================

    public const STATUSES = [
        'Registered' => '1. ລົງທະບຽນແລ້ວ',
        'Vital_Checked' => '2. ກວດ vital signs ແລ້ວ',
        'With_Doctor' => '3. ຢູ່ກັບທ່ານໝໍ',
        'Waiting_Test_Results' => '4. ລໍຖ້າຜົນກວດ',
        'Results_Ready' => '5a. ຜົນກວດພ້ອມ',
        'Ready_For_Payment' => '5b. ພ້ອມຈ່າຍເງິນ',
        'Completed' => '6. ສຳເລັດ',
        'Cancelled' => 'ຍົກເລີກ',
    ];

    public const PRIORITIES = [
        'Normal' => 'ປົກກະຕິ',
        'Urgent' => 'ຮີບ',
        'Emergency' => 'ສຸກເສີນ',
    ];

    // ======================== RELATIONSHIPS ========================

}