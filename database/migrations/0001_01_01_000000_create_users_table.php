<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // ชื่อจริง
        $table->string('username')->unique(); // เพิ่ม: ชื่อผู้ใช้สำหรับ Login
        $table->string('email')->nullable(); // (Laravel บังคับมี แต่เราอาจไม่ใช้ login ก็ได้)
        $table->string('password');
        $table->string('pin')->nullable(); // เก็บค่า Hash ของ PIN
        $table->enum('role', ['admin', 'staff'])->default('staff'); // เพิ่ม: แบ่งสิทธิ์
        $table->string('phone')->nullable(); // เพิ่ม: เบอร์โทรพนักงาน
        $table->boolean('is_active')->default(true); // เพิ่ม: สถานะพนักงาน
        $table->rememberToken();
        $table->timestamps();
        $table->softDeletes(); // เพิ่ม: Soft Delete
    });
    
    // ... (ส่วนอื่นๆ ด้านล่างปล่อยไว้เหมือนเดิม)
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
