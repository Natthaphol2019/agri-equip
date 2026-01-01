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
        Schema::create('task_activities', function (Blueprint $table) {
            $table->id();
            // ผูกกับใบงาน (ถ้าลบใบงาน Log หายไปด้วย)
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            // ใครเป็นคนทำ (Staff คนไหน)
            $table->foreignId('user_id')->nullable()->constrained('users');

            // ประเภทกิจกรรม
            $table->enum('activity_type', [
                'check_in',       // เริ่มงาน
                'photo_uploaded', // อัปรูป
                'issue_reported', // แจ้งปัญหา
                'finished'        // จบงาน
            ]);

            $table->text('description')->nullable(); // หมายเหตุ
            $table->json('image_paths')->nullable(); // เก็บ path รูป (รองรับหลายรูป)

            // เก็บพิกัด GPS ตอนกดปุ่ม
            $table->decimal('location_lat', 10, 8)->nullable();
            $table->decimal('location_lng', 11, 8)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_activities');
    }
};
