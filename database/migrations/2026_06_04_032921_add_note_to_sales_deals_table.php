<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('sales_deals', function (Blueprint $table) {
        // เพิ่มคอลัมน์ note (สามารถเป็นค่าว่างได้)
        $table->text('note')->nullable()->after('status'); 
    });
}

public function down()
{
    Schema::table('sales_deals', function (Blueprint $table) {
        $table->dropColumn('note');
    });
}
};
