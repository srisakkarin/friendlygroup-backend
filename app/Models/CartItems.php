<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItems extends Model
{
    use HasFactory;
    protected $table = 'cart_items'; // ระบุชื่อตาราง
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
    ];
    public function cart()
    {
        return $this->belongsTo(Carts::class);
    }
    public function product()
    {
        return $this->belongsTo(ShopProduct::class, 'product_id', 'pro_id');
    }
    public function variant()
    {
        return $this->belongsTo(ShopProductVariants::class, 'variant_id', 'pvar_id');
    }
}
