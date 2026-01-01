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
        Schema::table('bookings', function (Blueprint $table) {
            // เพิ่มคอลัมน์เก็บ path รูปถ่ายหน้างาน
            $table->string('image_path')->nullable()->after('actual_end');
            
            // เพิ่มคอลัมน์เก็บหมายเหตุ
            $table->text('note')->nullable()->after('image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'note']);
        });
    }
};