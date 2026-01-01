<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_number',
        'customer_id',
        'equipment_id',
        'assigned_staff_id',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'status',
        'total_price',
        'deposit_amount',
        'payment_status',
        'payment_proof',
        'payment_trans_ref', // ✅ เพิ่มบรรทัดนี้: เพื่อเก็บเลข Ref ของสลิป (เช่น 014xxxx)
        'image_path',
        'note'
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'deposit_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function assignedStaff() 
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function activities()
    {
        return $this->hasMany(TaskActivity::class);
    }
}