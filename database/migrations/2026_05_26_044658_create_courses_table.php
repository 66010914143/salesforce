<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_name'); // ชื่อหลักสูตร (เช่น ALT, TSM, จป., EVOC)
            $table->text('description')->nullable(); // คำอธิบายคอร์ส (ถ้ามี)
            $table->decimal('default_price', 10, 2)->nullable(); // ราคาตั้งต้น (ช่วยให้เซลส์ดึงราคาไปใช้ได้เลย)
            $table->boolean('is_active')->default(true); // สถานะเปิด/ปิดคอร์ส (true=ขายอยู่, false=เลิกขายแล้ว)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};