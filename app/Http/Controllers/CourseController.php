<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // 1. หน้าแรกของคอร์ส: ดึงรายชื่อคอร์สทั้งหมดไปโชว์
    public function index()
    {
        // 🔒 ล็อกความปลอดภัย: ถ้าไม่ใช่ Admin หรือ Manager ไม่ให้เห็นหน้ารายการคอร์ส เตะไปหน้าบันทึกงานขาย
        if (auth()->check() && !auth()->user()->isAdmin() && strtolower(auth()->user()->role) !== 'manager') {
            return redirect()->route('deals.index');
        }

        $courses = Course::latest()->get();
        return view('courses.index', compact('courses'));
    }

    // 2. หน้าฟอร์มเพิ่มคอร์สใหม่
    public function create()
    {
        // 🔒 ล็อกความปลอดภัย: ถ้าไม่ใช่ Admin หรือ Manager ไม่ให้เข้าหน้าฟอร์มเพิ่มคอร์ส เตะไปหน้าบันทึกงานขาย
        if (auth()->check() && !auth()->user()->isAdmin() && strtolower(auth()->user()->role) !== 'manager') {
            return redirect()->route('deals.index');
        }

        return view('courses.create');
    }

    // 3. ฟังก์ชันบันทึกข้อมูลคอร์สใหม่ลงฐานข้อมูล
    public function store(Request $request)
    {
        // 🔒 ล็อกความปลอดภัย: ป้องกันการแอบยิง Request บันทึกคอร์สมาจากหลังบ้าน ถ้าไม่ใช่ Admin หรือ Manager ดีดออกทันที
        if (auth()->check() && !auth()->user()->isAdmin() && strtolower(auth()->user()->role) !== 'manager') {
            return redirect()->route('deals.index');
        }

        $request->validate([
            'course_name' => 'required|string|max:255|unique:courses,course_name',
            'default_price' => 'nullable|numeric|min:0',
        ]);

        Course::create($request->all());

        return redirect()->route('courses.index')->with('success', 'เพิ่มคอร์สเรียนเรียบร้อยแล้ว');
    }
}