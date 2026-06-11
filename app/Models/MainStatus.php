<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainStatus extends Model
{
    use HasFactory;

    // 🟢 ล็อคชื่อตารางให้ตรงกับฐานข้อมูลเป๊ะๆ ป้องกันระบบหาตารางไม่เจอ
    // (หากตารางใน phpMyAdmin ของคุณชื่อ 'main_status' แบบไม่มี s ให้ลบ es ตัวหลังสุดออกนะครับ)
    protected $table = 'main_statuses';

    protected $fillable = ['name']; 
}