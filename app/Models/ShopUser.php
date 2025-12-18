<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopUser extends Model
{
    use HasFactory;
    public $table = 'shop_openshop';
    protected $fillable = ['shop_users_id','shop_name','shop_business_type','shop_mcate_id','shop_status'];
    // public $timestamps = false;
    const CREATED_AT = 'shop_create';
    const UPDATED_AT = 'shop_update';

    public function user()
    {
        return $this->belongsTo(User::class, 'shop_users_id');
    }
}
