<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('main_statuses', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique(); // เก็บชื่อสถานะ เช่น Forecast, Denied
        $table->string('label')->nullable(); // เก็บชื่อแสดงผลภาษาไทย (ถ้ามี) เช่น (ติดตามสถานะ), (ปฏิเสธ/ยกเลิก)
        $table->timestamps();
    });
}
};
