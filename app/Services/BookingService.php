<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\Leave;
use Carbon\Carbon;
use Exception;

class BookingService
{
    // --- ฟังก์ชันหลัก: สร้างการจอง ---
    public function createBooking(array $data)
    {
        $start = $data['scheduled_start'];
        $end = $data['scheduled_end'];
        $equipmentId = $data['equipment_id'];
        $staffId = $data['assigned_staff_id'] ?? null; 

        // 1. ตรวจสอบเครื่องจักร (ใช้ฟังก์ชันที่แก้แล้วด้านล่าง)
        $equipmentCheck = $this->checkEquipmentAvailability($equipmentId, $start, $end);
        if (!$equipmentCheck['available']) {
            throw new Exception($equipmentCheck['message']);
        }

        // 2. ตรวจสอบพนักงาน
        if ($staffId) {
            $staffCheck = $this->checkStaffAvailability($staffId, $start, $end);
            if (!$staffCheck['available']) {
                throw new Exception($staffCheck['message']);
            }
        }

        // 3. สร้างเลข Job Number
        $data['job_number'] = $this->generateJobNumber();
        
        // 4. กำหนดสถานะเริ่มต้น
        $data['status'] = 'scheduled'; 

        // 5. บันทึก
        return Booking::create($data);
    }

    // --- เช็คเครื่องจักร (แก้จุดที่ Error บ่อย) ---
    public function checkEquipmentAvailability($equipmentId, $start, $end)
    {
        $equipment = Equipment::find($equipmentId);
        if (!$equipment) {
            return ['available' => false, 'message' => 'ไม่พบข้อมูลเครื่องจักร'];
        }

        // ✅ แก้ตรงนี้ (สำคัญ): ดึงค่าจาก Enum Object ออกมาเป็น String ก่อน
        $status = $equipment->current_status;
        
        // เช็คว่าถ้าเป็น Object (Enum) ให้ดึงค่า ->value ออกมา
        if (is_object($status) && property_exists($status, 'value')) {
            $status = $status->value;
        } elseif (is_object($status)) {
            // กรณีเป็น Object อื่นๆ ที่ไม่มี value ให้แปลงเป็น string หรือใช้ค่า default
             $status = (string)$status; 
        }

        // ตอนนี้ $status เป็นตัวหนังสือ (String) แล้ว เอาไปเทียบได้ไม่ Error
        if ($status !== 'available') {
            return ['available' => false, 'message' => 'เครื่องจักรนี้สถานะไม่พร้อมใช้งาน (' . $status . ')'];
        }

        // เช็คคิวจอง (Booking Collision)
        $isBooked = Booking::where('equipment_id', $equipmentId)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->where('scheduled_start', '<', $end)
                      ->where('scheduled_end', '>', $start);
                });
            })->exists();

        if ($isBooked) {
            return ['available' => false, 'message' => 'ช่วงเวลานี้มีลูกค้าท่านอื่นจองเครื่องจักรแล้ว'];
        }

        // เช็คการซ่อม (Maintenance)
        $isUnderMaintenance = Maintenance::where('equipment_id', $equipmentId)
            ->where(function ($query) use ($start, $end) {
                $query->whereNull('completion_date') // ยังซ่อมไม่เสร็จ (วันจบเป็น NULL)
                      ->orWhere(function ($q) use ($start, $end) {
                          $q->whereNotNull('completion_date') // ซ่อมเสร็จแล้วแต่เวลาชนกัน
                            ->where('created_at', '<', $end)
                            ->where('completion_date', '>', $start);
                      });
            })->exists();

        if ($isUnderMaintenance) {
            return ['available' => false, 'message' => 'เครื่องจักรติดภารกิจซ่อมบำรุงในช่วงเวลานี้'];
        }

        return ['available' => true];
    }

    // --- เช็คพนักงาน ---
    public function checkStaffAvailability($staffId, $start, $end)
    {
        // เช็คคิวงานขับรถ
        $isBusy = Booking::where('assigned_staff_id', $staffId)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($query) use ($start, $end) {
                $query->where('scheduled_start', '<', $end)
                      ->where('scheduled_end', '>', $start);
            })->exists();

        if ($isBusy) {
            return ['available' => false, 'message' => 'พนักงานรายนี้ติดภารกิจขับรถคันอื่นในช่วงเวลาดังกล่าว'];
        }

        // เช็ควันลา (Leave)
        if (class_exists(Leave::class)) {
            $isOnLeave = Leave::where('user_id', $staffId)
                ->where('status', 'approved') 
                ->where(function ($query) use ($start, $end) {
                    $query->where('start_date', '<', $end)
                          ->where('end_date', '>', $start);
                })->exists();

            if ($isOnLeave) {
                return ['available' => false, 'message' => 'พนักงานลางานในช่วงเวลานั้น'];
            }
        }

        return ['available' => true];
    }

    // --- สร้างเลข Job ---
    private function generateJobNumber()
    {
        $prefix = 'JOB-' . Carbon::now()->format('Ymd') . '-';
        $latest = Booking::where('job_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$latest) {
            return $prefix . '001';
        }
        
        $number = intval(substr($latest->job_number, -3)) + 1;
        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}