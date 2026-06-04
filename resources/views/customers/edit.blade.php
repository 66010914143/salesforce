@extends('layouts.app')

@section('page_title', isset($customer->type) && $customer->type === 'individual' ? 'แก้ไขข้อมูลลูกค้าบุคคล' : 'แก้ไขข้อมูลลูกค้า / บริษัท')

@section('content')
<div class="max-w-2xl mx-auto">
    
    <div class="mb-4">
        <a href="{{ route('customers.index') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> กลับไปหน้ารายชื่อลูกค้า
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-slate-50/50">
            <h3 class="text-base font-bold text-gray-800">
                @if(isset($customer->type) && $customer->type === 'individual')
                    แก้ไขข้อมูลลูกค้าบุคคล
                @else
                    แก้ไขข้อมูลลูกค้า / บริษัท
                @endif
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">กรุณาปรับปรุงข้อมูลด้านล่างให้ถูกต้อง จากนั้นกดปุ่มบันทึก</p>
        </div>

        <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            @if(isset($customer->type))
                <input type="hidden" name="type" value="{{ $customer->type }}">
            @endif

            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                    @if(isset($customer->type) && $customer->type === 'individual')
                        ชื่อ-นามสกุลลูกค้า <span class="text-rose-500">*</span>
                    @else
                        ชื่อบริษัท / องค์กร <span class="text-rose-500">*</span>
                    @endif
                </label>
                <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $customer->company_name) }}" required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('company_name') border-rose-500 @enderror">
                @error('company_name')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if(!isset($customer->type) || $customer->type !== 'individual')
            <div>
                <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อผู้ติดต่อหลัก</label>
                <input type="text" name="contact_name" id="contact_name" value="{{ old('contact_name', $customer->contact_name) }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            @endif

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

            {{-- 🎯 ส่วนที่เพิ่มใหม่: โชว์ฟอร์มแก้ไขรายชื่อผู้เรียนร่วม (เฉพาะลูกค้าบุคคล) --}}
            @if(isset($customer->type) && $customer->type === 'individual')
            <div class="pt-4 border-t border-gray-100 space-y-3">
                <label class="block text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-users text-indigo-500"></i> รายชื่อผู้เรียนร่วมเพิ่มเติม (ถ้ามี)
                </label>
                
                <div id="members-container" class="space-y-3">
                    {{-- วนลูปข้อมูลผู้เรียนเดิมที่มีอยู่ในระบบมาแสดงผล --}}
                    @if(isset($extraMembers) && count($extraMembers) > 0)
                        @foreach($extraMembers as $index => $member)
                            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center bg-gray-50 p-3 rounded-lg border border-gray-200 member-row">
                                <div class="flex-1 w-full">
                                    <input type="text" name="extra_names[]" value="{{ $member['name'] }}" placeholder="ชื่อ-นามสกุล ผู้เรียนร่วมคนที่ {{ $index + 2 }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white" required>
                                </div>
                                <div class="flex-1 w-full">
                                    <input type="text" name="extra_phones[]" value="{{ $member['phone'] !== '-' ? $member['phone'] : '' }}" placeholder="เบอร์โทรศัพท์ (ถ้ามี)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                                </div>
                                <div class="flex-1 w-full">
                                    <input type="email" name="extra_emails[]" value="{{ isset($member['email']) && $member['email'] !== '-' ? $member['email'] : '' }}" placeholder="อีเมล / Gmail (ถ้ามี)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                                </div>
                                <button type="button" class="btn-remove-member inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 focus:outline-none transition-colors w-full sm:w-auto shrink-0">
                                    <i class="fa-solid fa-trash-can mr-1"></i> ลบ
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
                
                <div>
                    <button type="button" id="btn-add-member" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                        <i class="fa-solid fa-plus mr-1.5 text-indigo-500"></i> เพิ่มรายชื่อผู้เรียนร่วม
                    </button>
                </div>
            </div>
            @endif

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

{{-- 🎯 Javascript จัดการปุ่มกดเพิ่ม/ลบ สำหรับการแก้ไข (เฉพาะลูกค้าบุคคล) --}}
@if(isset($customer->type) && $customer->type === 'individual')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('members-container');
        const btnAdd = document.getElementById('btn-add-member');

        if (btnAdd && container) {
            btnAdd.addEventListener('click', function() {
                const currentRows = container.querySelectorAll('.member-row').length;
                const nextCount = currentRows + 2; 

                const row = document.createElement('div');
                row.className = 'flex flex-col sm:flex-row gap-3 items-start sm:items-center bg-gray-50 p-3 rounded-lg border border-gray-200 member-row animate-fadeIn';
                
                row.innerHTML = `
                    <div class="flex-1 w-full">
                        <input type="text" name="extra_names[]" placeholder="ชื่อ-นามสกุล ผู้เรียนร่วมคนที่ ${nextCount}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white" required>
                    </div>
                    <div class="flex-1 w-full">
                        <input type="text" name="extra_phones[]" placeholder="เบอร์โทรศัพท์ (ถ้ามี)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                    </div>
                    <div class="flex-1 w-full">
                        <input type="email" name="extra_emails[]" placeholder="อีเมล / Gmail (ถ้ามี)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                    </div>
                    <button type="button" class="btn-remove-member inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 focus:outline-none transition-colors w-full sm:w-auto shrink-0">
                        <i class="fa-solid fa-trash-can mr-1"></i> ลบ
                    </button>
                `;
                container.appendChild(row);
            });

            container.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.btn-remove-member');
                if (removeBtn) {
                    removeBtn.closest('.member-row').remove();
                    renamePlaceholders();
                }
            });
        }

        // ฟังก์ชันจัดเลขลำดับ Placeholder ใหม่หลังจากมีการลบแถวออก
        function renamePlaceholders() {
            const rows = container.querySelectorAll('.member-row');
            rows.forEach((row, index) => {
                const input = row.querySelector('input[name="extra_names[]"]');
                if (input) {
                    input.placeholder = `ชื่อ-นามสกุล ผู้เรียนร่วมคนที่ ${index + 2}`;
                }
            });
        }
    });
</script>
@endif
@endsection