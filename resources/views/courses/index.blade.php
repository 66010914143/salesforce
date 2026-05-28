@extends('layouts.app')

@section('content')
<div class="space-y-6 p-6">
    <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-gray-800">ระบบจัดการคอร์สเรียน (Master Data)</h2>
            <p class="text-sm text-gray-500">จัดการรายชื่อหลักสูตรทั้งหมดสำหรับนำไปใช้ในระบบบันทึกงานขาย</p>
        </div>
        <a href="{{ route('courses.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-150 shadow-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            เพิ่มคอร์สเรียนใหม่
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-600 text-sm font-semibold uppercase tracking-wider">
                        <th class="py-4 px-6" style="width: 10%;">ID</th>
                        <th class="py-4 px-6" style="width: 25%;">ชื่อคอร์ส/หลักสูตร</th>
                        <th class="py-4 px-6" style="width: 25%;">รายละเอียด</th>
                        <th class="py-4 px-6" style="width: 15%;">ราคาตั้งต้น (บาท)</th>
                        <th class="py-4 px-6" style="width: 13%;">สถานะการใช้งาน</th>
                        <th class="py-4 px-6 text-center" style="width: 12%;">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm divide-y divide-gray-100">
                    @forelse($courses as $course)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="py-4 px-6 font-medium text-gray-500">{{ $course->id }}</td>
                            <td class="py-4 px-6 font-semibold text-gray-900">{{ $course->course_name }}</td>
                            <td class="py-4 px-6 text-gray-500">{{ $course->description ?? 'ไม่มีคำอธิบาย' }}</td>
                            <td class="py-4 px-6 font-medium">
                                {{ $course->default_price ? number_format($course->default_price, 2) : '-' }}
                            </td>
                            <td class="py-4 px-6">
                                @if($course->is_active ?? true)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        กำลังเปิดขาย
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ปิดใช้งาน
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('courses.edit', $course->id) }}" class="inline-flex items-center bg-amber-50 hover:bg-amber-100 text-amber-700 font-medium px-2.5 py-1.5 rounded-lg text-xs transition duration-150 border border-amber-200">
                                        แก้ไข
                                    </a>
                                    
                                    <form action="{{ route('courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบคอร์สเรียนนี้? คอร์สที่ถูกใช้งานในดีลงานขายไปแล้วจะไม่สามารถลบได้');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center bg-red-50 hover:bg-red-100 text-red-600 font-medium px-2.5 py-1.5 rounded-lg text-xs transition duration-150 border border-red-200">
                                            ลบ
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-6 text-center text-gray-400">ยังไม่มีข้อมูลคอร์สเรียนในระบบ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection