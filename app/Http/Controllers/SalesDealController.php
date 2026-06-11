<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesDeal;
use App\Models\Customer;
use App\Models\Course;
use App\Models\DealItem;
use App\Models\User;
use App\Models\DealLog; // 🟢 เพิ่ม Model สำหรับบันทึกประวัติ
// ➕ เพิ่มการเรียกใช้งาน Model ทั้ง 4 ตัวสำหรับ Dropdown Master Data
use App\Models\SubStatus;
use App\Models\CustomerGroup;
use App\Models\MasterCategory;
use App\Models\MasterChannel;
use App\Models\MasterChannel as MasterChannelModel;
use App\Models\MainStatus; // ➕ เพิ่มการเรียกใช้งาน Model สำหรับสถานะหลักใหม่จากฐานข้อมูล
use Illuminate\Support\Facades\Auth;

class SalesDealController extends Controller
{
    // หน้าแสดงรายการดีลงานขายทั้งหมด
    public function index(Request $request)
    {
        $status = $request->get('status');
        $selectedSalesPerson = $request->get('sales_person_id');
        
        // 🟢 รับค่าตัวกรองเดือน, ปี, ประเภทลูกค้า และ ชื่อบริษัท
        $selectedMonth = $request->get('month');
        $selectedYear = $request->get('year');
        $customerType = $request->get('customer_type'); 
        $searchCompany = $request->get('search_company'); // 🟢 เพิ่มรับค่าค้นหาบริษัท

        // ดึงข้อมูลดีลพร้อมข้อมูลลูกค้าและสินค้าที่เชื่อมโยงกัน (โหลด user พ่วงเข้ามาเพื่อใช้เป็นผู้ดูแลดีล)
        $query = SalesDeal::with(['customer', 'dealItems.course', 'user']);

        // 🔒 ระบบล็อกสิทธิ์คัดแยกมุมมองข้อมูลดีลงานขาย (อัปเดตเพิ่มสิทธิ์ให้ Manager เห็นเหมือน Admin)
        $isUserAdminOrManager = Auth::check() && (Auth::user()->isAdmin() || strtolower(Auth::user()->role) === 'manager');

        if ($isUserAdminOrManager) {
            // สิทธิ์ Admin และ Manager: ถ้าเลือกพนักงานจากกล่อง Dropdown ให้กรองเฉพาะดีลของพนักงานคนนั้น ถ้าไม่มีเลือกให้แสดงทั้งหมด
            if ($request->filled('sales_person_id')) {
                $query->where('user_id', $selectedSalesPerson);
            }
        } else {
            // สิทธิ์ Sales ทั่วไป: ล็อกผลลัพธ์ให้มองเห็นเฉพาะดีลงานขายที่เป็นของตนเอง 100% เสมอ
            $query->where('user_id', Auth::id());
        }

        // 🟢 เพิ่มการกรองตามชื่อบริษัท (แก้ไขเอาคอลัมน์ name ที่ไม่มีจริงออก ป้องกัน Error 1054)
        if ($searchCompany) {
            $query->whereHas('customer', function($q) use ($searchCompany) {
                $q->where('company_name', 'LIKE', '%' . $searchCompany . '%');
            });
        }

        // 🔍 🟢 ปรับปรุงการกรองตามเดือนที่สร้างดีล (ให้ค้นหาจากคอลัมน์ deal_date แทน เพื่อให้ตรงกับวันที่ในงานขาย)
        if ($selectedMonth) {
            $query->whereMonth('deal_date', $selectedMonth);
        }

        // 🔍 🟢 ปรับปรุงการกรองตามปีที่สร้างดีล (รองรับการแปลงปี พ.ศ. เป็น ค.ศ. อัตโนมัติ ป้องกันค้นหาไม่เจอ)
        if ($selectedYear) {
            $yearValue = (int)$selectedYear;
            if ($yearValue > 2500) {
                $yearValue = $yearValue - 543;
            }
            $query->whereYear('deal_date', $yearValue);
        }

        // 🟢 แก้ไขระบบกวาดข้อมูลประเภทลูกค้า: ป้องกันกรณีฐานข้อมูลเก็บค่า person หรือ individual สลับกัน
        if ($customerType) {
            $query->whereHas('customer', function($q) use ($customerType) {
                if ($customerType === 'organization') {
                    // กรองกลุ่มองค์กร (รองรับทั้ง corporate และ organization)
                    $q->where(function($subQ) {
                        $subQ->where('type', 'LIKE', '%corporate%')
                             ->orWhere('type', 'LIKE', '%organization%');
                    });
                } elseif ($customerType === 'individual') {
                    // กรองกลุ่มบุคคล (ดักครบทั้ง person, individual และตัวพิมพ์เล็ก/ใหญ่)
                    $q->where(function($subQ) {
                        $subQ->where('type', 'LIKE', '%person%')
                             ->orWhere('type', 'LIKE', '%individual%');
                    });
                } else {
                    // กรณีค่าอื่น ๆ ให้ค้นหาตรง ๆ ตามที่ส่งมา
                    $q->where('type', 'LIKE', '%' . $customerType . '%');
                }
            });
        }

        // 🛠️ [จุดที่แก้ไขเด็ดขาด] ตรวจสอบจับคู่ภาษาไทย/คำผสมจากปุ่มหน้าบ้าน แล้ว Map กลับเข้าฐานข้อมูลตามรูปภาพหลักฐาน
        if ($request->filled('status')) {
            $statusStr = strtolower(trim($status));
            
            // ดักจับและแปลงค่าให้ตรงรูปแบบ String ในฐานข้อมูล
            if (str_contains($statusStr, 'close') || str_contains($statusStr, 'ปิดการขาย')) {
                $query->where('status', 'Closed Sale');
            } elseif (str_contains($statusStr, 'follow') || str_contains($statusStr, 'ติดตาม')) {
                $query->where('status', 'Following');
            } elseif (str_contains($statusStr, 'forecast') || str_contains($statusStr, 'คาดการณ์')) {
                $query->where('status', 'Forecast');
            } elseif (str_contains($statusStr, 'denied') || str_contains($statusStr, 'ปฏิเสธ')) {
                $query->where('status', 'Denied');
            } else {
                // กรณีเป็นค่าอื่นๆ นอกเหนือจากแท็บหลัก ให้ดึงแบบยืดหยุ่นปกติ
                $query->where('status', $status);
            }
        }

        $deals = $query->latest()->paginate(15);

        // ⚡ เพิ่มการ Mapping เพื่อผูกตัวแปร salesPerson ให้ชี้ไปที่ Object พนักงานขาย (User) โดยตรงแบบปลอดภัย ป้องกันปัญหากับหน้า View เดิม
        $deals->getCollection()->transform(function ($deal) {
            if (!isset($deal->salesPerson) || empty($deal->salesPerson)) {
                $deal->setRelation('salesPerson', $deal->user);
            }
            return $deal;
        });
            
        // 🟢 แนบ request กลับไปที่ pagination เพื่อไม่ให้ parameter หลุดเวลาเปลี่ยนหน้า
        $deals->appends($request->all());

        // ดึงรายชื่อพนักงานทั้งหมดส่งไปให้ Admin และ Manager เลือกกรองในหน้า View
        $salesPersons = User::all();

        // 🟢 ดึงข้อมูลสถานะหลักจากฐานข้อมูลเพื่อนำไปสร้าง Dropdown Filter
        $mainStatuses = MainStatus::orderBy('id', 'asc')->get();

        // 🔔 เช็คงานค้าง (Following, Forecast) ของผู้ใช้งานปัจจุบัน หรือของทุกคนกรณีเป็น Admin/Manager เพื่อนำไปทำแจ้งเตือน
        // ปรับปรุงเงื่อนไขเช็คให้ครอบคลุมและใช้ LOWER ในฐานข้อมูลเพื่อความเสถียร
        $pendingDealsCount = 0;
        if (Auth::check()) {
            $pendingQuery = SalesDeal::whereRaw('LOWER(status) IN (?, ?)', ['following', 'forecast']);
            
            // ถ้าไม่ใช่ Admin หรือ Manager ให้คัดกรองเฉพาะงานของตัวเอง
            if (!$isUserAdminOrManager) {
                $pendingQuery->where('user_id', Auth::id());
            }
            
            $pendingDealsCount = $pendingQuery->count();
        }

        // ➕ ตรวจสอบสถานะว่าพนักงานคนนี้เคยากดรับทราบการแจ้งเตือนงานขายไปหรือยังจาก Session
        $showAlert = !session()->has('deal_alert_dismissed');

        // 🟢 ส่งค่าทั้งหมด (รวมถึง $mainStatuses และ $searchCompany) กลับไปแสดงผลที่ View
        return view('deals.index', compact('deals', 'status', 'salesPersons', 'selectedSalesPerson', 'selectedMonth', 'selectedYear', 'customerType', 'searchCompany', 'mainStatuses', 'pendingDealsCount', 'showAlert'));
    }

