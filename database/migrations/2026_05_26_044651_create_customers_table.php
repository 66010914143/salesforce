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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');          // ชื่อบริษัท / ชื่อองค์กร
            $table->string('contact_name')->nullable(); // ชื่อผู้ติดต่อหลัก (ยอมให้ว่างได้)
            $table->string('phone')->nullable();        // เบอร์โทรศัพท์ (ยอมให้ว่างได้)
            $table->string('email')->nullable();        // อีเมลติดต่อ (ยอมให้ว่างได้)
            $table->timestamps();                       // created_at และ updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};