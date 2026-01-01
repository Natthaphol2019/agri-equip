@extends('layouts.guest')

@section('content')

    {{-- Error Message --}}
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-center border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div x-data="pinSystem()" class="text-center">
        
        {{-- STATE 1: เลือกพนักงาน --}}
        <div x-show="!selectedUser" x-transition:enter="transition ease-out duration-300">
            <h3 class="text-lg font-bold text-gray-700 mb-6">เลือกบัญชีพนักงาน</h3>
            
            <div class="grid grid-cols-2 gap-4 max-h-[350px] overflow-y-auto p-2">
                @foreach($staffs as $staff)
                    <button @click="selectUser({{ $staff->id }}, '{{ $staff->name }}')" 
                        class="flex flex-col items-center p-4 bg-white border border-gray-200 rounded-xl shadow-sm hover:border-agri-primary hover:bg-green-50 hover:shadow-md transition transform hover:-translate-y-1">
                        {{-- Avatar --}}
                        <img src="https://ui-avatars.com/api/?name={{ $staff->name }}&background=1B4D3E&color=fff&size=128" 
                             class="w-16 h-16 rounded-full mb-3 shadow-sm object-cover">
                        <span class="font-bold text-gray-700 text-sm line-clamp-1">{{ $staff->name }}</span>
                    </button>
                @endforeach
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('login') }}" class="text-gray-400 hover:text-agri-primary text-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> กลับไปหน้า Admin Login
                </a>
            </div>
        </div>

        {{-- STATE 2: ใส่ PIN --}}
        <div x-show="selectedUser" x-cloak x-transition:enter="transition ease-out duration-300">
            
            {{-- หัวข้อ --}}
            <div class="mb-6">
                <img :src="'https://ui-avatars.com/api/?name=' + userName + '&background=1B4D3E&color=fff&size=128'" 
                     class="w-20 h-20 rounded-full mx-auto shadow-lg mb-3 border-4 border-white">
                <h3 class="text-xl font-bold text-gray-800" x-text="userName"></h3>
                <p class="text-gray-500 text-sm mt-1">กรุณาระบุ PIN 4 หลัก</p>
            </div>

            {{-- จุดแสดง PIN --}}
            <div class="flex justify-center gap-4 mb-8">
                <template x-for="i in 4">
                    <div class="w-4 h-4 rounded-full border-2 border-gray-300 transition-all duration-200"
                         :class="pin.length >= i ? 'bg-agri-primary border-agri-primary scale-125' : 'bg-transparent'"></div>
                </template>
            </div>

            {{-- ปุ่มตัวเลข (Numpad) --}}
            <div class="grid grid-cols-3 gap-4 w-[260px] mx-auto mb-6 select-none">
                <template x-for="num in [1,2,3,4,5,6,7,8,9]">
                    <button @click="addPin(num)" 
                            class="h-16 w-16 rounded-full bg-gray-50 border border-gray-200 text-2xl font-semibold text-gray-600 shadow-sm hover:bg-agri-primary hover:text-white hover:border-agri-primary hover:shadow-lg transition active:scale-95 flex items-center justify-center">
                        <span x-text="num"></span>
                    </button>
                </template>
                
                {{-- ปุ่มล่าง --}}
                <button @click="reset()" class="h-16 w-16 flex items-center justify-center text-gray-400 hover:text-red-500 font-bold text-sm">
                    ยกเลิก
                </button>
                <button @click="addPin(0)" class="h-16 w-16 rounded-full bg-gray-50 border border-gray-200 text-2xl font-semibold text-gray-600 shadow-sm hover:bg-agri-primary hover:text-white hover:border-agri-primary hover:shadow-lg transition active:scale-95 flex items-center justify-center">
                    0
                </button>
                <button @click="delPin()" class="h-16 w-16 flex items-center justify-center text-gray-400 hover:text-gray-600 active:scale-90 transition">
                    <i class="fa-solid fa-delete-left text-2xl"></i>
                </button>
            </div>

            {{-- Form ซ่อน (เอาไว้ Submit) --}}
            <form x-ref="pinForm" method="POST" action="{{ route('staff.login.submit') }}">
                @csrf
                <input type="hidden" name="user_id" :value="userId">
                <input type="hidden" name="pin" :value="pin">
            </form>

        </div>
    </div>

    <script>
        function pinSystem() {
            return {
                selectedUser: false,
                userId: null,
                userName: '',
                pin: '',

                selectUser(id, name) {
                    this.selectedUser = true;
                    this.userId = id;
                    this.userName = name;
                    this.pin = '';
                },
                
                addPin(num) {
                    if (this.pin.length < 4) {
                        this.pin += num;
                        if (this.pin.length === 4) {
                            // Delay นิดนึงให้เห็นจุดเต็มก่อนส่ง
                            setTimeout(() => {
                                this.$refs.pinForm.submit();
                            }, 150);
                        }
                    }
                },

                delPin() {
                    this.pin = this.pin.slice(0, -1);
                },

                reset() {
                    this.selectedUser = false;
                    this.pin = '';
                }
            }
        }
    </script>
@endsection