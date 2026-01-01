<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BookingService;
use App\Models\Booking;
use Exception;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function store(Request $request)
    {
        // Validate ข้อมูล
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'equipment_id' => 'required|exists:equipment,id',
            'assigned_staff_id' => 'nullable|exists:users,id', // พนักงานอาจยังไม่มอบหมายตอนจอง
            'scheduled_start' => 'required|date|after:now',
            'scheduled_end' => 'required|date|after:scheduled_start',
            'total_price' => 'required|numeric'
        ]);
        // ตรวจสอบว่าถ้ามีการระบุ assigned_staff_id ให้เช็คว่าพนักงานติดงานอยู่หรือไม่
        if ($request->filled('assigned_staff_id')) {
            $isStaffBusy = Booking::where('assigned_staff_id', $request->assigned_staff_id)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('scheduled_start', [$request->scheduled_start, $request->scheduled_end])
                        ->orWhereBetween('scheduled_end', [$request->scheduled_start, $request->scheduled_end])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('scheduled_start', '<', $request->scheduled_start)
                                ->where('scheduled_end', '>', $request->scheduled_end);
                        });
                })->exists();

            if ($isStaffBusy) {
                return response()->json([
                    'message' => 'พนักงานรายนี้ติดงานอื่นในช่วงเวลาดังกล่าว กรุณาเลือกคนอื่น'
                ], 409); // 409 Conflict
            }
        }
        try {
            $booking = $this->bookingService->createBooking($validated);

            return response()->json([
                'message' => 'จองงานและมอบหมายพนักงานสำเร็จ',
                'data' => $booking
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'จองไม่สำเร็จ',
                'error' => $e->getMessage()
            ], 422); // 422 Unprocessable Entity
        }
    }
    
}