@extends('layouts.app')

@section('page_title', 'บันทึกงานขาย (Deals)')

@section('content')

@php
    // คำนวณจำนวนงานค้างแบบ Real-time สำหรับแสดงบน Badge (อัปเดตให้รองรับ Manager)
    $isUserAdmin = auth()->check() && (auth()->user()->isAdmin() || strtolower(auth()->user()->role) === 'manager');
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

    // ดึงรายชื่อบริษัทที่ไม่ซ้ำกันทั้งหมดสำหรับนำมาสร้างเป็นตัวเลือกในช่องค้นหาบริษัท
    $uniqueCompanies = \Illuminate\Support\Facades\DB::table('sales_deals')
        ->join('customers', 'sales_deals.customer_id', '=', 'customers.id')
        ->when(!$isUserAdmin, function($q) use ($currentUserId) {
            $q->where('sales_deals.user_id', $currentUserId);
        })
        ->select('customers.company_name')
        ->whereNotNull('customers.company_name')
        ->distinct()
        ->orderBy('customers.company_name', 'asc')
        ->pluck('company_name');
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
        <div id="deal-alert-container" class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-xl shadow-sm animate-pulse-once hidden">
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap gap-4">
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
                <div>
                    <button type="button" id="btn-acknowledge-alert" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-lg text-xs transition-colors shadow-sm whitespace-nowrap">
                        รับทราบ
                    </button>
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
                <i class="fa-solid fa-file-invoice-dollar mr-2"></i>  เปิดการขาย องค์กร/บุคคล
            </a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col items-start justify-between gap-4">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-filter text-indigo-500 text-lg"></i>
            <span class="font-semibold text-gray-750 text-sm">เครื่องมือคัดกรอง: เลือกดูงานขายตามเงื่อนไข</span>
        </div>
        
        <form id="autoSubmitForm" action="{{ route('deals.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full">
            
            <div class="w-full sm:w-auto min-w-[220px] flex-1 sm:flex-none">
                <select name="search_company" id="company-search-select" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 w-full cursor-pointer h-[42px]" onchange="document.getElementById('autoSubmitForm').submit();">
                    <option value="">-- 🔍 ค้นหาชื่อบริษัท... --</option>
                    @foreach($uniqueCompanies as $companyName)
                        <option value="{{ $companyName }}" {{ (request('search_company') ?? $searchCompany ?? '') == $companyName ? 'selected' : '' }}>
                            {{ $companyName }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(auth()->check() && (auth()->user()->isAdmin() || strtolower(auth()->user()->role) === 'manager'))
                <div class="w-full sm:w-auto min-w-[200px] flex-1 sm:flex-none">
                    <select name="sales_person_id" id="sales-search-select" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 w-full cursor-pointer h-[42px]" onchange="document.getElementById('autoSubmitForm').submit();">
                        <option value="">-- แสดงพนักงานทุกคน --</option>
                        @foreach($salesPersons as $person)
                            <option value="{{ $person->id }}" {{ (request('sales_person_id') ?? $selectedSalesPerson ?? '') == $person->id ? 'selected' : '' }}>
                                {{ $person->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="w-full sm:w-auto min-w-[160px] flex-1 sm:flex-none">
                <select name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 w-full cursor-pointer h-[42px]" onchange="document.getElementById('autoSubmitForm').submit();">
                    <option value="">-- สถานะทั้งหมด --</option>
                    @php
                        $availableStatuses = ['Closed Sale', 'Following', 'Forecast'];
                    @endphp
                    @foreach($availableStatuses as $vStatus)
                        <option value="{{ $vStatus }}" {{ (request('status') ?? $status ?? '') == $vStatus ? 'selected' : '' }}>
                            {{ $vStatus }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[140px] flex-1 sm:flex-none">
                <select name="customer_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 w-full cursor-pointer h-[42px]" onchange="document.getElementById('autoSubmitForm').submit();">
                    <option value="">-- ทุกประเภท --</option>
                    <option value="organization" {{ (request('customer_type') ?? $customerType ?? '') == 'organization' ? 'selected' : '' }}>🏢 องค์กร</option>
                    <option value="individual" {{ (request('customer_type') ?? $customerType ?? '') == 'individual' ? 'selected' : '' }}>👤 บุคคล</option>
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[120px] flex-1 sm:flex-none">
                <select name="month" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 w-full cursor-pointer h-[42px]" onchange="document.getElementById('autoSubmitForm').submit();">
                    <option value="">-- ทุกเดือน --</option>
                    @php
                        $months = [
                            '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
                            '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
                            '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
                        ];
                    @endphp
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ (request('month') ?? $selectedMonth ?? '') == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[100px] flex-1 sm:flex-none">
                <select name="year" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 w-full cursor-pointer h-[42px]" onchange="document.getElementById('autoSubmitForm').submit();">
                    <option value="">-- ทุกปี --</option>
                    @php
                        $currentYear = date('Y');
                    @endphp
                    @for($y = $currentYear + 1; $y >= $currentYear - 5; $y--)
                        <option value="{{ $y }}" {{ (request('year') ?? $selectedYear ?? '') == $y ? 'selected' : '' }}>
                            {{ $y + 543 }}
                        </option>
                    @endfor
                </select>
            </div>
            
            <div class="flex items-center gap-2 ml-auto">
                @if(request('search_company') || request('customer_type') || request('sales_person_id') || request('month') || request('year') || request('status') || isset($selectedMonth) || isset($selectedYear) || isset($searchCompany))
                    <a href="{{ route('deals.index') }}" class="bg-rose-50 hover:bg-rose-100 text-rose-600 font-medium rounded-lg text-sm px-4 py-2.5 text-center transition-colors h-[42px] whitespace-nowrap shadow-sm border border-rose-200" title="ล้างการกรองทั้งหมด">
                        <i class="fa-solid fa-rotate-left"></i> ล้างการค้นหา
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="flex flex-wrap gap-2 text-sm">
        <a href="{{ route('deals.index', ['customer_type' => (request('customer_type') ?? $customerType ?? ''), 'sales_person_id' => (request('sales_person_id') ?? $selectedSalesPerson ?? ''), 'month' => (request('month') ?? $selectedMonth ?? ''), 'year' => (request('year') ?? $selectedYear ?? ''), 'search_company' => (request('search_company') ?? $searchCompany ?? '')]) }}" class="flex items-center px-4 py-2 rounded-lg font-medium transition-colors {{ !($status ?? request('status')) ? 'bg-slate-800 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            ทั้งหมด
        </a>
        <a href="{{ route('deals.index', ['status' => 'Closed Sale', 'customer_type' => (request('customer_type') ?? $customerType ?? ''), 'sales_person_id' => (request('sales_person_id') ?? $selectedSalesPerson ?? ''), 'month' => (request('month') ?? $selectedMonth ?? ''), 'year' => (request('year') ?? $selectedYear ?? ''), 'search_company' => (request('search_company') ?? $searchCompany ?? '')]) }}" class="flex items-center px-4 py-2 rounded-lg font-medium transition-colors {{ ($status ?? request('status')) == 'Closed Sale' ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-600 border border-gray-200 hover:bg-emerald-50' }}">
            Closed Sale
        </a>
        <a href="{{ route('deals.index', ['status' => 'Following', 'customer_type' => (request('customer_type') ?? $customerType ?? ''), 'sales_person_id' => (request('sales_person_id') ?? $selectedSalesPerson ?? ''), 'month' => (request('month') ?? $selectedMonth ?? ''), 'year' => (request('year') ?? $selectedYear ?? ''), 'search_company' => (request('search_company') ?? $searchCompany ?? '')]) }}" class="flex items-center px-4 py-2 rounded-lg font-medium transition-colors {{ ($status ?? request('status')) == 'Following' ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border border-gray-200 hover:bg-blue-50' }}">
            Following
            @if($followingBadge > 0)
                <span class="ml-1.5 inline-flex items-center justify-center min-w-[20px] px-1.5 py-0.5 text-[11px] font-bold text-red-600 bg-red-100 rounded-full border border-red-200 animate-pulse">{{ $followingBadge }}</span>
            @endif
        </a>
        <a href="{{ route('deals.index', ['status' => 'Forecast', 'customer_type' => (request('customer_type') ?? $customerType ?? ''), 'sales_person_id' => (request('sales_person_id') ?? $selectedSalesPerson ?? ''), 'month' => (request('month') ?? $selectedMonth ?? ''), 'year' => (request('year') ?? $selectedYear ?? ''), 'search_company' => (request('search_company') ?? $searchCompany ?? '')]) }}" class="flex items-center px-4 py-2 rounded-lg font-medium transition-colors {{ ($status ?? request('status')) == 'Forecast' ? 'bg-amber-600 text-white' : 'bg-white text-amber-600 border border-gray-200 hover:bg-amber-50' }}">
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
                        <th class="px-6 py-4">พนักงาน</th> 
                        <th class="px-6 py-4">คอร์ส / สินค้า</th>
                        <th class="px-6 py-4">วันที่บันทึก</th> 
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
                            $dealTotal = 0;
                            $itemsFormattedArray = [];
                            foreach($deal->dealItems as $dItem) {
                                $itemDiscount = $dItem->discount ?? $dItem->discount_per_person ?? 0;
                                $itemTotal = ($dItem->price_per_person - $itemDiscount) * $dItem->total_person;
                                if($itemTotal < 0) $itemTotal = 0;
                                $dealTotal += $itemTotal;

                                $itemsFormattedArray[] = [
                                    'course_name' => $dItem->course->course_name ?? 'ไม่ระบุ',
                                    'price_per_person' => number_format($dItem->price_per_person, 2),
                                    'discount' => number_format($itemDiscount, 2),
                                    'total_person' => number_format($dItem->total_person),
                                    'item_total' => number_format($itemTotal, 2)
                                ];
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900">
                                <div>{{ $deal->customer->company_name ?? 'ไม่ระบุชื่อบริษัท' }}</div>
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
                            
                            <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                {{ $deal->deal_date ? \Carbon\Carbon::parse($deal->deal_date)->addYears(543)->format('d/m/Y') : ($deal->created_at ? \Carbon\Carbon::parse($deal->created_at)->addYears(543)->format('d/m/Y') : '-') }}
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
                                @endif
                                @if($deal->status == 'Following')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span> Following
                                    </span>
                                @endif
                                @if($deal->status != 'Closed Sale' && $deal->status != 'Following')
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
                                        <span>🧾 เลขที่ใบเสร็จ: {{ $deal->receipt_no }}</span>
                                    </div>
                                @endif
                                <div class="text-xs text-gray-600 max-w-xs truncate" title="{{ $deal->note ?? $deal->updated_note }}">
                                    {{ $deal->updated_note ?? $deal->note ?? '-' }}
                                </div>
                            </td> 
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" 
                                            data-deal-info="{{ json_encode([
                                                'company_name' => $deal->customer->company_name ?? 'ไม่ระบุชื่อบริษัท',
                                                'group' => $deal->group,
                                                'sales_person' => $deal->salesPerson->name ?? $deal->user->name ?? 'ไม่ระบุ',
                                                'status' => $deal->status,
                                                'progress' => $deal->progress,
                                                'receipt_no' => $deal->receipt_no,
                                                'note' => $deal->updated_note ?? $deal->note ?? '-',
                                                'total_amount' => number_format($dealTotal, 2),
                                                'deal_date' => $deal->deal_date ? \Carbon\Carbon::parse($deal->deal_date)->format('Y-m-d') : ($deal->created_at ? \Carbon\Carbon::parse($deal->created_at)->format('Y-m-d') : ''),
                                                'closed_date' => $deal->status == 'Closed Sale' ? \Carbon\Carbon::parse($deal->updated_at)->setTimezone('Asia/Bangkok')->format('d/m/Y') : '-',
                                                'updated_at' => $deal->updated_at ? \Carbon\Carbon::parse($deal->updated_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i') . ' น.' : '-',
                                                'items' => $itemsFormattedArray
                                            ]) }}"
                                            onclick="openViewDealModal(this)"
                                            class="inline-flex items-center bg-sky-50 hover:bg-sky-100 text-sky-700 font-medium px-3 py-1.5 rounded-lg text-xs transition-colors border border-sky-200 shadow-sm">
                                        👁️ ดูรายละเอียด
                                    </button>
                                    <a href="{{ route('deals.items', $deal->id) }}" class="inline-flex items-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-3 py-1.5 rounded-lg text-xs transition-colors border border-gray-200">
                                        ⚙️ จัดการคอร์ส ({{ $deal->dealItems->count() }})
                                    </a>
                                    <a href="{{ route('deals.edit', $deal->id) }}" class="inline-flex items-center bg-amber-50 hover:bg-amber-100 text-amber-700 font-medium px-3 py-1.5 rounded-lg text-xs transition-colors border border-amber-200">
                                        ✏️ อัพเดทการขาย
                                    </a>
                                    
                                    @if(auth()->user()->isAdmin() || strtolower(auth()->user()->role) === 'manager')
                                        <form action="{{ route('deals.destroy', $deal->id) }}" method="POST" onsubmit="return confirm('⚠️ ยืนยันลบการขาย: คุณแน่ใจใช่ไหมว่าต้องการลบการขายนี้ออกจากระบบอย่างถาวร?');" class="inline-block">
                                            @csrf
                                            @if($deal->id)
                                                @method('DELETE')
                                            @endif
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
                            <td colspan="10" class="px-6 py-12 text-center text-gray-400"> <i class="fa-solid fa-file-circle-xmark text-3xl mb-2 block"></i>
                                ยังไม่มีการเปิดการขายในระบบ (หรือไม่มีข้อมูลตามเงื่อนไขที่คุณค้นหา)
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($deals, 'hasPages') && $deals->hasPages())
            <div class="p-4 border-t border-gray-100 bg-slate-50">
                {{ $deals->appends([
                    'status' => $status ?? request('status'), 
                    'customer_type' => request('customer_type') ?? $customerType ?? '', 
                    'sales_person_id' => request('sales_person_id') ?? $selectedSalesPerson ?? '', 
                    'month' => request('month') ?? $selectedMonth ?? '', 
                    'year' => request('year') ?? $selectedYear ?? '', 
                    'search_company' => request('search_company') ?? $searchCompany ?? ''
                ])->links() }}
            </div>
        @endif
    </div>

</div>

<div id="viewDealModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 overflow-y-auto transition-all duration-300">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 max-w-2xl w-full my-auto transform transition-all overflow-hidden flex flex-col">
        <div class="bg-slate-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">👁️</span>
                <h3 class="text-lg font-bold text-gray-800">รายละเอียดข้อมูลงานขาย</h3>
            </div>
            <button type="button" onclick="closeViewDealModal()" class="text-gray-400 hover:text-gray-600 transition-colors text-2xl font-semibold leading-none">&times;</button>
        </div>

        <div class="p-6 space-y-6 overflow-y-auto max-h-[70vh]">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-gray-150">
                <div>
                    <span class="text-xs font-semibold text-gray-400 uppercase block tracking-wider">บริษัทลูกค้า</span>
                    <span id="modalCompanyName" class="text-base font-bold text-slate-800 block mt-0.5">-</span>
                    <span id="modalGroupBadge" class="inline-flex items-center text-[11px] font-medium text-slate-600 bg-slate-200/80 px-2 py-0.5 rounded-md mt-1.5 hidden"></span>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-400 uppercase block tracking-wider">พนักงานผู้ดูแล</span>
                    <span id="modalSalesPerson" class="text-base font-medium text-slate-800 block mt-0.5">-</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div>
                    <span class="text-xs font-semibold text-gray-400 uppercase block tracking-wider mb-2">สถานะการติดตาม</span>
                    <div id="modalStatusContainer"></div>
                    
                    <div id="modalClosedDateContainer" class="hidden mt-3">
                        <span class="text-xs font-semibold text-emerald-600/80 uppercase block tracking-wider">📅 วันที่ปิดการขาย</span>
                        <span id="modalClosedDate" class="text-sm font-bold text-emerald-600 block mt-0.5">-</span>
                    </div>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-400 uppercase block tracking-wider mb-2">ความคืบหน้าล่าสุด</span>
                    <div id="modalProgressContainer"></div>
                </div>
            </div>

            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase block tracking-wider mb-2">รายการคอร์ส / สินค้าที่เสนอขาย</span>
                <div class="border border-gray-100 rounded-xl overflow-hidden shadow-sm">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-100 text-slate-600 font-bold">
                                <th class="px-4 py-2.5">คอร์ส / สินค้า</th>
                                <th class="px-4 py-2.5">วันที่บันทึก</th> 
                                <th class="px-4 py-2.5 text-right">ราคา/คน</th>
                                <th class="px-4 py-2.5 text-center">จำนวนคน</th>
                                <th class="px-4 py-2.5 text-right">ส่วนลด</th>
                                <th class="px-4 py-2.5 text-right">ยอดรวม</th>
                            </tr>
                        </thead>
                        <tbody id="modalItemsTableBody" class="divide-y divide-gray-100 text-gray-700">
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 font-bold border-t border-gray-100 text-slate-900">
                                <td colspan="5" class="px-4 py-3 text-right text-sm">ยอดเงินรวมทั้งหมด:</td> 
                                <td id="modalTotalAmount" class="px-4 py-3 text-right text-sm text-indigo-600 font-extrabold">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="space-y-3 pt-4 border-t border-gray-100">
                <div id="modalReceiptContainer" class="hidden">
                    <span class="text-xs font-semibold text-gray-400 uppercase block tracking-wider">🧾 เลขที่ใบเสร็จ</span>
                    <span id="modalReceiptNo" class="text-sm font-bold text-indigo-600 block mt-0.5">-</span>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-400 uppercase block tracking-wider">บันทึกเพิ่มเติม / โน้ต</span>
                    <div id="modalNote" class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100 whitespace-pre-line mt-1.5 font-normal leading-relaxed"></div>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-gray-100 flex justify-end">
            <button type="button" onclick="closeViewDealModal()" class="bg-white hover:bg-gray-50 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm transition-colors border border-gray-200 shadow-sm">
                ปิดหน้าต่าง
            </button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if ($('#company-search-select').length) {
            $('#company-search-select').select2({
                placeholder: "-- 🔍 ค้นหาชื่อบริษัท... --",
                allowClear: true,
                width: '100%'
            }).on('select2:select select2:unselect', function (e) {
                document.getElementById('autoSubmitForm').submit();
            });
        }

        if ($('#sales-search-select').length) {
            $('#sales-search-select').select2({
                placeholder: "-- แสดงพนักงานทุกคน --",
                allowClear: true,
                width: '100%'
            }).on('select2:select select2:unselect', function (e) {
                document.getElementById('autoSubmitForm').submit();
            });
        }

        $('#viewDealModal').on('click', function(e) {
            if (e.target === this) {
                closeViewDealModal();
            }
        });

        const currentPendingCount = parseInt("{{ $totalPendingCount }}") || 0;
        const alertBox = document.getElementById('deal-alert-container');

        if (alertBox && currentPendingCount > 0) {
            const savedCount = localStorage.getItem('acknowledged_deals_count');
            
            if (savedCount === null || parseInt(savedCount) !== currentPendingCount) {
                alertBox.classList.remove('hidden');
            }
        }

        const btnAckAlert = document.getElementById('btn-acknowledge-alert');
        if (btnAckAlert) {
            btnAckAlert.addEventListener('click', function() {
                const alertContainer = this.closest('#deal-alert-container');
                localStorage.setItem('acknowledged_deals_count', currentPendingCount);

                fetch("{{ route('deals.acknowledgeAlert') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ acknowledged: true })
                })
                .then(response => {
                    if (response.ok) {
                        if (alertContainer) {
                            alertContainer.style.transition = 'all 0.4s ease';
                            alertContainer.style.opacity = '0';
                            alertContainer.style.transform = 'translateY(-10px)';
                            setTimeout(() => alertContainer.remove(), 400);
                        }
                    }
                });
            });
        }
    });

    function openViewDealModal(element) {
        let data = $(element).data('deal-info');

        $('#modalCompanyName').text(data.company_name);
        if (data.group && data.group.trim() !== '') {
            $('#modalGroupBadge').text('📁 กลุ่ม: ' + data.group).removeClass('hidden');
        } else {
            $('#modalGroupBadge').addClass('hidden');
        }
        $('#modalSalesPerson').text('👤 ' + data.sales_person);
        $('#modalNote').text(data.note || '-');
        $('#modalTotalAmount').text('฿' + data.total_amount);

        let statusHtml = '';
        if (data.status === 'Closed Sale') {
            statusHtml = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Closed Sale</span>`;
            if(data.closed_date && data.closed_date !== '-') {
                $('#modalClosedDate').text(data.closed_date);
                $('#modalClosedDateContainer').removeClass('hidden').addClass('block');
            } else {
                $('#modalClosedDateContainer').addClass('hidden').removeClass('block');
            }
        } else if (data.status === 'Following') {
            statusHtml = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200"><span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span> Following</span>`;
            $('#modalClosedDateContainer').addClass('hidden').removeClass('block');
        } else {
            statusHtml = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span> Forecast</span>`;
            $('#modalClosedDateContainer').addClass('hidden').removeClass('block');
        }

        if(data.updated_at && data.updated_at !== '-') {
            statusHtml += `<div class="mt-2 text-[11px] text-gray-500 font-medium">🕒 อัปเดตล่าสุด: ${data.updated_at}</div>`;
        }
        $('#modalStatusContainer').html(statusHtml);

        let progressHtml = '';
        if (data.status === 'Closed Sale') {
            progressHtml = `<span class="inline-flex items-center text-[11px] font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-100">🎉 ปิดการขายสำเร็จ</span>`;
        } else if (data.progress && data.progress.trim() !== '') {
            progressHtml = `<span class="inline-flex items-center text-[11px] font-medium text-indigo-700 bg-indigo-50 px-2 py-1 rounded-md border border-indigo-200 shadow-sm">📌 ${data.progress}</span>`;
        } else {
            progressHtml = `<span class="inline-flex items-center text-[11px] font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">- ยังไม่มีอัปเดต -</span>`;
        }
        $('#modalProgressContainer').html(progressHtml);

        if (data.receipt_no && data.receipt_no.trim() !== '') {
            $('#modalReceiptNo').text(data.receipt_no);
            $('#modalReceiptContainer').removeClass('hidden');
        } else {
            $('#modalReceiptContainer').addClass('hidden');
        }

        let formattedDealDate = '-';
        if (data.deal_date) {
            let d = new Date(data.deal_date);
            if (!isNaN(d.getTime())) {
                let day = String(d.getDate()).padStart(2, '0');
                let month = String(d.getMonth() + 1).padStart(2, '0');
                let year = d.getFullYear() + 543; 
                formattedDealDate = `${day}/${month}/${year}`;
            }
        }

        let tableRows = '';
        if (data.items && data.items.length > 0) {
            data.items.forEach(function(item) {
                tableRows += `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-2.5 font-medium text-slate-900">${item.course_name}</td>
                        <td class="px-4 py-2.5 text-left text-gray-600 whitespace-nowrap">${formattedDealDate}</td> 
                        <td class="px-4 py-2.5 text-right text-gray-600">฿${item.price_per_person}</td>
                        <td class="px-4 py-2.5 text-center text-gray-600 font-semibold">${item.total_person}</td>
                        <td class="px-4 py-2.5 text-right text-rose-600">฿${item.discount}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-slate-800">฿${item.item_total}</td>
                    </tr>
                `;
            });
        } else {
            tableRows = `<tr><td colspan="6" class="px-4 py-4 text-center text-gray-400 italic">ไม่มีข้อมูลรายการสินค้า</td></tr>`; 
        }
        $('#modalItemsTableBody').html(tableRows);

        $('#viewDealModal').removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
    }

    function closeViewDealModal() {
        $('#viewDealModal').addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    }
</script>
@endsection