    // หน้าฟอร์มสร้างดีลงานขายใหม่
    public function create()
    {
        // 🎯 ดึง total_people มาด้วย และแก้ไขชื่อให้มี (รวม X คน) ต่อท้าย เพื่อให้หน้าเว็บนำไปใช้ได้ง่ายๆ
        $customers = Customer::select('id', 'company_name', 'contact_name', 'type', 'total_people')->get()->map(function ($customer) {
            $customer->company_name = $customer->company_name . ' (รวม ' . ($customer->total_people ?? 1) . ' คน)';
            return $customer;
        });
        
        $courses = Course::all(); // ดึงรายชื่อคอร์สไปให้เลือกใน Dropdown

        // ➕ ดึงข้อมูลจาก 4 ตาราง Master Data เพื่อนำไปใช้ในหน้าสร้างดีล
        $subStatuses = SubStatus::orderBy('name', 'asc')->get();
        $customerGroups = CustomerGroup::orderBy('name', 'asc')->get();
        $categories = MasterCategory::orderBy('name', 'asc')->get();
        $channels = MasterChannel::orderBy('name', 'asc')->get();

        // 📊 [ส่วนที่เพิ่มใหม่] ดึงข้อมูลสำหรับ Dropdown ไดนามิก 3 ตัวแปรใหม่ไปใช้ใน View
        $groups = CustomerGroup::orderBy('name', 'asc')->get();
        $progresses = SubStatus::orderBy('name', 'asc')->get();

        // 🔄 ดึงข้อมูลสถานะหลักจากตารางฐานข้อมูลจริงแทนการ Hardcode Array เดิม
        $mainStatuses = MainStatus::orderBy('id', 'asc')->get();

        return view('deals.create', compact('customers', 'courses', 'subStatuses', 'customerGroups', 'categories', 'channels', 'groups', 'progresses', 'mainStatuses'));
    }

