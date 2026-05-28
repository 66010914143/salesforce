<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            // เพิ่มคอลัมน์ใหม่ต่อท้าย
            $table->text('description')->nullable()->after('course_name');
            $table->decimal('default_price', 10, 2)->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('default_price');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            // กรณีต้องการย้อนกลับ (Rollback) ให้ลบคอลัมน์ทิ้ง
            $table->dropColumn(['description', 'default_price', 'is_active']);
        });
    }
};
