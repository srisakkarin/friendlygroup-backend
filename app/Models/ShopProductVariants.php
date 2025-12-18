<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ShopProductVariants extends Model
{
    use HasFactory;

    public $table = 'shop_product_variants';
    protected $primaryKey = 'pvar_id';
    protected $fillable = [
        'pvar_shop_id', 'pvar_pro_id', 'pvar_n1', 'pvar_name1', 
        'pvar_n2', 'pvar_name2', 'pvar_price', 'pvar_sku'
    ];
    public $timestamps = false;

    // Relationship to ShopProductStock
    public function stock()
    {
        return $this->hasOne(ShopProductStock::class, 'tock_pvar_id', 'pvar_id');
    }

    // Automatically delete related stock when a variant is deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($variant) {
            // Log the deletion event for debugging
            Log::info('Deleting variant:', [
                'pvar_id' => $variant->pvar_id,
                'pvar_shop_id' => $variant->pvar_shop_id,
                'pvar_pro_id' => $variant->pvar_pro_id,
            ]);

            // Check if the related stock exists and delete it
            if ($variant->stock) {
                try {
                    $variant->stock()->delete(); // Use forceDelete() if using Soft Deletes
                    Log::info('Related stock deleted:', [
                        'tock_pvar_id' => $variant->pvar_id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to delete related stock:', [
                        'pvar_id' => $variant->pvar_id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                Log::info('No related stock found for variant:', [
                    'pvar_id' => $variant->pvar_id
                ]);
            }
        });
    }
}