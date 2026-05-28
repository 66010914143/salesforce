<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_deal_id')->constrained()->onDelete('cascade'); // เชื่อมกับดีลหลัก
            $table->foreignId('course_id')->constrained()->onDelete('cascade');     // เชื่อมกับหลักสูตร
            $table->integer('quantity')->default(0); // จำนวนคน (Person / Prospect)
            $table->decimal('price_per_person', 12, 2)->default(0.00); // ราคาขายต่อคน (Asking Price)
            $table->decimal('total_revenue', 12, 2)->default(0.00); // ประมาณการรายได้ (คำนวณอัตโนมัติ)
            
            // ✨ เพิ่มคอลัมน์ที่ขาดไปเพื่อแก้ Error เรื่อง Unknown column 'total_person'
            $table->integer('total_person')->default(0); 
            $table->decimal('discount', 12, 2)->default(0.00); 
            
            $table->timestamps();
        });
    }
};