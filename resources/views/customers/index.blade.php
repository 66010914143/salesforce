@extends('layouts.app')

@section('page_title', 'รายชื่อลูกค้า / บริษัททั้งหมด')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">รายชื่อลูกค้า / บริษัททั้งหมด</h2>
            <p class="text-gray-500 text-sm mt-1">จัดการข้อมูลบริษัทคู่ค้าและลูกค้าบุคคลเพื่อใช้ในงานขาย</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            
            <div class="inline-flex bg-slate-100 rounded-lg p-1 mr-1 border border-slate-200">
                <a href="{{ route('customers.index') }}" 
                   class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ !request('filter') || request('filter') === 'all' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    ทั้งหมด
                </a>
                <a href="{{ route('customers.index', ['filter' => 'corporate']) }}" 
                   class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ request('filter') === 'corporate' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    องค์กร
                </a>
                <a href="{{ route('customers.index', ['filter' => 'individual']) }}" 
                   class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ request('filter') === 'individual' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    บุคคล
                </a>
            </div>

            <a href="{{ route('customers.create', ['type' => 'individual']) }}" class="inline-flex items-center bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <i class="fa-solid fa-plus mr-2"></i> เพิ่มลูกค้าบุคคล
            </a>
            <a href="{{ route('customers.create', ['type' => 'corporate']) }}" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <i class="fa-solid fa-plus mr-2"></i> เพิ่มลูกค้าองค์กร/หน่วยงาน
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
                        <th class="px-6 py-3.5">ชื่อลูกค้า / บริษัท</th>
                        <th class="px-6 py-3.5">ผู้ติดต่อหลัก</th>
                        <th class="px-6 py-3.5">เบอร์โทรศัพท์</th>
                        <th class="px-6 py-3.5">อีเมลติดต่อ</th>
                        <th class="px-6 py-3.5 text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if(isset($customer->type) && $customer->type === 'individual')
                                        <span class="inline-flex items-center gap-1.5 font-medium text-gray-900">
                                            <span class="w-2 h-2 rounded-full bg-orange-500" title="ลูกค้าบุคคล"></span>
                                            {{ $customer->name ?? $customer->company_name }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 font-medium text-gray-900">
                                            <span class="w-2 h-2 rounded-full bg-indigo-500" title="ลูกค้าองค์กร"></span>
                                            {{ $customer->company_name }}
                                        </span>
                                    @endif

                                    @if(isset($customer->total_people) && $customer->total_people > 1)
                                        <button type="button" 
                                                onclick="toggleCustomerDropdown('extra-members-{{ $customer->id }}')"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 border border-indigo-100 transition-colors focus:outline-none ml-1">
                                            <i class="fa-solid fa-users text-indigo-400 text-[10px]"></i>
                                            <span>รวม {{ $customer->total_people }} คน</span>
                                            <i class="fa-solid fa-chevron-down text-[9px] ml-0.5 transition-transform duration-200" id="icon-{{ $customer->id }}"></i>
                                        </button>
                                    @endif
                                </div>

                                @if(isset($customer->total_people) && $customer->total_people > 1 && $customer->note)
                                    <div id="extra-members-{{ $customer->id }}" class="hidden mt-2 ml-4 p-2.5 bg-slate-50 border border-slate-200 rounded-lg max-w-md shadow-sm">
                                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">รายชื่อผู้เรียนร่วมเพิ่มเติม:</p>
                                        <div class="text-xs text-gray-600 leading-normal whitespace-pre-line">{{ trim(str_replace('[รายชื่อผู้เรียนร่วมเพิ่มเติม]:', '', $customer->note)) }}</div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-500">
                            @if(isset($customer->type) && $customer->type === 'individual')
                                <span class="text-gray-400">-</span>
                            @else
                                {{ $customer->contact_name ?? '-' }}
                            @endif
                        </td>
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

<script>
    function toggleCustomerDropdown(elementId) {
        const dropdown = document.getElementById(elementId);
        const customerId = elementId.replace('extra-members-', '');
        const icon = document.getElementById('icon-' + customerId);
        
        if (dropdown) {
            dropdown.classList.toggle('hidden');
            if (icon) {
                icon.classList.toggle('rotate-180');
            }
        }
    }
</script>
@endsection