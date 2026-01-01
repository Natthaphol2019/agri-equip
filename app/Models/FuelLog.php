<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelLog extends Model
{
    use HasFactory;

    // ✅ ต้องมีบรรทัดนี้ เพื่ออนุญาตให้บันทึกข้อมูลได้
    protected $fillable = [
        'equipment_id',
        'user_id',
        'amount',
        'liters',
        'mileage',
        'image_path',
        'note',
        'refill_date',
    ];

    // ความสัมพันธ์กับตาราง Equipment (Optional: เอาไว้ดึงชื่อรถ)
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    // ความสัมพันธ์กับ User (Optional: เอาไว้ดูว่าใครเติม)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}