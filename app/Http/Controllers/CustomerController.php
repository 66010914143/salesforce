<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * แสดงรายชื่อลูกค้าทั้งหมด
     */
    public function index()
    {
        // ดึงข้อมูลลูกค้าล่าสุด และทำระบบแบ่งหน้า (Pagination) หน้าละ 10 รายชื่อ
        $customers = Customer::latest()->paginate(10);
        
        return view('customers.index', compact('customers'));
    }

    /**
     * หน้าฟอร์มสำหรับเพิ่มข้อมูลลูกค้าใหม่ (เพิ่มเพื่อแก้ไข Error: create does not exist)
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * บันทึกข้อมูลลูกค้าใหม่ลงฐานข้อมูล (เพิ่มคู่กับฟังก์ชัน create)
     */
    public function store(Request $request)
    {
        // 1. ตรวจสอบความถูกต้องของข้อมูล (Validation)
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
        ]);

        // 2. บันทึกข้อมูลใหม่
        Customer::create([
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'phone'        => $request->phone,
            'email'        => $request->email,
        ]);

        // 3. เปลี่ยนหน้ากลับไปที่รายการทั้งหมด พร้อมแจ้งเตือนสำเร็จ
        return redirect()->route('customers.index')->with('success', 'บันทึกข้อมูลลูกค้าเรียบร้อยแล้ว!');
    }

    /**
     * หน้าฟอร์มสำหรับแก้ไขข้อมูลลูกค้า
     */
    public function edit(Customer $customer)
    {
        // Laravel จะดึงข้อมูลลูกค้าตาม ID ที่ส่งมาให้ อัตโนมัติ (Route Model Binding)
        return view('customers.edit', compact('customer'));
    }

    /**
     * อัปเดตข้อมูลลูกค้าลงฐานข้อมูล
     */
    public function update(Request $request, Customer $customer)
    {
        // 1. ตรวจสอบความถูกต้องของข้อมูล (Validation)
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
        ]);

        // 2. อัปเดตข้อมูล
        $customer->update([
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'phone'        => $request->phone,
            'email'        => $request->email,
        ]);

        // 3. เปลี่ยนหน้ากลับไปที่รายการทั้งหมด พร้อมแจ้งเตือนสำเร็จ
        return redirect()->route('customers.index')->with('success', 'อัปเดตข้อมูลลูกค้าเรียบร้อยแล้ว!');
    }

    /**
     * ลบข้อมูลลูกค้า
     */
    public function destroy(Customer $customer)
    {
        // ลบข้อมูลออกจากฐานข้อมูล
        $customer->delete();

        // เปลี่ยนหน้ากลับไปที่รายการทั้งหมด พร้อมแจ้งเตือนสำเร็จ
        return redirect()->route('customers.index')->with('success', 'ลบข้อมูลลูกค้าออกจากระบบแล้ว!');
    }
}