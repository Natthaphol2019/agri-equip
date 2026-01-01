<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Staff extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // ✅ กำหนดชื่อตาราง (Table Name)
    // กรณีที่ 1: ถ้า Staff ใช้ตารางเดียวกับ User ปกติ (Login รวมกัน) ให้ใช้ 'users'
    // กรณีที่ 2: ถ้าแยกตาราง Staff ต่างหาก ให้เปลี่ยนเป็น 'staffs' หรือชื่อตารางของคุณ
    protected $table = 'users'; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',     // เช่น 'admin', 'staff'
        'phone',    // เบอร์โทร (ถ้ามี)
        'status',   // สถานะ (active, inactive)
        'avatar',   // รูปโปรไฟล์ (ถ้ามี)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // =========================================================================
    // 🔗 RELATIONSHIPS (ความสัมพันธ์)
    // =========================================================================

    /**
     * งานที่ได้รับมอบหมาย (Bookings)
     * เชื่อมโยงกับ Model Booking ผ่านคอลัมน์ assigned_staff_id
     */
    public function jobs()
    {
        return $this->hasMany(Booking::class, 'assigned_staff_id');
    }

    /**
     * ดึงเฉพาะงานที่กำลังทำอยู่ (Active Jobs)
     * เอาไว้เรียกใช้แบบ $staff->activeJobs
     */
    public function activeJobs()
    {
        return $this->jobs()->whereIn('status', ['scheduled', 'in_progress']);
    }

    /**
     * ประวัติงานที่ทำเสร็จแล้ว
     */
    public function completedJobs()
    {
        return $this->jobs()->whereIn('status', ['completed', 'completed_pending_approval']);
    }
}