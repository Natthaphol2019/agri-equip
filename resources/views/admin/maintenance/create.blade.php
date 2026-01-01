@extends('layouts.admin')

@section('title', 'เพิ่มบันทึกซ่อมบำรุง')
@section('header', 'แจ้งซ่อมบำรุง')

@section('content')
<div class="max-w-2xl mx-auto">
    
    <div class="mb-4">
        <a href="{{ route('admin.maintenance.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> กลับ
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-bold text-gray-800">รายละเอียดการซ่อม</h2>
            <p class="text-xs text-gray-500">บันทึกข้อมูลการซ่อมบำรุงเครื่องจักร</p>
        </div>

        <form action="{{ route('admin.maintenance.store') }}" method="POST" class="p-6 space-y-5">
            @csrf
            
            {{-- เลือกเครื่องจักร --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">เครื่องจักร</label>
                <select name="equipment_id" required class="w-full rounded-xl border-gray-300 focus:ring-agri-primary focus:border-agri-primary">
                    <option value="" disabled selected>-- เลือกเครื่องจักร --</option>
                    @foreach($equipments as $eq)
                        {{-- ✅ เช็คค่า request('equipment_id') เพื่อ auto-select --}}
                        <option value="{{ $eq->id }}" {{ (old('equipment_id') ?? request('equipment_id')) == $eq->id ? 'selected' : '' }}>
                            {{ $eq->name }} ({{ $eq->equipment_code }}) - {{ $eq->current_status }}
                        </option>
                    @endforeach
                </select>
                @error('equipment_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- สาเหตุ/อาการ --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">สาเหตุ/อาการเสีย</label>
                <textarea name="description" rows="3" class="w-full rounded-xl border-gray-300 focus:ring-agri-primary focus:border-agri-primary" placeholder="ระบุอาการ...">{{ old('description') }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- ค่าใช้จ่าย --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">ค่าใช้จ่าย (บาท)</label>
                <input type="number" name="cost" step="0.01" class="w-full rounded-xl border-gray-300 focus:ring-agri-primary focus:border-agri-primary" value="{{ old('cost') ?? 0 }}">
            </div>

            {{-- วันที่ซ่อม --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">วันที่ซ่อมเสร็จ</label>
                <input type="date" name="completion_date" class="w-full rounded-xl border-gray-300 focus:ring-agri-primary focus:border-agri-primary" value="{{ old('completion_date') ?? date('Y-m-d') }}">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <button type="reset" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50 font-bold text-sm">รีเซ็ต</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-agri-primary text-white hover:bg-agri-hover font-bold text-sm shadow-lg shadow-agri-primary/30">
                    <i class="fa-solid fa-save mr-1"></i> บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>
</div>
@endsection