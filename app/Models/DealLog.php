<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_deal_id',
        'user_id',
        'old_status',
        'new_status',
        'note'
    ];

    // เชื่อมกลับไปหาพนักงานที่เป็นคนอัปเดต (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // เชื่อมกลับไปหางานขาย (SalesDeal)
    public function salesDeal()
    {
        return $this->belongsTo(SalesDeal::class);
    }
}