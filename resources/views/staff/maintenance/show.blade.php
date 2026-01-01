@extends('layouts.staff')

@section('title', 'รายละเอียดแจ้งซ่อม')
@section('header', 'ติดตามสถานะงานซ่อม')

@section('content')
    <div class="max-w-2xl mx-auto">
        
        <a href="{{ route('staff.maintenance.index') }}" class="inline-flex items-center text-gray-500 hover:text-agri-primary text-sm mb-4 transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> กลับหน้ารายการ
        </a>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            
            {{-- Header Status Bar --}}
            @php
                $statusColor = match($log->status) {
                    'pending' => 'bg-yellow-500',
                    'in_progress' => 'bg-blue-600',
                    'completed' => 'bg-green-600',
                    default => 'bg-gray-500'
                };
                $statusIcon = match($log->status) {
                    'pending' => 'fa-clock',
                    'in_progress' => 'fa-screwdriver-wrench',
                    'completed' => 'fa-check-circle',
                    default => 'fa-question-circle'
                };
                $statusText = match($log->status) {
                    'pending' => 'รอแอดมินรับเรื่อง',
                    'in_progress' => 'กำลังดำเนินการซ่อม',
                    'completed' => 'ซ่อมเสร็จสิ้น',
                    default => 'ไม่ทราบสถานะ'
                };
            @endphp

            <div class="{{ $statusColor }} px-6 py-4 flex justify-between items-center text-white">
                <div class="flex items-center gap-3">
                    <i class="fa-solid {{ $statusIcon }} text-xl opacity-80"></i>
                    <span class="font-bold text-lg">{{ $statusText }}</span>
                </div>
                <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-mono tracking-wider">#{{ str_pad($log->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>

            <div class="p-6">
                {{-- Info Grid --}}
                <div class="grid grid-cols-2 gap-6 mb-8 pb-8 border-b border-gray-100">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">เครื่องจักร</p>
                        <p class="text-lg font-bold text-gray-800">{{ $log->equipment->name }}</p>
                        <p class="text-xs text-gray-500">{{ $log->equipment->equipment_code }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">วันที่แจ้ง</p>
                        <p class="text-gray-800">{{ $log->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $log->created_at->format('H:i') }} น.</p>
                    </div>
                </div>

                {{-- Description --}}
                <div class="mb-8">
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-align-left text-agri-primary"></i> อาการที่แจ้ง
                    </h4>
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-gray-700 leading-relaxed">
                        {{ $log->description }}
                    </div>
                </div>

                {{-- Image Evidence --}}
                @if($log->image_url)
                    <div class="mb-8">
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-image text-agri-primary"></i> รูปภาพประกอบ
                        </h4>
                        <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                            <img src="{{ asset('storage/'.$log->image_url) }}" class="w-full h-auto object-cover max-h-[400px]" alt="Maintenance Evidence">
                        </div>
                    </div>
                @endif

                {{-- Admin Response Section (Only if processed) --}}
                @if($log->status != 'pending')
                    <div class="mt-8 bg-green-50 rounded-xl p-5 border border-green-100">
                        <div class="flex items-start gap-3">
                            <div class="bg-green-100 p-2 rounded-full text-green-600">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-green-800 mb-1">การตอบกลับจากระบบ</h4>
                                <p class="text-sm text-green-700 mb-2">
                                    สถานะล่าสุด: 
                                    <span class="font-bold underline">
                                        {{ $log->status == 'in_progress' ? 'กำลังซ่อมแซม' : 'ซ่อมเสร็จสิ้นแล้ว' }}
                                    </span>
                                </p>
                                <p class="text-xs text-green-600 opacity-70">
                                    อัปเดตเมื่อ: {{ $log->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection