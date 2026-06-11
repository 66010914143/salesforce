<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterCategory extends Model
{
    use HasFactory;

    protected $table = 'master_categories'; // ระบุชื่อตารางให้ตรงกับในฐานข้อมูล

    protected $fillable = ['name'];
}