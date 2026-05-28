<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // เพิ่มฟิลด์ role เพื่อแบ่งแยกระหว่าง แอดมิน/หัวหน้า และ เซลส์ทั่วไป
        $table->string('role')->default('sales')->after('email'); 
    });
}
};