    // บันทึกดีลและไอเท็มสินค้าลงฐานข้อมูล
    public function store(Request $request)
    {
        // ตรวจสอบข้อมูล (เอา course_id, price_per_person, total_person ออกเพราะเราแยกไปทำอีกหน้าแล้ว)
        $request->validate([
            'customer_id'      => 'required',
            'deal_date'        => 'nullable',
            'status'           => 'nullable', // 🟢 ปลดล็อคจาก required 
        ]);

        // 1. บันทึกข้อมูลดีลหลัก
        $userId = Auth::check() ? Auth::id() : 1;
        $dealDate = $request->deal_date ?: now()->format('Y-m-d');

        // 🟢 ระบบกรองและแปลงค่าสถานะแบบเด็ดขาด (ป้องกันการเซฟผิด 100%)
        $newStatus = 'Following'; // ค่าเริ่มต้น
        if ($request->filled('status')) {
            $rawStatus = $request->status;
            if (is_numeric($rawStatus)) {
                if ($rawStatus == 3) { $newStatus = 'Closed Sale'; }
                elseif ($rawStatus == 1) { $newStatus = 'Following'; }
                elseif ($rawStatus == 2) { $newStatus = 'Forecast'; }
                elseif ($rawStatus == 4) { $newStatus = 'Denied'; }
            } else {
                $statusStr = '';
                if (is_array($rawStatus) && isset($rawStatus['name'])) {
                    $statusStr = strtolower($rawStatus['name']);
                } elseif (is_object($rawStatus) && isset($rawStatus->name)) {
                    $statusStr = strtolower($rawStatus->name);
                } elseif (is_string($rawStatus)) {
                    $statusStr = strtolower($rawStatus);
                }

                if (str_contains($statusStr, 'close')) { $newStatus = 'Closed Sale'; }
                elseif (str_contains($statusStr, 'follow')) { $newStatus = 'Following'; }
                elseif (str_contains($statusStr, 'forecast')) { $newStatus = 'Forecast'; }
                elseif (str_contains($statusStr, 'denied')) { $newStatus = 'Denied'; }
                else { $newStatus = is_string($rawStatus) ? $rawStatus : 'Following'; }
            }
        }

        // 💡 แก้ไข: นำฟิลด์ total_revenue ออก เพื่อไม่ให้เกิด Error: Column not found 1054 เนื่องจากใช้ระบบรวมยอดจากตารางย่อยแทน
        $deal = SalesDeal::create([
            'user_id'       => $userId, 
            'customer_id'   => $request->customer_id,
            'deal_date'     => $dealDate,
            'group'         => $request->group,
            'category'      => $request->category,
            'tools'         => $request->tools,
            'promotion'     => $request->promotion,
            'status'        => $newStatus, // 🟢 บันทึกค่าที่แปลงเสร็จแล้ว
            'progress'      => $request->progress,
            'receipt_no'    => $request->receipt_no,
            'updated_note'  => $request->updated_note,
        ]);

        // เปลี่ยน Redirect ให้เด้งไปที่หน้าจัดการคอร์ส (Items) ของดีลที่เพิ่งสร้างทันที
        return redirect()->route('deals.items', $deal->id)->with('success', 'สร้างดีลงานขายสำเร็จ! กรุณาเพิ่มรายการคอร์สเรียนด้านล่าง');
    }

