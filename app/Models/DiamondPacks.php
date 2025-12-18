<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondPacks extends Model
{
    // Diamond Packs
    use HasFactory;
    public $table = "diamond_packs";
    public $timestamps = false;

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'diamond_pack_id'); // One-to-Many
    }
}
