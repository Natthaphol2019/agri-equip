<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\MaintenanceLog;
use App\Services\LineMessagingApi;
use App\Services\PromptPayService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffJobController extends Controller
{
    /**
     * 🟢 1. หน้าแสดงรายการงานทั้งหมด (My Jobs)
     */
    public function index()
    {
        $myJobs = Booking::with(['customer', 'equipment'])
            ->where('assigned_staff_id', Auth::id())
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('scheduled_start', 'asc')
            ->get();

        $qrCodes = [];
        $promptPayNo = env('PROMPTPAY_NUMBER');

        foreach ($myJobs as $job) {
            if ($job->status == 'in_progress') {
                $balance = $job->total_price - $job->deposit_amount;
                if ($balance > 0 && $promptPayNo) {
                    try {
                        $qrCodes[$job->id] = PromptPayService::generatePayload($promptPayNo, $balance);
                    } catch (\Exception $e) { }
                }
            }
        }

        $historyJobs = Booking::with(['customer'])
            ->where('assigned_staff_id', Auth::id())
            ->whereIn('status', ['completed', 'completed_pending_approval'])
            ->latest('actual_end')
            ->take(5)
            ->get();

        $equipments = Equipment::where('deleted_at', null)->get();

        return view('staff.jobs.index', compact('myJobs', 'historyJobs', 'equipments', 'qrCodes'));
    }

    /**
     * 🟢 2. หน้ารายละเอียดงาน (Job Detail)
     */
    public function show($id)
    {
        $job = Booking::with(['customer', 'equipment'])->findOrFail($id);
        
        if ($job->assigned_staff_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $balance = $job->total_price - $job->deposit_amount;
        $qrData = null;
        $promptPayNo = env('PROMPTPAY_NUMBER');

        if ($balance > 0 && $promptPayNo) {
            $qrData = PromptPayService::generatePayload($promptPayNo, $balance);
        }

        return view('staff.jobs.show', compact('job', 'qrData', 'balance'));
    }

    /**
     * 🟢 3. เริ่มงาน (AJAX)
     */
    public function startWork(Request $request, $id)
    {
        $job = Booking::with('equipment')
            ->where('id', $id)
            ->where('assigned_staff_id', Auth::id())
            ->firstOrFail();

        $job->update([
            'status' => 'in_progress',
            'actual_start' => Carbon::now(),
        ]);

        try {
            $msg = "▶️ เริ่มปฏิบัติงาน!\n📄 Job: {$job->job_number}\n👤 Staff: " . Auth::user()->name;
            LineMessagingApi::send($msg);
        } catch (\Exception $e) { }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'เริ่มงานแล้ว! ลุยเลย ✌️',
                'job_id' => $job->id,
                'new_status' => 'in_progress'
            ]);
        }

        return back()->with('success', 'เริ่มงานแล้ว! สู้ๆ ครับ ✌️');
    }

    /**
     * 🟢 4. จบงาน (AJAX)
     */
    public function finishWork(Request $request, $id)
    {
        $job = Booking::with('equipment')
            ->where('id', $id)
            ->where('assigned_staff_id', Auth::id())
            ->firstOrFail();

        $balance = $job->total_price - $job->deposit_amount;

        $request->validate([
            'job_image' => 'required|image|max:10240',
            'payment_proof' => ($balance > 0) ? 'required|image|max:10240' : 'nullable|image|max:10240',
            'note' => 'nullable|string',
        ]);

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payments', 'public');
        }

        $imagePath = null;
        if ($request->hasFile('job_image')) {
            $imagePath = $request->file('job_image')->store('job_evidence', 'public');
        }

        $job->update([
            'status' => 'completed_pending_approval',
            'actual_end' => Carbon::now(),
            'image_path' => $imagePath,
            'payment_proof' => $paymentProofPath,
            'payment_status' => $paymentProofPath ? 'paid' : $job->payment_status,
            'note' => $request->note,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'ส่งงานเรียบร้อย! ขอบคุณครับ 🙏',
                'job_id' => $job->id,
                'new_status' => 'completed'
            ]);
        }

        return back()->with('success', "บันทึกงานเรียบร้อย!");
    }

    /**
     * 🟢 5. Dashboard พนักงาน
     */
    public function dashboard()
    {
        $userId = Auth::id();

        $counts = [
            'in_progress' => Booking::where('assigned_staff_id', $userId)->where('status', 'in_progress')->count(),
            'scheduled'   => Booking::where('assigned_staff_id', $userId)->where('status', 'scheduled')->count(),
            'completed'   => Booking::where('assigned_staff_id', $userId)
                                    ->whereIn('status', ['completed', 'completed_pending_approval'])
                                    ->whereMonth('actual_end', Carbon::now()->month)
                                    ->whereYear('actual_end', Carbon::now()->year)
                                    ->count(),
        ];

        $urgentJobs = Booking::with(['customer', 'equipment'])
            ->where('assigned_staff_id', $userId)
            ->where(function($q) {
                $q->where('status', 'in_progress')
                  ->orWhere(function($sub) {
                      $sub->where('status', 'scheduled')
                          ->whereDate('scheduled_start', Carbon::today());
                  });
            })
            ->orderByRaw("FIELD(status, 'in_progress', 'scheduled')") 
            ->orderBy('scheduled_start', 'asc')
            ->limit(10)
            ->get();

        return view('staff.dashboard', compact('counts', 'urgentJobs'));
    }

    /**
     * 🟢 6. แจ้งซ่อมทั่วไป (จากหน้าแรก Staff หรือปุ่มด่วน)
     */
    public function reportGeneral(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'description' => 'required|string',
            'image' => 'nullable|image|max:10240'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('maintenance_reports', 'public');
        }

        // 1. สร้าง Log แจ้งซ่อม
        MaintenanceLog::create([
            'equipment_id' => $request->equipment_id,
            'reported_by' => Auth::id(),
            'description' => $request->description,
            'image_path' => $imagePath,
            'maintenance_date' => now(),
            'status' => 'pending', // รอแอดมินรับเรื่อง
            'cost' => 0
        ]);

        // 2. อัปเดตสถานะรถเป็น 'maintenance' (ซ่อม) ทันที
        Equipment::where('id', $request->equipment_id)->update([
            'current_status' => 'maintenance'
        ]);

        return back()->with('success', 'แจ้งซ่อมเรียบร้อย! รถถูกเปลี่ยนสถานะเป็น "กำลังซ่อม"');
    }

    /**
     * 🟢 7. หน้าประวัติการแจ้งซ่อมของฉัน
     */
    public function maintenanceIndex() {
        $myMaintenanceLogs = MaintenanceLog::with('equipment')
            ->where('reported_by', Auth::id())
            ->latest()
            ->limit(20)
            ->get();
            
        return view('staff.maintenance.index', compact('myMaintenanceLogs'));
    }

    /**
     * 🟢 8. แสดงฟอร์มแจ้งซ่อม (ถ้ามีหน้าแยก)
     */
    public function createReport() {
        $equipments = Equipment::all();
        return view('staff.maintenance.create', compact('equipments'));
    }

    /**
     * 🟢 9. บันทึกจากหน้าฟอร์มแจ้งซ่อมแยก (ถ้ามี)
     */
    public function storeReport(Request $request) { 
        return $this->reportGeneral($request); // ใช้ Logic เดียวกับ reportGeneral
    }
    
    // ไว้เผื่อแจ้งปัญหาเฉพาะงาน (ถ้ามีปุ่มแจ้งในหน้ารายละเอียดงาน)
    public function reportIssue(Request $request, $jobId) { 
        // Logic คล้าย reportGeneral แต่อาจจะผูกกับ Job ID ด้วย (ถ้า Table รองรับ)
        return $this->reportGeneral($request);
    }
}