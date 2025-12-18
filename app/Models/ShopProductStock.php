<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopProductStock extends Model
{
    use HasFactory;
    public $table = 'shop_product_stock';
    protected $primaryKey = 'tock_id';
    protected $fillable = ['tock_shop_id', 'tock_pro_id', 'tock_pvar_id', 'tcok_instock'];
    public $timestamps = false;
    public function product()
    {
        return $this->belongsTo(ShopProduct::class, 'tock_pro_id', 'pro_id');
    }
    public function variant()
    {
        return $this->belongsTo(ShopProductVariants::class, 'tock_pvar_id', 'pvar_id');
    }
}
