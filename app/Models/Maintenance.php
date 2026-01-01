<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Maintenance extends Model
{
    use HasFactory;

    // ชื่อตารางใน Database (ถ้าชื่อตารางคุณเป็น maintenances อยู่แล้ว บรรทัดนี้ไม่ต้องใส่ก็ได้)
    protected $table = 'maintenance_logs';

    protected $fillable = [
        'equipment_id', // รถคันไหน
        'maintenance_type', // ใน SQL คุณใช้ column นี้
        'start_date',   // เริ่มซ่อมวันไหน
        'end_date',     // เสร็จวันไหน
        'description',  // สาเหตุการซ่อม
        'completion_date', // ใน SQL ใช้ completion_date แทน end_date
        'status',       // pending (รอซ่อม), in_progress (กำลังซ่อม), completed (เสร็จแล้ว)
        'cost',         // ค่าใช้จ่าย (ถ้ามี)
    ];

    // แปลงค่าวันที่ให้เป็น Object อัตโนมัติ (จะได้ใช้คำสั่งเปรียบเทียบวันได้ง่ายๆ)
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // ความสัมพันธ์: การซ่อมนี้เป็นของรถคันไหน?
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}