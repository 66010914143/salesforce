<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * แสดงรายชื่อลูกค้าทั้งหมด (พร้อมระบบคัดกรองประเภท)
     */
    public function index(Request $request)
    {
        // 1. สร้าง Query ดึงข้อมูลเริ่มต้น
        $query = Customer::query();

        // 2. ตรวจสอบว่ามีการส่งค่า filter มาหรือไม่ (และไม่ใช่ 'all')
        // 💡 เปิดใช้งานการค้นหาด้วยคอลัมน์ type เพื่อให้ปุ่มกรองทำงานได้ถูกต้อง
        if ($request->has('filter') && $request->filter !== 'all') {
            $query->where('type', $request->filter);
        }

        // 3. ดึงข้อมูลลูกค้าล่าสุด และทำระบบแบ่งหน้า (Pagination) หน้าละ 10 รายชื่อ
        $customers = $query->latest()->paginate(10);
        
        // 4. แนบค่า URL Parameter ไปกับปุ่มเปลี่ยนหน้าด้วย เพื่อป้องกันเวลากดหน้า 2 แล้วตัวกรองหลุด
        $customers->appends($request->all());
        
        return view('customers.index', compact('customers'));
    }

    /**
     * หน้าฟอร์มสำหรับเพิ่มข้อมูลลูกค้าใหม่
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * บันทึกข้อมูลลูกค้าใหม่ลงฐานข้อมูล
     */
    public function store(Request $request)
    {
        // 1. ตรวจสอบความถูกต้องของข้อมูล (Validation) นำการเช็ค type ออก
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
        ]);

        // โค้ดส่วนเพิ่มใหม่: ประมวลผลรายชื่อผู้เรียนร่วมเพิ่มเติม และ 🎯 นับจำนวนคนจริงจากข้อมูลที่กรอก
        $note = null;
        $total_people = 1; // 🎯 เริ่มนับที่ 1 เสมอ (คือตัวลูกค้าหลัก)

        if ($request->has('extra_names')) {
            $note = "[รายชื่อผู้เรียนร่วมเพิ่มเติม]:";
            foreach ($request->extra_names as $key => $name) {
                if (!empty($name)) {
                    $phone = $request->extra_phones[$key] ?? '-';
                    // ดึงค่า extra_emails เข้ามา และนำไปต่อสตริงในตัวแปร $note
                    $extra_email = $request->extra_emails[$key] ?? '-';
                    $note .= "\n- " . $name . " (โทร: " . $phone . ", อีเมล: " . $extra_email . ")";
                    
                    $total_people++; // 🎯 บวกรวมจำนวนคนเพิ่มให้อัตโนมัติทุกครั้งที่มีรายชื่อผู้เรียนร่วม
                }
            }
        }

        // 2. บันทึกข้อมูลใหม่ (เปิดให้บันทึก type, total_people และ note เพิ่มเติมสำหรับผูกดีลอัตโนมัติ)
        Customer::create([
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'phone'        => $request->phone,
            'email'        => $request->email,
            'type'         => $request->input('type', 'corporate'),
            'total_people' => $total_people, // 🎯 ใช้ตัวเลขที่ระบบหลังบ้านนับได้จริง (แทนการรับค่าจากหน้าบ้าน)
            'note'         => $note,
        ]);

        // 3. เปลี่ยนหน้ากลับไปที่รายการทั้งหมด พร้อมแจ้งเตือนสำเร็จ
        return redirect()->route('customers.index')->with('success', 'บันทึกข้อมูลลูกค้าเรียบร้อยแล้ว!');
    }

    /**
     * หน้าฟอร์มสำหรับแก้ไขข้อมูลลูกค้า
     */
    public function edit(Customer $customer)
    {
        $extraMembers = [];
        
        // ตรวจสอบว่ามี Note และมีคำว่า [รายชื่อผู้เรียนร่วมเพิ่มเติม]: หรือไม่
        if ($customer->note && str_contains($customer->note, '[รายชื่อผู้เรียนร่วมเพิ่มเติม]:')) {
            // แยกข้อความออกเป็นบรรทัดๆ
            $lines = explode("\n", $customer->note);
            
            foreach ($lines as $line) {
                $line = trim($line);
                // ถ้าบรรทัดนั้นขึ้นต้นด้วย "- " แสดงว่าเป็นรายชื่อ
                if (str_starts_with($line, '-')) {
                    // แกะชื่อ (ข้อความตั้งแต่หลัง '-' ถึงก่อนหน้า '(')
                    $namePart = explode('(', $line)[0] ?? '';
                    $name = trim(str_replace('-', '', $namePart));

                    // แกะข้อมูลในวงเล็บ (เบอร์โทร, อีเมล)
                    $details = '';
                    if (preg_match('/\((.*?)\)/', $line, $match)) {
                        $details = $match[1];
                    }

                    $phone = '';
                    $email = '';

                    // ดึงเบอร์โทร
                    if (str_contains($details, 'โทร:')) {
                        preg_match('/โทร:\s*([^,)]*)/', $details, $pMatch);
                        $phone = isset($pMatch[1]) ? trim($pMatch[1]) : '';
                    }

                    // ดึงอีเมล
                    if (str_contains($details, 'อีเมล:')) {
                        preg_match('/อีเมล:\s*([^)]*)/', $details, $eMatch);
                        $email = isset($eMatch[1]) ? trim($eMatch[1]) : '';
                    }

                    if (!empty($name)) {
                        $extraMembers[] = [
                            'name'  => $name,
                            'phone' => $phone,
                            'email' => $email,
                        ];
                    }
                }
            }
        }

        return view('customers.edit', compact('customer', 'extraMembers'));
    }

    /**
     * อัปเดตข้อมูลลูกค้าลงฐานข้อมูล
     */
    public function update(Request $request, Customer $customer)
    {
        // 1. ตรวจสอบความถูกต้องของข้อมูล (Validation) นำการเช็ค type ออก
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
        ]);

        // 🎯 ประมวลผลรายชื่อผู้เรียนร่วมที่ถูกแก้ไข หรือถูกเพิ่ม/ลบ ใหม่ เพื่อนำไปอัปเดตทับข้อมูลเดิม และนับจำนวนคนใหม่
        $note = null;
        $total_people = 1; // 🎯 เริ่มนับใหม่ที่ 1 ทุกครั้งที่มีการอัปเดต

        if ($request->has('extra_names')) {
            $note = "[รายชื่อผู้เรียนร่วมเพิ่มเติม]:";
            foreach ($request->extra_names as $key => $name) {
                if (!empty($name)) {
                    $phone = $request->extra_phones[$key] ?? '-';
                    $extra_email = $request->extra_emails[$key] ?? '-';
                    $note .= "\n- " . $name . " (โทร: " . $phone . ", อีเมล: " . $extra_email . ")";
                    
                    $total_people++; // 🎯 บวกรวมจำนวนคนเพิ่มให้อัตโนมัติ
                }
            }
        }

        // 2. อัปเดตข้อมูล (นำการบันทึก type ออก และเพิ่มการอัปเดต note กับ total_people)
        $customer->update([
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'phone'        => $request->phone,
            'email'        => $request->email,
            'total_people' => $total_people, // 🎯 อัปเดตโดยใช้ตัวเลขที่ระบบหลังบ้านนับได้จริง
            'note'         => $note,
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