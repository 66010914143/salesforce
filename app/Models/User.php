<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // เพิ่มฟิลด์ role ตรงนี้เพื่อให้ระบบยอมให้บันทึกสิทธิ์ได้
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * ฟังก์ชันเช็คว่าเป็น Admin (หัวหน้า) หรือไม่
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * ฟังก์ชันเช็คว่าเป็น Manager (หัวหน้า/ผู้บริหาร) หรือไม่ (เพิ่มใหม่)
     */
    public function isManager()
    {
        return $this->role === 'manager';
    }

    /**
     * ฟังก์ชันเช็คว่าเป็น Sales (พนักงานขาย) หรือไม่
     */
    public function isSales()
    {
        return $this->role === 'sales';
    }

    /**
     * ความสัมพันธ์: ผู้ใช้งาน 1 คน สามารถเป็นเจ้าของได้หลายดีล
     */
    public function deals()
    {
        return $this->hasMany(SalesDeal::class, 'user_id');
    }
}