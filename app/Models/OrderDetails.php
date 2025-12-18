<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
    ];
    
    public function order()
    {
        return $this->belongsTo(Orders::class,'order_id','id');
    }

    public function product()
    {
        return $this->belongsTo(ShopProduct::class,'product_id','pro_id');
    }

    public function variant()
    {
        return $this->belongsTo(ShopProductVariants::class, 'variant_id', 'pvar_id');
    }
}
