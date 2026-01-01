@extends('layouts.admin')

@section('title', 'แก้ไขงาน #' . $job->job_number)
@section('header', 'แก้ไขรายละเอียดงาน')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.jobs.index') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center gap-1 transition">
            <i class="fa-solid fa-arrow-left"></i> กลับหน้ารายการ
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Header สีส้ม (สื่อถึงการแก้ไข) --}}
        <div class="bg-orange-50 px-6 py-4 border-b border-orange-100 flex justify-between items-center">
            <h3 class="font-bold text-lg text-orange-800 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square"></i> แก้ไขงาน #{{ $job->job_number }}
            </h3>
            <span class="px-3 py-1 bg-white text-orange-600 rounded-full text-xs font-bold border border-orange-200 shadow-sm">
                {{ $job->status }}
            </span>
        </div>

        <div class="p-6">
            {{-- ⚠️ Warning Alert --}}
            <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-yellow-500 mt-1"></i>
                <div>
                    <h4 class="font-bold text-yellow-800 text-sm">ข้อควรระวัง</h4>
                    <p class="text-sm text-yellow-700 mt-1">
                        อนุญาตให้เปลี่ยนเฉพาะ <strong>"พนักงานขับรถ"</strong> เท่านั้น <br>
                        หากต้องการเปลี่ยน "วัน-เวลา" หรือ "เครื่องจักร" กรุณา 
                        <button type="button" onclick="document.getElementById('cancelForm').submit();" class="underline font-bold hover:text-yellow-900">ยกเลิกงานนี้</button> 
                        แล้วสร้างใหม่
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.jobs.update', $job->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Read Only Fields (สีเทา) --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">ลูกค้า</label>
                            <div class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl border border-gray-200 flex items-center gap-2">
                                <i class="fa-solid fa-user text-gray-400"></i>
                                {{ $job->customer->name }} ({{ $job->customer->phone }})
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">เครื่องจักร</label>
                            <div class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl border border-gray-200 flex items-center gap-2">
                                <i class="fa-solid fa-tractor text-gray-400"></i>
                                {{ $job->equipment->name }}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">เริ่มงาน</label>
                            <div class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl border border-gray-200">
                                {{ \Carbon\Carbon::parse($job->scheduled_start)->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">สิ้นสุด</label>
                            <div class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl border border-gray-200">
                                {{ \Carbon\Carbon::parse($job->scheduled_end)->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 my-6">

                {{-- Editable Field (พนักงาน) --}}
                <div class="mb-8">
                    <label class="block text-sm font-bold text-gray-800 mb-2">
                        พนักงานขับรถ (Assigned Staff) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-agri-primary"></i>
                        <select name="assigned_staff_id" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-agri-primary/20 focus:border-agri-primary outline-none bg-white text-gray-800 font-medium shadow-sm transition">
                            <option value="">-- เลือกพนักงาน --</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ $job->assigned_staff_id == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 ml-1">เลือกพนักงานคนใหม่ที่ต้องการมอบหมายให้รับผิดชอบงานนี้</p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    {{-- ปุ่มยกเลิกงาน (ซ้าย) --}}
                    <button type="button" onclick="if(confirm('ยืนยันที่จะยกเลิกงานนี้?')) document.getElementById('cancelForm').submit();" class="text-red-500 hover:text-red-700 text-sm font-medium px-4 py-2 hover:bg-red-50 rounded-lg transition">
                        <i class="fa-solid fa-trash-can mr-1"></i> ยกเลิกงานนี้
                    </button>

                    {{-- ปุ่มบันทึก (ขวา) --}}
                    <div class="flex gap-3">
                        <a href="{{ route('admin.jobs.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-500 hover:bg-gray-100 transition">
                            ย้อนกลับ
                        </a>
                        <button type="submit" class="bg-agri-primary text-white px-6 py-2.5 rounded-xl shadow-lg shadow-agri-primary/30 hover:bg-agri-hover hover:-translate-y-0.5 transition font-bold flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> บันทึกการเปลี่ยนแปลง
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    {{-- Hidden Cancel Form --}}
    <form id="cancelForm" action="{{ route('admin.jobs.cancel', $job->id) }}" method="POST" class="hidden">
        @csrf
    </form>

</div>
@endsection