<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'order_number',
        'shipping_address',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
    ];
    
    public function user()
    {
        return $this->belongsTo(Users::class,'user_id','id');
    }
    public function shop()
    {
        return $this->belongsTo(ShopUser::class,'shop_id','shop_id');
    }
    public function details()
    {
        return $this->hasMany(OrderDetails::class,'order_id','id');
    } 
}
