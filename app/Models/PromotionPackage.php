<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionPackage extends Model
{
    use HasFactory;
    public function packageTransactions()
    {
        return $this->hasMany(PromotionPackageTransactions::class, 'promotion_package_id', 'id');
    }
}
