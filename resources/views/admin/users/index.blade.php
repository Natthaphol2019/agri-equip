@extends('layouts.admin') {{-- ✅ เปลี่ยนจาก layouts.app เป็น layouts.admin --}}
@section('title', 'จัดการพนักงาน')
@section('header', 'พนักงานทั้งหมด')

@section('content')
<div class="max-w-5xl mx-auto">
    
    {{-- Toolbar: ค้นหา & ปุ่มเพิ่ม --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
        <form action="{{ route('admin.users.index') }}" method="GET" class="relative w-full sm:max-w-xs">
            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อ, username..." 
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border-none shadow-sm bg-white focus:ring-2 focus:ring-agri-primary/20 outline-none text-sm transition">
        </form>
        
        <a href="{{ route('admin.users.create') }}" class="w-full sm:w-auto bg-agri-primary text-white px-5 py-2.5 rounded-xl text-sm font-medium shadow-md hover:bg-agri-hover transition flex items-center justify-center gap-2 active:scale-95">
            <i class="fa-solid fa-user-plus"></i> 
            <span>เพิ่มพนักงาน</span>
        </a>
    </div>

    {{-- Staff List --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            {{-- Table Head (Desktop) --}}
            <thead class="bg-gray-50/50 border-b border-gray-100 text-gray-500 text-xs uppercase tracking-wider hidden md:table-header-group">
                <tr>
                    <th class="px-6 py-4 font-semibold">พนักงาน</th>
                    <th class="px-6 py-4 font-semibold">Username/PIN</th>
                    <th class="px-6 py-4 font-semibold">สถานะ</th>
                    <th class="px-6 py-4 font-semibold text-right">จัดการ</th>
                </tr>
            </thead>
            
            {{-- Table Body --}}
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="group hover:bg-gray-50/50 transition flex flex-col md:table-row p-4 md:p-0 relative">
                    <td class="px-2 md:px-6 py-2 md:py-4 flex items-center gap-4">
                        <div class="relative">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&color=fff" class="w-12 h-12 rounded-full shadow-sm">
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-base">{{ $user->name }}</p>
                            <p class="text-xs text-gray-400 md:hidden">{{ $user->email ?? 'ไม่มีอีเมล' }}</p>
                        </div>
                    </td>
                    <td class="px-2 md:px-6 py-1 md:py-4 text-sm text-gray-600 flex justify-between md:table-cell">
                        <span class="md:hidden text-gray-400 font-medium">ข้อมูล:</span>
                        <div>
                            <p class="font-medium text-agri-primary font-mono">{{ $user->username }}</p>
                            <p class="text-xs text-gray-400">
                                @if($user->pin) <i class="fa-solid fa-lock text-[10px] mr-1"></i> มี PIN แล้ว @else - @endif
                            </p>
                        </div>
                    </td>
                    <td class="px-2 md:px-6 py-1 md:py-4 flex justify-between md:table-cell">
                        <span class="md:hidden text-gray-400 font-medium">สถานะ:</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> พร้อมทำงาน
                        </span>
                    </td>
                    <td class="px-2 md:px-6 py-2 md:py-4 text-right md:table-cell mt-2 md:mt-0 flex justify-end gap-2">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('ยืนยันลบพนักงาน {{ $user->name }}?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">ไม่พบข้อมูลพนักงาน</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection