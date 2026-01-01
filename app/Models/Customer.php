<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'customer_code', 'name', 'phone', 'address', 
        'latitude', 'longitude', 'customer_type', 'notes'
    ];
}