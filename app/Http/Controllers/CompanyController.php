<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    // แสดงรายการบริษัททั้งหมด
    public function index()
    {
        $companies = Company::latest()->paginate(10);
        return view('companies.index', compact('companies'));
    }

    // หน้าฟอร์มเพิ่มบริษัท
    public function create()
    {
        return view('companies.create');
    }

    // ฟังก์ชันบันทึกข้อมูลลง Database
    public function store(Request $request)
    {
        // ตรวจสอบความถูกต้องของข้อมูล (Validation)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'tel_1' => 'nullable|string|max:20',
            'tel_2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        // บันทึกข้อมูล
        Company::create($validated);

        return redirect()->route('companies.index')->with('success', 'เพิ่มข้อมูลบริษัทลูกค้าเรียบร้อยแล้ว!');
    }
}