    // หน้าฟอร์มแก้ไขดีลงานขาย
    public function edit(SalesDeal $deal)
    {
        // 🟢 โหล่งข้อมูลประวัติและชื่อคนที่อัปเดตแนบมาด้วย
        $deal->load(['logs.user', 'customer']);

        // 🎯 ดึงข้อมูลลูกค้ามาต่อท้ายด้วยจำนวนคน เพื่อให้เลือกในหน้าแก้ไขได้ง่ายๆ เช่นกัน
        $customers = Customer::all()->map(function ($customer) {
            $customer->company_name = $customer->company_name . ' (รวม ' . ($customer->total_people ?? 1) . ' คน)';
            return $customer;
        });

        // 🟢 ดึงข้อมูลและตัดแบ่งรายชื่อผู้เข้าร่วมเพิ่มเติมจากคอลัมน์ note ของลูกค้า
        $additional_names = [];
        if ($deal->customer && !empty($deal->customer->note)) {
            // ค้นหาข้อความส่วนที่เป็นรายชื่อลูกค้าเพิ่มเติมด้วย Regular Expression
            if (preg_match('/\[รายชื่อผู้เรียนร่วมเพิ่มเติม\]:\s*(.*?)(?=\[|$)/s', $deal->customer->note, $matches)) {
                $raw_list = $matches[1];
                // แยกด้วยบรรทัดใหม่ หรือ เครื่องหมายขีด (-) เพื่อเอาเฉพาะรายชื่อ
                $lines = preg_split('/[\r\n]+/', $raw_list);
                foreach ($lines as $line) {
                    $trimmed = trim(preg_replace('/^\s*-\s*/', '', $line));
                    if (!empty($trimmed)) {
                        $additional_names[] = $trimmed;
                    }
                }
            }
        }

        // ➕ ดึงข้อมูลจาก 4 ตาราง Master Data เพื่อนำไปใช้ในหน้าแก้ไขดีล
        $subStatuses = SubStatus::orderBy('name', 'asc')->get();
        $customerGroups = CustomerGroup::orderBy('name', 'asc')->get();
        $categories = MasterCategory::orderBy('name', 'asc')->get();
        $channels = MasterChannel::orderBy('name', 'asc')->get();

        // 📊 [ส่วนที่เพิ่มใหม่] ดึงข้อมูลสำหรับ Dropdown ไดนามิก 3 ตัวแปรใหม่ไปใช้ใน View หน้าแก้ไข ป้องกัน Error Undefined variable
        $groups = CustomerGroup::orderBy('name', 'asc')->get();
        $progresses = SubStatus::orderBy('name', 'asc')->get();

        // 🔄 ดึงข้อมูลสถานะหลักจากตารางฐานข้อมูลจริงแทนการ Hardcode Array เดิม ป้องกัน Error ในหน้าแก้ไข
        $mainStatuses = MainStatus::orderBy('id', 'asc')->get();

        // ส่งตัวแปรเดิมไปใช้งานที่ View อย่างครบถ้วนตามหลัก Route Model Binding พร้อมส่ง $additional_names ไปวนลูปเพิ่ม
        return view('deals.edit', compact('deal', 'customers', 'additional_names', 'subStatuses', 'customerGroups', 'categories', 'channels', 'groups', 'progresses', 'mainStatuses'));
    }

