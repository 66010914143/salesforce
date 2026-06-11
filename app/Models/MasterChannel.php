<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterChannel extends Model
{
    use HasFactory;

    protected $table = 'master_channels'; // ระบุชื่อตารางให้ตรงกับในฐานข้อมูล

    protected $fillable = ['name'];
}