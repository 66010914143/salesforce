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
        Schema::table('customers', function (Blueprint $table) {
            // เพิ่มคอลัมน์ type ต่อจาก id (กำหนดค่าเริ่มต้นเป็น corporate)
            $table->string('type')->default('corporate')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // ลบคอลัมน์ type ออกหากมีการย้อนกลับ (Rollback)
            $table->dropColumn('type');
        });
    }
};