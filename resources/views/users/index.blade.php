@extends('layouts.app')

@section('page_title', 'จัดการพนักงานและสิทธิ์การใช้งาน')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-gray-100 bg-slate-50/50">
                    <h4 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-users text-slate-400"></i> รายชื่อผู้ใช้งานในระบบทั้งหมด
                    </h4>
                    <p class="text-xs text-gray-400 mt-1">แสดงพนักงานขายและผู้ดูแลระบบที่มีสิทธิ์ล็อกอินเข้าใช้งานในปัจจุบัน</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 font-bold text-xs uppercase tracking-wider border-b border-gray-100">
                                <th class="py-3.5 px-5">ชื่อ-นามสกุล</th>
                                <th class="py-3.5 px-5">อีเมล (Username)</th>
                                <th class="py-3.5 px-5 text-center">สิทธิ์การใช้งาน</th>
                                <th class="py-3.5 px-5 text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($users as $user)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3.5 px-5 font-medium text-gray-900">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs uppercase">
                                                {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                                            </div>
                                            <span>{{ $user->name }}</span>
                                            @if(auth()->id() == $user->id)
                                                <span class="text-[10px] bg-slate-100 text-slate-600 font-bold px-1.5 py-0.5 rounded-sm">คุณ</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5 font-mono text-xs text-gray-500">{{ $user->email }}</td>
                                    <td class="py-3.5 px-5 text-center">
                                        @if($user->isAdmin())
                                            <span class="inline-flex items-center gap-1 text-xs bg-purple-50 text-purple-700 font-bold px-2.5 py-1 rounded-full border border-purple-100 shadow-2xs">
                                                <i class="fa-solid fa-user-shield text-[10px]"></i> ผู้ดูแลระบบ (Admin)
                                            </span>
                                        @elseif($user->isManager())
                                            <span class="inline-flex items-center gap-1 text-xs bg-amber-50 text-amber-700 font-bold px-2.5 py-1 rounded-full border border-amber-100 shadow-2xs">
                                                <i class="fa-solid fa-user-tie text-[10px]"></i> หัวหน้า/ผู้บริหาร (Manager)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs bg-sky-50 text-sky-700 font-bold px-2.5 py-1 rounded-full border border-sky-100 shadow-2xs">
                                                <i class="fa-solid fa-user-tag text-[10px]"></i> พนักงานขาย (Sales)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" onclick="openEditUserModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}')" class="text-amber-500 hover:text-amber-700 p-1.5 hover:bg-amber-50 rounded-lg transition-colors cursor-pointer" title="แก้ไขข้อมูล">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>

                                            @if(auth()->id() != $user->id)
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('❗ ยืนยันที่จะลบพนักงานคนนี้ออกจากระบบใช่หรือไม่? ข้อมูลประวัติการล็อกอินจะถูกตัดสิทธิ์ทันที');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-500 hover:text-rose-700 p-1.5 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="ลบพนักงาน">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-400 italic">ลบไม่ได้</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-400 italic">ไม่มีข้อมูลพนักงานในระบบ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
            <div class="border-b border-gray-100 pb-3 mb-4">
                <h4 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-emerald-500"></i> เพิ่มพนักงานใหม่
                </h4>
                <p class="text-xs text-gray-400 mt-0.5">สร้างบัญชีผู้ใช้และกำหนดบทบาทเพื่อเข้าใช้งานระบบ</p>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-bold text-gray-600 uppercase mb-1">ชื่อ-นามสกุลพนักงาน <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="ตัวอย่าง: สมชาย ตั้งใจขาย" class="w-full text-sm bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors">
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-gray-600 uppercase mb-1">อีเมลผู้ใช้งาน (Email/Username) <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="ตัวอย่าง: somchai@company.com" class="w-full text-sm font-mono bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors">
                </div>

                <div>
                    <label for="role" class="block text-xs font-bold text-gray-600 uppercase mb-1">สิทธิ์ระดับการใช้งาน <span class="text-rose-500">*</span></label>
                    <select name="role" id="role" required class="w-full text-sm bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors cursor-pointer font-semibold text-gray-700">
                        <option value="sales" {{ old('role') == 'sales' ? 'selected' : '' }}>🔵 Sales (พนักงานขาย: เห็นเฉพาะงานตนเอง)</option>
                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>🟠 Manager (หัวหน้า/ผู้บริหาร: เห็นรวมทั้งหมด)</option>
                    </select>
                </div>

                <div class="pt-1 border-t border-dashed border-gray-100 my-2"></div>

                <div>
                    <label for="password" class="block text-xs font-bold text-gray-600 uppercase mb-1">รหัสผ่านตั้งต้น <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" id="password" required placeholder="อย่างน้อย 6 ตัวอักษร" class="w-full text-sm bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-600 uppercase mb-1">ยืนยันรหัสผ่านอีกครั้ง <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="กรอกรหัสผ่านให้ตรงกัน" class="w-full text-sm bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors">
                </div>

                <button type="submit" class="w-full mt-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm py-2.5 px-4 rounded-lg shadow-xs hover:shadow-md transition-all cursor-pointer flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> บันทึกและเปิดใช้งานบัญชี
                </button>
            </form>
        </div>

    </div>
