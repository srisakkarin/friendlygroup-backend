<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionPackageTransactions extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'promotion_package_id', 'action', 'start_date', 'end_date', 'created_by_user_id', 'created_by_admin_id', 'status'];
    
    public function promotionPackage()
    {
        return $this->belongsTo(PromotionPackage::class);
    }

    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}
