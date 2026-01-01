@extends('layouts.admin')

@section('title', 'เพิ่มพนักงาน')
@section('header', 'ลงทะเบียนพนักงานใหม่')

@section('content')
<div class="max-w-3xl mx-auto">
    
    {{-- ปุ่มย้อนกลับ --}}
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center gap-1 transition w-fit">
            <i class="fa-solid fa-arrow-left"></i> กลับหน้ารายการ
        </a>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            
            {{-- Header --}}
            <div class="bg-gray-50/50 px-8 py-6 border-b border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-agri-primary text-white flex items-center justify-center text-xl shadow-md shadow-agri-primary/20">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">สร้างบัญชีผู้ใช้งาน</h3>
                    <p class="text-sm text-gray-500">กรอกข้อมูลเพื่อสร้างบัญชีสำหรับพนักงาน (Staff)</p>
                </div>
            </div>

            <div class="p-8 space-y-8">
                
                {{-- 1. ข้อมูลทั่วไป --}}
                <div class="space-y-5">
                    <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-regular fa-id-card"></i> ข้อมูลส่วนตัว
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="name" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-agri-primary/20 focus:border-agri-primary outline-none transition placeholder-gray-300" placeholder="เช่น สมชาย ใจดี" required value="{{ old('name') }}">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">Username (สำหรับเข้าสู่ระบบ) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="fa-solid fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="username" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-agri-primary/20 focus:border-agri-primary outline-none transition font-mono text-sm placeholder-gray-300" placeholder="เช่น somchai.j" required value="{{ old('username') }}">
                            </div>
                            <p class="text-xs text-gray-400 mt-1 ml-1">ใช้อักษรภาษาอังกฤษและตัวเลขเท่านั้น</p>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100">

                {{-- 2. ความปลอดภัย --}}
                <div class="space-y-5">
                    <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved"></i> ความปลอดภัย
                    </h4>

                    <div class="bg-blue-50/50 rounded-xl p-6 border border-blue-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Password --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">รหัสผ่าน (Password) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="password" id="password" class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none bg-white transition" placeholder="••••••••" required>
                                <i class="fa-regular fa-eye absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer hover:text-blue-500 transition" onclick="togglePassword('password')"></i>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">ขั้นต่ำ 6 ตัวอักษร</p>
                        </div>

                        {{-- PIN --}}
                        <div>
                            <label class="block text-sm font-bold text-green-700 mb-1.5 flex items-center gap-1">
                                <i class="fa-solid fa-mobile-screen-button"></i> รหัส PIN (4 หลัก)
                            </label>
                            <div class="relative">
                                <input type="text" name="pin" maxlength="4" class="w-full px-4 py-2.5 rounded-xl border border-green-200 bg-green-50/50 text-green-800 text-center tracking-[0.5em] font-bold text-lg focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none placeholder-green-800/20" placeholder="0000">
                            </div>
                            <p class="text-[10px] text-green-600 mt-1 text-center">* สำหรับพนักงานขับรถใช้ Login หน้าจอสัมผัส</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footer Action --}}
            <div class="bg-gray-50 px-8 py-5 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-200 transition">
                    ยกเลิก
                </a>
                <button type="submit" class="bg-agri-primary text-white px-8 py-2.5 rounded-xl shadow-lg shadow-agri-primary/30 hover:bg-agri-hover hover:-translate-y-0.5 transition font-bold flex items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i> บันทึกข้อมูล
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        const icon = input.nextElementSibling;
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection