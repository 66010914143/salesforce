<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubStatus;
use App\Models\CustomerGroup;
use App\Models\MasterCategory;
use App\Models\MasterChannel;
use App\Models\MainStatus;
use Illuminate\Support\Str; // ➕ เพิ่มการเรียกใช้ Str ของ Laravel

class MasterDataController extends Controller
{
    private function getModel($type)
    {
        switch ($type) {
            case 'sub-status': return new SubStatus();
            case 'customer-group': return new CustomerGroup();
            case 'category': return new MasterCategory();
            case 'channel': return new MasterChannel();
            case 'main-status': return new MainStatus();
            default: abort(404, 'ไม่พบประเภทข้อมูลที่ระบุ');
        }
    }

    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $subStatuses = SubStatus::orderBy('id', 'desc')->get();
        $customerGroups = CustomerGroup::orderBy('id', 'desc')->get();
        $categories = MasterCategory::orderBy('id', 'desc')->get();
        $channels = MasterChannel::orderBy('id', 'desc')->get();
        $mainStatusesData = MainStatus::orderBy('id', 'desc')->get();

        return view('admin.master-data.index', compact('subStatuses', 'customerGroups', 'categories', 'channels', 'mainStatusesData'));
    }

    public function store(Request $request, $type)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ], [
            'name.required' => 'กรุณากรอกชื่อตัวเลือก'
        ]);

        $model = $this->getModel($type);
        $model->create([
            'name' => $request->name
        ]);

        return redirect()->back()->with('success', 'เพิ่มตัวเลือกใหม่เรียบร้อยแล้ว');
    }

    public function update(Request $request, $type, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        // ➕ อัปเดตเงื่อนไข: ตรวจจับคำหลักภาษาอังกฤษ ไม่ว่าจะมีภาษาไทยต่อท้ายหรือไม่ก็ตาม
        if ($type === 'main-status') {
            $checkStatus = MainStatus::findOrFail($id);
            if (Str::contains($checkStatus->name, ['Denied', 'Closed Sale', 'Following', 'Forecast'])) {
                return redirect()->back()->with('error', 'ระบบไม่อนุญาตให้แก้ไขสถานะหลักเริ่มต้นได้!');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ], [
            'name.required' => 'กรุณากรอกชื่อตัวเลือก'
        ]);

        $model = $this->getModel($type);
        $item = $model->findOrFail($id);
        $item->update([
            'name' => $request->name
        ]);

        return redirect()->back()->with('success', 'แก้ไขข้อมูลตัวเลือกเรียบร้อยแล้ว');
    }

    public function destroy($type, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($type === 'main-status') {
            $status = MainStatus::findOrFail($id);

            // ➕ อัปเดตเงื่อนไข: ตรวจจับคำหลักภาษาอังกฤษ ไม่ว่าจะมีภาษาไทยต่อท้ายหรือไม่ก็ตาม
            if (Str::contains($status->name, ['Denied', 'Closed Sale', 'Following', 'Forecast'])) {
                return redirect()->back()->with('error', 'ระบบไม่อนุญาตให้ลบสถานะหลักเริ่มต้นได้!');
            }

            if (\App\Models\SalesDeal::where('status', $status->name)->exists()) {
                return redirect()->back()->with('error', 'ไม่สามารถลบสถานะนี้ได้ เนื่องจากมีข้อมูลดีลงานขายในระบบใช้งานอยู่!');
            }
        }

        $model = $this->getModel($type);
        $item = $model->findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'ลบตัวเลือกออกจากระบบเรียบร้อยแล้ว');
    }
}