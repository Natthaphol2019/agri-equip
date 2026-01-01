@extends('layouts.admin')

@section('title', 'Accept Maintenance')
@section('header', 'ยืนยันรับเรื่องซ่อม')

@section('content')
<div class="max-w-3xl mx-auto">
    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-red-600 text-white px-6 py-4 flex justify-between items-center">
            <h2 class="font-bold text-lg flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check"></i> ตรวจสอบแจ้งซ่อม
            </h2>
            <a href="{{ route('admin.maintenance.index') }}" class="text-red-100 hover:text-white text-sm font-medium">
                <i class="fa-solid fa-arrow-left mr-1"></i> ย้อนกลับ
            </a>
        </div>

        <div class="p-6 md:p-8 space-y-8">
            
            {{-- ส่วนที่ 1: ข้อมูลจาก Staff --}}
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-red-400"></div>
                
                <h3 class="text-gray-800 font-bold mb-4 flex items-center gap-2 pb-2 border-b border-gray-200">
                    <i class="fa-solid fa-user-tag text-gray-400"></i> ข้อมูลจากพนักงาน
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">เครื่องจักร</p>
                        <p class="text-lg font-bold text-gray-800">{{ $log->equipment->name }}</p>
                        <span class="inline-block bg-white border border-gray-200 px-2 py-0.5 rounded text-xs text-gray-500 mt-1">
                            {{ $log->equipment->equipment_code }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">วันที่แจ้ง</p>
                        <p class="text-gray-800 font-medium">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">รายละเอียดอาการ</p>
                        <div class="bg-white p-3 rounded-lg border border-red-100 text-red-600 font-medium">
                            "{{ $log->description }}"
                        </div>
                    </div>
                    @if($log->image_url)
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">รูปภาพประกอบ</p>
                        <a href="{{ asset('storage/'.$log->image_url) }}" target="_blank" class="block w-fit group relative overflow-hidden rounded-lg shadow-sm border border-gray-200">
                            <img src="{{ asset('storage/'.$log->image_url) }}" class="max-h-64 object-cover group-hover:scale-105 transition duration-500">
                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                <i class="fa-solid fa-magnifying-glass-plus text-white text-2xl"></i>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ส่วนที่ 2: ฟอร์ม Admin --}}
            <div>
                <h3 class="text-gray-800 font-bold mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-screwdriver-wrench text-agri-primary"></i> ส่วนดำเนินการ (Admin)
                </h3>
                
                <form action="{{ route('admin.maintenance.accept_submit', $log->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">บันทึกเพิ่มเติม / มอบหมายงาน *</label>
                        <textarea name="admin_note" rows="3" class="w-full border-gray-300 rounded-xl shadow-sm focus:border-agri-primary focus:ring focus:ring-agri-primary/20 transition p-3" required placeholder="ระบุรายละเอียดการรับเรื่อง เช่น ส่งร้านช่างดำ, รออะไหล่, หรือมอบหมายให้ช่างในบริษัท..."></textarea>
                        <p class="text-xs text-gray-400 mt-1 ml-1">ข้อความนี้จะถูกบันทึกในระบบเพื่อให้ติดตามสถานะได้</p>
                    </div>

                    <button type="submit" class="w-full bg-red-600 text-white font-bold py-3.5 rounded-xl shadow-lg hover:bg-red-700 hover:shadow-red-200 hover:-translate-y-1 transition duration-200 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check-circle"></i> ยืนยันรับเรื่องและเริ่มซ่อมทันที
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection