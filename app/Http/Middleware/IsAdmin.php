<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. ถ้ายังไม่ล็อกอิน -> ไปหน้า Login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. ถ้าล็อกอินแล้ว และเป็น Admin -> อนุญาตให้ผ่าน
        if (Auth::user()->role === 'admin') {
            return $next($request);
        }

        // 3. ⚠️ ถ้าล็อกอินแล้ว แต่ไม่ใช่ Admin (เช่น Staff พยายามเข้า)
        // ให้ดีดไปหน้า Staff Dashboard แทนการ Error
        return redirect()->route('staff.dashboard')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงส่วนของผู้ดูแลระบบ');
    }
}