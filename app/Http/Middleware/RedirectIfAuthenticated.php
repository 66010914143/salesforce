<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // 🔒 ถ้าไม่ได้ล็อกอิน หรือเซสชันหมดอายุ ให้ดีดส่งกลับไปที่หน้าจอ login เสมอ
        return $request->expectsJson() ? null : route('login');
    }
}