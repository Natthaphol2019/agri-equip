@extends('layouts.staff')

@section('title', 'ประวัติการแจ้งซ่อม')
@section('header', 'แจ้งซ่อมเครื่องจักร')

@section('content')
    {{-- ใช้ x-data ควบคุม State การกรอง --}}
    <div class="max-w-4xl mx-auto" x-data="{ currentFilter: 'all' }">
        
        {{-- Header & Action --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-agri-primary"></i> ประวัติการแจ้งซ่อม
                </h2>
                <p class="text-sm text-gray-500">รายการที่คุณเคยแจ้งปัญหาไว้ทั้งหมด</p>
            </div>
            <a href="{{ route('staff.maintenance.create') }}" 
               class="w-full md:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm flex items-center justify-center gap-2 transition transform active:scale-95 hover:shadow-lg">
                <i class="fa-solid fa-plus"></i> แจ้งซ่อมใหม่
            </a>
        </div>

        {{-- Filter Tabs (ปุ่มกรอง) --}}
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
            {{-- ปุ่มทั้งหมด --}}
            <button @click="currentFilter = 'all'" 
                    class="px-4 py-2 rounded-full text-sm font-bold shadow-sm transition-all duration-300 whitespace-nowrap transform active:scale-95 border"
                    :class="currentFilter === 'all' ? 'bg-agri-primary text-white border-agri-primary scale-105' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'">
                <i class="fa-solid fa-layer-group me-1"></i> ทั้งหมด
            </button>

            {{-- ปุ่มรอรับเรื่อง --}}
            <button @click="currentFilter = 'pending'" 
                    class="px-4 py-2 rounded-full text-sm font-bold shadow-sm transition-all duration-300 whitespace-nowrap transform active:scale-95 border"
                    :class="currentFilter === 'pending' ? 'bg-yellow-400 text-yellow-900 border-yellow-400 scale-105' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'">
                <i class="fa-regular fa-clock me-1"></i> รอรับเรื่อง
            </button>

            {{-- ปุ่มกำลังซ่อม --}}
            <button @click="currentFilter = 'in_progress'" 
                    class="px-4 py-2 rounded-full text-sm font-bold shadow-sm transition-all duration-300 whitespace-nowrap transform active:scale-95 border"
                    :class="currentFilter === 'in_progress' ? 'bg-blue-500 text-white border-blue-500 scale-105' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'">
                <i class="fa-solid fa-tools me-1"></i> กำลังซ่อม
            </button>

            {{-- ปุ่มเสร็จสิ้น --}}
            <button @click="currentFilter = 'completed'" 
                    class="px-4 py-2 rounded-full text-sm font-bold shadow-sm transition-all duration-300 whitespace-nowrap transform active:scale-95 border"
                    :class="currentFilter === 'completed' ? 'bg-green-500 text-white border-green-500 scale-105' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'">
                <i class="fa-solid fa-check me-1"></i> เสร็จสิ้น
            </button>
        </div>

        {{-- Grid List --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($myMaintenanceLogs as $log)
                {{-- ใช้ x-show กรองตาม status --}}
                <div x-show="currentFilter === 'all' || currentFilter === '{{ $log->status }}'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group h-full flex flex-col relative">
                    
                    {{-- Status Strip --}}
                    <div class="h-1.5 w-full {{ $log->status == 'pending' ? 'bg-yellow-400' : ($log->status == 'in_progress' ? 'bg-blue-500' : 'bg-green-500') }}"></div>
                    
                    <div class="p-5 flex flex-col h-full">
                        {{-- Top Part --}}
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg group-hover:text-agri-primary transition line-clamp-1">{{ $log->equipment->name }}</h3>
                                <p class="text-xs text-gray-400 font-mono">{{ $log->equipment->equipment_code }}</p>
                            </div>
                            
                            @if($log->status == 'pending')
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1 shadow-sm whitespace-nowrap">
                                    <i class="fa-regular fa-clock"></i> รอรับเรื่อง
                                </span>
                            @elseif($log->status == 'in_progress')
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1 shadow-sm whitespace-nowrap">
                                    <i class="fa-solid fa-tools"></i> กำลังซ่อม
                                </span>
                            @else
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1 shadow-sm whitespace-nowrap">
                                    <i class="fa-solid fa-check"></i> เสร็จสิ้น
                                </span>
                            @endif
                        </div>

                        {{-- Description Box --}}
                        <div class="bg-gray-50 rounded-lg p-3 mb-4 flex-grow border border-gray-100 group-hover:border-gray-200 transition">
                            <p class="text-xs font-bold text-gray-500 mb-1 flex items-center gap-1">
                                <i class="fa-solid fa-triangle-exclamation text-red-400"></i> อาการที่แจ้ง:
                            </p>
                            <p class="text-sm text-gray-700 line-clamp-2 leading-relaxed">{{ $log->description }}</p>
                        </div>

                        {{-- Footer Part --}}
                        <div class="flex items-center justify-between mt-auto pt-3 border-t border-gray-50">
                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                <i class="fa-regular fa-calendar-alt"></i> {{ $log->created_at->format('d/m/Y H:i') }}
                            </span>
                            <a href="{{ route('staff.maintenance.show', $log->id) }}" class="text-sm font-bold text-agri-primary hover:text-agri-secondary hover:underline flex items-center gap-1 transition">
                                ดูรายละเอียด <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                {{-- กรณีไม่มีข้อมูลเลย (Server Side Empty) --}}
                <div class="col-span-1 md:col-span-2 text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100 border-dashed">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                        <i class="fa-solid fa-folder-open text-gray-300 text-3xl"></i>
                    </div>
                    <h3 class="text-gray-500 font-medium">ยังไม่เคยมีประวัติการแจ้งซ่อม</h3>
                    <p class="text-sm text-gray-400 mt-1 mb-6">เครื่องจักรทำงานได้ดีเยี่ยม!</p>
                    <a href="{{ route('staff.maintenance.create') }}" class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 shadow-sm transition hover:border-red-300 hover:text-red-500">
                        <i class="fa-solid fa-plus text-red-500"></i> แจ้งซ่อมครั้งแรก
                    </a>
                </div>
            @endforelse

            {{-- กรณีมีข้อมูล แต่กรองแล้วไม่เจอ (Client Side Empty) --}}
            <div x-show="false" {{-- ซ่อนไว้ก่อน จะใช้ JS เช็ค --}}
                 x-effect="$el.style.display = (currentFilter !== 'all' && document.querySelectorAll(`[x-show*='${currentFilter}']:not([style*='display: none'])`).length === 0) ? 'block' : 'none'"
                 class="col-span-1 md:col-span-2 text-center py-10">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                    <i class="fa-solid fa-filter text-gray-400 text-xl"></i>
                </div>
                <p class="text-gray-500">ไม่มีรายการในสถานะนี้</p>
            </div>
        </div>
    </div>
@endsection