@extends('layouts.admin')
@section('title', 'เมนูทั้งหมด')
@section('header', 'เมนูการจัดการ (All Apps)')

@section('content')
<div class="max-w-2xl mx-auto pb-8">
    
    {{-- Section 1: งานหลัก (Core Operations) --}}
    <div class="mb-8">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 px-2 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-agri-primary"></span> การทำงานหลัก
        </h3>
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-4">
            
            {{-- งานบริการ --}}
            <a href="{{ route('admin.jobs.index') }}" class="flex flex-col items-center group cursor-pointer">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shadow-sm border border-blue-100 group-hover:scale-110 group-hover:shadow-md transition duration-300">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2 group-hover:text-blue-600">งานบริการ</span>
            </a>

            {{-- แจ้งซ่อม --}}
            <a href="{{ route('admin.maintenance.index') }}" class="flex flex-col items-center group cursor-pointer">
                <div class="w-16 h-16 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-2xl shadow-sm border border-orange-100 group-hover:scale-110 group-hover:shadow-md transition duration-300">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2 group-hover:text-orange-500">แจ้งซ่อม</span>
            </a>

            {{-- เครื่องจักร --}}
            <a href="{{ route('admin.equipments.index') }}" class="flex flex-col items-center group cursor-pointer">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl shadow-sm border border-indigo-100 group-hover:scale-110 group-hover:shadow-md transition duration-300">
                    <i class="fa-solid fa-tractor"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2 group-hover:text-indigo-600">เครื่องจักร</span>
            </a>

            {{-- รายงาน --}}
            <a href="{{ route('admin.reports.index') }}" class="flex flex-col items-center group cursor-pointer">
                <div class="w-16 h-16 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl shadow-sm border border-purple-100 group-hover:scale-110 group-hover:shadow-md transition duration-300">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2 group-hover:text-purple-600">รายงาน</span>
            </a>

        </div>
    </div>

    {{-- Section 2: การจัดการคน (People & Staff) --}}
    <div class="mb-8">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 px-2 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> บุคลากร
        </h3>
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-4">
            
            {{-- ข้อมูลลูกค้า --}}
            <a href="{{ route('admin.customers.index') }}" class="flex flex-col items-center group cursor-pointer">
                <div class="w-16 h-16 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-2xl shadow-sm border border-teal-100 group-hover:scale-110 group-hover:shadow-md transition duration-300">
                    <i class="fa-solid fa-users"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2 group-hover:text-teal-600">ลูกค้า</span>
            </a>

            {{-- ✅ จัดการพนักงาน (Staff) --}}
            <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center group cursor-pointer">
                <div class="w-16 h-16 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center text-2xl shadow-sm border border-green-100 group-hover:scale-110 group-hover:shadow-md transition duration-300">
                    <i class="fa-solid fa-id-card-clip"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2 group-hover:text-green-600">พนักงาน</span>
            </a>

        </div>
    </div>

    {{-- Section 3: ระบบ (System) --}}
    <div class="mb-8">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 px-2 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> ตั้งค่าระบบ
        </h3>
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-4">
            
            {{-- โปรไฟล์ --}}
            <a href="{{ route('admin.profile') }}" class="flex flex-col items-center group cursor-pointer">
                <div class="w-16 h-16 rounded-2xl bg-gray-50 text-gray-600 flex items-center justify-center text-2xl shadow-sm border border-gray-100 group-hover:scale-110 group-hover:shadow-md transition duration-300">
                    <i class="fa-solid fa-user-circle"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2 group-hover:text-gray-800">โปรไฟล์</span>
            </a>

            {{-- ตั้งค่า --}}
            <a href="{{ route('admin.settings.index') }}" class="flex flex-col items-center group cursor-pointer">
                <div class="w-16 h-16 rounded-2xl bg-gray-50 text-gray-600 flex items-center justify-center text-2xl shadow-sm border border-gray-100 group-hover:scale-110 group-hover:shadow-md transition duration-300">
                    <i class="fa-solid fa-gear"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2 group-hover:text-gray-800">ตั้งค่า</span>
            </a>

            {{-- ออกจากระบบ --}}
            <button onclick="confirmLogout()" class="flex flex-col items-center group cursor-pointer w-full">
                <div class="w-16 h-16 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center text-2xl shadow-sm border border-red-100 group-hover:bg-red-100 group-hover:scale-110 group-hover:shadow-md transition duration-300">
                    <i class="fa-solid fa-power-off"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2 group-hover:text-red-500">ออกระบบ</span>
            </button>

        </div>
    </div>

</div>
@endsection