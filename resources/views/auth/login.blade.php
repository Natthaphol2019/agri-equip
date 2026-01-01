@extends('layouts.guest')

@section('content')

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('login.submit') }}" method="POST">
        @csrf

        {{-- Username --}}
        <div class="mb-5">
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">ชื่อผู้ใช้งาน</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-regular fa-user text-gray-400"></i>
                </div>
                <input type="text" name="username" id="username" required autofocus
                    class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-agri-accent focus:border-agri-primary transition sm:text-sm"
                    placeholder="Username">
            </div>
        </div>

        {{-- Password --}}
        <div class="mb-6" x-data="{ show: false }">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-lock text-gray-400"></i>
                </div>
                <input :type="show ? 'text' : 'password'" name="password" id="password" required
                    class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-agri-accent focus:border-agri-primary transition sm:text-sm"
                    placeholder="••••••••">
                <button type="button" @click="show = !show" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-gray-400 hover:text-agri-primary focus:outline-none">
                    <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
        </div>

        <button type="submit" 
            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-agri-primary hover:bg-agri-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-agri-accent transition-transform transform active:scale-95">
            เข้าสู่ระบบ (Admin)
        </button>

        {{-- ✅ ปุ่มไปหน้าพนักงาน --}}
        <div class="mt-6 pt-6 border-t border-gray-100 text-center">
            <a href="{{ route('staff.login') }}" 
               class="inline-flex items-center gap-2 text-agri-primary font-bold hover:text-agri-secondary transition bg-green-50 px-4 py-2 rounded-full text-sm">
                <i class="fa-solid fa-users"></i> เข้าสู่ระบบพนักงาน (PIN)
            </a>
        </div>
    </form>

@endsection