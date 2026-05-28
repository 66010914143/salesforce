<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('sales_deals', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // เซลส์ผู้ดูแลดีลนี้
        $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // ลูกค้าที่คุยด้วย
        $table->date('deal_date');          // Date / วันที่บันทึกดีล
        $table->string('group')->nullable();    // Group
        $table->string('category')->nullable(); // Category
        $table->string('tools')->nullable();    // Tools
        $table->string('promotion')->nullable(); // Promotion
        $table->decimal('discount', 12, 2)->default(0.00); // ส่วนลดรวมของดีลนี้
        $table->string('status')->default('Forecast'); // Status: Forecast, Following, Closed Sale, Denied
        $table->string('progress')->nullable();  // Progress
        $table->string('receipt_no')->nullable(); // เลขที่ใบเสร็จ (จากชีตมกราคม)
        $table->text('updated_note')->nullable(); // Up-dated (Noted) / หมายเหตุการคุยล่าสุด
        $table->timestamps();
    });
}
};