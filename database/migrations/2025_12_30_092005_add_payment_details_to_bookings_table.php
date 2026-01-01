<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        // เพิ่มยอดมัดจำ (ถ้า 0 คือไม่ได้มัดจำ)
        $table->decimal('deposit_amount', 12, 2)->default(0)->after('total_price');
        
        // เก็บรูปสลิปโอนเงินงวดสุดท้าย (ที่ Staff ถ่ายหรืออัปโหลด)
        $table->string('payment_proof')->nullable()->after('payment_status');
    });
}

public function down(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->dropColumn(['deposit_amount', 'payment_proof']);
    });
}
};
