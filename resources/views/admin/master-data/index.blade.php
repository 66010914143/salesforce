@extends('layouts.app')

@section('page_title', '⚙️ ตั้งค่าข้อมูลระบบ (Dropdown Master Data)')

@section('content')
<div class="container mx-auto" x-data="{ 
    isEditModalOpen: false, 
    editActionUrl: '', 
    editItemName: '' 
}">

    <div class="bg-white rounded-xl shadow-xs p-6 mb-6 border border-gray-200">
        <h3 class="text-lg font-bold text-slate-800 mb-1 flex items-center">
            <i class="fa-solid fa-circle-info text-sky-500 mr-2"></i> พื้นที่จัดการข้อมูลหลักของระบบ (Master Data)
        </h3>
        <p class="text-sm text-gray-500">
            Specific สำหรับผู้ใช้งานสิทธิ์ <span class="text-purple-600 font-bold">Admin</span> เท่านั้นที่สามารถเพิ่ม แก้ไข หรือลบตัวเลือกในหน้านี้ได้ ข้อมูลที่คุณปรับแต่งจะไปส่งผลกับช่องเลือกในหน้าฟอร์มบันทึกงานขาย (Deals) ทันที
        </p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <div class="bg-white rounded-xl shadow-xs border border-gray-200 flex flex-col">
            <div class="p-5 border-b border-gray-100 bg-slate-50 rounded-t-xl flex justify-between items-center">
                <span class="font-bold text-slate-700 flex items-center"><i class="fa-solid fa-tags mr-2 text-slate-400"></i> สถานะย่อย (Sub Status)</span>
            </div>
            <div class="p-5 flex-1 flex flex-col">
                <form action="{{ route('admin.master-data.store', 'sub-status') }}" method="POST" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="name" required placeholder="เพิ่มสถานะย่อยใหม่..." class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-slate-500">
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-900 transition-colors shrink-0 cursor-pointer"><i class="fa-solid fa-plus mr-1"></i> เพิ่ม</button>
                </form>
                <div class="overflow-y-auto max-h-[250px] border border-gray-100 rounded-lg">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @forelse($subStatuses as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $item->name }}</td>
                                    <td class="px-4 py-3 text-right flex justify-end gap-2">
                                        <button @click="isEditModalOpen = true; editItemName = '{{ $item->name }}'; editActionUrl = '{{ route('admin.master-data.update', ['type' => 'sub-status', 'id' => $item->id]) }}'" class="text-slate-500 hover:text-amber-500 p-1 cursor-pointer" title="แก้ไข"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <form action="{{ route('admin.master-data.destroy', ['type' => 'sub-status', 'id' => $item->id]) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบตัวเลือกนี้?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-500 p-1 cursor-pointer" title="ลบ"><i class="fa-solid fa-trash-can"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-4 text-center text-gray-400">ยังไม่มีข้อมูลตัวเลือก</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-xs border border-gray-200 flex flex-col">
            <div class="p-5 border-b border-gray-100 bg-slate-50 rounded-t-xl flex justify-between items-center">
                <span class="font-bold text-slate-700 flex items-center"><i class="fa-solid fa-users mr-2 text-slate-400"></i> กลุ่มลูกค้า (Customer Group)</span>
            </div>
            <div class="p-5 flex-1 flex flex-col">
                <form action="{{ route('admin.master-data.store', 'customer-group') }}" method="POST" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="name" required placeholder="เพิ่มกลุ่มลูกค้าใหม่..." class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-slate-500">
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-900 transition-colors shrink-0 cursor-pointer"><i class="fa-solid fa-plus mr-1"></i> เพิ่ม</button>
                </form>
                <div class="overflow-y-auto max-h-[250px] border border-gray-100 rounded-lg">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @forelse($customerGroups as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $item->name }}</td>
                                    <td class="px-4 py-3 text-right flex justify-end gap-2">
                                        <button @click="isEditModalOpen = true; editItemName = '{{ $item->name }}'; editActionUrl = '{{ route('admin.master-data.update', ['type' => 'customer-group', 'id' => $item->id]) }}'" class="text-slate-500 hover:text-amber-500 p-1 cursor-pointer" title="แก้ไข"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <form action="{{ route('admin.master-data.destroy', ['type' => 'customer-group', 'id' => $item->id]) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบตัวเลือกนี้?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-500 p-1 cursor-pointer" title="ลบ"><i class="fa-solid fa-trash-can"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-4 text-center text-gray-400">ยังไม่มีข้อมูลตัวเลือก</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-xs border border-gray-200 flex flex-col">
            <div class="p-5 border-b border-gray-100 bg-slate-50 rounded-t-xl flex justify-between items-center">
                <span class="font-bold text-slate-700 flex items-center"><i class="fa-solid fa-layer-group mr-2 text-slate-400"></i> หมวดหมู่ (Category)</span>
            </div>
            <div class="p-5 flex-1 flex flex-col">
                <form action="{{ route('admin.master-data.store', 'category') }}" method="POST" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="name" required placeholder="เพิ่มหมวดหมู่ใหม่..." class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-slate-500">
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-900 transition-colors shrink-0 cursor-pointer"><i class="fa-solid fa-plus mr-1"></i> เพิ่ม</button>
                </form>
                <div class="overflow-y-auto max-h-[250px] border border-gray-100 rounded-lg">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @forelse($categories as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $item->name }}</td>
                                    <td class="px-4 py-3 text-right flex justify-end gap-2">
                                        <button @click="isEditModalOpen = true; editItemName = '{{ $item->name }}'; editActionUrl = '{{ route('admin.master-data.update', ['type' => 'category', 'id' => $item->id]) }}'" class="text-slate-500 hover:text-amber-500 p-1 cursor-pointer" title="แก้ไข"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <form action="{{ route('admin.master-data.destroy', ['type' => 'category', 'id' => $item->id]) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบตัวเลือกนี้?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-500 p-1 cursor-pointer" title="ลบ"><i class="fa-solid fa-trash-can"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-4 text-center text-gray-400">ยังไม่มีข้อมูลตัวเลือก</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-xs border border-gray-200 flex flex-col">
            <div class="p-5 border-b border-gray-100 bg-slate-50 rounded-t-xl flex justify-between items-center">
                <span class="font-bold text-slate-700 flex items-center"><i class="fa-solid fa-circle-nodes mr-2 text-slate-400"></i> ช่องทาง (Channel)</span>
            </div>
            <div class="p-5 flex-1 flex flex-col">
                <form action="{{ route('admin.master-data.store', 'channel') }}" method="POST" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="name" required placeholder="เพิ่มช่องทางใหม่..." class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-slate-500">
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-900 transition-colors shrink-0 cursor-pointer"><i class="fa-solid fa-plus mr-1"></i> เพิ่ม</button>
                </form>
                <div class="overflow-y-auto max-h-[250px] border border-gray-100 rounded-lg">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @forelse($channels as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $item->name }}</td>
                                    <td class="px-4 py-3 text-right flex justify-end gap-2">
                                        <button @click="isEditModalOpen = true; editItemName = '{{ $item->name }}'; editActionUrl = '{{ route('admin.master-data.update', ['type' => 'channel', 'id' => $item->id]) }}'" class="text-slate-500 hover:text-amber-500 p-1 cursor-pointer" title="แก้ไข"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <form action="{{ route('admin.master-data.destroy', ['type' => 'channel', 'id' => $item->id]) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบตัวเลือกนี้?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-500 p-1 cursor-pointer" title="ลบ"><i class="fa-solid fa-trash-can"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-4 text-center text-gray-400">ยังไม่มีข้อมูลตัวเลือก</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-xs border border-gray-200 flex flex-col">
            <div class="p-5 border-b border-gray-100 bg-slate-50 rounded-t-xl flex justify-between items-center">
                <span class="font-bold text-slate-700 flex items-center"><i class="fa-solid fa-star mr-2 text-slate-400"></i> สถานะหลัก (Main Status)</span>
            </div>
            <div class="p-5 flex-1 flex flex-col">
                <form action="{{ route('admin.master-data.store', 'main-status') }}" method="POST" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="name" required placeholder="เพิ่มสถานะหลักใหม่..." class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-slate-500">
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-900 transition-colors shrink-0 cursor-pointer"><i class="fa-solid fa-plus mr-1"></i> เพิ่ม</button>
                </form>
                <div class="overflow-y-auto max-h-[250px] border border-gray-100 rounded-lg">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @forelse($mainStatusesData as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $item->name }}</td>
                                    <td class="px-4 py-3 text-right flex justify-end gap-2">
                                        @if(!in_array($item->name, [
                                            'Denied', 'Closed Sale', 'Following', 'Forecast',
                                            'Denied (ปฏิเสธ/ยกเลิก)', 'Closed Sale (ปิดการขายสำเร็จ)', 'Following (กำลังติดตามงาน)', 'Forecast (ประมาณการยอดขาย)'
                                        ]))
                                            <button @click="isEditModalOpen = true; editItemName = '{{ $item->name }}'; editActionUrl = '{{ route('admin.master-data.update', ['type' => 'main-status', 'id' => $item->id]) }}'" class="text-slate-500 hover:text-amber-500 p-1 cursor-pointer" title="แก้ไข"><i class="fa-solid fa-pen-to-square"></i></button>
                                            <form action="{{ route('admin.master-data.destroy', ['type' => 'main-status', 'id' => $item->id]) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบตัวเลือกนี้?')" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-slate-400 hover:text-rose-500 p-1 cursor-pointer" title="ลบ"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-xs px-2 py-1 bg-gray-50 border border-gray-200 rounded-md inline-flex items-center select-none" title="ระบบล็อกไว้เพื่อความปลอดภัย">
                                                <i class="fa-solid fa-lock mr-1 text-[11px]"></i> ระบบ
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-4 text-center text-gray-400">ยังไม่มีข้อมูลตัวเลือก</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div x-show="isEditModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-all">
        <div class="bg-white rounded-xl shadow-xl border border-gray-100 max-w-md w-full p-6" @click.away="isEditModalOpen = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-800"><i class="fa-solid fa-pen-to-square text-amber-500 mr-1"></i> แก้ไขข้อมูลตัวเลือก</h3>
                <button @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form :action="editActionUrl" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">ชื่อตัวเลือกปัจจุบัน</label>
                    <input type="text" name="name" x-model="editItemName" required class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-slate-500">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="isEditModalOpen = false" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">ยกเลิก</button>
                    <button type="submit" class="px-4 py-2 bg-amber-500 text-white text-xs font-bold rounded-lg hover:bg-amber-600 transition-colors shadow-sm shadow-amber-500/30 cursor-pointer">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection