<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    // เพิ่มการเปิดสิทธิ์บันทึกข้อมูลให้กับคอลัมน์ทั้งหมดที่สร้างขึ้นใหม่ให้ตรงกับฐานข้อมูลจริง
    protected $fillable = [
        'course_name',
        'description',
        'default_price',
        'is_active',
    ];

    // เพิ่มความสัมพันธ์เพื่อใช้ตรวจสอบข้อมูลในดีลงานขายก่อนสั่งลบ (ป้องกัน Error ลบไม่ได้)
    public function dealItems()
    {
        return $this->hasMany(DealItem::class, 'course_id');
    }
}