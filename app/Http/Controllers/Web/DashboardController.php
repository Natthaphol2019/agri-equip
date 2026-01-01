<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\MaintenanceLog;
use App\Models\User;
use App\Models\FuelLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Financial
        $totalIncome = Booking::where('status', 'completed')->sum('total_price');
        $maintenanceCost = MaintenanceLog::sum('cost'); 
        $fuelCost = FuelLog::sum('amount'); 
        $totalExpense = $maintenanceCost + $fuelCost;
        $netProfit = $totalIncome - $totalExpense;

        // 2. Operations
        $completedJobs = Booking::where('status', 'completed')->count();
        $pendingJobs = Booking::where('status', 'completed_pending_approval')->count();
        $activeMachines = Booking::where('status', 'in_progress')->count();
        $availableStaff = User::where('role', 'staff')->count(); 
        $fuelRequests = FuelLog::whereDate('created_at', today())->count();

        // 3. Alerts (Sort by Urgency)
        $recentJobs = Booking::with(['customer', 'assignedStaff'])->latest()->take(5)->get();
        $maintenanceAlerts = Equipment::whereRaw('current_hours >= (maintenance_hour_threshold - 10)')
            ->orderByRaw('(maintenance_hour_threshold - current_hours) ASC')
            ->get();

        // 4. Calendar Events
        $calendarBookings = Booking::with(['customer', 'equipment', 'assignedStaff'])
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(function ($job) {
                $statusConfig = match ($job->status) {
                    'pending' => ['color' => 'bg-gray-100 text-gray-600 border-gray-200', 'icon' => 'fa-clock', 'label' => 'รอจ่ายงาน'],
                    'scheduled' => ['color' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => 'fa-calendar-check', 'label' => 'นัดหมายแล้ว'],
                    'in_progress' => ['color' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => 'fa-spinner fa-spin', 'label' => 'กำลังดำเนินการ'],
                    'completed_pending_approval' => ['color' => 'bg-orange-50 text-orange-700 border-orange-200', 'icon' => 'fa-clipboard-question', 'label' => 'รอตรวจสอบ'],
                    'completed' => ['color' => 'bg-green-50 text-green-700 border-green-200', 'icon' => 'fa-circle-check', 'label' => 'เสร็จสิ้น'],
                    default => ['color' => 'bg-gray-50 text-gray-500 border-gray-200', 'icon' => 'fa-circle', 'label' => '-'],
                };

                $start = $job->scheduled_start ? Carbon::parse($job->scheduled_start) : $job->created_at;
                $end = $job->scheduled_end ? Carbon::parse($job->scheduled_end) : $start->copy()->addHours(2);

                return [
                    'id' => $job->id,
                    'job_number' => $job->job_number ?? 'JOB-'.$job->id,
                    'title' => $job->customer->name,
                    'phone' => $job->customer->phone,
                    'location' => $job->customer->address ?? '-',
                    'equipment' => $job->equipment->name ?? '-',
                    'equipment_code' => $job->equipment->equipment_code ?? '',
                    'staff' => $job->assignedStaff ? $job->assignedStaff->name : 'ยังไม่ระบุช่าง',
                    'staff_avatar' => $job->assignedStaff 
                        ? 'https://ui-avatars.com/api/?name='.urlencode($job->assignedStaff->name).'&background=random&color=fff&size=64' 
                        : null,
                    'start_date' => $start->format('Y-m-d'),
                    'time_range' => $start->format('H:i') . ' - ' . $end->format('H:i'),
                    'price' => number_format($job->total_price),
                    'status' => $statusConfig,
                    'url' => route('admin.jobs.show', $job->id)
                ];
            });

        return view('admin.dashboard', compact(
            'totalIncome', 'totalExpense', 'netProfit',
            'completedJobs', 'pendingJobs', 'activeMachines', 'availableStaff', 'fuelRequests',
            'recentJobs', 'maintenanceAlerts', 'calendarBookings'
        ));
    }

    public function getFinancialData(Request $request)
    {
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        $labels = [];
        $current = $start->copy();
        while ($current <= $end) {
            $labels[] = $current->format('d M');
            $current->addDay();
        }

        $incomes = Booking::selectRaw('DATE(actual_end) as date, SUM(total_price) as total')
            ->where('status', 'completed')
            ->whereBetween('actual_end', [$start, $end])
            ->groupBy('date')->pluck('total', 'date');

        $fuelCosts = FuelLog::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')->pluck('total', 'date');

        $maintCosts = MaintenanceLog::selectRaw('DATE(completion_date) as date, SUM(cost) as total')
            ->where('status', 'completed')
            ->whereBetween('completion_date', [$start, $end])
            ->groupBy('date')->pluck('total', 'date');

        $hours = Booking::selectRaw('DATE(scheduled_start) as date, SUM(TIMESTAMPDIFF(HOUR, scheduled_start, scheduled_end)) as total')
            ->where('status', 'completed')
            ->whereBetween('scheduled_start', [$start, $end])
            ->groupBy('date')->pluck('total', 'date');

        $incomeData = [];
        $costData = [];
        $hourData = [];
        $sumIncome = 0; $sumCost = 0; $sumHours = 0;

        $current = $start->copy();
        while ($current <= $end) {
            $dateKey = $current->format('Y-m-d');
            $inc = $incomes[$dateKey] ?? 0;
            $fc = $fuelCosts[$dateKey] ?? 0;
            $mc = $maintCosts[$dateKey] ?? 0;
            $hr = $hours[$dateKey] ?? 0;
            
            $totalC = $fc + $mc;
            $incomeData[] = $inc;
            $costData[] = $totalC;
            $hourData[] = $hr;

            $sumIncome += $inc;
            $sumCost += $totalC;
            $sumHours += $hr;
            $current->addDay();
        }

        return response()->json([
            'labels' => $labels,
            'income' => $incomeData,
            'costs' => $costData,
            'hours' => $hourData,
            'summary' => [
                'total_income' => $sumIncome,
                'total_cost' => $sumCost,
                'net_profit' => $sumIncome - $sumCost,
                'total_hours' => $sumHours
            ]
        ]);
    }
}