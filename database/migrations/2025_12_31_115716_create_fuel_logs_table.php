<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('fuel_logs', function (Blueprint $table) {
        $table->id();
        
        // เติมใส่รถคันไหน?
        $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
        
        // ใครเป็นคนเติม? (พนักงาน)
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        // ข้อมูลการเติม
        $table->decimal('amount', 10, 2); // ยอดเงิน (บาท) *สำคัญสุด
        $table->decimal('liters', 8, 2)->nullable(); // จำนวนลิตร (เผื่ออยากเก็บ)
        $table->decimal('mileage', 10, 2)->nullable(); // เลขไมล์/ชั่วโมงตอนเติม (เผื่อเช็คอัตรากินน้ำมัน)
        
        // หลักฐาน
        $table->string('image_path')->nullable(); // รูปสลิป/หน้าตู้
        $table->text('note')->nullable(); // หมายเหตุ
        
        $table->dateTime('refill_date'); // วันที่เติม
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
    }
};
