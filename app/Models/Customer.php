<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // เปิดสิทธิ์ให้ตารางฐานข้อมูลยอมรับการบันทึกฟิลด์เหล่านี้
    protected $fillable = [
        'type',
        'company_name',
        'contact_name',
        'phone',
        'email',
        'total_people',
        'note'
    ];

    // เชื่อมโยงความสัมพันธ์ไปยังตารางดีลงานขาย
    public function salesDeals()
    {
        return $this->hasMany(SalesDeal::class);
    }

    /**
     * ส่วนที่เพิ่มใหม่: ช่วยแปลงชื่อแสดงผลให้ถูกต้องอัตโนมัติสำหรับนำไปเลือกในหน้าดีล
     */
    public function getDisplayNameAttribute()
    {
        if ($this->type === 'individual') {
            return $this->name ?? $this->company_name;
        }
        return $this->company_name;
    }
}