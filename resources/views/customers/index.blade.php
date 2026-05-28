@extends('layouts.app')

@section('page_title', 'รายชื่อลูกค้า / บริษัททั้งหมด')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">รายชื่อลูกค้า / บริษัท (Corporate Prospects)</h2>
            <p class="text-gray-500 text-sm mt-1">จัดการข้อมูลบริษัทคู่ค้าและผู้ติดต่อหลักเพื่อใช้ในงานขาย</p>
        </div>
        <div>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <i class="fa-solid fa-plus mr-2"></i> เพิ่มลูกค้าใหม่
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm flex items-center gap-2 shadow-sm">
        <i class="fa-solid fa-circle-check text-emerald-500 text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-gray-600">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-gray-700 font-semibold">
                        <th class="px-6 py-3.5">ชื่อบริษัท / องค์กร</th>
                        <th class="px-6 py-3.5">ผู้ติดต่อหลัก</th>
                        <th class="px-6 py-3.5">เบอร์โทรศัพท์</th>
                        <th class="px-6 py-3.5">อีเมลติดต่อ</th>
                        <th class="px-6 py-3.5 text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $customer->company_name }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $customer->contact_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $customer->phone ?? '-' }}</td>
                        <td class="px-6 py-4">
                            @if($customer->email)
                            <a href="mailto:{{ $customer->email }}" class="text-indigo-600 hover:underline">{{ $customer->email }}</a>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-3">
                                <a href="{{ route('customers.edit', $customer->id) }}" class="text-slate-400 hover:text-indigo-600 transition-colors" title="แก้ไขข้อมูล">
                                    <i class="fa-solid fa-pen-to-square text-base"></i>
                                </a>
                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลลูกค้ารายนี้?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors" title="ลบข้อมูล">
                                        <i class="fa-solid fa-trash-can text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <i class="fa-solid fa-folder-open text-3xl text-gray-300"></i>
                                <p class="text-sm">ยังไม่มีข้อมูลรายชื่อลูกค้าในระบบ</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($customers, 'links') && $customers->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-slate-50/50">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection