<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_code',
        'room_name',
        'room_type',
        'capacity',
        'equipment_list',
        'is_available',
        'current_user_id',
        'notes'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'capacity' => 'integer',
    ];

    // =================== RELATIONSHIPS ===================

    // ຜູ້ໃຊ້ຫ້ອງປັດຈຸບັນ
    public function currentUser()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    // ການປິ່ນປົວທີ່ໃຊ້ຫ້ອງນີ້
    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    // ການປິ່ນປົວທີ່ກຳລັງດຳເນີນການ
    public function activeTreatments()
    {
        return $this->hasMany(Treatment::class)->where('status', 'In_Progress');
    }

    // =================== SCOPES ===================

    // ຫ້ອງວ່າງ
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    // ຫ້ອງຕາມປະເພດ
    public function scopeByType($query, $type)
    {
        return $query->where('room_type', $type);
    }

    // =================== METHODS ===================

    // ເຊັດຫ້ອງວ່າງ
    public function setAvailable()
    {
        $this->update([
            'is_available' => true,
            'current_user_id' => null
        ]);
    }

    // ຈອງຫ້ອງ
    public function occupyBy($userId)
    {
        $this->update([
            'is_available' => false,
            'current_user_id' => $userId
        ]);
    }
}