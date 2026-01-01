<?php
namespace App\Services;

use App\Models\Equipment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EquipmentService
{
    // ฟังก์ชันสร้างเครื่องจักรใหม่
    public function createEquipment(array $data, ?UploadedFile $image = null)
    {
        // 1. สร้างรหัสเครื่องจักร (Running Number) เช่น EQ-001
        $data['equipment_code'] = $this->generateEquipmentCode();

        // 2. ถ้ามีการอัปโหลดรูป
        if ($image) {
            // บันทึกรูปลง storage/app/public/equipment_images
            $path = $image->store('equipment_images', 'public');
            $data['image_path'] = $path;
        }

        // 3. บันทึกลงฐานข้อมูล
        return Equipment::create($data);
    }

    // ฟังก์ชันช่วย Gen รหัส (Private)
    private function generateEquipmentCode(): string
    {
        // หาตัวล่าสุด
        $latest = Equipment::latest('id')->first();
        if (!$latest) {
            return 'EQ-001';
        }

        // ตัด string เอาเฉพาะตัวเลขมา +1
        // สมมติ EQ-005 -> เอา 005 มา +1 เป็น 6 -> แปลงกลับเป็น EQ-006
        $number = intval(substr($latest->equipment_code, 3)) + 1;
        return 'EQ-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}