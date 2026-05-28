<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('ชื่อบริษัท/องค์กร (Corp Name)');
            $table->string('branch')->nullable()->comment('สาขา (Branch)');
            $table->string('contact_person')->nullable()->comment('ผู้ติดต่อ (Contact Person)');
            $table->string('tel_1')->nullable()->comment('เบอร์โทรศัพท์ 1 (Tel.1)');
            $table->string('tel_2')->nullable()->comment('เบอร์โทรศัพท์ 2 (Tel.2)');
            $table->string('email')->nullable()->comment('อีเมล (Email)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};