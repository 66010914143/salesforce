<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * แสดงหน้าจอเข้าสู่ระบบ (Login View)
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * ระบบจัดการตรวจสอบสิทธิ์การเข้าสู่ระบบ
     */
    public function login(Request $request)
    {
        // 1. ตรวจสอบรูปแบบข้อมูลขั้นต้น
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. เริ่มทำการตรวจสอบอีเมลและรหัสผ่านกับฐานข้อมูล
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // ✨ แยกทางเดิน: ถ้าเป็น Admin ให้ไปหน้าแดชบอร์ด ถ้าเป็น User ทั่วไปให้ไปหน้างานขาย
            if (auth()->user()->isAdmin()) {
                return redirect()->to('/');
            } else {
                return redirect()->route('deals.index');
            }
        }

        // ❌ หากรหัสไม่ถูกต้อง ส่งค่ากลับไปแจ้งเตือนสีแดงหน้า Login
        return back()->withErrors([
            'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง',
        ])->onlyInput('email');
    }

    /**
     * ระบบออกจากระบบ (Logout)
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 🔒 ออกจากระบบแล้ว ดีดกลับไปหน้าล็อกอินหลัก
        return redirect()->route('login');
    }
}