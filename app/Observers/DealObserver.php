<?php

namespace App\Observers;

use App\Models\Deal;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DealObserver
{
    /**
     * ดักจับเหตุการณ์ก่อนที่ข้อมูลดีลจะถูกบันทึกลงฐานข้อมูลครั้งแรก (เพิ่มเพื่อผูกสิทธิ์เจ้าของดีล)
     */
    public function creating($deal)
    {
        if (Auth::check()) {
            $deal->user_id = Auth::id();
        }
    }

    // ดักจับเมื่อมีคนสร้างดีลงานขายใหม่ (Created)
    public function created($deal)
    {
        ActivityLog::create([
            'user_id' => Auth::id(), // ID ของเซลส์ที่ล็อกอินอยู่
            'log_type' => 'Deal',
            'record_id' => $deal->id,
            'action' => 'Created',
            'description' => "ได้สร้างดีลงานขายใหม่รหัส #" . $deal->id . " (สถานะเริ่มต้น: " . ($deal->status ?? 'Forecast') . ")",
            'new_values' => $deal->toArray(),
        ]);
    }

    // ดักจับเมื่อมีการแก้ไขดีลงานขาย (Updated)
    public function updated($deal)
    {
        // หาว่ามีคอลัมน์ไหนบ้างที่ถูกเปลี่ยนค่าไปจากเดิม
        $changes = $deal->getChanges();
        $original = array_intersect_key($deal->getRawOriginal(), $changes);

        // ดึงเฉพาะค่าที่เปลี่ยนไปบันทึก เพื่อไม่ให้ข้อมูลรกเกินไป
        if (!empty($changes)) {
            // ดึงชื่อฟิลด์ที่เด่นๆ มาทำเป็นข้อความอธิบายความเข้าใจง่าย
            $statusNote = isset($changes['status']) ? " เปลี่ยนสถานะเป็น " . $changes['status'] : "";
            $priceNote = isset($changes['receipt_no']) ? " ใส่เลขที่ใบเสร็จ " . $changes['receipt_no'] : "";

            ActivityLog::create([
                'user_id' => Auth::id(),
                'log_type' => 'Deal',
                'record_id' => $deal->id,
                'action' => 'Updated',
                'description' => "ได้ทำการแก้ไขข้อมูลดีลงานขาย" . $statusNote . $priceNote,
                'old_values' => $original,
                'new_values' => $changes,
            ]);
        }
    }

    // ดักจับเมื่อมีการลบดีลงานขาย (Deleted)
    public function deleted($deal)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'log_type' => 'Deal',
            'record_id' => $deal->id,
            'action' => 'Deleted',
            'description' => "ได้ลบดีลงานขายรหัส #" . $deal->id . " ออกจากระบบ",
            'old_values' => $deal->toArray(),
        ]);
    }
}