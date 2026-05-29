@extends('layouts.app')

@section('page_title', 'บันทึกงานขาย (Deals)')

@section('content')

@php
    // คำนวณจำนวนงานค้างแบบ Real-time สำหรับแสดงบน Badge
    $isUserAdmin = auth()->check() && auth()->user()->isAdmin();
    $currentUserId = auth()->id();

    // นับจำนวน Following
    $followingBadge = \Illuminate\Support\Facades\DB::table('sales_deals')
        ->where('status', 'Following')
        ->when(!$isUserAdmin, function($q) use ($currentUserId) {
            $q->where('user_id', $currentUserId);
        })->count();

    // นับจำนวน Forecast
    $forecastBadge = \Illuminate\Support\Facades\DB::table('sales_deals')
        ->where('status', 'Forecast')
        ->when(!$isUserAdmin, function($q) use ($currentUserId) {
            $q->where('user_id', $currentUserId);
        })->count();

    $totalPendingCount = $followingBadge + $forecastBadge;
@endphp

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* ปรับแต่งสไตล์ Select2 ให้เข้ากับธีม Tailwind CSS ของหน้าเดิม */
    .select2-container--default .select2-selection--single {
        background-color: #f9fafb !important;
        border-color: #d1d5db !important;
        border-radius: 0.5rem !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
        padding-left: 4px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111827 !important;
        font-size: 0.875rem !important;
    }
    .select2-dropdown {
        border-color: #d1d5db !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
    }
    .select2-search__field {
        border-color: #d1d5db !important;
        border-radius: 0.375rem !important;
        padding: 5px 8px !important;
    }

    /* เพิ่มการตกแต่ง UI สำหรับ Select2 Multiple Select ให้เข้ากับดีไซน์เดิม */
    .select2-container--default .select2-selection--multiple {
        background-color: #ffffff !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem !important;
        min-height: 32px !important;
        display: inline-flex !important;
        align-items: center !important;
        padding-left: 4px !important;
        padding-right: 4px !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        color: #334155 !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
        padding: 2px 0 !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        color: #334155 !important;
        border-radius: 0.25rem !important;
        padding: 2px 6px !important;
        font-size: 0.75rem !important;
        margin: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #ef4444 !important;
        margin-right: 4px !important;
        border: none !important;
        background: transparent !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background: transparent !important;
        color: #b91c1c !important;
    }
</style>

