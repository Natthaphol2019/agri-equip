<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\Booking;
use App\Models\FuelLog;
use App\Models\MaintenanceLog;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ⚠️ ล้างข้อมูลเก่าก่อน (Optional: ถ้าอยากเริ่มใหม่หมดให้เปิดคอมเมนต์)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate(); Customer::truncate(); Equipment::truncate(); 
        Booking::truncate(); FuelLog::truncate(); MaintenanceLog::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🌱 เริ่มต้นการจำลองข้อมูล (Seeding)...');

        // ---------------------------------------------------------
        // 1. สร้าง Users (Admin & Staff)
        // ---------------------------------------------------------
        $this->createUsers();

        // ---------------------------------------------------------
        // 2. สร้างลูกค้า (Customers)
        // ---------------------------------------------------------
        $this->createCustomers();

        // ---------------------------------------------------------
        // 3. สร้างเครื่องจักร (Equipment)
        // ---------------------------------------------------------
        $this->createEquipments();

        // ---------------------------------------------------------
        // 4. สร้างข้อมูล Transaction (งาน, น้ำมัน, ซ่อม)
        // ---------------------------------------------------------
        $this->generateTransactions();
        
        $this->command->info('✅ เสร็จสิ้น! ข้อมูลพร้อมใช้งานแล้วครับ 🚀');
    }

    private function createUsers()
    {
        // 1.1 สร้าง Admin
        if (!User::where('username', 'admin')->exists()) {
            User::create([
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@agritech.com',
                'password' => Hash::make('password'), // รหัส: password
                'role' => 'admin',
            ]);
        }

        // 1.2 สร้าง Staff (ช่าง/คนขับ)
        $staffs = [
            ['name' => 'ช่างสมชาย (Senior)', 'username' => 'somchai', 'pin' => '1111'],
            ['name' => 'ช่างวิชัย (Junior)', 'username' => 'wichai',  'pin' => '2222'],
            ['name' => 'คนขับยอดชาย',       'username' => 'yodchai', 'pin' => '3333'],
        ];

        foreach ($staffs as $s) {
            if (!User::where('username', $s['username'])->exists()) {
                User::create([
                    'name' => $s['name'],
                    'username' => $s['username'],
                    'email' => $s['username'] . '@agritech.com',
                    'password' => Hash::make('password'),
                    'role' => 'staff',
                    'pin' => Hash::make($s['pin']), // 🔑 PIN สำหรับ Login หน้าตู้
                ]);
            }
        }
    }

    private function createCustomers()
    {
        $customers = [
            ['name' => 'กำนันแม้น', 'type' => 'individual', 'phone' => '081-111-1111', 'address' => 'หมู่ 1 บ้านหนองนา'],
            ['name' => 'เจ๊แต๋ว สวนผลไม้', 'type' => 'farm', 'phone' => '089-222-2222', 'address' => 'สวนป้าแต๋ว ระยอง'],
            ['name' => 'บริษัท เกษตรรุ่งเรือง จำกัด', 'type' => 'company', 'phone' => '02-333-4444', 'address' => 'นิคมอุตสาหกรรม'],
            ['name' => 'ลุงมี นาข้าว', 'type' => 'individual', 'phone' => '085-555-5555', 'address' => 'ทุ่งกุลาร้องไห้'],
            ['name' => 'ไร่อ้อย สุขใจ', 'type' => 'farm', 'phone' => '090-666-6666', 'address' => 'กาญจนบุรี'],
        ];

        foreach ($customers as $c) {
            Customer::firstOrCreate(
                ['name' => $c['name']], // เช็คชื่อซ้ำ
                [
                    'customer_code' => 'CUS-' . rand(100, 999),
                    'customer_type' => $c['type'],
                    'phone' => $c['phone'],
                    'address' => $c['address'],
                ]
            );
        }
    }

    private function createEquipments()
    {
        $equipments = [
            [
                'name' => 'รถไถ Kubota L5018', 
                'code' => 'TR-001', 
                'type' => 'tractor', 
                'rate' => 500, 
                'maintenance' => 500
            ],
            [
                'name' => 'รถเกี่ยวข้าว Yanmar', 
                'code' => 'HV-001', 
                'type' => 'harvester', 
                'rate' => 1200, 
                'maintenance' => 300
            ],
            [
                'name' => 'โดรนพ่นยา DJI Agras', 
                'code' => 'DR-001', 
                'type' => 'drone', 
                'rate' => 800, 
                'maintenance' => 100
            ],
            [
                'name' => 'รถขุดเล็ก (Backhoe)', 
                'code' => 'EX-001', 
                'type' => 'excavator', 
                'rate' => 1500, 
                'maintenance' => 600
            ],
        ];

        foreach ($equipments as $e) {
            Equipment::firstOrCreate(
                ['equipment_code' => $e['code']],
                [
                    'name' => $e['name'],
                    'type' => $e['type'],
                    'hourly_rate' => $e['rate'],
                    'maintenance_hour_threshold' => $e['maintenance'],
                    'current_hours' => rand(10, $e['maintenance'] - 50), // สุ่มชั่วโมงใช้งาน
                    'current_status' => 'available',
                ]
            );
        }
    }

    private function generateTransactions()
    {
        $customers = Customer::all();
        $equipments = Equipment::all();
        $staffs = User::where('role', 'staff')->get();

        if ($customers->isEmpty() || $equipments->isEmpty() || $staffs->isEmpty()) return;

        // Loop ย้อนหลัง 60 วัน -> อนาคต 7 วัน
        $startDate = Carbon::now()->subDays(60);
        $endDate = Carbon::now()->addDays(7);

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            
            // 1. สุ่มสร้างงาน (Booking)
            if (rand(0, 10) > 3) { // 70% chance ที่จะมีงานในวันนี้
                $jobsCount = rand(1, 3); // 1-3 งานต่อวัน
                for ($i = 0; $i < $jobsCount; $i++) {
                    $this->createSingleJob($date, $customers, $equipments, $staffs);
                }
            }

            // 2. สุ่มเติมน้ำมัน (FuelLog)
            if (rand(1, 100) <= 15) { // 15% chance
                $this->createFuelLog($date, $equipments, $staffs);
            }

            // 3. สุ่มซ่อมบำรุง (MaintenanceLog)
            if (rand(1, 100) <= 5) { // 5% chance
                $this->createMaintenanceLog($date, $equipments);
            }
        }
    }

    private function createSingleJob($date, $customers, $equipments, $staffs)
    {
        $equipment = $equipments->random();
        
        // เวลาเริ่มงาน (8:00 - 14:00)
        $startHour = rand(8, 14);
        $duration = rand(2, 6); // 2-6 ชั่วโมง
        
        $scheduledStart = $date->copy()->setTime($startHour, 0);
        $scheduledEnd = $scheduledStart->copy()->addHours($duration);

        // สถานะงาน
        $isPast = $date->lessThan(Carbon::now());
        $status = $isPast ? 'completed' : 'scheduled';
        
        // ราคางาน
        $totalPrice = $duration * ($equipment->hourly_rate ?? 500);

        Booking::create([
            'job_number' => 'JOB-' . $date->format('ymd') . '-' . rand(1000, 9999),
            'customer_id' => $customers->random()->id,
            'equipment_id' => $equipment->id,
            'assigned_staff_id' => $staffs->random()->id,
            'scheduled_start' => $scheduledStart,
            'scheduled_end' => $scheduledEnd,
            'actual_start' => $isPast ? $scheduledStart : null,
            'actual_end' => $isPast ? $scheduledEnd : null,
            'status' => $status,
            'total_price' => $totalPrice,
            'deposit_amount' => $totalPrice * 0.3, // มัดจำ 30%
            'note' => 'Auto Generated by Seeder',
        ]);

        // อัปเดตชั่วโมงรถ
        if ($isPast) {
            $equipment->increment('current_hours', $duration);
        }
    }

    private function createFuelLog($date, $equipments, $staffs)
    {
        FuelLog::create([
            'equipment_id' => $equipments->random()->id,
            'user_id' => $staffs->random()->id,
            'amount' => rand(500, 2000),
            'liters' => rand(20, 60),
            'refill_date' => $date,
            'note' => 'เติมน้ำมันหน้างาน (Seeder)',
        ]);
    }

    private function createMaintenanceLog($date, $equipments)
    {
        $eq = $equipments->random();
        MaintenanceLog::create([
            'equipment_id' => $eq->id,
            'maintenance_type' => 'corrective', // หรือ 'preventive'
            'description' => 'เปลี่ยนถ่ายน้ำมันเครื่อง / เช็คช่วงล่าง',
            'status' => 'completed',
            'cost' => rand(1500, 8000),
            'technician_name' => 'อู่ช่างแดง เซอร์วิส',
            'maintenance_date' => $date,
            'completion_date' => $date->copy()->addHours(3),
        ]);
        
        // รีเซ็ตชั่วโมงหลังซ่อม (สมมติ)
        // $eq->update(['current_hours' => 0]); 
    }
}