</div>

<div id="editUserModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 transition-all duration-300">
    <div id="modalContainer" class="bg-white rounded-xl shadow-xl border border-gray-100 w-full max-w-md p-6 relative transform transition-all duration-200 scale-95 opacity-0">
        <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
            <h4 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-amber-500"></i> แก้ไขข้อมูลพนักงาน
            </h4>
            <button type="button" onclick="closeEditUserModal()" class="text-gray-400 hover:text-rose-500 transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <form id="editUserForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label for="edit_name" class="block text-xs font-bold text-gray-600 uppercase mb-1">ชื่อ-นามสกุลพนักงาน <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="edit_name" required class="w-full text-sm bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-amber-500 focus:bg-white transition-colors">
            </div>

            <div>
                <label for="edit_email" class="block text-xs font-bold text-gray-600 uppercase mb-1">อีเมลผู้ใช้งาน (Email/Username) <span class="text-rose-500">*</span></label>
                <input type="email" name="email" id="edit_email" required class="w-full text-sm font-mono bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-amber-500 focus:bg-white transition-colors">
            </div>

            <div>
                <label for="edit_role" class="block text-xs font-bold text-gray-600 uppercase mb-1">สิทธิ์ระดับการใช้งาน <span class="text-rose-500">*</span></label>
                <select name="role" id="edit_role" required class="w-full text-sm bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-amber-500 focus:bg-white transition-colors cursor-pointer font-semibold text-gray-700">
                    <option value="sales">🔵 Sales (พนักงานขาย: เห็นเฉพาะงานตนเอง)</option>
                    <option value="manager">🟠 Manager (หัวหน้า/ผู้บริหาร: เห็นรวมทั้งหมด)</option>
                    <option value="admin" class="hidden">🔮 Admin (ผู้ดูแลระบบ)</option>
                </select>
            </div>

            <div class="pt-1 border-t border-dashed border-gray-100 my-2"></div>

            <div class="bg-amber-50/40 border border-amber-100 rounded-lg p-3">
                <label for="edit_password" class="block text-xs font-bold text-amber-800 uppercase mb-1">
                    <i class="fa-solid fa-key text-amber-500/70 mr-0.5"></i> เปลี่ยนรหัสผ่านใหม่
                </label>
                <input type="password" name="password" id="edit_password" placeholder="กรอกรหัสใหม่เฉพาะเมื่อต้องการเปลี่ยน (อย่างน้อย 6 ตัว)" class="w-full text-sm bg-white border border-amber-200 rounded-lg px-3 py-2 focus:outline-none focus:border-amber-500 transition-colors">
                <p class="text-[10px] text-amber-600/80 mt-1">* ปล่อยว่างไว้หากใช้รหัสผ่านเดิมต่อไป</p>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-lg transition-colors cursor-pointer">
                    ยกเลิก
                </button>
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm py-2 px-4 rounded-lg shadow-xs hover:shadow-md transition-all cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditUserModal(id, name, email, role) {
        const modal = document.getElementById('editUserModal');
        const container = document.getElementById('modalContainer');
        
        // ผูกค่าข้อมูลเดิมเข้ากับฟอร์มบน Modal
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        
        // จัดการแสดงผล Option ของ Admin กรณีที่เป็นการแก้ไขข้อมูลตัวเองหรือ Admin ด้วยกัน
        const roleSelect = document.getElementById('edit_role');
        const adminOption = roleSelect.querySelector('option[value="admin"]');
        if(role === 'admin') {
            adminOption.classList.remove('hidden');
        } else {
            adminOption.classList.add('hidden');
        }
        roleSelect.value = role;
        
        document.getElementById('edit_password').value = ''; // ล้างช่องรหัสผ่านเก่าออกก่อนทุกครั้ง
        
        // ปรับเส้นทาง Action Form ให้ตรงตาม ID พนักงานที่เลือกแก้ไข
        document.getElementById('editUserForm').action = '/users/' + id;
        
        // สั่งเปิดแสดงผลตัวครอบ Modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // เล่น Animation คลี่ฟอร์มให้ลื่นไหลสวยงาม
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeEditUserModal() {
        const modal = document.getElementById('editUserModal');
        const container = document.getElementById('modalContainer');
        
        // เล่น Animation หดกลับ
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        
        // ปิดซ่อนหลังจากอนิเมชันทำงานเสร็จสิ้น
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 150);
    }
</script>
@endsection