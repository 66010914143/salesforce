<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // เปิดสิทธิ์ให้ตารางฐานข้อมูลยอมรับการบันทึกฟิลด์เหล่านี้
    protected $fillable = [
        'company_name',
        'contact_name',
        'phone',
        'email'
    ];

    // เชื่อมโยงความสัมพันธ์ไปยังตารางดีลงานขาย
    public function salesDeals()
    {
        return $this->hasMany(SalesDeal::class);
    }
}