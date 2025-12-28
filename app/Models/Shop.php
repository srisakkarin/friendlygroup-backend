<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'logo', 'code', 'address', 'is_active'];

    public function users()
    {
        return $this->hasMany(Users::class, 'shop_id');
    }
}