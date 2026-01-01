@extends('layouts.admin')

@section('title', 'เพิ่มเครื่องจักร')
@section('header', 'เพิ่มเครื่องจักรใหม่')

@section('content')
<div class="max-w-5xl mx-auto">
    
    <div class="mb-6">
        <a href="{{ route('admin.equipments.index') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center gap-1 transition w-fit">
            <i class="fa-solid fa-arrow-left"></i> กลับหน้ารายการ
        </a>
    </div>

    <form action="{{ route('admin.equipments.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- 🟢 LEFT: ข้อมูลหลัก --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 text-lg mb-6 flex items-center gap-2 border-b border-gray-100 pb-3">
                        <i class="fa-solid fa-circle-info text-agri-primary"></i> ข้อมูลทั่วไป
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        {{-- ชื่อ --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">ชื่อเครื่องจักร <span class="text-red-500">*</span></label>
                            <input type="text" name="name" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-agri-primary/20 focus:border-agri-primary outline-none transition" placeholder="เช่น รถไถ Kubota L5018" value="{{ old('name') }}" required>
                        </div>

                        {{-- ประเภท --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">ประเภท <span class="text-red-500">*</span></label>
                            <select name="type" id="typeSelect" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-agri-primary/20 focus:border-agri-primary outline-none bg-white" required>
                                <option value="" selected disabled>-- เลือกประเภท --</option>
                                <option value="tractor" {{ old('type') == 'tractor' ? 'selected' : '' }}>รถไถ (Tractor)</option>
                                <option value="drone" {{ old('type') == 'drone' ? 'selected' : '' }}>โดรน (Drone)</option>
                                <option value="harvester" {{ old('type') == 'harvester' ? 'selected' : '' }}>รถเกี่ยว (Harvester)</option>
                                <option value="sprayer" {{ old('type') == 'sprayer' ? 'selected' : '' }}>รถพ่นยา (Sprayer)</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>อื่นๆ</option>
                            </select>
                        </div>

                        {{-- รหัส (Auto) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">รหัสเครื่องจักร (Code)</label>
                            <div class="relative">
                                <input type="text" name="equipment_code" id="equipmentCode" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 focus:outline-none" placeholder="Auto Generate" readonly>
                                <i class="fa-solid fa-lock absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            </div>
                        </div>

                        {{-- ทะเบียน --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">เลขทะเบียน / Serial No.</label>
                            <input type="text" name="registration_number" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-agri-primary/20 focus:border-agri-primary outline-none transition" placeholder="ระบุเลขทะเบียน (ถ้ามี)" value="{{ old('registration_number') }}">
                        </div>
                    </div>
                </div>

                {{-- การตั้งค่าซ่อมบำรุง --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-6">
                    <h3 class="font-bold text-gray-800 text-lg mb-6 flex items-center gap-2 border-b border-gray-100 pb-3">
                        <i class="fa-solid fa-screwdriver-wrench text-orange-500"></i> การตั้งค่าระบบ
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">รอบซ่อมบำรุง (ชั่วโมง) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="maintenance_hour_threshold" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition" value="{{ old('maintenance_hour_threshold', 100) }}" required>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">ชม.</span>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1">แจ้งเตือนเมื่อใช้งานครบกำหนด</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">ค่าเช่า (บาท/ชั่วโมง) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="hourly_rate" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition" value="{{ old('hourly_rate', 0) }}" min="0" required>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">฿</span>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1">ใช้คำนวณราคาอัตโนมัติ</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 🔵 RIGHT: รูปภาพ & สถานะ --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <label class="block text-sm font-bold text-gray-700 mb-3">รูปภาพเครื่องจักร</label>
                    
                    {{-- Image Preview --}}
                    <div class="relative w-full aspect-square bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center overflow-hidden hover:bg-gray-100 transition group cursor-pointer" onclick="document.getElementById('imageInput').click()">
                        <img id="imagePreview" class="absolute inset-0 w-full h-full object-cover hidden">
                        <div id="uploadPlaceholder" class="text-center p-4">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-300 mb-2 group-hover:text-agri-primary transition"></i>
                            <p class="text-xs text-gray-500">คลิกเพื่ออัปโหลดรูปภาพ</p>
                        </div>
                    </div>
                    <input type="file" name="image" id="imageInput" class="hidden" accept="image/*" onchange="previewImage(event)">
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <label class="block text-sm font-bold text-gray-700 mb-3">สถานะเริ่มต้น</label>
                    <select name="current_status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-agri-primary/20 focus:border-agri-primary outline-none bg-white">
                        <option value="available">✅ ว่าง (Available)</option>
                        <option value="maintenance">🛠️ ซ่อมบำรุง (Maintenance)</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-agri-primary text-white py-3 rounded-xl shadow-lg shadow-agri-primary/30 hover:bg-agri-hover hover:-translate-y-0.5 transition font-bold flex items-center justify-center gap-2">
                    <i class="fa-solid fa-save"></i> บันทึกข้อมูล
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Preview Image
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('imagePreview');
            const placeholder = document.getElementById('uploadPlaceholder');
            output.src = reader.result;
            output.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
    }

    // Auto Generate Code
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('typeSelect');
        const codeInput = document.getElementById('equipmentCode');
        const prefixes = { 'tractor': 'TR-', 'drone': 'DR-', 'harvester': 'HV-', 'sprayer': 'SP-', 'other': 'OT-' };

        typeSelect.addEventListener('change', function() {
            const prefix = prefixes[this.value] || 'EQ-';
            // สุ่มเลข 3 หลัก (จำลอง)
            const random = Math.floor(Math.random() * 900) + 100; 
            codeInput.value = prefix + random;
        });
    });
</script>
@endsection