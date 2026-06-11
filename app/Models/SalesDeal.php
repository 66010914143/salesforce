<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDeal extends Model
{
    use HasFactory;

    // เปิดสิทธิ์ให้ระบบบันทึกฟิลด์เหล่านี้ลงฐานข้อมูลได้ (Mass Assignment)
    protected $fillable = [
        'user_id',
        'customer_id',
        'deal_date',
        'group',
        'category',
        'tools',
        'promotion',
        'status',
        'progress',
        'receipt_no',
        'note',          // 🟢 เพิ่มฟิลด์นี้เพื่อไม่ให้ติด Error ตอน Controller สั่งอัปเดต
        'updated_note',
        'discount',
        'total_revenue'  // 🟢 เพิ่มฟิลด์นี้เพื่อให้ Controller เซฟยอดรวมได้
    ];

    // 🟢 กำหนดการแปลงค่าวันที่ (Casting) เพื่อให้ดึงไปแสดงผลหรือจัดการได้ง่ายขึ้น
    protected $casts = [
        'deal_date' => 'date',
    ];

    // บอกว่าดีลนี้เป็นของเซลส์คนไหน
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // บอกว่าดีลนี้เป็นของลูกค้ารายไหน
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // บอกว่าดีลนี้มีสินค้า/คอร์สอะไรบ้าง (เชื่อมไปตารางย่อย)
    public function dealItems()
    {
        return $this->hasMany(DealItem::class);
    }

    // เพิ่มฟังก์ชัน items() เพื่อให้ตรงกับการเรียกใช้งานของ DashboardController
    public function items()
    {
        return $this->hasMany(DealItem::class, 'sales_deal_id');
    }

    // เพิ่มความสัมพันธ์เพื่อดึงประวัติการอัปเดตที่เกี่ยวข้องกับดีลนี้ออกมาโชว์ (โมดูล 4)
    public function activityLogs()
    {
        return $this->hasMany(\App\Models\ActivityLog::class, 'record_id')
                    ->where('log_type', 'Deal')
                    ->latest(); // เรียงจากใหม่ไปเก่าอัตโนมัติ
    }

    // ดึงประวัติการอัปเดตทั้งหมดของดีลนี้ เรียงจากใหม่สุดไปเก่าสุด
    public function logs()
    {
        return $this->hasMany(DealLog::class, 'sales_deal_id')->latest();
    }

    // ➕ เพิ่มความสัมพันธ์กับสถานะหลักเพื่อดึงข้อมูลข้ามตารางกับตัว Dropdown Master Data (กรณีฐานข้อมูลเชื่อมโยงกันด้วยชื่อสถานะ)
    public function mainStatus()
    {
        return $this->belongsTo(MainStatus::class, 'status', 'name');
    }
}