<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_type',
        'record_id',
        'action',
        'description',
        'old_values',
        'new_values'
    ];

    // แปลงข้อมูลเก่า/ใหม่จาก JSON ให้กลายเป็น Array อัตโนมัติเวลาดึงไปใช้
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // เชื่อมโยงข้อมูลไปยังพนักงานผู้ใช้งาน (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}