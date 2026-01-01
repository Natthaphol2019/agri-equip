<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    // หน้ารายชื่อลูกค้า
    public function index(Request $request)
    {
        $query = Customer::query();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $customers = $query->latest()->paginate(10);
        
        return view('admin.customers.index', compact('customers'));
    }

    // หน้าฟอร์มเพิ่ม
    public function create()
    {
        return view('admin.customers.create');
    }

    // บันทึกข้อมูลใหม่
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'customer_type' => 'required|in:individual,farm,company',
            'tax_id' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'province' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]);

        // Auto Generate Customer Code (CUS-001)
        $latestCustomer = Customer::latest('id')->first();
        if ($latestCustomer && preg_match('/CUS-(\d+)/', $latestCustomer->customer_code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $newCode = 'CUS-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $data['customer_code'] = $newCode;

        Customer::create($data);

        return redirect()->route('admin.customers.index')
            ->with('success', "เพิ่มลูกค้าสำเร็จ! รหัสสมาชิกของคุณคือ $newCode");
    }

    // ✅ หน้าฟอร์มแก้ไข
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    // ✅ บันทึกการแก้ไข
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'customer_type' => 'required|in:individual,farm,company',
            'tax_id' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'province' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]);

        // อัปเดตข้อมูล (ยกเว้น customer_code)
        $customer->update($data);

        return redirect()->route('admin.customers.index')
            ->with('success', "แก้ไขข้อมูลลูกค้า '{$customer->name}' เรียบร้อยแล้ว");
    }

    // ✅ ลบข้อมูล
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete(); // หรือใช้ SoftDelete ตาม Model

        return redirect()->route('admin.customers.index')
            ->with('success', 'ลบข้อมูลลูกค้าเรียบร้อยแล้ว');
    }
}