<div class="space-y-6">

    @if($totalPendingCount > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-xl shadow-sm animate-pulse-once">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-bell text-amber-500 text-xl animate-bounce"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-800 font-bold">
                            ⚠️ แจ้งเตือนงานรอการติดตาม!
                        </p>
                        <p class="text-sm text-amber-700 mt-0.5">
                            คุณการขายงานสถานะ <span class="font-bold">Following</span> หรือ <span class="font-bold">Forecast</span> ที่ต้องเร่งติดตามจำนวน <span class="text-red-600 font-bold text-base px-1">{{ $totalPendingCount }}</span> รายการ เพื่อปิดการขายให้สำเร็จ
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div>
            <h3 class="text-lg font-bold text-gray-800">รายการขายและสถานะการติดตามงานขาย</h3>
            <p class="text-gray-500 text-sm mt-1">ติดตามสถานะเงิน Forecast, Closed Sale และงานที่กำลัง Following ขององค์กร</p>
        </div>
        <div>
            <a href="{{ route('deals.create') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg font-medium text-sm transition-colors shadow-sm">
                <i class="fa-solid fa-file-invoice-dollar mr-2"></i> บันทึกการขายใหม่
            </a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-filter text-indigo-500 text-lg"></i>
            <span class="font-semibold text-gray-750 text-sm">เครื่องมือคัดกรอง: เลือกดูงานขายตามเงื่อนไข</span>
        </div>
        
        <form action="{{ route('deals.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            
            @if(auth()->check() && auth()->user()->isAdmin())
                <div class="w-full sm:w-auto min-w-[220px]">
                    <select name="sales_person_id" id="sales-search-select" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 w-full cursor-pointer">
                        <option value="">-- แสดงการขายของพนักงานทุกคน --</option>
                        @foreach($salesPersons as $person)
                            <option value="{{ $person->id }}" {{ ($selectedSalesPerson ?? '') == $person->id ? 'selected' : '' }}>
                                {{ $person->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="w-full sm:w-auto">
                <select name="month" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 min-w-[140px] cursor-pointer h-[42px]">
                    <option value="">-- ทุกเดือน --</option>
                    @php
                        $months = [
                            '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
                            '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
                            '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
                        ];
                    @endphp
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ ($selectedMonth ?? '') == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto">
                <select name="year" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 min-w-[120px] cursor-pointer h-[42px]">
                    <option value="">-- ทุกปี --</option>
                    @php
                        $currentYear = date('Y');
                    @endphp
                    @for($y = $currentYear + 1; $y >= $currentYear - 5; $y--)
                        <option value="{{ $y }}" {{ ($selectedYear ?? '') == $y ? 'selected' : '' }}>
                            พ.ศ. {{ $y + 543 }} ({{ $y }})
                        </option>
                    @endfor
                </select>
            </div>
            
            @if(($selectedSalesPerson ?? '') || ($selectedMonth ?? '') || ($selectedYear ?? ''))
                <a href="{{ route('deals.index', ['status' => $status]) }}" class="text-xs text-rose-500 hover:underline flex items-center gap-1 ml-1" title="ล้างการกรองทั้งหมด">
                    <i class="fa-solid fa-rotate-left"></i> ล้างตัวกรอง
                </a>
            @endif
        </form>
    </div>

    <div class="flex flex-wrap gap-2 text-sm">
        <a href="{{ route('deals.index', ['sales_person_id' => $selectedSalesPerson, 'month' => $selectedMonth ?? '', 'year' => $selectedYear ?? '']) }}" class="flex items-center px-4 py-2 rounded-lg font-medium transition-colors {{ !$status ? 'bg-slate-800 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            ทั้งหมด
        </a>
        <a href="{{ route('deals.index', ['status' => 'Closed Sale', 'sales_person_id' => $selectedSalesPerson, 'month' => $selectedMonth ?? '', 'year' => $selectedYear ?? '']) }}" class="flex items-center px-4 py-2 rounded-lg font-medium transition-colors {{ $status == 'Closed Sale' ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-600 border border-gray-200 hover:bg-emerald-50' }}">
            Closed Sale
        </a>
        <a href="{{ route('deals.index', ['status' => 'Following', 'sales_person_id' => $selectedSalesPerson, 'month' => $selectedMonth ?? '', 'year' => $selectedYear ?? '']) }}" class="flex items-center px-4 py-2 rounded-lg font-medium transition-colors {{ $status == 'Following' ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border border-gray-200 hover:bg-blue-50' }}">
            Following
            @if($followingBadge > 0)
                <span class="ml-1.5 inline-flex items-center justify-center min-w-[20px] px-1.5 py-0.5 text-[11px] font-bold text-red-600 bg-red-100 rounded-full border border-red-200 animate-pulse">{{ $followingBadge }}</span>
            @endif
        </a>
        <a href="{{ route('deals.index', ['status' => 'Forecast', 'sales_person_id' => $selectedSalesPerson, 'month' => $selectedMonth ?? '', 'year' => $selectedYear ?? '']) }}" class="flex items-center px-4 py-2 rounded-lg font-medium transition-colors {{ $status == 'Forecast' ? 'bg-amber-600 text-white' : 'bg-white text-amber-600 border border-gray-200 hover:bg-amber-50' }}">
            Forecast
            @if($forecastBadge > 0)
                <span class="ml-1.5 inline-flex items-center justify-center min-w-[20px] px-1.5 py-0.5 text-[11px] font-bold text-red-600 bg-red-100 rounded-full border border-red-200 animate-pulse">{{ $forecastBadge }}</span>
            @endif
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-200 text-slate-600 text-xs uppercase tracking-wider font-semibold">
                        <th class="px-6 py-4">บริษัทลูกค้า</th>
                        <th class="px-6 py-4">พนักงาน</th> <th class="px-6 py-4">คอร์ส / สินค้า</th>
                        <th class="px-6 py-4 text-right">ราคา/คน</th>
                        <th class="px-6 py-4 text-center">จำนวนคน</th>
                        <th class="px-6 py-4 text-right">ยอดเงินรวม</th>
                        <th class="px-6 py-4 text-center">สถานะ / ความคืบหน้า</th>
                        <th class="px-6 py-4">บันทึกเพิ่มเติม</th>
                        <th class="px-6 py-4 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($deals as $deal)
                        @php 
                            $item = $deal->dealItems->first(); 
                            // คำนวณยอดรวมของการขายนี้สดๆ จากทุกคอร์สรวมกัน
                            $dealTotal = 0;
                            foreach($deal->dealItems as $dItem) {
                                // 🛠️ ดึงตัวแปรส่วนลดให้ตรงกับ Database
                                $itemDiscount = $dItem->discount ?? $dItem->discount_per_person ?? 0;
                                $itemTotal = ($dItem->price_per_person - $itemDiscount) * $dItem->total_person;
                                if($itemTotal < 0) $itemTotal = 0;
                                $dealTotal += $itemTotal;
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900">
                                <div>{{ $deal->customer->company_name }}</div>
                                @if($deal->group)
                                    <span class="inline-flex items-center text-[11px] font-normal text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded mt-1">
                                        📁 กลุ่ม: {{ $deal->group }}
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 text-gray-700 font-medium">
                                <span class="inline-flex items-center gap-1 text-slate-700">
                                    👤 {{ $deal->salesPerson->name ?? $deal->user->name ?? 'ไม่ระบุ' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-gray-700">
                                @if($item)
                                    {{ $item->course->course_name ?? 'ไม่ระบุ' }}
                                    @if($deal->dealItems->count() > 1)
                                        <span class="text-xs text-indigo-500 font-semibold ml-1">(+อีก {{ $deal->dealItems->count() - 1 }} คอร์ส)</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">ยังไม่มีคอร์ส</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-gray-600">
                                {{ $item ? '฿'.number_format($item->price_per_person ?? 0, 2) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center text-gray-600 font-semibold">
                                {{ $item ? number_format($item->total_person ?? 0) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">฿{{ number_format($dealTotal, 2) }}</td>
                            
                            <td class="px-6 py-4 text-center">
                                @if($deal->status == 'Closed Sale')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Closed Sale
                                    </span>
                                @elseif($deal->status == 'Following')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span> Following
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span> Forecast
                                    </span>
                                @endif

                                <div class="mt-2">
                                    @if($deal->status == 'Closed Sale')
                                        <span class="inline-flex items-center text-[11px] font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-100">
                                            🎉 ปิดการขายสำเร็จ
                                        </span>
                                    @elseif($deal->progress)
                                        <span class="inline-flex items-center text-[11px] font-medium text-indigo-700 bg-indigo-50 px-2 py-1 rounded-md border border-indigo-200 shadow-sm">
                                            📌 {{ $deal->progress }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-[11px] font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
                                            - ยังไม่มีอัปเดต -
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @if($deal->receipt_no)
                                    <div class="text-[11px] font-bold text-indigo-600 mb-1 flex items-center gap-1">
                                        <span>🧾 เลขที่: {{ $deal->receipt_no }}</span>
                                    </div>
                                @endif
                                <div class="text-xs text-gray-600 max-w-xs truncate" title="{{ $deal->note ?? $deal->updated_note }}">
                                    {{ $deal->updated_note ?? $deal->note ?? '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('deals.items', $deal->id) }}" class="inline-flex items-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-3 py-1.5 rounded-lg text-xs transition-colors border border-gray-200">
                                        ⚙️ จัดการคอร์ส ({{ $deal->dealItems->count() }})
                                    </a>
                                    <a href="{{ route('deals.edit', $deal->id) }}" class="inline-flex items-center bg-amber-50 hover:bg-amber-100 text-amber-700 font-medium px-3 py-1.5 rounded-lg text-xs transition-colors border border-amber-200">
                                        ✏️ แก้ไขการขาย
                                    </a>
                                    
                                    @if(auth()->user()->isAdmin() || strtolower(auth()->user()->role) === 'manager')
                                        <form action="{{ route('deals.destroy', $deal->id) }}" method="POST" onsubmit="return confirm('⚠️ ยืนยันลบการขาย: คุณแน่ใจใช่ไหมว่าต้องการลบการขายนี้ออกจากระบบอย่างถาวร?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center bg-rose-50 hover:bg-rose-100 text-rose-600 font-medium px-3 py-1.5 rounded-lg text-xs transition-colors border border-rose-200">
                                                🗑️ ลบการขาย
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-400"> <i class="fa-solid fa-file-circle-xmark text-3xl mb-2 block"></i>
                                ยังไม่มีการเปิดการขายในระบบ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deals->hasPages())
            <div class="p-4 border-t border-gray-100 bg-slate-50">
                {{ $deals->appends(['status' => $status, 'sales_person_id' => $selectedSalesPerson, 'month' => $selectedMonth ?? '', 'year' => $selectedYear ?? ''])->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    $(document).ready(function() {
        $('#sales-search-select').select2({
            placeholder: "-- พิมพ์เพื่อค้นหาชื่อพนักงาน --",
            allowClear: true,
            width: 'resolve'
        });
    });

    // 🟢 แจ้งเตือนงานค้างด้วย SweetAlert2 (จะเด้งเมื่อมีงานที่ยังไม่ได้ปิดการขาย)
    document.addEventListener("DOMContentLoaded", function() {
        let pendingCount = {{ $totalPendingCount }};
        
        // ใช้ Session Storage เช็คเพื่อไม่ให้เด้งรบกวนทุกครั้งที่กด Refresh ให้เด้งแค่ตอนเปิดหน้านี้ครั้งแรก
        if (pendingCount > 0 && !sessionStorage.getItem('pendingAlertShown')) {
            Swal.fire({
                title: '<span style="color:#b45309;">แจ้งเตือนงานค้าง!</span>',
                html: `คุณมีงานขายในสถานะ <b>Following</b> และ <b>Forecast</b> จำนวน <b style="color:#ef4444; font-size:1.1rem;">${pendingCount}</b> งาน <br><span style="font-size:0.9rem; color:#6b7280; margin-top:8px; display:block;">โปรดติดตามงานและอัปเดตเป็น Closed Sale เมื่อปิดการขายเรียบร้อยแล้ว</span>`,
                icon: 'warning',
                confirmButtonText: 'รับทราบ',
                confirmButtonColor: '#f59e0b',
                toast: true, 
                position: 'top-end',
                timer: 6000, 
                timerProgressBar: true,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
            
            // บันทึกไว้ว่าแจ้งเตือนแล้ว จะได้ไม่เด้งซ้ำรัวๆ
            sessionStorage.setItem('pendingAlertShown', 'true');
        }
    });
</script>
@endsection