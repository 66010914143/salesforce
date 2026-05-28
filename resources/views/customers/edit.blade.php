@extends('layouts.app')

@section('page_title', 'แก้ไขข้อมูลลูกค้า')

@section('content')
<div class="max-w-2xl mx-auto">
    
    <div class="mb-4">
        <a href="{{ route('customers.index') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> กลับไปหน้ารายชื่อลูกค้า
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-slate-50/50">
            <h3 class="text-base font-bold text-gray-800">แก้ไขข้อมูลลูกค้า / บริษัท</h3>
            <p class="text-xs text-gray-500 mt-0.5">กรุณาปรับปรุงข้อมูลด้านล่างให้ถูกต้อง จากนั้นกดปุ่มบันทึก</p>
        </div>

        <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT') <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อบริษัท / องค์กร <span class="text-rose-500">*</span></label>
                <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $customer->company_name) }}" required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('company_name') border-rose-500 @enderror">
                @error('company_name')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อผู้ติดต่อหลัก</label>
                <input type="text" name="contact_name" id="contact_name" value="{{ old('contact_name', $customer->contact_name) }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">อีเมลติดต่อ</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('email') border-rose-500 @enderror">
                    @error('email')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('customers.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    ยกเลิก
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
</div>
@endsection