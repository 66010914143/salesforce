<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('customers', function (Blueprint $table) {
        // เพิ่มคอลัมน์เก็บจำนวนคน ค่าเริ่มต้นเป็น 1 คน
        $table->integer('total_people')->default(1)->after('type'); 
    });
}

public function down()
{
    Schema::table('customers', function (Blueprint $table) {
        $table->dropColumn('total_people');
    });
}
};
