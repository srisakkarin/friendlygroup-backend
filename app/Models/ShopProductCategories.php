<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopProductCategories extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'parent_id', 'order'];

    public function parent()
    {
        return $this->belongsTo(ShopProductCategories::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(ShopProductCategories::class, 'id', 'parent_id');
    }
    public function products()
    {
        return $this->hasMany(ShopProduct::class, 'category_id', 'id');
    }
}
