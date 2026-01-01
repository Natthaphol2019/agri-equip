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

        // สร้าง QR Code ล่วงหน้าสำหรับงานที่กำลังทำอยู่ (ถ้ามี)
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
        
        // ป้องกันไม่ให้ดูงานคนอื่น
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

        // แจ้งเตือน LINE (ถ้ามี)
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
     * 🟢 5. Dashboard พนักงาน (แก้ไขส่วนนี้ที่ทำให้เกิด Error)
     */
    public function dashboard()
    {
        $userId = Auth::id();

        // ✅ 1. ดึงจำนวนงานแต่ละสถานะ (แก้ Error Undefined index)
        $counts = [
            'in_progress' => Booking::where('assigned_staff_id', $userId)->where('status', 'in_progress')->count(),
            'scheduled'   => Booking::where('assigned_staff_id', $userId)->where('status', 'scheduled')->count(),
            // นับเฉพาะงานที่เสร็จในเดือนนี้
            'completed'   => Booking::where('assigned_staff_id', $userId)
                                    ->whereIn('status', ['completed', 'completed_pending_approval'])
                                    ->whereMonth('actual_end', Carbon::now()->month)
                                    ->whereYear('actual_end', Carbon::now()->year)
                                    ->count(),
        ];

        // ✅ 2. ดึงงานด่วน (งานที่กำลังทำ หรือ งานนัดหมายวันนี้)
        $urgentJobs = Booking::with(['customer', 'equipment'])
            ->where('assigned_staff_id', $userId)
            ->where(function($q) {
                $q->where('status', 'in_progress')
                  ->orWhere(function($sub) {
                      $sub->where('status', 'scheduled')
                          ->whereDate('scheduled_start', Carbon::today());
                  });
            })
            // เรียงลำดับ: กำลังทำมาก่อน -> ตามด้วยงานนัดหมายเรียงตามเวลา
            ->orderByRaw("FIELD(status, 'in_progress', 'scheduled')") 
            ->orderBy('scheduled_start', 'asc')
            ->limit(10)
            ->get();

        return view('staff.dashboard', compact('counts', 'urgentJobs'));
    }

    /**
     * 🟢 เมนูอื่นๆ (Maintenance, Report)
     */
    public function maintenanceIndex() {
        return view('staff.maintenance.index', [
            'myMaintenanceLogs' => MaintenanceLog::where('reported_by', Auth::id())->latest()->limit(20)->get()
        ]);
    }

    public function createReport() {
        return view('staff.maintenance.create', ['equipments' => Equipment::where('deleted_at', null)->get()]);
    }

    public function storeReport(Request $request) { 
        // Logic บันทึกแจ้งซ่อม (ถ้ามี)
        return back()->with('success', 'บันทึกแจ้งซ่อมเรียบร้อย'); 
    }
    
    public function reportIssue(Request $request, $jobId) { return back(); }
    public function reportGeneral(Request $request) { return back(); }
}