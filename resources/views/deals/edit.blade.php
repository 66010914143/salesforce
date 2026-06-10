@extends('layouts.app')

@section('page_title', 'แก้ไขสถานะและอัปเดตการ')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-200 gap-4">
        <div>
            @if(isset($deal->customer) && $deal->customer->type === 'corporate')
                <h3 class="text-lg font-bold text-gray-800">🏢 อัปเดตสถานะการขาย บริษัท: {{ $deal->customer->company_name ?? 'ไม่ระบุชื่อบริษัท' }}</h3>
            @else
                <h3 class="text-lg font-bold text-gray-800">👤 อัปเดตสถานะการขาย ลูกค้า: {{ $deal->customer->company_name ?? $deal->customer->name ?? 'ไม่ระบุชื่อลูกค้า' }}</h3>
            @endif
            <p class="text-gray-500 text-sm mt-1">ปรับปรุงสถานะการติดตามงาน และบันทึกความคืบหน้าล่าสุด</p>
        </div>
        <a href="{{ route('deals.index') }}" class="inline-flex items-center justify-center text-sm text-slate-500 hover:text-slate-800 border border-slate-200 px-4 py-2 rounded-lg hover:bg-slate-50 transition-colors">
            ⬅️ ยกเลิก / กลับหน้าตาราง
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('deals.update', $deal->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-bold text-gray-700">
                            @if(isset($deal->customer) && $deal->customer->type === 'corporate')
                                บริษัท <span class="text-red-500">*</span>
                            @else
                                ลูกค้า <span class="text-red-500">*</span>
                            @endif
                        </label>

                        @if(isset($deal->customer) && $deal->customer->type !== 'corporate')
                            <div class="relative" x-data="{ open: false }">
                                <button @click.prevent="open = !open" type="button" class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-indigo-600 border border-indigo-200 hover:bg-indigo-50 transition-colors focus:outline-none shadow-sm cursor-pointer">
                                    📋 รายชื่อ ({{ $deal->customer->total_people ?? ($deal->customer->students ? $deal->customer->students->count() + 1 : 1) }} คน)
                                    <svg class="h-3 w-3 text-indigo-400 transition-transform" :class="{'rotate-180': open}" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" 
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute left-0 mt-1.5 w-72 rounded-xl bg-white p-2 shadow-xl ring-1 ring-black/5 z-50 border border-gray-200" 
                                     style="display: none;">
                                    
                                    <div class="p-3">
                                        <p class="text-xs text-gray-500 mb-2 border-b pb-1">รายชื่อผู้เรียนทั้งหมดในดีลนี้</p>
                                        <ul class="text-sm space-y-2 text-gray-700">
                                            @php $nameIndex = 1; @endphp
                                            
                                            <li class="text-indigo-600 font-medium flex items-start gap-2">
                                                <i class="fa-solid fa-user-check text-xs mt-1"></i> 
                                                <span class="leading-tight">
                                                    {{ $nameIndex++ }}. {{ $deal->customer->name ?? $deal->customer->company_name ?? 'ไม่ระบุ' }} (หลัก)
                                                    @if(!empty($deal->customer->phone) || !empty($deal->customer->email))
                                                        <span class="text-xs font-normal text-indigo-500">
                                                            <br>(โทร: {{ $deal->customer->phone ?? '-' }}, อีเมล: {{ $deal->customer->email ?? '-' }})
                                                        </span>
                                                    @endif
                                                </span>
                                            </li>
                                            
                                            @if(isset($deal->customer->students) && $deal->customer->students->count() > 0)
                                                @foreach($deal->customer->students as $student)
                                                    <li class="flex items-start gap-2 text-gray-600">
                                                        <i class="fa-solid fa-user text-xs text-gray-400 mt-1"></i>
                                                        <span class="leading-tight">
                                                            {{ $nameIndex++ }}. {{ $student->name ?? $student->full_name ?? 'ไม่ระบุชื่อ' }}
                                                            @if(!empty($student->phone) || !empty($student->email))
                                                                <span class="text-xs text-gray-500">
                                                                    <br>(โทร: {{ $student->phone ?? '-' }}, อีเมล: {{ $student->email ?? '-' }})
                                                                </span>
                                                            @endif
                                                        </span>
                                                    </li>
                                                @endforeach
                                            @endif
                                            
                                            @if(isset($additional_names) && count($additional_names) > 0)
                                                @foreach($additional_names as $name)
                                                    <li class="flex items-start gap-2 text-gray-600">
                                                        <i class="fa-solid fa-user text-xs text-gray-400 mt-1"></i>
                                                        <span class="leading-tight">{{ $nameIndex++ }}. {{ $name }}</span>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                    </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="relative">
                        <div class="w-full rounded-lg border border-gray-300 pl-4 pr-36 py-2.5 text-sm bg-gray-100 text-gray-600 select-none cursor-not-allowed">
                            {{ $deal->customer->company_name ?? $deal->customer->name }}
                        </div>
                        <input type="hidden" name="customer_id" value="{{ $deal->customer_id }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">วันที่เปิดการขาย <span class="text-red-500">*</span></label>
                    <input type="date" name="deal_date" value="{{ \Carbon\Carbon::parse($deal->deal_date)->format('Y-m-d') }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-gray-50" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">หมวดหมู่กลุ่ม (Group)</label>
                    <select name="group" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <option value="SME" {{ $deal->group == 'SME' ? 'selected' : '' }}>SME</option>
                        <option value="Corporate" {{ $deal->group == 'Corporate' ? 'selected' : '' }}>Corporate</option>
                        <option value="Government" {{ $deal->group == 'Government' ? 'selected' : '' }}>Government (หน่วยงานรัฐ)</option>
                        <option value="Individual" {{ $deal->group == 'Individual' ? 'selected' : '' }}>Individual (บุคคลทั่วไป)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">ประเภทงาน / หมวดหมู่ (Category)</label>
                    <input type="text" name="category" value="{{ $deal->category }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="เช่น New (ลูกค้าใหม่), Renewal, Upsell">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">สถานะย่อย / ความคืบหน้า (Progress)</label>
                    <input type="text" name="progress" value="{{ $deal->progress }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="เช่น ติดตามครั้งที่ 1, รอใบเสนอราคา, นัดเข้าพบ">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">เลขที่ใบเสร็จ / ใบกำกับภาษี (ถ้ามี)</label>
                    <input type="text" name="receipt_no" value="{{ $deal->receipt_no }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="เช่น INV-202605001">
                </div>
            </div>

            <hr class="border-gray-100">

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-3">สถานะการขายปัจจุบัน (Pipeline) <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    
                    <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none transition-all {{ $deal->status == 'Forecast' ? 'border-amber-500 ring-1 ring-amber-500 bg-amber-50/30' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="radio" name="status" value="Forecast" class="sr-only" {{ $deal->status == 'Forecast' ? 'checked' : '' }} onchange="highlightRadio(this)">
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-bold text-amber-700">Forecast</span>
                                <span class="mt-1 flex items-center text-xs text-gray-500">คาดการณ์ / รอดำเนินการ</span>
                            </span>
                        </span>
                        <span class="check-icon absolute right-4 top-4 text-lg text-amber-500 {{ $deal->status == 'Forecast' ? 'block' : 'hidden' }}">✓</span>
                    </label>

                    <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none transition-all {{ $deal->status == 'Following' ? 'border-blue-500 ring-1 ring-blue-500 bg-blue-50/30' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="radio" name="status" value="Following" class="sr-only" {{ $deal->status == 'Following' ? 'checked' : '' }} onchange="highlightRadio(this)">
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-bold text-blue-700">Following</span>
                                <span class="mt-1 flex items-center text-xs text-gray-500">กำลังติดตาม / เสนอราคา</span>
                            </span>
                        </span>
                        <span class="check-icon absolute right-4 top-4 text-lg text-blue-500 {{ $deal->status == 'Following' ? 'block' : 'hidden' }}">✓</span>
                    </label>

                    <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none transition-all {{ $deal->status == 'Closed Sale' ? 'border-emerald-500 ring-1 ring-emerald-500 bg-emerald-50/30' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="radio" name="status" value="Closed Sale" class="sr-only" {{ $deal->status == 'Closed Sale' ? 'checked' : '' }} onchange="highlightRadio(this)">
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-bold text-emerald-700">Closed Sale</span>
                                <span class="mt-1 flex items-center text-xs text-gray-500">ปิดการขายสำเร็จ (Won)</span>
                            </span>
                        </span>
                        <span class="check-icon absolute right-4 top-4 text-lg text-emerald-500 {{ $deal->status == 'Closed Sale' ? 'block' : 'hidden' }}">✓</span>
                    </label>

                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">บันทึกความคืบหน้า / โน้ต (Progress Note) 📝</label>
                <textarea name="note" rows="4" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="บันทึกการพูดคุยล่าสุด, สิ่งที่ต้องทำต่อไป, หรือเหตุผลที่ปิดการขาย...">{{ $deal->note ?? $deal->updated_note }}</textarea>
                <p class="text-xs text-gray-500 mt-2">บันทึกนี้จะนำไปแสดงในหน้าตารางรวมการขาย (Deals Index) เพื่อให้ทีมงานสามารถอัปเดตสถานการณ์ล่าสุดได้ทันที</p>
            </div>

            <div class="bg-gray-50 -mx-6 -mb-6 p-4 border-t border-gray-100 flex justify-end gap-3 rounded-b-xl">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-lg text-sm transition-colors shadow-sm">
                    💾 บันทึกอัปเดตการขาย
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                ⏱️ ประวัติการอัปเดตและกิจกรรมล่าสุด
            </h3>
            <span class="text-xs bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full font-semibold">
                ทั้งหมด {{ $deal->logs ? $deal->logs->count() : 0 }} รายการ
            </span>
        </div>
        
        <div class="p-6">
            @if($deal->logs && $deal->logs->count() > 0)
                <div class="relative border-l-2 border-gray-200 ml-3 space-y-8">
                    @foreach($deal->logs as $log)
                        <div class="relative pl-6">
                            @php
                                $dotColor = 'bg-gray-400';
                                $textColor = 'text-gray-600';
                                if($log->new_status == 'Closed Sale') { $dotColor = 'bg-emerald-500'; $textColor = 'text-emerald-600'; }
                                elseif($log->new_status == 'Following') { $dotColor = 'bg-blue-500'; $textColor = 'text-blue-600'; }
                                elseif($log->new_status == 'Forecast') { $dotColor = 'bg-amber-500'; $textColor = 'text-amber-600'; }
                            @endphp

                            <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $dotColor }} ring-4 ring-white"></div>
                            
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-baseline mb-1">
                                <h4 class="text-sm font-bold text-gray-800">
                                    @if($log->old_status != $log->new_status)
                                        เปลี่ยนสถานะเป็น <span class="{{ $textColor }}">{{ $log->new_status }}</span>
                                    @else
                                        อัปเดตข้อมูลเพิ่มเติม <span class="{{ $textColor }}">({{ $log->new_status }})</span>
                                    @endif
                                </h4>
                                <span class="text-xs text-gray-400 mt-1 sm:mt-0 font-medium">
                                    📅 {{ \Carbon\Carbon::parse($log->created_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i') }} น.
                                </span>
                            </div>
                            
                            <div class="text-xs text-gray-500 mb-2">
                                <span class="font-semibold text-gray-700">👤 ผู้บันทึก:</span> 
                                {{ $log->user->name ?? 'ไม่ระบุชื่อผู้ใช้' }}
                            </div>
                            
                            @if(!empty($log->note))
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-600 italic mt-1">
                                    "{{ $log->note }}"
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-400 text-sm">
                    📁 ยังไม่มีบันทึกประวัติกิจกรรมสำหรับการขายงานขายนี้
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function highlightRadio(element) {
        document.querySelectorAll('input[name="status"]').forEach(el => {
            const parent = el.closest('label');
            parent.className = 'relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none border-gray-200 hover:bg-gray-50 transition-all';
            const icon = parent.querySelector('.check-icon');
            if(icon) {
                icon.classList.add('hidden');
                icon.classList.remove('block');
            }
        });

        const parent = element.closest('label');
        const val = element.value;
        let colorClass = 'border-amber-500 ring-1 ring-amber-500 bg-amber-50/30';
        
        if (val === 'Following') {
            colorClass = 'border-blue-500 ring-1 ring-blue-500 bg-blue-50/30';
        } else if (val === 'Closed Sale') {
            colorClass = 'border-emerald-500 ring-1 ring-emerald-500 bg-emerald-50/30';
        }

        parent.className = `relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none transition-all ${colorClass}`;
        const icon = parent.querySelector('.check-icon');
        if(icon) {
            icon.classList.remove('hidden');
            icon.classList.add('block');
        }
    }
</script>
@endsection