    // อัปเดตข้อมูลดีลงานขายลงฐานข้อมูล
    public function update(Request $request, SalesDeal $deal)
    {
        // 🟢 ปลดล็อค Validation ให้ยืดหยุ่น ป้องกันระบบตีกลับเงียบๆ
        $request->validate([
            'customer_id'      => 'nullable',
            'deal_date'        => 'nullable',
            'status'           => 'nullable', // เปลี่ยนเป็น nullable 
            'progress'         => 'nullable|string|max:255',
        ]);

        $oldStatus = $deal->status;
        $note = $request->updated_note ?? $request->note;
        $dealDate = $request->deal_date ?: $deal->deal_date;
        $customerId = $request->customer_id ?: $deal->customer_id;

        // 🟢 ระบบกรองและแปลงค่าสถานะแบบเด็ดขาด (ป้องกันการเซฟผิด 100%)
        $newStatus = $oldStatus;
        if ($request->filled('status')) {
            $rawStatus = $request->status;
            if (is_numeric($rawStatus)) {
                if ($rawStatus == 3) { $newStatus = 'Closed Sale'; }
                elseif ($rawStatus == 1) { $newStatus = 'Following'; }
                elseif ($rawStatus == 2) { $newStatus = 'Forecast'; }
                elseif ($rawStatus == 4) { $newStatus = 'Denied'; }
            } else {
                $statusStr = '';
                if (is_array($rawStatus) && isset($rawStatus['name'])) {
                    $statusStr = strtolower($rawStatus['name']);
                } elseif (is_object($rawStatus) && isset($rawStatus->name)) {
                    $statusStr = strtolower($rawStatus->name);
                } elseif (is_string($rawStatus)) {
                    $statusStr = strtolower($rawStatus);
                }

                if (str_contains($statusStr, 'close')) { $newStatus = 'Closed Sale'; }
                elseif (str_contains($statusStr, 'follow')) { $newStatus = 'Following'; }
                elseif (str_contains($statusStr, 'forecast')) { $newStatus = 'Forecast'; }
                elseif (str_contains($statusStr, 'denied')) { $newStatus = 'Denied'; }
                else { $newStatus = is_string($rawStatus) ? $rawStatus : $oldStatus; }
            }
        }

        // 💡 แก้ไข: เอาฟิลด์คำว่า 'note' ออก เพราะตารางนี้ใช้คอลัมน์ 'updated_note' ในการเก็บข้อมูลบันทึกข้อความย่อยเท่านั้นป้องกัน Error 1054
        $deal->update([
            'customer_id'   => $customerId,
            'deal_date'     => $dealDate,
            'group'         => $request->group ?? $deal->group,
            'category'      => $request->category ?? $deal->category,
            'status'        => $newStatus, // 🟢 บันทึกเป็นข้อความลง DB 
            'progress'      => $request->progress ?? $deal->progress,
            'receipt_no'    => $request->receipt_no ?? $deal->receipt_no,
            'updated_note'  => $request->updated_note ?? $request->note ?? $deal->updated_note,
        ]);

        // 🟢 2. บันทึกประวัติ (Log) เมื่อสถานะเปลี่ยน หรือมีการพิมพ์ข้อความโน้ต
        if ($oldStatus != $newStatus || !empty($note)) {
            DealLog::create([
                'sales_deal_id' => $deal->id,
                'user_id'       => Auth::id() ?? 1,
                'old_status'    => $oldStatus,
                'new_status'    => $newStatus, // 🟢 บันทึกประวัติตามค่าที่แปลงแล้ว
                'note'          => $note
            ]);
        }

        return redirect()->route('deals.index')->with('success', 'อัปเดตข้อมูลสถานะดีลงานขายเรียบร้อยแล้ว!');
    }

