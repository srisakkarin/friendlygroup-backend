<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carts extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class);
    }
    public function shop()
    {
        return $this->belongsTo(ShopUser::class,'shop_id','shop_id');
    }
    public function items()
    {
        return $this->hasMany(CartItems::class,'cart_id','id');
    }
}
