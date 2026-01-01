<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ===========================
    // 1. ADMIN LOGIN (Username/Password)
    // ===========================
    public function loginForm()
    {
        return view('auth.login'); 
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // เช็ค Role เพื่อ Redirect ให้ถูกหน้า
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } else {
                // ✅ แก้ไข: ถ้า Staff ล็อกอินแบบ Username ให้ไป Dashboard เหมือนกัน
                return redirect()->route('staff.dashboard');
            }
        }

        return back()->withErrors(['username' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
    }

    // ===========================
    // 2. STAFF LOGIN (PIN System)
    // ===========================
    public function staffLoginForm()
    {
        // ดึง user ที่ไม่ใช่ admin มาแสดง
        $staffs = User::where('role', '!=', 'admin')->get(); 
        
        return view('auth.login-staff', compact('staffs'));
    }

    public function staffLoginSubmit(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'pin' => 'required|string|size:4',
        ]);

        $user = User::find($request->user_id);

        // ตรวจสอบ PIN (Hash)
        // ⚠️ ต้องมั่นใจว่าใน DB เก็บ PIN แบบ Hash แล้ว
        if (!$user->pin || !Hash::check($request->pin, $user->pin)) {
            return back()->with('error', '❌ รหัส PIN ไม่ถูกต้อง!');
        }

        // Login สำเร็จ
        Auth::login($user);
        $request->session()->regenerate();

        // ✅ Redirect ไปหน้า Dashboard ของ Staff
        return redirect()->route('staff.dashboard')
                         ->with('success', 'สวัสดีครับ ' . $user->name . ' 👋');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}