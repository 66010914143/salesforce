<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'branch',
        'contact_person',
        'tel_1',
        'tel_2',
        'email',
    ];

    // ความสัมพันธ์: บริษัทหนึ่งแห่งสามารถมีประวัติการซื้อ (Sales) ได้หลายรายการ
    public function sales()
    {
        return $this->hasMany(SalesTransaction::class, 'company_id');
    }
}