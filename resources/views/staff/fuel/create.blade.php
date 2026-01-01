@extends('layouts.staff')

@section('title', 'เติมน้ำมัน')
@section('header', 'บันทึกเติมน้ำมัน')

@section('content')
<div class="max-w-lg mx-auto pb-20">
    
    <div class="mb-4">
        <a href="{{ route('staff.jobs.index') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 text-sm font-bold">
            <i class="fa-solid fa-arrow-left"></i> กลับหน้างาน
        </a>
    </div>

    <form action="{{ route('staff.fuel.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            
            <div class="bg-orange-50 px-6 py-4 border-b border-orange-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-gas-pump"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">ข้อมูลการเติมน้ำมัน</h3>
                    <p class="text-xs text-orange-600">กรุณากรอกข้อมูลและถ่ายรูปสลิป</p>
                </div>
            </div>

            <div class="p-6 space-y-5">
                
                {{-- 1. เลือกรถ --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">เครื่องจักร *</label>
                    <div class="relative">
                        <i class="fa-solid fa-tractor absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="equipment_id" required class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 appearance-none">
                            <option value="" disabled selected>-- เลือกรถที่เติม --</option>
                            @foreach($equipments as $eq)
                                <option value="{{ $eq->id }}" {{ old('equipment_id') == $eq->id ? 'selected' : '' }}>
                                    {{ $eq->name }} ({{ $eq->equipment_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- 2. ราคา --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">จำนวนเงิน *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">฿</span>
                            <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                        </div>
                    </div>
                    {{-- 3. ลิตร --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">จำนวนลิตร</label>
                        <div class="relative">
                            <i class="fa-solid fa-flask absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="number" step="0.01" name="liters" placeholder="ระบุลิตร" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                        </div>
                    </div>
                </div>

                {{-- 4. รูปถ่าย --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">รูปสลิป/หน้าตู้ (บังคับ) *</label>
                    <div class="mt-1 flex justify-center rounded-xl border-2 border-dashed border-gray-300 px-6 py-6 hover:border-orange-400 transition bg-gray-50">
                        <div class="text-center">
                            <i class="fa-solid fa-camera text-3xl text-gray-400 mb-2"></i>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="file-upload" class="relative cursor-pointer rounded-md bg-white font-bold text-orange-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-orange-500 focus-within:ring-offset-2 hover:text-orange-500">
                                    <span>อัปโหลดรูปภาพ</span>
                                    <input id="file-upload" name="image" type="file" accept="image/*" capture="environment" required class="sr-only">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">หรือถ่ายรูปหน้าตู้จ่ายน้ำมัน</p>
                        </div>
                    </div>
                </div>

                {{-- 5. เลขไมล์ --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">เลขไมล์/ชั่วโมงทำงาน</label>
                    <input type="number" step="0.1" name="mileage" placeholder="ระบุเลขชั่วโมงรถปัจจุบัน" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">หมายเหตุ</label>
                    <textarea name="note" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500" placeholder="เช่น เติมปั๊ม ปตท."></textarea>
                </div>

            </div>

            <div class="p-6 bg-gray-50 border-t border-gray-100">
                <button type="submit" class="w-full bg-orange-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-orange-200 hover:bg-orange-700 hover:-translate-y-0.5 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-save"></i> บันทึกข้อมูล
                </button>
            </div>
        </div>
    </form>
</div>
@endsection