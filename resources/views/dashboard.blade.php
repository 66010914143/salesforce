@extends('layouts.app')

@section('page_title', 'แดชบอร์ดภาพรวมฝ่ายขาย')

@section('content')
<style>
    .select2-container--default .select2-selection--single {
        background-color: #ffffff !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem !important;
        height: 32px !important;
        display: inline-flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        padding-left: 8px !important;
        padding-right: 20px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 30px !important;
        top: 1px !important;
        right: 4px !important;
    }
    .select2-dropdown {
        border-color: #e5e7eb !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        z-index: 9999 !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #e5e7eb !important;
        border-radius: 0.25rem !important;
        outline: none !important;
        padding: 4px 8px !important;
        font-size: 0.875rem !important;
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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="space-y-6">
    
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-lg font-bold text-gray-800">ระบบสรุปยอดเงิน</h1>
            <p class="text-gray-500 text-sm mt-1">นี่คือภาพรวมข้อมูลยอดขายและสถานะทั้งหมด</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <form action="{{ url()->current() }}" method="GET" id="fiscalYearForm" class="flex flex-wrap items-center gap-2 bg-slate-50 border border-gray-200 px-3 py-1.5 rounded-lg shadow-2xs">
                
                @if(request()->has('sales_person_ids'))
                    @foreach(request('sales_person_ids') as $m_sp)
                        <input type="hidden" name="sales_person_ids[]" value="{{ $m_sp }}">
                    @endforeach
                @endif
                @if(request()->has('table_status'))
                    <input type="hidden" name="table_status" value="{{ request('table_status') }}">
                @endif

                <label for="sales_person_id" class="text-xs font-bold text-slate-600 uppercase tracking-wide flex items-center gap-1">
                    👤 พนักงาน:
                </label>
                <select name="sales_person_id" id="sales_person_id" class="bg-white rounded-md border border-gray-300 text-sm font-semibold text-slate-700 px-2.5 py-1 focus:outline-none focus:border-indigo-500 cursor-pointer">
                    <option value="all" {{ (!isset($selectedSalesPerson) || empty($selectedSalesPerson) || (is_array($selectedSalesPerson) ? in_array('all', $selectedSalesPerson) : $selectedSalesPerson == 'all')) ? 'selected' : '' }}>👥 พนักงานทุกคน (ภาพรวม)</option>
                    @foreach($salesPersons as $person)
                        <option value="{{ $person->id }}" {{ (isset($selectedSalesPerson) && (is_array($selectedSalesPerson) ? in_array($person->id, $selectedSalesPerson) : $selectedSalesPerson == $person->id)) ? 'selected' : '' }}>
                            {{ $person->name }}
                        </option>
                    @endforeach
                </select>

                <label for="fiscal_year" class="text-xs font-bold text-slate-600 uppercase tracking-wide flex items-center gap-1 ml-2">
                    📅 ช่วงเวลา:
                </label>

                @php
                    $months = [
                        '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', 
                        '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน', 
                        '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน', 
                        '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
                    ];
                @endphp
                <select name="fiscal_month" id="fiscal_month" onchange="document.getElementById('fiscalYearForm').submit();" class="bg-white rounded-md border border-gray-300 text-sm font-semibold text-slate-700 px-2.5 py-1 focus:outline-none focus:border-indigo-500 cursor-pointer">
                    @foreach($months as $key => $name)
                        <option value="{{ $key }}" {{ (isset($selectedMonth) && $selectedMonth == $key) ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>

                <select name="fiscal_year" id="fiscal_year" onchange="document.getElementById('fiscalYearForm').submit();" class="bg-white rounded-md border border-gray-300 text-sm font-semibold text-slate-700 px-2.5 py-1 focus:outline-none focus:border-indigo-500 cursor-pointer">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                            พ.ศ. {{ $year + 543 }} ({{ $year }})
                        </option>
                    @endforeach
                </select>

                @if((isset($selectedSalesPerson) && !empty($selectedSalesPerson) && (is_array($selectedSalesPerson) ? !in_array('all', $selectedSalesPerson) : $selectedSalesPerson !== 'all')) || $selectedMonth != date('m') || $selectedYear != date('Y'))
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center ml-1 px-2.5 py-1 border border-gray-300 shadow-2xs text-xs font-bold rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        <i class="fas fa-undo mr-1 text-gray-400"></i> ล้างค่า
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">ปิดการขายแล้ว (Closed Sale)</p>
                <h4 class="text-2xl font-bold text-emerald-600 mt-2">฿{{ number_format($totalClosed, 2) }}</h4>
            </div>
            <div class="p-4 bg-emerald-50 text-emerald-500 rounded-lg text-2xl">
                <i class="fa-solid fa-circle-dollar-to-slot"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">กำลังติดตาม (Following)</p>
                <h4 class="text-2xl font-bold text-blue-600 mt-2">฿{{ number_format($totalFollowing, 2) }}</h4>
            </div>
            <div class="p-4 bg-blue-50 text-blue-500 rounded-lg text-2xl">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">ประมาณการยอด (Forecast)</p>
                <h4 class="text-2xl font-bold text-amber-600 mt-2">฿{{ number_format($totalForecast, 2) }}</h4>
            </div>
            <div class="p-4 bg-amber-50 text-amber-500 rounded-lg text-2xl">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    คอร์สที่ ปิดการขายแล้ว (Closed Sale)
                </h3>
            </div>
            <div class="relative w-full style-chart flex-1" style="min-height: 240px;">
                <canvas id="closedCoursesChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span>
                    คอร์สที่ กำลังติดตาม (Following)
                </h3>
            </div>
            <div class="relative w-full style-chart flex-1" style="min-height: 240px;">
                <canvas id="followingCoursesChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                    คอร์สที่ ประมาณการยอด (Forecast)
                </h3>
            </div>
            <div class="relative w-full style-chart flex-1" style="min-height: 240px;">
                <canvas id="forecastCoursesChart"></canvas>
            </div>
        </div>

    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between border-b border-gray-100 pb-4 mb-4 gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <h4 class="font-bold text-gray-700">
                    <i class="fa-solid fa-trophy text-yellow-500 mr-2"></i> 
                    เปรียบเทียบยอดขายรายบุคคลจำแนกตามคอร์สเรียน (ตามเงื่อนไขที่เลือก)
                </h4>
                <span class="text-xs bg-yellow-50 text-yellow-600 border border-yellow-100 px-2 py-1 rounded font-medium self-start sm:self-auto">
                    ประจำเดือน {{ $months[$selectedMonth ?? date('m')] }} ปี พ.ศ. {{ $selectedYear + 543 }}
                </span>
            </div>
            
            <form action="{{ url()->current() }}" method="GET" id="matrixFilterForm" class="flex flex-col md:flex-row items-center gap-3 bg-slate-50 p-2.5 rounded-lg border border-gray-200">
                <input type="hidden" name="sales_person_id" value="{{ request('sales_person_id', 'all') }}">
                <input type="hidden" name="fiscal_month" value="{{ $selectedMonth }}">
                <input type="hidden" name="fiscal_year" value="{{ $selectedYear }}">

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label for="matrix_sales_persons" class="text-xs font-bold text-slate-600 whitespace-nowrap">👤 เลือกพนักงาน:</label>
                    <div class="flex-1 md:w-[220px]">
                        <select name="sales_person_ids[]" id="matrix_sales_persons" multiple="multiple" class="w-full">
                            @foreach($salesPersons as $person)
                                <option value="{{ $person->id }}" {{ (request()->has('sales_person_ids') && in_array($person->id, request('sales_person_ids'))) ? 'selected' : '' }}>
                                    {{ $person->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto mt-2 md:mt-0">
                    <label for="matrix_status" class="text-xs font-bold text-slate-600 whitespace-nowrap">📌 สถานะ:</label>
                    <select name="table_status" id="matrix_status" onchange="document.getElementById('matrixFilterForm').submit();" class="flex-1 md:w-auto bg-white rounded-md border border-gray-300 text-xs font-semibold text-slate-700 px-2 py-1 focus:outline-none focus:border-indigo-500 cursor-pointer h-[32px]">
                        <option value="closed" {{ request('table_status', 'closed') == 'closed' ? 'selected' : '' }}>🟢 ปิดการขายแล้ว (Closed Sale)</option>
                        <option value="following" {{ request('table_status') == 'following' ? 'selected' : '' }}>🔵 กำลังติดตาม (Following)</option>
                        <option value="forecast" {{ request('table_status') == 'forecast' ? 'selected' : '' }}>🟡 ประมาณการยอด (Forecast)</option>
                    </select>
                </div>
            </form>
        </div>
        
        @php
            if(!isset($matrixCourses) || !isset($matrixData)) {
                $matrixCourses = ['ADR', 'ALT', 'Basic CPR', 'Safety Officer'];
                $matrixData = [
                    (object)['name' => 'นาย ก. (พนักงานตัวอย่าง 1)', 'sales' => ['ADR' => 500, 'ALT' => 650, 'Basic CPR' => 0, 'Safety Officer' => 1200]],
                    (object)['name' => 'นาย ข. (พนักงานตัวอย่าง 2)', 'sales' => ['ADR' => 505, 'ALT' => 150, 'Basic CPR' => 300, 'Safety Officer' => 0]],
                    (object)['name' => 'นาย ค. (พนักงานตัวอย่าง 3)', 'sales' => ['ADR' => 0, 'ALT' => 800, 'Basic CPR' => 50, 'Safety Officer' => 100]],
                ];
            }
        @endphp

        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="sticky left-0 bg-slate-50 px-4 py-3 text-left font-bold text-slate-700 border-r border-gray-200 shadow-[1px_0_0_0_#e5e7eb] z-10">
                            รายชื่อพนักงานขาย
                        </th>
                        @foreach($matrixCourses as $course)
                            <th scope="col" class="px-4 py-3 text-right font-bold text-slate-700 whitespace-nowrap">
                                {{ $course }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($matrixData as $row)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="sticky left-0 bg-white hover:bg-slate-50 px-4 py-3 font-medium text-slate-800 border-r border-gray-200 shadow-[1px_0_0_0_#e5e7eb] whitespace-nowrap z-10">
                                {{ $row->name }}
                            </td>
                            @foreach($matrixCourses as $course)
                                @php
                                    $amount = $row->sales[$course] ?? 0;
                                    
                                    // Heatmap: ปรับสีตัวอักษรและพื้นหลังตามความสูงของยอดขาย
                                    $bgClass = '';
                                    $textClass = 'text-slate-400'; // ถ้าไม่มียอดให้สีจางๆ
                                    
                                    if ($amount > 500) {
                                        $bgClass = 'bg-emerald-50';
                                        $textClass = 'text-emerald-700 font-bold';
                                    } elseif ($amount > 0) {
                                        $bgClass = '';
                                        $textClass = 'text-slate-700 font-medium';
                                    }
                                @endphp
                                <td class="px-4 py-3 text-right {{ $bgClass }} {{ $textClass }} whitespace-nowrap">
                                    {{ $amount > 0 ? '฿'.number_format($amount, 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($matrixCourses) + 1 }}" class="px-4 py-6 text-center text-gray-500">
                                ยังไม่มีข้อมูลการขายในเงื่อนไขที่ท่านเลือก
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
            <h4 class="font-bold text-gray-700"><i class="fa-solid fa-graduation-cap text-indigo-400 mr-2"></i> สรุปรายได้แยกตามคอร์สเรียน (Master Data)</h4>
            <span class="text-xs bg-indigo-50 text-indigo-600 border border-indigo-100 px-2 py-1 rounded font-medium">
                ประจำเดือน {{ $months[$selectedMonth ?? date('m')] }} ปี พ.ศ. {{ $selectedYear + 543 }}
            </span>
        </div>
        <div class="relative w-full style-chart" style="height: 320px;">
            <canvas id="courseRevenueChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
            <h4 class="font-bold text-gray-700"><i class="fa-solid fa-chart-bar text-slate-400 mr-2"></i> สรุปผลยอดขายภาพรวมแต่ละเดือน (ปีนี้)</h4>
            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded">อัปเดตเรียลไทม์ (ปี พ.ศ. {{ $selectedYear + 543 }})</span>
        </div>
        <div class="relative w-full style-chart" style="height: 320px;">
            <canvas id="monthlySalesChart"></canvas>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // 🛠 เปิดใช้งานระบบค้นหารายชื่อพนักงานขายใน Dropdown ด้วย Select2 แบบ Single Select ตามเงื่อนไขใหม่
        if (typeof jQuery !== 'undefined' && $.fn.select2) {
            $('#sales_person_id').select2({
                placeholder: "👥 ค้นหารายชื่อพนักงาน",
                allowClear: false
            }).on('change', function() {
                // ส่งข้อมูลฟอร์มทันทีเมื่อมีการเปลี่ยนแปลงการเลือกพนักงาน
                document.getElementById('fiscalYearForm').submit();
            });

            // 🛠 เพิ่มระบบ Select2 แบบ Multiple Select สำหรับตารางเปรียบเทียบข้อมูลโดยเฉพาะ
            $('#matrix_sales_persons').select2({
                placeholder: "👥 พนักงานทุกคน",
                allowClear: true
            }).on('change select2:select select2:unselect', function() {
                document.getElementById('matrixFilterForm').submit();
            });
        }

        // -------------------------------------------------------------
        // 1. กราฟเดิม: รายได้แยกตามคอร์สเรียน (Course Revenue Chart)
        // -------------------------------------------------------------
        var ctxCourse = document.getElementById('courseRevenueChart');
        if(ctxCourse) {
            var courseLabels = @json($courseLabels ?? []);
            var courseData = @json($courseData ?? []);

            new Chart(ctxCourse.getContext('2d'), {
                type: 'bar', 
                data: {
                    labels: courseLabels.length > 0 ? courseLabels : ['ไม่มีข้อมูลในเดือนนี้'],
                    datasets: [{
                        label: 'รายได้จากคอร์ส (บาท)',
                        data: courseData.length > 0 ? courseData : [0],
                        backgroundColor: 'rgba(99, 102, 241, 0.2)', 
                        borderColor: 'rgba(99, 102, 241, 1)',    
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#6b7280',
                                font: { family: 'ui-sans-serif, system-ui, sans-serif' }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                color: '#9ca3af',
                                font: { family: 'ui-sans-serif, system-ui, sans-serif' },
                                callback: function(value) { return '฿' + value.toLocaleString(); }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            titleFont: { family: 'ui-sans-serif, system-ui, sans-serif', size: 13 },
                            bodyFont: { family: 'ui-sans-serif, system-ui, sans-serif', size: 14, weight: 'bold' },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' รายได้: ฿' + context.raw.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                            }
                        }
                    }
                }
            });
        }

        // -------------------------------------------------------------
        // 2. กราฟเดิม: สรุปผลยอดขายแต่ละเดือน
        // -------------------------------------------------------------
        var ctxMonthly = document.getElementById('monthlySalesChart');
        if(ctxMonthly) {
            var dataRevenue = @json($chartData);

            new Chart(ctxMonthly.getContext('2d'), {
                type: 'bar', 
                data: {
                    labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
                    datasets: [{
                        label: 'รายได้จาก Closed Sale (บาท)',
                        data: dataRevenue,
                        backgroundColor: 'rgba(16, 185, 129, 0.2)', 
                        borderColor: 'rgba(16, 185, 129, 1)',    
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#9ca3af',
                                font: { family: 'ui-sans-serif, system-ui, sans-serif' }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                color: '#9ca3af',
                                font: { family: 'ui-sans-serif, system-ui, sans-serif' },
                                callback: function(value) { return '฿' + value.toLocaleString(); }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            titleFont: { family: 'ui-sans-serif, system-ui, sans-serif', size: 13 },
                            bodyFont: { family: 'ui-sans-serif, system-ui, sans-serif', size: 14, weight: 'bold' },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' ยอดขาย: ฿' + context.raw.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                            }
                        }
                    }
                }
            });
        }

        // -------------------------------------------------------------
        // 📊 ฟังก์ชันกลางสำหรับสร้างกราฟแท่งแบบมินิ (คอร์สเรียนรายสถานะ)
        // -------------------------------------------------------------
        function createMiniCourseChart(canvasId, labels, data, baseColor, labelText) {
            var ctx = document.getElementById(canvasId);
            if (!ctx) return;

            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels.length > 0 ? labels : ['ไม่มีข้อมูล'],
                    datasets: [{
                        label: labelText,
                        data: data.length > 0 ? data : [0],
                        backgroundColor: baseColor.replace('1)', '0.15)'), 
                        borderColor: baseColor,
                        borderWidth: 1.2,
                        borderRadius: 4,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#4b5563',
                                font: { family: 'ui-sans-serif, system-ui, sans-serif', size: 11 }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                color: '#9ca3af',
                                font: { family: 'ui-sans-serif, system-ui, sans-serif', size: 10 },
                                callback: function(value) { return '฿' + value.toLocaleString(); }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            padding: 10,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ฿' + context.raw.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                            }
                        }
                    }
                }
            });
        }

        // แปลงข้อมูลจาก PHP Array ส่งไปยัง JavaScript เพื่อประมวลผลวาดกราฟคอร์สเรียนทั้ง 3 รูปแบบ
        var closedLabels = @json(collect($closedSaleCourses ?? [])->map(fn($item) => $item->course->course_name ?? 'ไม่ระบุ')->values());
        var closedValues = @json(collect($closedSaleCourses ?? [])->map(fn($item) => (float)$item->total_revenue_sum)->values());
        createMiniCourseChart('closedCoursesChart', closedLabels, closedValues, 'rgba(16, 185, 129, 1)', 'ยอดรวมสุทธิ');

        var followingLabels = @json(collect($followingCourses ?? [])->map(fn($item) => $item->course->course_name ?? 'ไม่ระบุ')->values());
        var followingValues = @json(collect($followingCourses ?? [])->map(fn($item) => (float)$item->total_revenue_sum)->values());
        createMiniCourseChart('followingCoursesChart', followingLabels, followingValues, 'rgba(59, 130, 246, 1)', 'ยอดคาดการณ์');

        var forecastLabels = @json(collect($forecastCourses ?? [])->map(fn($item) => $item->course->course_name ?? 'ไม่ระบุ')->values());
        var forecastValues = @json(collect($forecastCourses ?? [])->map(fn($item) => (float)$item->total_revenue_sum)->values());
        createMiniCourseChart('forecastCoursesChart', forecastLabels, forecastValues, 'rgba(245, 158, 11, 1)', 'ยอดประมาณการ');
    });
</script>
@endsection