<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    // ระบุชื่อตารางให้ชัดเจน (บางที Laravel อาจหาเป็น equipments เติม s)
    protected $table = 'equipment';

    protected $fillable = [
        'equipment_code',
        'name',
        'type',
        'registration_number',
        'current_hours',
        'maintenance_hour_threshold',
        'hourly_rate',
        'current_status',
        'image_path'
    ];

    // ความสัมพันธ์กับใบซ่อม (ใช้ในหน้า Maintenance)
    public function activeMaintenance()
    {
        return $this->hasOne(MaintenanceLog::class)->whereNull('completion_date')->latest();
    }
    public function getMaintenanceStatusAttribute()
    {
        // ถ้าไม่ได้ตั้งค่าซ่อมบำรุงไว้ ให้ถือว่าปกติ
        if (!$this->maintenance_hour_threshold) {
            return 'ok';
        }

        // คำนวณชั่วโมงที่เหลือ
        $remaining = $this->maintenance_hour_threshold - $this->current_hours;

        if ($remaining <= 0) {
            return 'overdue'; // 🔴 เลยกำหนดแล้ว (ใช้เกิน)
        }

        if ($remaining <= 10) {
            return 'soon'; // 🟡 เหลือวิ่งได้อีกไม่ถึง 10 ชม. (เตือนล่วงหน้า)
        }

        return 'ok'; // 🟢 ปกติ
    }
}