<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_code')->unique(); // รหัสรถ เช่น TR-001
            $table->string('name'); // ชื่อรถ
            
            // ✅ แก้ไขตรงนี้: เพิ่ม 'excavator' เข้าไปในรายการ
            $table->enum('type', ['drone', 'tractor', 'harvester', 'sprayer', 'excavator', 'other']);
            
            $table->string('registration_number')->unique()->nullable(); // ทะเบียน
            $table->decimal('current_hours', 10, 2)->default(0); // ชั่วโมงทำงานปัจจุบัน
            $table->decimal('maintenance_hour_threshold', 8, 2); // รอบเช็คระยะ (เช่น ทุก 500 ชม.)
            $table->decimal('hourly_rate', 10, 2)->nullable(); // ✅ เพิ่ม: เรทราคาต่อชั่วโมง (เพื่อให้ Seeder ทำงานได้สมบูรณ์)
            
            // สถานะ (State Machine)
            $table->enum('current_status', [
                'available',
                'booked',
                'in_use',
                'maintenance',
                'breakdown'
            ])->default('available');
            
            $table->string('image_path')->nullable(); // รูปภาพรถ
            $table->timestamps();
            $table->softDeletes(); // ลบแบบกู้คืนได้
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};