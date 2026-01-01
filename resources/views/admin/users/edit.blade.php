@extends('layouts.admin')

@section('title', 'จัดการบัญชีผู้ใช้')
@section('header', 'ข้อมูลพนักงาน: ' . $user->name)

@section('content')
<div class="max-w-2xl mx-auto">
    
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center gap-1 transition w-fit">
            <i class="fa-solid fa-arrow-left"></i> กลับหน้ารายการ
        </a>
    </div>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            
            {{-- Header --}}
            <div class="bg-gray-50/50 px-8 py-6 border-b border-gray-100 flex items-center gap-5">
                <div class="relative">
                    <div class="w-16 h-16 rounded-full border-4 border-white shadow-sm overflow-hidden bg-white">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=1B4D3E&color=fff" class="w-full h-full object-cover">
                    </div>
                    <div class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">สถานะ: ปกติ</p>
                </div>
            </div>

            <div class="p-8 space-y-8">
                
                {{-- 🔒 1. ข้อมูลส่วนตัว (ล็อคแก้ไข) --}}
                <div class="space-y-5 opacity-75">
                    <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 pb-2">
                        <i class="fa-solid fa-lock"></i> ข้อมูลระบุตัวตน (แก้ไขไม่ได้)
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">ชื่อ-นามสกุล</label>
                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                {{-- ใช้ readonly และ bg-gray-100 เพื่อบอกว่าห้ามแก้ --}}
                                <input type="text" name="name" value="{{ $user->name }}" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none" readonly>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">Username</label>
                            <div class="relative">
                                <i class="fa-solid fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="username" value="{{ $user->username }}" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none font-mono" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🔑 2. ส่วนรีเซ็ตรหัสผ่าน (แก้ไขได้) --}}
                <div class="bg-yellow-50/50 rounded-2xl p-6 border border-yellow-100 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-yellow-400"></div>
                    
                    <h4 class="text-sm font-bold text-yellow-800 uppercase tracking-wider flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-key"></i> ตั้งรหัสผ่านใหม่ (Reset Credentials)
                    </h4>
                    
                    <p class="text-xs text-yellow-700 mb-4 bg-white/50 p-2 rounded border border-yellow-200 inline-block">
                        <i class="fa-solid fa-circle-info mr-1"></i> กรอกเฉพาะเมื่อพนักงานลืมรหัสผ่าน หรือต้องการเปลี่ยนใหม่
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Password Reset --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">รหัสผ่านใหม่ (Password)</label>
                            <div class="relative group">
                                <input type="password" name="password" id="password" class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-yellow-300 focus:border-yellow-500 focus:ring-4 focus:ring-yellow-500/20 outline-none bg-white transition placeholder-gray-300" placeholder="ตั้งรหัสใหม่...">
                                <i class="fa-regular fa-eye absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer hover:text-yellow-600 transition" onclick="togglePassword('password', this)"></i>
                            </div>
                        </div>

                        {{-- PIN Reset --}}
                        <div>
                            <label class="block text-sm font-bold text-green-700 mb-1.5 flex items-center gap-1">
                                <i class="fa-solid fa-mobile-screen-button"></i> รหัส PIN ใหม่ (4 หลัก)
                            </label>
                            <div class="relative group">
                                <input type="password" name="pin" id="pin" maxlength="4" class="w-full px-4 pr-10 py-2.5 rounded-xl border border-green-300 bg-white text-green-800 text-center tracking-[0.5em] font-bold text-lg focus:border-green-500 focus:ring-4 focus:ring-green-500/20 outline-none placeholder-green-800/20" placeholder="••••">
                                <i class="fa-regular fa-eye absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer hover:text-green-600 transition" onclick="togglePassword('pin', this)"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footer Action --}}
            <div class="bg-gray-50 px-8 py-5 border-t border-gray-100 flex items-center justify-between">
                {{-- ปุ่มลบ (ซ้าย) --}}
                @if(auth()->id() !== $user->id)
                    <button type="button" onclick="confirmDelete()" class="text-red-500 hover:text-red-700 text-sm font-medium flex items-center gap-1 px-3 py-2 rounded-lg hover:bg-red-50 transition">
                        <i class="fa-solid fa-trash-can"></i> ลบบัญชี
                    </button>
                @else
                    <div></div>
                @endif

                {{-- ปุ่มบันทึก (ขวา) --}}
                <div class="flex gap-3">
                    <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-200 transition">
                        ยกเลิก
                    </a>
                    <button type="submit" class="bg-agri-primary text-white px-8 py-2.5 rounded-xl shadow-lg shadow-agri-primary/30 hover:bg-agri-hover hover:-translate-y-0.5 transition font-bold flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Hidden Delete Form --}}
    <form id="delete-form" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="hidden">
        @csrf @method('DELETE')
    </form>

</div>

<script>
    function togglePassword(inputId, icon) {
        const input = document.getElementById(inputId);
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

    function confirmDelete() {
        if(confirm('⚠️ ยืนยันที่จะลบบัญชีนี้ออกจากระบบ?')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endsection