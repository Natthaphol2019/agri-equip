<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. แสดงหน้าล็อกอิน (หน้าเดียว)
    public function loginForm()
    {
        // ถ้าล็อกอินอยู่แล้ว ให้เด้งไป Dashboard เลย ไม่ต้องกรอกใหม่
        if (Auth::check()) {
            return $this->redirectUser();
        }
        return view('auth.login'); 
    }

    // 2. ประมวลผลล็อกอิน
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // พยายามล็อกอิน
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // ✅ เรียกใช้ฟังก์ชันแยกเส้นทางอัตโนมัติ
            return $this->redirectUser();
        }

        // ล็อกอินไม่ผ่าน
        return back()->withErrors([
            'username' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง',
        ])->withInput(); // คืนค่า input เดิมจะได้ไม่ต้องพิมพ์ใหม่
    }

    // 3. ฟังก์ชันแยกเส้นทาง (Smart Redirect Logic)
    private function redirectUser()
    {
        $role = Auth::user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } 
        
        // ถ้าไม่ใช่ admin (คือ staff) ไปหน้านี้
        return redirect()->route('staff.dashboard');
    }

    // 4. ออกจากระบบ
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}