    // ลบข้อมูลดีลงานขาย
    public function destroy(SalesDeal $deal)
    {
        // 🔒 ดักตรวจสอบสิทธิ์: อนุญาตให้เฉพาะ Admin และ Manager เท่านั้นที่สามารถลบข้อมูลนี้ได้
        if (!Auth::user()->isAdmin() && strtolower(Auth::user()->role) !== 'manager') {
            return redirect()->route('deals.index')->with('error', '⚠️ คุณไม่มีสิทธิ์ในการลบข้อมูลดีลงานขายนี้กลุ่มผู้ใช้งานของคุณถูกจำกัดสิทธิ์!');
        }

        // ลบข้อมูลไอเท็มที่ผูกกับดีลนี้ก่อนเพื่อไม่ให้ติด Constraint
        $deal->dealItems()->delete();
        
        // ลบตัวดีลหลัก
        $deal->delete();

        return redirect()->route('deals.index')->with('success', 'ลบข้อมูลดีลงานขายออกจากระบบแล้ว!');
    }

    // ฟังก์ชันสำหรับบันทึกคอร์สใหม่ผ่าน AJAX (แก้ไขคีย์ให้ตรงตามโครงสร้างตารางจริงของคุณ)
    public function quickStoreCourse(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:courses,course_name'
        ]);

        $course = Course::create([
            'course_name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'id' => $course->id,
            'name' => $course->course_name
        ]);
    }

    // ==========================================
    // ส่วนที่เพิ่มใหม่สำหรับระบบจัดการ คอร์ส (CRUD)
    // ==========================================

    // แสดงรายชื่อคอร์สทั้งหมดที่มีในระบบ
    public function indexCourse()
    {
        $courses = Course::latest()->get();
        return view('courses.index', compact('courses'));
    }

    // หน้าฟอร์มสำหรับแก้ไขชื่อคอร์ส
    public function editCourse(Course $course)
    {
        return view('courses.edit', compact('course'));
    }

    // บันทึกการแก้ไขชื่อคอร์สลงฐานข้อมูล (ปรับปรุงให้รองรับ Redirect และฟิลด์ใหม่ทั้งหมด)
    public function updateCourse(Request $request, Course $course)
    {
        $request->validate([
            'course_name' => 'required|string|unique:courses,course_name,' . $course->id,
            'description' => 'nullable|string',
            'default_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean'
        ]);

        $course->update([
            'course_name' => $request->course_name,
            'description' => $request->description,
            'default_price' => $request->default_price,
            'is_active' => $request->has('is_active') ? $request->is_active : true
        ]);

        // รองรับกรณีเรียกผ่าน AJAX
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'id' => $course->id,
                'course_name' => $course->course_name
            ]);
        }

        // กรณีเรียกผ่านแบบฟอร์มหน้าเว็บปกติ ให้ Redirect กลับพร้อมข้อความ
        return redirect()->route('courses.index')->with('success', 'อัปเดตข้อมูลคอร์สเรียนเรียบร้อยแล้ว!');
    }

    // ลบคอร์สออกจากระบบ (ปรับปรุงให้รองรับ Redirect กลับไปหน้าเดิมเมื่อลบสำเร็จหรือติดเงื่อนไข)
    public function destroyCourse(Course $course)
    {
        // ตรวจสอบก่อนว่าคอร์สนี้ถูกนำไปใช้ในดีลขายใด ๆ หรือไม่เพื่อความปลอดภัย
        if ($course->dealItems()->exists()) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'ไม่สามารถลบคอร์สนี้ได้ เนื่องจากมีข้อมูลใช้งานอยู่ในดีลงานขาย!'
                ], 422);
            }
            return redirect()->route('courses.index')->with('error', 'ไม่สามารถลบคอร์สนี้ได้ เนื่องจากมีข้อมูลใช้งานอยู่ในดีลงานขาย!');
        }

        $course->delete();
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true
            ]);
        }

        return redirect()->route('courses.index')->with('success', 'ลบคอร์สเรียนออกจากฐานข้อมูลเรียบร้อยแล้ว!');
    }

    // ==========================================
    // ส่วนที่เพิ่มใหม่สำหรับจัดการคอร์สย่อย (Deal Items) หลายรายการ
    // ==========================================

    // หน้าจอสำหรับจัดการรายการคอร์สเรียนย่อยที่ผูกกับดีลหลักนี้
    public function manageItems($id)
    {
        // ดึงข้อมูลดีลหลัก พร้อมลูกคัา และไอเท็มคอร์สที่มีอยู่แล้วโหลดประวัติ activityLogs พ่วงไปด้วย
        $deal = SalesDeal::with(['customer', 'dealItems.course', 'activityLogs.user'])->findOrFail($id);
        
        // ดึงรายชื่อคอร์สทั้งหมดจากฐานข้อมูล เพื่อให้เลือกใน Dropdown
        $courses = Course::all();
        $courseOptions = $courses; // เพิ่มตัวแปรนี้เพื่อแก้ปัญหา Undefined variable ในหน้า View

        // 🟢 เพิ่มส่วนตัดแบ่งรายชื่อผู้เข้าร่วมเพิ่มเติมจากคอลัมน์ note ของลูกค้าเพื่อส่งไปหน้าจัดการไอเท็ม
        $additional_names = [];
        if ($deal->customer && !empty($deal->customer->note)) {
            if (preg_match('/\[รายชื่อผู้เรียนร่วมเพิ่มเติม\]:\s*(.*?)(?=\[|$)/s', $deal->customer->note, $matches)) {
                $raw_list = $matches[1];
                $lines = preg_split('/[\r\n]+/', $raw_list);
                foreach ($lines as $line) {
                    $trimmed = trim(preg_replace('/^\s*-\s*/', '', $line));
                    if (!empty($trimmed)) {
                        $additional_names[] = $trimmed;
                    }
                }
            }
        }

        return view('deals.items', compact('deal', 'courses', 'courseOptions', 'additional_names'));
    }

    // บันทึกรายการคอร์สย่อยเข้าฐานข้อมูล และคำนวณเงินรวมอัตโนมัติ
    public function storeItem(Request $request, $id)
    {
        // ปรับปรุง Validation ให้รองรับชื่อตัวแปรส่วนลดทั้ง 2 รูปแบบที่มีโอกาสส่งมาจากฟอร์มหน้าบ้าน
        $request->validate([
            'course_id'           => 'required',
            'price_per_person'    => 'required|numeric|min:0',
            'discount'            => 'nullable|numeric|min:0',
            'discount_per_person' => 'nullable|numeric|min:0',
            'total_person'        => 'required|integer|min:1',
        ]);

        // ดักรับค่าส่วนลด: ไม่ว่าหน้าบ้านจะตั้งชื่อ input ว่า discount หรือ discount_per_person ระบบจะดึงมาคำนวณได้ถูกต้อง
        $discount = $request->discount ?? $request->discount_per_person ?? 0;

        // คำนวณยอดรวมของคอร์สนี้โดยอัตโนมัติ ((ราคาต่อคน - ส่วนลดต่อคน) x จำนวนคน)
        $totalRevenue = ($request->price_per_person - $discount) * $request->total_person;

        // ป้องกันกรณีใส่ส่วนลดเยอะกว่าราคาจนยอดรวมติดลบ
        if ($totalRevenue < 0) {
            $totalRevenue = 0;
        }

        // บันทึกลงตารางย่อย deal_items
        DealItem::create([
            'sales_deal_id'       => $id,
            'course_id'           => $request->course_id,
            'price_per_person'    => $request->price_per_person,
            'discount'            => $discount, // แก้ไขตรงนี้ให้ตรงกับคอลัมน์ในฐานข้อมูล!
            'total_person'        => $request->total_person,
            'total_revenue'       => $totalRevenue,
        ]);

        // คำนวณยอดรวมคอร์สย่อยทั้งหมด แล้วนำไปอัปเดตลงดีลหลัก (sales_deals)
        $mainDeal = SalesDeal::findOrFail($id);
        $grandTotalItems = DealItem::where('sales_deal_id', $id)->sum('total_revenue');
        
        // 💡 อัปเดตยอดรวมในดีลหลักผ่านตัวแปร (หากในอนาคตต้องการอัปเดตยอดรวมไว้แสดงผล)
        // โดยไม่ขัดกับเงื่อนไขเดิมของระบบ
        if (\Schema::hasColumn('sales_deals', 'total_revenue')) {
            $mainDeal->update([
                'total_revenue' => $grandTotalItems
            ]);
        }

        return redirect()->route('deals.items', $id)->with('success', 'เพิ่มรายการคอร์สเรียนเข้าไปในดีลนี้และอัปเดตยอดเงินรวมเรียบร้อยแล้ว!');
    }

    // items
    public function items($id)
    {
        // โหลดข้อมูลดีลพ่วงข้อมูลประวัติความสัมพันธ์ activityLogs.user เข้าไปด้วยเพื่อส่งไปแสดงผลที่ View หน้าจัดการดีลได้
        $deal = SalesDeal::with(['customer', 'dealItems.course', 'activityLogs.user'])->findOrFail($id);
        $courses = Course::all();
        $courseOptions = $courses; // เพิ่มตัวแปรนี้เพื่อแก้ปัญหา Undefined variable ในหน้า View

        // 🟢 เพิ่มส่วนตัดแบ่งรายชื่อผู้เข้าร่วมเพิ่มเติมจากคอลัมน์ note ของลูกค้าเพื่อส่งไปหน้าจัดการไอเท็ม
        $additional_names = [];
        if ($deal->customer && !empty($deal->customer->note)) {
            if (preg_match('/\[รายชื่อผู้เรียนร่วมเพิ่มเติม\]:\s*(.*?)(?=\[|$)/s', $deal->customer->note, $matches)) {
                $raw_list = $matches[1];
                $lines = preg_split('/[\r\n]+/', $raw_list);
                foreach ($lines as $line) {
                    $trimmed = trim(preg_replace('/^\s*-\s*/', '', $line));
                    if (!empty($trimmed)) {
                        $additional_names[] = $trimmed;
                    }
                }
            }
        }

        return view('deals.items', compact('deal', 'courses', 'courseOptions', 'additional_names'));
    }

    // destroyItem
    public function destroyItem($id)
    {
        $item = DealItem::findOrFail($id);
        $dealId = $item->sales_deal_id; // เก็บ ID ดีลหลักไว้สำหรับ redirect กลับ
        
        $item->delete(); // สั่งลบแถวคอร์สย่อยออกจากตาราง deal_items

        // หลังลบเสร็จ คำนวณยอดเงินรวมของไอเท็มที่เหลืออยู่ใหม่ทั้งหมด แล้วนำไปอัปเดตลงดีลหลัก
        $mainDeal = SalesDeal::findOrFail($dealId);
        $grandTotalItems = DealItem::where('sales_deal_id', $dealId)->sum('total_revenue');
        
        if (\Schema::hasColumn('sales_deals', 'total_revenue')) {
            $mainDeal->update([
                'total_revenue' => $grandTotalItems
            ]);
        }

        return redirect()->route('deals.items', $dealId)->with('success', 'ลบรายการคอร์สเรียนออกจากดีลและคำนวณยอดเงินรวมใหม่เรียบร้อยแล้ว!');
    }

    // printQuotation
    public function printQuotation($id)
    {
        $deal = SalesDeal::with(['customer', 'dealItems.course'])->findOrFail($id);
        return view('deals.quotation', compact('deal'));
    }

    // dismissAlert
    public function dismissAlert()
    {
        session(['deal_alert_dismissed' => true]);
        return response()->json(['success' => true]);
    }
}