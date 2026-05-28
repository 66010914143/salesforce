<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesDeal;
use App\Models\User; // เพิ่มโมเดล User สำหรับดึงรายชื่อพนักงาน
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 🔒 ป้องกันพนักงานพิมพ์ URL เข้ามาตรงๆ ถ้าไม่ใช่ Admin หรือ Manager ให้ดีดเตะไปหน้างานขายทันที
        if (auth()->check() && !auth()->user()->isAdmin() && strtolower(auth()->user()->role) !== 'manager') {
            return redirect()->route('deals.index');
        }

        // 🔮 โมดูลที่ 5: โค้ดจำลองสิทธิ์ผู้ใช้งานระดับหัวหน้า (Admin) ชั่วคราวเพื่อใช้ทดสอบระบบตามความต้องการ
        if (!auth()->check()) {
            $mockUser = new \App\Models\User();
            $mockUser->id = 1;
            $mockUser->name = 'lesforce';
            $mockUser->email = 'lesforce@company.com';
            $mockUser->role = 'admin'; // กำหนดสิทธิ์ให้เป็น admin (หัวหน้า) เสมอกับความต้องการ
            auth()->login($mockUser);
        }

        // 1. รับค่าปีงบประมาณ เดือน และพนักงาน ที่มีการเลือกมาจากหน้าบ้าน (ถ้าไม่มีการส่งมา ให้ใช้ปีและเดือนปัจจุบันเป็นค่าเริ่มต้น)
        $selectedYear = $request->get('fiscal_year', date('Y'));
        $selectedMonth = $request->get('fiscal_month', date('m'));
        $selectedSalesPerson = $request->get('sales_person_id'); // รับค่าพนักงานขาย

        // 2. สร้าง Query ดึงข้อมูลดีลที่มีเงื่อนไขกรองตามปีและเดือนของวันที่บันทึกดีล (deal_date)
        $query = SalesDeal::with(['items.course'])
            ->whereYear('deal_date', $selectedYear)
            ->whereMonth('deal_date', $selectedMonth);

        // หากมีการเลือกพนักงาน และค่านั้นไม่ใช่ค่าว่างหรือ 'all' (พนักงานทั้งหมด) ให้ทำการกรองข้อมูล
        if (!empty($selectedSalesPerson) && $selectedSalesPerson !== 'all') {
            $query->where(function($q) use ($selectedSalesPerson) {
                $tableName = (new SalesDeal())->getTable();
                
                if (Schema::hasColumn($tableName, 'user_id')) {
                    $q->orWhere('user_id', $selectedSalesPerson);
                }
                
                if (Schema::hasColumn($tableName, 'sales_person_id')) {
                    $q->orWhere('sales_person_id', $selectedSalesPerson);
                }

                // กรณีที่ระบบใช้ Eloquent Relation 'user'
                $q->orWhereHas('user', function($subQuery) use ($selectedSalesPerson) {
                    $subQuery->where('id', $selectedSalesPerson);
                });
            });
        }

        $deals = $query->get();

        // ตั้งค่าตัวแปรเริ่มต้นสถิติตามปกติ
        $totalClosed = 0;
        $totalFollowing = 0;
        $totalForecast = 0;

        // ตัวแปรสำหรับเก็บยอดขาย 12 เดือน (ม.ค. - ธ.ค.) สำหรับกราฟเดิม (คงไว้เพื่อป้องกันระบบหลักพัง)
        $chartData = array_fill(0, 12, 0);

        // ตัวแปรสำหรับเก็บข้อมูลรายได้แยกตามรายคอร์สเรียน (Master Data) สำหรับกราฟแท่งตัวใหม่
        $courseRevenueMap = [];

        // ➕ แผนผังสำหรับเก็บข้อมูลสรุปยอดคนและรายได้ของคอร์สเรียนแยกตาม 3 สถานะเพื่อนำไปแสดงผลบนตารางใหม่
        $closedSaleCoursesMap = [];
        $followingCoursesMap = [];
        $forecastCoursesMap = [];

        foreach ($deals as $deal) {
            // คำนวณยอดรวมเงินของแต่ละดีล
            $dealTotal = 0;
            foreach ($deal->items as $item) {
                $price = $item->price_per_person ?? 0;
                // รองรับทั้งชื่อฟิลด์ discount และ discount_per_person
                $discount = $item->discount_per_person ?? $item->discount ?? 0; 
                $qty = $item->total_person ?? 0;
                
                $itemTotal = ($price - $discount) * $qty;
                if ($itemTotal < 0) $itemTotal = 0;
                $dealTotal += $itemTotal;

                // สะสมยอดรายได้แยกตามชื่อคอร์ส (เฉพาะคอร์สที่อยู่ในสถานะ Closed Sale)
                if ($deal->status === 'Closed Sale' && $item->course) {
                    $courseName = $item->course->course_name ?? 'ไม่ระบุชื่อคอร์ส';
                    if (!isset($courseRevenueMap[$courseName])) {
                        $courseRevenueMap[$courseName] = 0;
                    }
                    $courseRevenueMap[$courseName] += $itemTotal;
                }

                // ➕ สะสมจำนวนคนและยอดเงินแยกตามชื่อคอร์สเรียนและสถานะของดีลเพื่อป้อนข้อมูลเข้าตารางใหม่
                if ($item->course) {
                    $courseName = $item->course->course_name ?? 'ไม่ระบุชื่อคอร์ส';
                    
                    if ($deal->status === 'Closed Sale') {
                        if (!isset($closedSaleCoursesMap[$courseName])) {
                            $closedSaleCoursesMap[$courseName] = ['course_name' => $courseName, 'total_person' => 0, 'total_revenue' => 0];
                        }
                        $closedSaleCoursesMap[$courseName]['total_person'] += $qty;
                        $closedSaleCoursesMap[$courseName]['total_revenue'] += $itemTotal;
                    } elseif ($deal->status === 'Following') {
                        if (!isset($followingCoursesMap[$courseName])) {
                            $followingCoursesMap[$courseName] = ['course_name' => $courseName, 'total_person' => 0, 'total_revenue' => 0];
                        }
                        $followingCoursesMap[$courseName]['total_person'] += $qty;
                        $followingCoursesMap[$courseName]['total_revenue'] += $itemTotal;
                    } elseif ($deal->status === 'Forecast') {
                        if (!isset($forecastCoursesMap[$courseName])) {
                            $forecastCoursesMap[$courseName] = ['course_name' => $courseName, 'total_person' => 0, 'total_revenue' => 0];
                        }
                        $forecastCoursesMap[$courseName]['total_person'] += $qty;
                        $forecastCoursesMap[$courseName]['total_revenue'] += $itemTotal;
                    }
                }
            }

            // แยกยอดเงินไปบวกตามสถานะของดีล
            if ($deal->status === 'Closed Sale') {
                $totalClosed += $dealTotal;

                // จัดยอดลง Array กราฟตามเดือน (อิงจากวันที่อัปเดต)
                if ($deal->updated_at) {
                    // Carbon ช่วยดึงตัวเลขเดือน (1-12) เอามาลบ 1 เพื่อให้ตรงกับ Index ของ Array (0-11)
                    $monthIndex = Carbon::parse($deal->updated_at)->format('n') - 1; 
                    $chartData[$monthIndex] += $dealTotal;
                }

            } elseif ($deal->status === 'Following') {
                $totalFollowing += $dealTotal;
                
            } elseif ($deal->status === 'Forecast') {
                $totalForecast += $dealTotal;
            }
        }

        // แปลงข้อมูลรายได้คอร์สเรียนเป็นชุดข้อมูลสำหรับ Chart.js (คัดเลือกเฉพาะคอร์สที่มียอดเงิน)
        arsort($courseRevenueMap); // เรียงจากคอร์สที่ทำรายได้มากที่สุดไปน้อยสุด
        $courseLabels = array_keys($courseRevenueMap);
        $courseData = array_values($courseRevenueMap);

        // ➕ จัดเรียงลำดับคอร์สเรียนตามรายได้สุทธิจากมากไปน้อย และแปลงโครงสร้างเป็น Object เพื่อรองรับโครงสร้างในหน้า Blade View ได้ทันที
        uasort($closedSaleCoursesMap, function($a, $b) { return $b['total_revenue'] <=> $a['total_revenue']; });
        uasort($followingCoursesMap, function($a, $b) { return $b['total_revenue'] <=> $a['total_revenue']; });
        uasort($forecastCoursesMap, function($a, $b) { return $b['total_revenue'] <=> $a['total_revenue']; });

        $closedSaleCourses = collect($closedSaleCoursesMap)->map(function($data) {
            return (object)[
                'course' => (object)['course_name' => $data['course_name']],
                'total_person_sum' => $data['total_person'],
                'total_revenue_sum' => $data['total_revenue']
            ];
        })->values();

        $followingCourses = collect($followingCoursesMap)->map(function($data) {
            return (object)[
                'course' => (object)['course_name' => $data['course_name']],
                'total_person_sum' => $data['total_person'],
                'total_revenue_sum' => $data['total_revenue']
            ];
        })->values();

        $forecastCourses = collect($forecastCoursesMap)->map(function($data) {
            return (object)[
                'course' => (object)['course_name' => $data['course_name']],
                'total_person_sum' => $data['total_person'],
                'total_revenue_sum' => $data['total_revenue']
            ];
        })->values();

        // 3. คำนวณถอดภาษีมูลค่าเพิ่ม (VAT 7% แบบรวมใน Inclusive VAT) จากยอดที่ปิดการขายได้ (Closed Sale)
        // ยอดรวมทั้งหมดที่มี VAT = $totalClosed (Gross)
        // ยอดก่อนภาษี (Net) = Gross / 1.07
        // ยอดภาษี (VAT 7%) = Gross - Net
        $totalNet = $totalClosed / 1.07;
        $totalVat = $totalClosed - $totalNet;

        // 4. ดึงรายชื่อปีทั้งหมดที่มีอยู่ในระบบ เพื่อเอาไปทำตัวเลือก Dropdown บนหน้าแดชบอร์ด
        $availableYears = SalesDeal::selectRaw('YEAR(deal_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // ป้องกันกรณีฐานข้อมูลว่าง ให้มีปีปัจจุบันรองรับไว้เสมอ
        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }

        // 5. ดึงรายชื่อพนักงานทั้งหมดไปแสดงใน Dropdown ให้เลือก (เรียงตามชื่อ)
        $salesPersons = User::orderBy('name', 'asc')->get();

        // ส่งตัวแปรทั้งหมด (รวมถึงตัวแปรเพิ่มใหม่ของระบบภาษี ระบบปี เดือน ระบบกราฟรายคอร์ส พนักงาน และตารางสรุปคอร์สทั้ง 3 สถานะ) ไปที่หน้า dashboard.blade.php
        return view('dashboard', compact(
            'totalClosed',
            'totalFollowing',
            'totalForecast',
            'chartData',
            'totalNet',
            'totalVat',
            'availableYears',
            'selectedYear',
            'selectedMonth',
            'courseLabels',
            'courseData',
            'salesPersons',
            'selectedSalesPerson',
            'closedSaleCourses',
            'followingCourses',
            'forecastCourses'
        ));
    }
}