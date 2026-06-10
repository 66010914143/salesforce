@extends('layouts.app')

@section('page_title', request('type') === 'individual' ? 'เพิ่มลูกค้าบุคคลใหม่' : 'เพิ่มลูกค้า / บริษัทใหม่')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <a href="{{ route('customers.index') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
            <i class="fa-solid fa-arrow-left-long mr-2"></i> กลับไปหน้ารายชื่อลูกค้า
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-slate-50">
            <h3 class="text-base font-bold text-gray-800">
                @if(request('type') === 'individual')
                    แบบฟอร์มกรอกข้อมูลลูกค้าบุคคล
                @else
                    แบบฟอร์มกรอกข้อมูลลูกค้าองค์กร (organization)
                @endif
            </h3>           
        </div>

        <form action="{{ route('customers.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <input type="hidden" name="type" value="{{ request('type', 'corporate') }}">

            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                    @if(request('type') === 'individual')
                        ชื่อ-นามสกุลลูกค้า <span class="text-rose-500">*</span>
                    @else
                        ชื่อบริษัท / ชื่อองค์กร <span class="text-rose-500">*</span>
                    @endif
                </label>
                <input type="text" name="company_name" id="company_name" required 
                    placeholder="{{ request('type') === 'individual' ? 'ตัวอย่าง: นายสมชาย ใจดี' : 'ตัวอย่าง: บริษัท เอบีซี จำกัด (มหาชน)' }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                @error('company_name')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 {{ request('type') === 'individual' ? '' : 'md:grid-cols-2' }} gap-4">
                @if(request('type') !== 'individual')
                <div>
                    <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อผู้ติดต่อหลัก</label>
                    <input type="text" name="contact_name" id="contact_name" placeholder="ตัวอย่าง: คุณสมชาย ใจดี" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
                @endif

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" id="phone" placeholder="ตัวอย่าง: 0812345678" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">อีเมลติดต่อ</label>
                <input type="email" name="email" id="email" placeholder="ตัวอย่าง: contact@company.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>

            @if(request('type') === 'individual')
            <div class="pt-4 border-t border-gray-100 space-y-3">
                <input type="hidden" name="total_people" id="total_people" value="1">
                
                <label class="block text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-users text-indigo-500"></i> รายชื่อผู้เรียนร่วมเพิ่มเติม (ถ้ามี)
                </label>
                
                <div id="members-container" class="space-y-3">
                </div>
                
                <div>
                    <button type="button" id="btn-add-member" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                        <i class="fa-solid fa-plus mr-1.5 text-indigo-500"></i> เพิ่มรายชื่อผู้เรียนร่วม
                    </button>
                </div>
            </div>
            @endif

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

@if(request('type') === 'individual')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('members-container');
        const btnAdd = document.getElementById('btn-add-member');
        const totalPeopleInput = document.getElementById('total_people');

        if (btnAdd && container) {
            btnAdd.addEventListener('click', function() {
                const currentRows = container.querySelectorAll('.member-row').length;
                const nextCount = currentRows + 2; // คนแรกคือลูกค้าหลัก แถวถัดไปจึงเริ่มนับคนที่ 2

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
                updateTotalPeopleCount();
            });

            container.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.btn-remove-member');
                if (removeBtn) {
                    removeBtn.closest('.member-row').remove();
                    updateTotalPeopleCount();
                    renamePlaceholders();
                }
            });
        }

        // ฟังก์ชันอัปเดตจำนวนคนทั้งหมดลง Input ลับ
        function updateTotalPeopleCount() {
            const extraCount = container.querySelectorAll('.member-row').length;
            totalPeopleInput.value = 1 + extraCount;
        }

        // ฟังก์ชันจัดเลขลำดับ Placeholder ใหม่หลังจากมีการลบแถวออก
        function renamePlaceholders() {
            const rows = container.querySelectorAll('.member-row');
            rows.forEach((row, index) => {
                const input = row.querySelector('input[name="extra_names[]"]');
                if (input) {
                    input.placeholder = 'ชื่อ-นามสกุล ผู้เรียนร่วมคนที่ ' + (index + 2);
                }
            });
        }
    });
</script>
@endif
@endsection