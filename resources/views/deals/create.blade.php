@extends('layouts.app')

@section('page_title', 'บันทึกการขายงานใหม่')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<style>
    /* ปรับแต่งหน้าตา Tom Select ให้เข้ากับ Tailwind CSS ของฟอร์มเดิม */
    .ts-control {
        border-radius: 0.5rem !important;
        border-color: #d1d5db !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
        line-height: 1.25rem !important;
        box-shadow: none !important;
        background-color: #fff !important;
    }
    .ts-wrapper.focus .ts-control {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2) !important;
    }
    .ts-dropdown {
        border-radius: 0.5rem !important;
        font-size: 0.875rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    }
    /* เพิ่มเติมสไตล์ Badge สรุปจำนวนคนในปุ่มเลือก */
    .badge-member-count {
        background-color: #e0e7ff;
        color: #4f46e5;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
</style>

<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <a href="{{ route('deals.index') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
            <i class="fa-solid fa-arrow-left-long mr-2"></i> กลับไปหน้ารายการงานขาย
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-slate-50">
            <h3 class="text-base font-bold text-gray-800">สร้างการขายใหม่ (Deal Information)</h3>
            <p class="text-gray-500 text-xs mt-1">กรอกข้อมูลบริษัทลูกค้าและสถานะการขาย (สามารถเพิ่มคอร์สและสินค้าได้ในขั้นตอนถัดไป)</p>
        </div>

        @if ($errors->any())
            <div class="p-4 mx-6 mt-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-sm rounded-r-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('deals.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <input type="hidden" name="user_id" value="{{ auth()->id() ?? 1 }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="customer_type" class="block text-sm font-medium text-gray-700 mb-1">ประเภทลูกค้า <span class="text-rose-500">*</span></label>
                    <select id="customer_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                        <option value="corporate">ลูกค้าองค์กร / หน่วยงาน</option>
                        <option value="individual">ลูกค้าบุคคล</option>
                    </select>
                </div>

                <div>
                    <label for="deal_date" class="block text-sm font-medium text-gray-700 mb-1">วันที่บันทึกการขาย <span class="text-rose-500">*</span></label>
                    <input type="date" name="deal_date" id="deal_date" required value="{{ old('deal_date', date('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>

                <div class="md:col-span-2">
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">เลือกบริษัทคู่ค้า / ลูกค้า <span class="text-rose-500">*</span></label>
                    <select name="customer_id" id="customer_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                        <option value="">-- พิมพ์ค้นหาหรือเลือกรายชื่อ --</option>
                        @foreach($customers as $customer)
                            @php
                                // แอบดึงข้อมูลรายชื่อย่อยออกมาจากก้อนตัวแปรของระบบเดิมอย่างปลอดภัย
                                $subMembers = collect([]);
                                if (isset($customer->subCustomers)) { $subMembers = $customer->subCustomers; }
                                elseif (isset($customer->children)) { $subMembers = $customer->children; }
                                elseif (isset($customer->sub_customers)) { $subMembers = $customer->sub_customers; }
                                elseif (isset($customer->members)) { $subMembers = $customer->members; }
                                
                                $cleanName = $customer->type === 'individual' ? ($customer->name ?? $customer->company_name) : $customer->company_name;
                            @endphp
                            <option value="{{ $customer->id }}" data-type="{{ $customer->type ?? 'corporate' }}" data-clean-name="{{ $cleanName }}" data-sub-members="{{ json_encode($subMembers) }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $cleanName }}
                            </option>
                        @endforeach
                    </select>

                    <div id="sub_members_card_box" class="mt-2 hidden">
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-600 shadow-inner">
                            <div class="font-bold text-slate-700 mb-2 flex items-center gap-1.5 text-sm">
                                <i class="fa-solid fa-address-book text-indigo-500"></i> รายชื่อผู้เรียนร่วมเพิ่มเติม:
                            </div>
                            <ul id="sub_members_list_render" class="space-y-1.5 pl-1">
                                </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">สถานะการขาย <span class="text-rose-500">*</span></label>
                    <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                        <option value="">-- เลือกสถานะการขาย --</option>
                        @foreach($mainStatuses as $statusItem)
                            <option value="{{ $statusItem->name }}" {{ old('status') == $statusItem->name ? 'selected' : '' }}>{{ $statusItem->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="progress" class="block text-sm font-medium text-gray-700 mb-1">สถานะย่อย</label>
                    <select name="progress" id="progress" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                        <option value="">-- เลือกสถานะย่อย --</option>
                        @foreach($subStatuses as $sub)
                            <option value="{{ $sub->name }}" {{ old('progress') == $sub->name ? 'selected' : '' }}>{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label for="group" class="block text-sm font-medium text-gray-700 mb-1">กลุ่มลูกค้า</label>
                    <select name="group" id="group" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                        <option value="">-- เลือก --</option>
                        @foreach($customerGroups as $group)
                            <option value="{{ $group->name }}" {{ old('group') == $group->name ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่</label>
                    <select name="category" id="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                        <option value="">-- เลือก --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ old('category') == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tools" class="block text-sm font-medium text-gray-700 mb-1">ช่องทาง</label>
                    <select name="tools" id="tools" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                        <option value="">-- เลือก --</option>
                        @foreach($channels as $channel)
                            <option value="{{ $channel->name }}" {{ old('tools') == $channel->name ? 'selected' : '' }}>{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="promotion" class="block text-sm font-medium text-gray-700 mb-1">โปรโมชัน</label>
                    <input type="text" name="promotion" id="promotion" value="{{ old('promotion') }}" placeholder="โปรโมชัน / ส่วนลดพิเศษ" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="receipt_no" class="block text-sm font-medium text-gray-700 mb-1">เลขที่ใบเสร็จ</label>
                    <input type="text" name="receipt_no" id="receipt_no" value="{{ old('receipt_no') }}" placeholder="ระบุเลขที่ใบเสร็จ (ถ้ามี)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
                <div>
                    <label for="quotation_no" class="block text-sm font-medium text-gray-700 mb-1">เลขที่ใบเสนอราคา</label>
                    <input type="text" name="quotation_no" id="quotation_no" value="{{ old('quotation_no') }}" placeholder="ระบุเลขที่ใบเสนอราคา (ถ้ามี)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>

            <div>
                <label for="updated_note" class="block text-sm font-medium text-gray-700 mb-1">บันทึกเพิ่มเติม (Up-dated Noted)</label>
                <textarea name="updated_note" id="updated_note" rows="3" placeholder="พิมพ์หมายเหตุ ความคืบหน้า หรือรายละเอียดเพิ่มเติมเกี่ยวกับการขายนี้..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('updated_note') }}</textarea>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('deals.index') }}" class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                    ยกเลิก
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                    <i class="fa-solid fa-arrow-right mr-1"></i> บันทึกและไปเพิ่มคอร์ส
                </button>
            </div>
        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const typeSelect = document.getElementById('customer_type');
    const customerSelect = document.getElementById('customer_id');
    
    const cardBox = document.getElementById('sub_members_card_box');
    const listRender = document.getElementById('sub_members_list_render');
    
    // 1. สำรองข้อมูลรายชื่อลูกค้าทั้งหมด และดึงข้อมูลรายชื่อย่อยเข้าอาร์เรย์กลางอย่างรอบคอบก่อนโครงสร้างพัง
    const allOptions = Array.from(customerSelect.options)
        .filter(opt => opt.value !== "")
        .map(opt => {
            let subData = [];
            try {
                subData = JSON.parse(opt.getAttribute('data-sub-members') || '[]');
            } catch(e) {
                subData = [];
            }
            return {
                value: opt.value,
                text: opt.getAttribute('data-clean-name') || opt.text,
                type: opt.getAttribute('data-type') || 'corporate',
                subMembers: subData // ฝังข้อมูลลูกทีมไว้เรียกใช้งาน
            };
        });

    // 2. เรียกใช้งานและ Custom ตัววาดหน้าตาของระบบ Tom Select (ดึงปุ่ม badge ยอดรวม และหน้าต่างเลือกให้มีรูปแบบเหมือนรูปตัวอย่าง)
    const ts = new TomSelect(customerSelect, {
        create: false,
        valueField: 'value',
        labelField: 'text',
        searchField: ['text'],
        placeholder: "-- พิมพ์ค้นหาหรือเลือกรายชื่อ --",
        allowEmptyOption: true,
        render: {
            // หน้าตากล่องผลลัพธ์หลังจากเลือกเสร็จสิ้น
            item: function(data, escape) {
                return `<div>${escape(data.text)}</div>`;
            },
            // หน้าตาของแถวตัวเลือกที่ดรอปดาวน์สยายลงมาตอนพิมพ์ค้นหา
            option: function(data, escape) {
                return `<div class="py-2 px-3 flex items-center justify-between">
                    <span>${escape(data.text)}</span>
                </div>`;
            }
        }
    });

    // 3. ฟังก์ชันสลับรายการลูกค้าตามประเภทที่กดเลือก
    function filterCustomers() {
        const selectedType = typeSelect.value;
        
        // ล้างค่าที่เคยเลือกและตัวเลือกทั้งหมดในปัจจุบันออกก่อน
        ts.clearOptions();
        ts.clear(); 
        
        // กรองเอาเฉพาะข้อมูลลูกค้าประเภทที่เลือกแมตช์กัน
        const filtered = allOptions.filter(opt => opt.type === selectedType);
        
        // นำตัวเลือกใหม่ที่กรองแล้วใส่กลับเข้าไปในระบบค้นหา
        ts.addOptions(filtered);
        ts.refreshOptions(false);
    }

    // 4. ผูกเหตุการณ์ตรวจจับเมื่อผู้ใช้งานเปลี่ยนประเภทลูกค้า
    typeSelect.addEventListener('change', filterCustomers);

    // เพิ่มเติม: ตัวตรวจจับเมื่อเลือกรายชื่อเสร็จ ให้ดึงรายชื่อย่อยทั้งหมดสยายมาเป็นการ์ดสีเทาข้างใต้ทันที
    ts.on('change', function(value) {
        cardBox.classList.add('hidden');
        listRender.innerHTML = '';

        if (!value) return;

        const selectedCustomer = allOptions.find(item => item.value == value);
        
        if (selectedCustomer && selectedCustomer.subMembers && selectedCustomer.subMembers.length > 0) {
            selectedCustomer.subMembers.forEach(m => {
                const li = document.createElement('li');
                li.className = 'flex items-center gap-1 py-0.5 text-slate-600';
                
                let name = m.name || m.company_name || 'ไม่ระบุชื่อ';
                let phoneInfo = m.phone ? `(โทร: ${m.phone}` : '';
                let emailInfo = m.email ? `, อีเมล: ${m.email})` : '';
                
                if(m.phone && !m.email) phoneInfo += ')';
                
                li.innerHTML = `<i class="fa-solid fa-minus text-slate-400 text-[10px] mr-1"></i> <span>- ${name} <span class="text-slate-400 font-light">${phoneInfo}${emailInfo}</span></span>`;
                listRender.appendChild(li);
            });
            
            cardBox.classList.remove('hidden');
        }
    });

    // 5. รองรับระบบ Old Value กรณีบันทึกฟอร์มไม่ผ่าน ให้กลับมาแสดงข้อมูลเดิมได้อย่างแม่นยำ
    const oldSelectedValue = "{{ old('customer_id') }}";
    if (oldSelectedValue) {
        const matchedOpt = allOptions.find(o => o.value == oldSelectedValue);
        if (matchedOpt) {
            typeSelect.value = matchedOpt.type;
        }
    }
    
    // รันทำงานเริ่มต้นครั้งแรกทันที
    filterCustomers();
    
    if (oldSelectedValue) {
        ts.setValue(oldSelectedValue);
        ts.trigger('change', oldSelectedValue);
    }
});
</script>
@endsection