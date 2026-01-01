@extends('layouts.staff')

@section('title', 'หน้าหลัก')
@section('header', 'ภาพรวมการทำงาน')

@section('content')
<div class="max-w-4xl mx-auto">
    
    {{-- 1. การ์ดสรุปสถานะ --}}
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-center">
            <h3 class="text-2xl font-bold text-blue-600">{{ $counts['in_progress'] }}</h3>
            <p class="text-xs text-blue-500 font-medium">กำลังทำ</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-100 text-center">
            <h3 class="text-2xl font-bold text-yellow-600">{{ $counts['scheduled'] }}</h3>
            <p class="text-xs text-yellow-500 font-medium">รอดำเนินการ</p>
        </div>
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-center">
            <h3 class="text-2xl font-bold text-green-600">{{ $counts['completed'] }}</h3>
            <p class="text-xs text-green-500 font-medium">เสร็จเดือนนี้</p>
        </div>
    </div>

    {{-- 2. เมนูลัด (Quick Actions) --}}
    <h3 class="font-bold text-gray-800 mb-3 px-1">เมนูด่วน</h3>
    <div class="grid grid-cols-2 gap-3 mb-6">
        <a href="{{ route('staff.jobs.index') }}" class="flex items-center gap-3 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="w-10 h-10 rounded-full bg-agri-primary text-white flex items-center justify-center">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-bold text-gray-800">งานทั้งหมด</p>
                <p class="text-xs text-gray-400">ดูรายการงาน</p>
            </div>
        </a>
        <a href="{{ route('staff.maintenance.create') }}" class="flex items-center gap-3 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="w-10 h-10 rounded-full bg-red-100 text-red-500 flex items-center justify-center">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-bold text-gray-800">แจ้งซ่อม</p>
                <p class="text-xs text-gray-400">แจ้งปัญหารถ</p>
            </div>
        </a>
    </div>

    {{-- 3. งานที่ต้องทำด่วน --}}
    <div class="flex justify-between items-end mb-3 px-1">
        <h3 class="font-bold text-gray-800">งานที่ต้องทำตอนนี้</h3>
        <a href="{{ route('staff.jobs.index') }}" class="text-xs text-agri-primary hover:underline">ดูทั้งหมด</a>
    </div>

    <div class="space-y-3">
        @forelse($urgentJobs as $job)
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $job->status == 'in_progress' ? 'bg-blue-500' : 'bg-yellow-400' }}"></div>
                <div class="pl-2">
                    <h4 class="font-bold text-gray-800 text-sm mb-1">{{ $job->customer->name }}</h4>
                    <p class="text-xs text-gray-500 flex items-center gap-1">
                        <i class="fa-solid fa-tractor text-gray-400"></i> {{ $job->equipment->name ?? '-' }}
                    </p>
                </div>
                <div class="text-right">
                    @if($job->status == 'in_progress')
                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-600 text-xs font-bold rounded-lg mb-1">กำลังทำ</span>
                    @else
                        <div class="text-xs font-bold text-agri-primary">{{ \Carbon\Carbon::parse($job->scheduled_start)->format('H:i') }} น.</div>
                        <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($job->scheduled_start)->format('d/m') }}</div>
                    @endif
                </div>
                <a href="{{ route('staff.jobs.show', $job->id) }}" class="absolute inset-0"></a>
            </div>
        @empty
            <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                <p class="text-gray-400 text-sm">ไม่มีงานด่วนในขณะนี้</p>
            </div>
        @endforelse
    </div>

</div>
@endsection