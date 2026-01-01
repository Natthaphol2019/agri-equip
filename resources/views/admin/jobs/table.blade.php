<div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-6 py-4 font-medium">Job No.</th>
                <th class="px-6 py-4 font-medium">ลูกค้า</th>
                <th class="px-6 py-4 font-medium">เครื่องจักร/พนักงาน</th>
                <th class="px-6 py-4 font-medium">เวลานัดหมาย</th>
                <th class="px-6 py-4 font-medium">สถานะ</th>
                <th class="px-6 py-4 font-medium text-right">จัดการ</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($jobs as $job)
                <tr class="hover:bg-blue-50/30 transition duration-150 group">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.jobs.show', $job->id) }}" class="font-bold text-agri-primary bg-agri-primary/10 px-2 py-1 rounded-md hover:bg-agri-primary hover:text-white transition inline-block">
                            {{ $job->job_number }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-800">{{ $job->customer->name ?? '-' }}</div>
                        <a href="tel:{{ $job->customer->phone ?? '' }}" class="text-xs text-gray-500 flex items-center gap-1 hover:text-blue-600 transition w-fit">
                            <i class="fa-solid fa-phone text-[10px]"></i> {{ $job->customer->phone ?? '' }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-800 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-tractor text-gray-400 text-xs"></i> {{ $job->equipment->name ?? '-' }}
                        </div>
                        
                        {{-- ⚡ Quick Assign Button --}}
                        <div class="mt-1">
                            @if($job->assignedStaff)
                                <button onclick="openAssignModal({{ $job->id }}, '{{ $job->job_number }}', {{ $job->assigned_staff_id }})" 
                                    class="text-xs text-gray-500 flex items-center gap-1 hover:text-blue-600 hover:bg-blue-50 px-1.5 py-0.5 rounded transition cursor-pointer border border-transparent hover:border-blue-100">
                                    <i class="fa-solid fa-user-gear text-[10px]"></i> {{ $job->assignedStaff->name }}
                                </button>
                            @else
                                <button onclick="openAssignModal({{ $job->id }}, '{{ $job->job_number }}')" 
                                    class="text-xs text-red-500 bg-red-50 border border-red-100 px-2 py-0.5 rounded flex items-center gap-1 hover:bg-red-100 transition animate-pulse cursor-pointer">
                                    <i class="fa-solid fa-user-plus"></i> ระบุช่าง
                                </button>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-800 font-medium">{{ \Carbon\Carbon::parse($job->scheduled_start)->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($job->scheduled_start)->format('H:i') }} น.</div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusConfig = match($job->status) {
                                'scheduled' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-100', 'label' => 'นัดหมาย'],
                                'in_progress' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'label' => 'กำลังทำ 🚜'],
                                'completed_pending_approval' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'border' => 'border-orange-200', 'label' => 'รอตรวจสอบ'],
                                'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-200', 'label' => 'เสร็จสิ้น ✅'],
                                'cancelled' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'border' => 'border-red-100', 'label' => 'ยกเลิก ❌'],
                                default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-500', 'border' => '', 'label' => $job->status]
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} {{ $statusConfig['border'] }} inline-block text-center min-w-[100px] shadow-sm">
                            {{ $statusConfig['label'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition">
                            
                            {{-- View --}}
                            <a href="{{ route('admin.jobs.show', $job->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-600 hover:bg-blue-50 transition" title="รายละเอียด">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            {{-- Review (ถ้าเสร็จแล้วรอตรวจ) --}}
                            @if($job->status == 'completed_pending_approval')
                                <a href="{{ route('admin.jobs.review', $job->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg border border-orange-300 bg-orange-50 text-orange-600 hover:bg-orange-500 hover:text-white transition shadow-sm animate-bounce" title="ตรวจสอบงาน">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </a>
                            @endif

                            {{-- Edit (เฉพาะงานที่ยังไม่เสร็จ) --}}
                            @if(in_array($job->status, ['scheduled', 'in_progress']))
                                <a href="{{ route('admin.jobs.edit', $job->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:text-orange-500 hover:border-orange-500 hover:bg-orange-50 transition" title="แก้ไข">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            @endif

                            {{-- Cancel (AJAX) --}}
                            @if($job->status == 'scheduled')
                                <button onclick="cancelJob({{ $job->id }}, '{{ $job->job_number }}')" class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-100 text-red-400 hover:text-red-600 hover:bg-red-50 transition" title="ยกเลิกงาน">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-12">
                        <div class="flex flex-col items-center justify-center text-gray-300">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <i class="fa-regular fa-folder-open text-3xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">ไม่พบข้อมูลงาน</p>
                            @if(request('status') || request('search'))
                                <button onclick="window.location.reload()" class="text-xs text-agri-primary hover:underline mt-2">ล้างตัวกรอง</button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination Links (AJAX Compatible) --}}
@if($jobs->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
        {{ $jobs->appends(request()->query())->links() }}
    </div>
@endif