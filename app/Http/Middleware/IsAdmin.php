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
        // เช็คว่าเป็น Admin หรือไม่
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        // ถ้าไม่ใช่ ให้เด้งกลับไปหน้า Login
        return redirect()->route('login')->with('error', 'ไม่มีสิทธิ์เข้าถึงส่วนนี้');
    }
}