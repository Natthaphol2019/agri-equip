<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\MaintenanceLog;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    // 1. หน้า Dashboard รวม
    public function index()
    {
        // รายการรอรับเรื่อง
        $reportedIssues = MaintenanceLog::where('status', 'pending')
            ->with(['equipment'])
            ->orderBy('created_at', 'asc')
            ->get();

        // รถถึงระยะซ่อม
        $needMaintenance = Equipment::whereRaw('current_hours >= maintenance_hour_threshold')
            ->where('current_status', 'available') 
            ->get();

        // กำลังซ่อม
        $inMaintenance = MaintenanceLog::where('status', 'in_progress')
            ->with('equipment')
            ->get();

        // ประวัติย้อนหลัง (10 รายการ)
        $history = MaintenanceLog::where('status', 'completed')
            ->with('equipment')
            ->latest()
            ->take(10)
            ->get();
            
        // ✅ เพิ่มตรงนี้: ส่งข้อมูลรถไปหน้า Index ด้วย (เผื่อใช้ Modal หรือ Dropdown)
        $equipments = Equipment::where('current_status', 'available')->get();

        return view('admin.maintenance.index', compact('reportedIssues', 'needMaintenance', 'inMaintenance', 'history', 'equipments'));
    }

    // 2. แสดงฟอร์มรับเรื่อง (GET)
    public function showAcceptForm($logId)
    {
        $log = MaintenanceLog::with('equipment')->findOrFail($logId);
        return view('admin.maintenance.accept', compact('log'));
    }

    // 3. บันทึกการรับเรื่อง (POST)
    public function accept(Request $request, $logId)
    {
        $request->validate([
            'admin_note' => 'required|string'
        ]);

        $log = MaintenanceLog::findOrFail($logId);
        
        $log->update([
            'status' => 'in_progress',
            'description' => $log->description . " | Admin Note: " . $request->admin_note,
            'maintenance_date' => now(), 
        ]);

        // ล็อกรถ
        $log->equipment->update(['current_status' => 'maintenance']);

        return redirect()->route('admin.maintenance.index')->with('success', 'รับเรื่องเรียบร้อย! รถเข้าสู่สถานะกำลังซ่อม 🛠️');
    }

    // 4. หน้าฟอร์มเปิดใบงานเอง (Create)
    public function create()
    {
        // ดึงรถที่สถานะ Available
        $equipments = Equipment::where('current_status', 'available')->get();
        
        // ถ้าไม่มีรถว่างเลย ให้ส่ง array ว่างไปป้องกัน error (หรือจะดึงทั้งหมดก็ได้)
        if($equipments->isEmpty()) {
             // กรณีอยากดึงรถทั้งหมดมาแสดงแม้ไม่ว่าง (Optional)
             // $equipments = Equipment::all();
        }

        return view('admin.maintenance.create', compact('equipments'));
    }

    // 5. บันทึกเปิดใบงานเอง (Store)
    public function store(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'description' => 'required|string|max:500',
        ]);

        MaintenanceLog::create([
            'equipment_id' => $request->equipment_id,
            'maintenance_date' => now(),
            'description' => $request->description,
            'status' => 'in_progress',
            'cost' => 0,
        ]);

        // ล็อกรถ
        Equipment::where('id', $request->equipment_id)->update([
            'current_status' => 'maintenance'
        ]);

        return redirect()->route('admin.maintenance.index')->with('success', 'ส่งเครื่องจักรเข้าซ่อมเรียบร้อยแล้ว');
    }

    // 6. ส่งเช็คระยะด่วน
    public function start(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);

        MaintenanceLog::create([
            'equipment_id' => $equipment->id,
            'maintenance_date' => now(),
            'description' => $request->description ?? 'ตรวจเช็คตามระยะ (Auto Start)',
            'cost' => 0,
            'status' => 'in_progress'
        ]);

        $equipment->update(['current_status' => 'maintenance']);

        return back()->with('success', "ส่ง {$equipment->name} เข้าซ่อมเรียบร้อย!");
    }

    // 7. จบงานซ่อม
    public function finish(Request $request, $id)
    {
        $request->validate([
            'cost' => 'required|numeric',
            'technician_name' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $log = MaintenanceLog::findOrFail($id);

        $log->update([
            'completion_date' => now(),
            'cost' => $request->cost,
            'technician_name' => $request->technician_name,
            'description' => $log->description . ($request->note ? ' | จบงาน: ' . $request->note : ''),
            'status' => 'completed'
        ]);

        // ปลดล็อกรถ
        $updateData = ['current_status' => 'available'];
        
        if ($request->has('reset_hours')) {
            $updateData['current_hours'] = 0;
        }

        $log->equipment->update($updateData);

        return redirect()->route('admin.maintenance.index')->with('success', 'ซ่อมเสร็จสิ้น! รถพร้อมใช้งานแล้วครับ 🚜💨');
    }
}