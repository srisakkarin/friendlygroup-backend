<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GlobalFunction; // เพิ่มบรรทัดนี้

class ShopProduct extends Model
{
    use HasFactory;

    public $table = 'shop_products';
    protected $primaryKey = 'pro_id';
    protected $fillable = [
        'pro_shop_id',
        'pro_mypro_id',
        'category_id',
        'pro_name',
        'pro_details',
        // 'pro_image',
        'pro_price',
        'pro_min',
        // 'pro_status',
        'pro_delete',
        'status',
        'visibility',
    ];
    const CREATED_AT = 'pro_create';
    const UPDATED_AT = 'pro_update';

    public function variants()
    {
        return $this->hasMany(ShopProductVariants::class, 'pvar_pro_id', 'pro_id');
    }

    public function stock()
    {
        return $this->hasOne(ShopProductStock::class, 'tock_pro_id', 'pro_id')->where('tock_pvar_id', 0);
    }

    public function category()
    {
        return $this->hasOne(ShopProductCategories::class, 'id', 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'pro_id');
    }

    protected static function boot()
    {
        parent::boot();

        // เมื่อมีการลบสินค้า
        static::deleting(function ($product) {
            // ลบ variants ที่เกี่ยวข้อง
            $product->variants()->delete();

            // ลบ stock ที่เกี่ยวข้อง (สำหรับ main product และ variants)
            // หากคุณใช้ onDelete('cascade') ใน migration สำหรับ tock_pro_id บนตาราง shop_product_stocks
            // คุณอาจจะไม่ต้องเรียก DB::table('shop_product_stock')->delete() ตรงนี้อีก
            // แต่ถ้าไม่ ก็ให้ตรวจสอบว่า stock()->delete() ครอบคลุมทั้งหมดหรือไม่
            $product->stock()->delete(); // สำหรับ tock_pvar_id = 0

            // หากมี stock สำหรับ variants แยกต่างหากและไม่ได้ถูกจัดการด้วย variants()->delete() ให้เพิ่ม:
            // หาก ShopProductVariants มีความสัมพันธ์กับ ShopProductStock และมีการใช้ deleting event ใน ShopProductVariants model
            // การเรียก $product->variants()->delete() จะจัดการ stock ของ variants ให้เอง
            // หรือถ้าไม่เช่นนั้น คุณอาจจะต้องเขียนเพิ่มเติม เช่น:
            // DB::table('shop_product_stocks')->where('tock_pro_id', $product->pro_id)->delete();


            // ลบรูปภาพจาก S3 และจากตาราง ProductImage
            foreach ($product->images as $image) {
                GlobalFunction::deleteFile($image->image); // เรียกใช้ฟังก์ชันลบไฟล์จาก S3
            }
            $product->images()->delete(); // ลบ record รูปภาพจากตาราง ProductImage
        });
    }
}