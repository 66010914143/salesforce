<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealItem extends Model
{
    use HasFactory;

    // อนุญาตให้บันทึกฟิลด์ที่จำเป็นทั้งหมดลงฐานข้อมูล
    protected $fillable = [
        'sales_deal_id',
        'course_id',
        'price_per_person',
        'total_person',
        'discount'
    ];

    // เชื่อมกลับไปหาดีลหลัก
    public function salesDeal()
    {
        return $this->belongsTo(SalesDeal::class);
    }

    // บอกว่ารายการนี้คือคอร์สอะไร
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}