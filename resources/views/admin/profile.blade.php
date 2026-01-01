@extends('layouts.admin')
@section('title', 'โปรไฟล์ของฉัน')
@section('header', 'บัญชีผู้ใช้')

@section('content')
<div class="max-w-5xl mx-auto">
    
    {{-- Alert Success (เพิ่ม Animation Slide Down) --}}
    @if(session('success'))
        <div id="alert-box" class="bg-green-50 text-green-700 px-4 py-3 rounded-xl flex items-center justify-between border border-green-100 shadow-sm mb-6 animate-bounce-short">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
            </div>
            <button onclick="this.parentElement.style.display='none'" class="text-green-500 hover:text-green-700 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- 🟢 COLUMN 1: Profile Card & Avatar --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative group">
                    {{-- Background Pattern --}}
                    <div class="h-32 bg-gradient-to-r from-agri-primary to-green-800 relative">
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                    </div>
                    
                    <div class="px-6 pb-6 text-center relative">
                        {{-- Avatar + Upload --}}
                        <div class="relative mx-auto -mt-16 w-32 h-32">
                            <input type="file" name="avatar" id="avatar_upload" class="hidden" accept="image/*" onchange="previewImage(event)">
                            <label for="avatar_upload" class="block w-32 h-32 rounded-full border-[6px] border-white shadow-lg bg-white overflow-hidden relative group cursor-pointer hover:scale-105 transition-transform duration-300">
                                <img id="avatar_preview" 
                                     src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=1B4D3E&color=fff&size=128' }}" 
                                     class="w-full h-full object-cover">
                                
                                {{-- Overlay icon กล้อง --}}
                                <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition duration-300 backdrop-blur-[2px]">
                                    <i class="fa-solid fa-camera text-white text-2xl mb-1"></i>
                                    <span class="text-white text-[10px] font-light">เปลี่ยนรูป</span>
                                </div>
                            </label>
                        </div>
                        
                        <h2 class="text-xl font-bold text-gray-800 mt-4">{{ Auth::user()->name }}</h2>
                        <div class="inline-flex items-center gap-1 px-3 py-1 bg-green-50 text-agri-primary text-xs font-bold rounded-full mt-2 uppercase tracking-wide">
                            <i class="fa-solid fa-shield-halved"></i> Administrator
                        </div>
                        
                        <p class="text-gray-500 text-sm mt-4 px-4">
                            จัดการข้อมูลส่วนตัวและรหัสผ่านของคุณได้ที่นี่
                        </p>
                    </div>
                </div>
            </div>

            {{-- 🟢 COLUMN 2: Edit Form --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4 mb-6">
                        <div class="p-2 bg-gray-50 rounded-lg text-agri-primary">
                            <i class="fa-solid fa-user-pen"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">แก้ไขข้อมูลส่วนตัว</h3>
                            <p class="text-xs text-gray-500">อัปเดตข้อมูลบัญชีและรหัสผ่าน</p>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        {{-- ชื่อผู้ใช้ --}}
                        <div class="group">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 ml-1">ชื่อ-นามสกุล</label>
                            <div class="relative">
                                <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-agri-primary transition-colors"></i>
                                <input type="text" name="name" value="{{ Auth::user()->name }}" required
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-agri-primary focus:ring-4 focus:ring-agri-primary/10 outline-none transition bg-gray-50 focus:bg-white">
                            </div>
                        </div>

                        {{-- อีเมล (Read Only) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 ml-1">อีเมล <span class="text-gray-400 font-normal text-xs">(เปลี่ยนไม่ได้)</span></label>
                            <div class="relative">
                                <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="email" value="{{ Auth::user()->email }}" disabled
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed">
                            </div>
                        </div>

                        <div class="border-t border-gray-100 my-4"></div>

                        {{-- เปลี่ยนรหัสผ่าน --}}
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-lock text-gray-400"></i> เปลี่ยนรหัสผ่าน
                            </h4>
                            
                            <div class="grid md:grid-cols-2 gap-5">
                                <div class="group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5 ml-1">รหัสผ่านใหม่</label>
                                    <div class="relative">
                                        <input type="password" name="password" id="password" placeholder="••••••••" 
                                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-agri-primary focus:ring-4 focus:ring-agri-primary/10 outline-none transition">
                                        <i class="fa-regular fa-eye absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer hover:text-agri-primary" onclick="togglePassword('password')"></i>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1 ml-1">* เว้นว่างไว้หากไม่ต้องการเปลี่ยน</p>
                                </div>

                                <div class="group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5 ml-1">ยืนยันรหัสผ่าน</label>
                                    <div class="relative">
                                        <input type="password" name="password_confirmation" id="password_confirm" placeholder="••••••••" 
                                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-agri-primary focus:ring-4 focus:ring-agri-primary/10 outline-none transition">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- ปุ่ม Action --}}
                    <div class="pt-6 mt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="reset" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition">
                            คืนค่าเดิม
                        </button>
                        <button type="submit" class="bg-agri-primary text-white px-6 py-2.5 rounded-xl shadow-lg shadow-agri-primary/30 hover:bg-agri-hover hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 font-medium flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> บันทึกการเปลี่ยนแปลง
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Scripts สำหรับหน้า Profile --}}
<script>
    // Preview รูปภาพเมื่อเลือกไฟล์
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('avatar_preview');
            output.src = reader.result;
        };
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    // Toggle ดูรหัสผ่าน
    function togglePassword(id) {
        const input = document.getElementById(id);
        if (input.type === "password") {
            input.type = "text";
        } else {
            input.type = "password";
        }
    }
</script>

<style>
    @keyframes bounce-short {
        0%, 100% { transform: translateY(-25%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
        50% { transform: translateY(0); animation-timing-function: cubic-bezier(0,0,0.2,1); }
    }
    .animate-bounce-short {
        animation: bounce-short 1s 1;
    }
</style>
@endsection