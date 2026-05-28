<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * หน้าแรก: แสดงรายชื่อพนักงานทั้งหมดในระบบ (เฉพาะ Admin)
     */
    public function index()
    {
        // 🔮 โค้ดสำหรับจำลองสิทธิ์ Admin ชั่วคราวเพื่อทดสอบระบบหน้าจอ
        if (!auth()->check()) {
            $mockUser = new \App\Models\User();
            $mockUser->id = 1;
            $mockUser->name = 'lesforce';
            $mockUser->email = 'lesforce@company.com';
            $mockUser->role = 'admin'; 
            auth()->login($mockUser);
        }

        // 🔒 ตรวจสอบสิทธิ์: หากไม่ใช่ Admin (ผู้ดูแลระบบ) จะไม่มีสิทธิ์เข้าหน้านี้เด็ดขาด
        if (!auth()->user()->isAdmin()) {
            abort(403, 'ขออภัย เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถจัดการสิทธิ์ผู้ใช้งานได้');
        }

        // ดึงรายชื่อพนักงานทั้งหมด เรียงจากคนที่สมัครล่าสุดขึ้นก่อน
        $users = User::latest()->get();

        return view('users.index', compact('users'));
    }

    /**
     * หน้าบันทึกข้อมูล: รับค่าจากฟอร์มเพื่อสร้างบัญชีพนักงานใหม่
     */
    public function store(Request $request)
    {
        // 🔒 ตรวจสอบสิทธิ์ Admin 
        if (!auth()->user()->isAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์ทำรายการนี้');
        }

        // ตรวจสอบความถูกต้องของข้อมูลที่กรอกเข้ามา (Validation) 
        // เปลี่ยนตรงนี้: อนุญาตให้สร้างเฉพาะ manager และ sales
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:manager,sales',
        ], [
            'name.required' => 'กรุณากรอกชื่อ-นามสกุลพนักงาน',
            'email.required' => 'กรุณากรอกอีเมลสำหรับเข้าใช้งาน',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้ถูกใช้งานในระบบแล้ว',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร',
            'password.confirmed' => 'รหัสผ่านกับการยืนยันรหัสผ่าน (Confirm Password) ไม่ตรงกัน',
            'role.in' => 'กรุณาเลือกบทบาทสิทธิ์การใช้งานที่ถูกต้อง (manager หรือ sales)',
        ]);

        // บันทึกข้อมูลพนักงานใหม่ลงฐานข้อมูลพร้อมเข้ารหัสความปลอดภัย (Hash Password)
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'เพิ่มพนักงานใหม่เข้าสู่ระบบเรียบร้อยแล้ว');
    }

    /**
     * หน้าอัปเดตข้อมูล: บันทึกการแก้ไขข้อมูลพนักงานหรือเปลี่ยนรหัสผ่านใหม่
     */
    public function update(Request $request, $id)
    {
        // 🔒 ตรวจสอบสิทธิ์ Admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์ทำรายการนี้');
        }

        // ตรวจสอบความถูกต้องของข้อมูลที่ส่งมาแก้ไข (Validation)
        // เปลี่ยนตรงนี้: อนุญาตให้อัปเดตเป็น admin (เผื่อแก้ตัวเอง), manager และ sales
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6', // เคลียร์สิทธิ์ให้ว่างได้ถ้าไม่แก้ไขรหัสผ่าน
            'role' => 'required|string|in:admin,manager,sales',
        ], [
            'name.required' => 'กรุณากรอกชื่อ-นามสกุลพนักงาน',
            'email.required' => 'กรุณากรอกอีเมลสำหรับเข้าใช้งาน',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้ถูกใช้งานในระบบแล้ว',
            'password.min' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร',
            'role.in' => 'กรุณาเลือกบทบาทสิทธิ์การใช้งานที่ถูกต้อง (admin, manager หรือ sales)',
        ]);

        // ค้นหาข้อมูลพนักงานและทำการอัปเดตข้อมูล
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // ดักจับเคสการขอเปลี่ยนรหัสผ่านพนักงาน (ถ้ามีการกรอกค่าเข้ามาในกล่องรหัสผ่านใหม่)
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'อัปเดตข้อมูลพนักงานเรียบร้อยแล้ว');
    }

    /**
     * หน้าลบข้อมูล: ลบพนักงานออกจากระบบ
     */
    public function destroy($id)
    {
        // 🔒 ตรวจสอบสิทธิ์ Admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์ทำรายการนี้');
        }

        // ป้องกันความผิดพลาด: ห้ามหัวหน้ากดลบบัญชีตัวเองเด็ดขาด
        if (auth()->id() == $id) {
            return redirect()->back()->with('error', 'ระบบปฏิเสธการทำรายการ: ไม่สามารถลบบัญชีผู้ใช้งานของตัวคุณเองได้');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'ลบพนักงานออกจากระบบเรียบร้อยแล้ว');
    }
}