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
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();

            // เชื่อมโยงเครื่องจักร
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');

            // เชื่อมโยงงาน (nullable เผื่อเป็นการซ่อมทั่วไปไม่ได้มาจากงานจ้าง)
            $table->unsignedBigInteger('booking_id')->nullable();

            // ประเภทการซ่อม (corrective=ซ่อมเมื่อเสีย, preventive=บำรุงรักษา)
            $table->string('maintenance_type')->default('corrective');

            // รายละเอียด
            $table->text('description');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->decimal('cost', 10, 2)->default(0);

            // รูปภาพและผู้ซ่อม
            $table->string('image_url')->nullable();
            $table->string('technician_name')->nullable();

            // วันที่
            $table->dateTime('maintenance_date')->nullable(); // วันที่เริ่ม
            $table->dateTime('completion_date')->nullable();  // วันที่เสร็จ

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
