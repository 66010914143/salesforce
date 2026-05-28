<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ลบตารางเก่าที่ค้างอยู่ในฐานข้อมูลออกก่อน เพื่อป้องกัน Error ตารางซ้ำครับ
        Schema::dropIfExists('deal_logs');

        Schema::create('deal_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_deal_id')->constrained('sales_deals')->onDelete('cascade'); // ผูกกับตาราง sales_deals
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ผูกกับตาราง users ว่าใครแก้
            $table->string('old_status')->nullable(); // สถานะเดิม
            $table->string('new_status')->nullable(); // สถานะใหม่
            $table->text('note')->nullable(); // ข้อความโน้ตที่เซลส์พิมพ์
            $table->timestamps(); // เก็บเวลา created_at, updated_at อัตโนมัติ
        });
    }

    public function down()
    {
        Schema::dropIfExists('deal_logs');
    }
};