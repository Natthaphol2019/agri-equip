<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\FuelLog;
use Illuminate\Support\Facades\Auth;

class FuelController extends Controller
{
    public function create()
    {
        $equipments = Equipment::whereIn('current_status', ['available', 'in_use'])->get();
        return view('staff.fuel.create', compact('equipments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // ✅ แก้ไข: equipment ต้องไม่มี s (ชื่อตารางใน DB)
            'equipment_id' => 'required|exists:equipment,id',
            'amount' => 'required|numeric|min:1',
            'image' => 'required|image|max:10240',
            'liters' => 'nullable|numeric',
            'mileage' => 'nullable|numeric',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('fuel_receipts', 'public');
        }

        FuelLog::create([
            'equipment_id' => $request->equipment_id,
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'liters' => $request->liters,
            'mileage' => $request->mileage,
            'image_path' => $imagePath,
            'note' => $request->note,
            'refill_date' => now(),
        ]);

        return back()->with('success', 'บันทึกการเติมน้ำมันเรียบร้อย! ⛽');
    }
}