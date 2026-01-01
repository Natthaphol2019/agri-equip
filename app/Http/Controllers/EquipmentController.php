<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;

class EquipmentController extends Controller
{
    // ดึงรายชื่อเครื่องจักรทั้งหมด + สถานะการซ่อมล่าสุด
    public function index()
    {
        // with('activeMaintenance') คือการดึงข้อมูลใบแจ้งซ่อมติดมาด้วย (ถ้ามี)
        $equipments = Equipment::with('activeMaintenance')->get();
        return response()->json($equipments);
    }
    
    // (เผื่อไว้) สร้างเครื่องจักรใหม่
    public function store(Request $request)
    {
        $data = $request->validate([
            'equipment_code' => 'required|unique:equipment',
            'name' => 'required',
            'type' => 'required',
            'maintenance_hour_threshold' => 'required|numeric'
        ]);
        
        return Equipment::create($data);
    }
}