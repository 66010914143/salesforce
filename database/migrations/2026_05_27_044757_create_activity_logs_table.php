<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // ใครเป็นคนทำ (เซลส์/หัวหน้า)
            $table->string('log_type'); // ประเภทเหตุการณ์ เช่น Deal, Customer, Course
            $table->unsignedBigInteger('record_id'); // ID ของข้อมูลที่ถูกกระทำ (เช่น ดีล ID ที่ 5)
            $table->string('action'); // การกระทำ เช่น Created, Updated, Deleted
            $table->text('description'); // ข้อความอธิบายรายละเอียด เช่น "เปลี่ยนสถานะจาก Following เป็น Closed Sale"
            $table->json('old_values')->nullable(); // ค่าเก่าก่อนแก้ไข (เผื่อเอาไว้สืบดูตัวเลขเดิม)
            $table->json('new_values')->nullable(); // ค่าใหม่หลังแก้ไข
            $table->timestamps(); // บันทึกวันและเวลาที่เกิดขึ้นอัตโนมัติ
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};