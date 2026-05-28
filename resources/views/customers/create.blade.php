@extends('layouts.app')

@section('page_title', 'เพิ่มลูกค้า / บริษัทใหม่')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <a href="{{ route('customers.index') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
            <i class="fa-solid fa-arrow-left-long mr-2"></i> กลับไปหน้ารายชื่อลูกค้า
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-slate-50">
            <h3 class="text-base font-bold text-gray-800">แบบฟอร์มกรอกข้อมูลลูกค้า (Corporate Prospect)</h3>
            <p class="text-gray-500 text-xs mt-1">กรุณากรอกข้อมูลของบริษัทคู่ค้าให้ครบถ้วนเพื่อใช้เชื่อมโยงกับงานขาย</p>
        </div>

        <form action="{{ route('customers.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อบริษัท / ชื่อองค์กร <span class="text-rose-500">*</span></label>
                <input type="text" name="company_name" id="company_name" required placeholder="ตัวอย่าง: บริษัท เอบีซี จำกัด (มหาชน)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                @error('company_name')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อผู้ติดต่อหลัก</label>
                    <input type="text" name="contact_name" id="contact_name" placeholder="ตัวอย่าง: คุณสมชาย ใจดี" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" id="phone" placeholder="ตัวอย่าง: 0812345678" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">อีเมลติดต่อ</label>
                <input type="email" name="email" id="email" placeholder="ตัวอย่าง: contact@company.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('customers.index') }}" class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                    ยกเลิก
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> บันทึกข้อมูลลูกค้า
                </button>
            </div>
        </form>
    </div>

</div>
@endsection