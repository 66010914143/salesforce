@extends('layouts.app')

@section('content')
<div class="space-y-6 p-6">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        
        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">แก้ไขคอร์สเรียน</h2>
                <p class="text-sm text-gray-500">อัปเดตข้อมูลรายละเอียดของคอร์สเรียน (ID: {{ $course->id }})</p>
            </div>
            <a href="{{ route('courses.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">พบข้อผิดพลาด:</h3>
                        <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('courses.update', $course->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label for="course_name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อคอร์ส/หลักสูตร <span class="text-red-500">*</span></label>
                    <input type="text" name="course_name" id="course_name" value="{{ old('course_name', $course->course_name) }}" required 
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">รายละเอียด</label>
                    <textarea name="description" id="description" rows="3" 
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border">{{ old('description', $course->description) }}</textarea>
                </div>

                <div>
                    <label for="default_price" class="block text-sm font-medium text-gray-700 mb-1">ราคาตั้งต้น (บาท) <span class="text-gray-400 text-xs font-normal">(เว้นว่างได้)</span></label>
                    <input type="number" step="0.01" min="0" name="default_price" id="default_price" value="{{ old('default_price', $course->default_price) }}" 
                        class="w-full md:w-1/2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border">
                </div>

                <div class="pt-2">
                    <label class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        
                        <input type="checkbox" name="is_active" value="1" 
                            {{ old('is_active', $course->is_active ?? true) ? 'checked' : '' }} 
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 h-5 w-5">
                        <span class="ml-2 text-sm font-medium text-gray-700">เปิดใช้งานคอร์สนี้ (อนุญาตให้นำไปใช้สร้างดีลงานขายได้)</span>
                    </label>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-50">
                <a href="{{ route('courses.index') }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium transition duration-150">
                    ยกเลิก
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition duration-150 shadow-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
</div>
@endsection