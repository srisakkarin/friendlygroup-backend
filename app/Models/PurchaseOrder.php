<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'diamond_pack_id',
        'user_id',
        'status',
        'payment_method',
        'data'
    ];
    public $timestamps = false;
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function diamondPack()
    {
        return $this->belongsTo(DiamondPacks::class, 'diamond_pack_id'); // Many-to-One
    }
}
