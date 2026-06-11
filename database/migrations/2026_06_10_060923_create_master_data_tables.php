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
        // 1. ตารางสถานะย่อย (Sub Statuses)
        Schema::create('sub_statuses', function (Blueprint $blue) {
            $blue->id();
            $blue->string('name');
            $blue->timestamps();
        });

        // 2. ตารางกลุ่มลูกค้า (Customer Groups)
        Schema::create('customer_groups', function (Blueprint $blue) {
            $blue->id();
            $blue->string('name');
            $blue->timestamps();
        });

        // 3. ตารางหมวดหมู่ (Master Categories)
        Schema::create('master_categories', function (Blueprint $blue) {
            $blue->id();
            $blue->string('name');
            $blue->timestamps();
        });

        // 4. ตารางช่องทาง (Master Channels)
        Schema::create('master_channels', function (Blueprint $blue) {
            $blue->id();
            $blue->string('name');
            $blue->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_channels');
        Schema::dropIfExists('master_categories');
        Schema::dropIfExists('customer_groups');
        Schema::dropIfExists('sub_statuses');
    }
};