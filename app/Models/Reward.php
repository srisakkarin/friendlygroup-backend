<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'required_points', 'type', 
        'discount_type', 'discount_value', 'image', 'is_active'
    ];

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }
}