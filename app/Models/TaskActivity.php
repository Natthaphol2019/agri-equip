<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskActivity extends Model
{
    protected $fillable = [
        'booking_id', 'user_id', 'activity_type', 
        'description', 'image_paths', 
        'location_lat', 'location_lng'
    ];

    protected $casts = [
        'image_paths' => 'array', // แปลง JSON เป็น Array อัตโนมัติ
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
    ];
}