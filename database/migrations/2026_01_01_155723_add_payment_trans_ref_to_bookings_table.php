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
            // เพิ่มคอลัมน์ payment_trans_ref แบบ VARCHAR(100), ยอมรับค่า NULL, ต่อท้าย payment_status
            $table->string('payment_trans_ref', 100)
                  ->nullable()
                  ->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // ลบคอลัมน์หากมีการ Rollback
            $table->dropColumn('payment_trans_ref');
        });
    }
};