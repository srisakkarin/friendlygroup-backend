<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPromotionPackage extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','promotion_package_id','start_date','end_date'];

    public function user()
    {
        return $this->belongsTo(Users::class);
    }

    public function promotionPackage()
    {
        return $this->belongsTo(PromotionPackage::class);
    }
}
