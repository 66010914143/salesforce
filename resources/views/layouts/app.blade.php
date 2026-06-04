<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salesforce CRM System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased">

    @php
        // คำนวณแจ้งเตือนสำหรับ Sidebar
        $sidebarPendingCount = 0;
        if(auth()->check()) {
            $isGlobalAdmin = auth()->user()->isAdmin() || auth()->user()->isManager();
            $globalUserId = auth()->id();
            
            // ใช้ DB::table เพื่อป้องกัน Error deleted_at
            $sidebarPendingCount = \Illuminate\Support\Facades\DB::table('sales_deals')
                ->whereIn('status', ['Following', 'Forecast'])
                ->when(!$isGlobalAdmin, function($q) use ($globalUserId) {
                    $q->where('user_id', $globalUserId);
                })->count();
        }
    @endphp

    <div class="flex h-screen overflow-hidden">
        
        <div class="w-64 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0">
            <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-transparent"></div>
                
                <div class="relative flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30 mr-3 shrink-0">
                    <i class="fa-solid fa-arrow-trend-up text-base"></i>
                </div>
                
                <div class="relative flex flex-col justify-center">
                    <span class="font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-300 text-xl tracking-tight leading-none mb-0.5">Sale Report</span>
                    <span class="text-[10px] text-emerald-400 font-bold tracking-widest uppercase leading-none">CRM System</span>
                </div>
            </div>
            
            <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-3">
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white transition-colors {{ Request::is('/') ? 'bg-slate-800 text-white' : '' }}">
                    <i class="fa-solid fa-gauge mr-3 w-5 text-center text-slate-400"></i> แดชบอร์ดภาพรวม
                </a>
                @endif

                @if(auth()->check())
                <a href="{{ url('/dashboard/sales') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white transition-colors {{ Request::is('dashboard/sales*') ? 'bg-slate-800 text-white' : '' }}">
                    <i class="fa-solid fa-chart-pie mr-3 w-5 text-center text-slate-400"></i> แดชบอร์ดส่วนบุคคล
                </a>
                @endif

                <a href="{{ route('customers.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white transition-colors {{ Request::is('customers*') ? 'bg-slate-800 text-white' : '' }}">
                    <i class="fa-solid fa-building mr-3 w-5 text-center text-slate-400"></i> ข้อมูลลูกค้า / บริษัท
                </a>
                
                <a href="{{ route('deals.index') }}" class="flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white transition-colors {{ Request::is('deals*') ? 'bg-slate-800 text-white' : '' }}">
                    <div class="flex items-center">
                        <i class="fa-solid fa-handshake mr-3 w-5 text-center text-slate-400"></i> บันทึกงานขาย (Deals)
                    </div>
                    @if($sidebarPendingCount > 0)
                        <span id="sidebar-alert-badge" class="bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse shadow-sm shadow-rose-500/50">
                            {{ $sidebarPendingCount }}
                        </span>
                    @endif
                </a>
                
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                <a href="{{ route('courses.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white transition-colors {{ Request::is('master/courses*') ? 'bg-slate-800 text-white' : '' }}">
                    <i class="fa-solid fa-book mr-3 w-5 text-center text-slate-400"></i> จัดการคอร์สเรียน
                </a>
                @endif

                @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white transition-colors {{ Request::is('users*') ? 'bg-slate-800 text-white' : '' }}">
                    <i class="fa-solid fa-users-gear mr-3 w-5 text-center text-slate-400"></i> จัดการพนักงานและสิทธิ์
                </a>
                @endif
            </nav>

            @if(auth()->check())
            <div class="px-3 py-2 border-t border-slate-800/60 bg-slate-950/20">
                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition-colors cursor-pointer" title="ออกจากระบบ">
                        <i class="fa-solid fa-right-from-bracket mr-3 w-5 text-center"></i> ออกจากระบบ (Logout)
                    </button>
                </form>
            </div>
            @endif

            <div class="p-4 border-t border-slate-800 bg-slate-950 text-xs text-slate-500 text-center">
                Sales Report System v1.0
            </div>
        </div>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-6 z-10">
                <h2 class="text-xl font-semibold text-gray-700">@yield('page_title', 'ระบบจัดการคอร์สเรียน')</h2>
                <div class="flex items-center space-x-3">
                    @if(auth()->check())
                        <span class="text-sm bg-slate-100 text-slate-700 px-3 py-1 rounded-full font-medium border border-slate-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-user-circle text-slate-500 text-base"></i> 
                            <span>{{ auth()->user()->name }}</span>
                            <span class="text-xs font-bold px-1.5 py-0.5 rounded-md shadow-3xs @if(auth()->user()->isAdmin()) bg-purple-100 text-purple-700 @elseif(auth()->user()->isManager()) bg-amber-100 text-amber-700 @else bg-sky-100 text-sky-700 @endif">
                                @if(auth()->user()->isAdmin()) Admin @elseif(auth()->user()->isManager()) Manager @else Sales @endif
                            </span>
                        </span>
                        
                        <form action="{{ route('logout') }}" method="POST" class="inline-flex items-center">
                            @csrf
                            <button type="submit" class="ml-1 text-gray-400 hover:text-rose-500 p-2 rounded-lg hover:bg-gray-50 transition-all cursor-pointer flex items-center justify-center" title="ออกจากระบบ">
                                <i class="fa-solid fa-power-off text-base"></i>
                            </button>
                        </form>
                    @else
                        <span class="text-sm bg-slate-100 text-slate-700 px-3 py-1 rounded-full font-medium border border-slate-200">
                            <i class="fa-solid fa-user-circle mr-1"></i> ทีมขาย (Sales)
                        </span>
                    @endif
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                @if(session('success'))
                    <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-r-lg shadow-sm flex items-center">
                        <i class="fa-solid fa-circle-check mr-2 text-emerald-500 text-lg"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

    </div>

    @if(auth()->check() && !auth()->user()->isAdmin() && !auth()->user()->isManager() && $sidebarPendingCount > 0)
        <div id="pending-tasks-popup" style="display: none;" class="fixed bottom-5 right-5 w-[340px] bg-white border border-amber-200 rounded-xl shadow-2xl z-50 p-5 transform transition-all duration-300 ease-out">
            <div class="flex items-start gap-3.5">
                <div class="flex-shrink-0 w-9 h-9 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 border border-amber-100">
                    <i class="fa-solid fa-circle-info text-lg"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900 text-base mb-1 tracking-wide">แจ้งเตือนงานค้าง</h4>
                    <p class="text-xs text-gray-600 leading-relaxed mb-4">
                        คุณมีรายการในสถานะ <span class="font-semibold text-slate-900">Following</span> และ <span class="font-semibold text-slate-900">Forecast</span> จำนวน <span class="font-bold text-rose-500 text-sm">{{ $sidebarPendingCount }}</span> งาน โปรดอัพเดทสถานะล่าสุดเป็น Closed sale หรือปรับเพิ่มข้อมูลบันทึกความคืบหน้า
                    </p>
                    <button id="btn-close-pending-popup" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-lg shadow-md hover:shadow-lg transition-all cursor-pointer text-center tracking-wider">
                        รับทราบ
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->check())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const popupElement = document.getElementById('pending-tasks-popup');
                const closePopupButton = document.getElementById('btn-close-pending-popup');
                const badgeElement = document.getElementById('sidebar-alert-badge'); // ดึงตัวเลขแจ้งเตือน
                
                // ดึงค่าจำนวนงานค้างปัจจุบันจากฐานข้อมูล
                const currentPendingCount = parseInt("{{ $sidebarPendingCount }}") || 0;
                const userId = "{{ auth()->id() }}";
                
                // เปลี่ยน Key ใหม่เพื่อให้ระบบเริ่มนับตรรกะใหม่
                const storageKey = 'crm_pending_sidebar_v2_' + userId;

                // ดึงจำนวนงานที่เคยกดรับทราบไปแล้ว
                const acknowledgedCountStr = localStorage.getItem(storageKey);
                const acknowledgedCount = parseInt(acknowledgedCountStr);

                // ตรรกะใหม่: ถ้ายังไม่เคยกดรับทราบ หรือจำนวนงานเปลี่ยนไปจากเดิม (เช่น มีงานเพิ่มใหม่) ให้แจ้งเตือนอีกครั้ง
                let needsAlert = (acknowledgedCountStr === null || acknowledgedCount !== currentPendingCount);

                // หากผู้ใช้กดเข้ามาดูที่หน้า Deals ให้ถือว่ารับทราบ "จำนวนล่าสุด" โดยอัตโนมัติ จะได้ไม่กวนใจ
                @if(Request::is('deals*'))
                    localStorage.setItem(storageKey, currentPendingCount);
                    needsAlert = false; // ปิดการแสดงผลแจ้งเตือนเพราะเข้ามาดูหน้างานแล้ว
                @endif

                // แสดงหรือซ่อน Pop-up มุมล่างขวา
                if (popupElement) {
                    if (needsAlert && currentPendingCount > 0) {
                        popupElement.style.display = 'block';
                    } else {
                        popupElement.style.display = 'none';
                    }
                }

                // ซ่อนหรือแสดงตัวเลขแจ้งเตือนที่ Sidebar
                if (badgeElement) {
                    if (!needsAlert) {
                        badgeElement.style.display = 'none';
                    } else {
                        badgeElement.style.display = 'inline-flex';
                    }
                }

                // เมื่อผู้ใช้งานคลิกปุ่มยืนยัน "รับทราบ" ที่มุมขวาล่าง
                if (closePopupButton) {
                    closePopupButton.addEventListener('click', function() {
                        // บันทึก "จำนวนล่าสุด" ลงไป เพื่อให้มันจำว่ารับทราบที่ยอดเท่านี้นะ
                        localStorage.setItem(storageKey, currentPendingCount);
                        
                        // ปิดกล่อง Pop-up
                        if (popupElement) {
                            popupElement.style.opacity = '0';
                            popupElement.style.transform = 'translateY(20px)';
                            setTimeout(() => { popupElement.style.display = 'none'; }, 300);
                        }
                        
                        // ปิด Badge ด้วยเมื่อกดรับทราบ
                        if (badgeElement) {
                            badgeElement.style.display = 'none';
                        }
                    });
                }
            });
        </script>
    @endif

</body>
</html>