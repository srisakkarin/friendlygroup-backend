<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productCategories = [
            [
                'name' => 'เสื้อผ้า',
                'description' => 'เสื้อผ้าสำหรับผู้ชาย ผู้หญิง และเด็ก',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'เครื่องประดับ',
                'description' => 'เครื่องประดับแฟชั่น สร้อยคอ แหวน กำไลข้อมือ',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'รองเท้า',
                'description' => 'รองเท้าผ้าใบ รองเท้าแตะ รองเท้าส้นสูง',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'กระเป๋า',
                'description' => 'กระเป๋าสะพาย กระเป๋าถือ กระเป๋าเป้',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'อุปกรณ์อิเล็กทรอนิกส์',
                'description' => 'โทรศัพท์มือถือ แท็บเล็ต หูฟัง',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'เครื่องใช้ไฟฟ้า',
                'description' => 'เครื่องใช้ไฟฟ้าในบ้าน เครื่องครัว',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'ของเล่นและเกม',
                'description' => 'ของเล่นเด็ก เกมกระดาน',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'เครื่องสำอางและความงาม',
                'description' => 'ผลิตภัณฑ์ดูแลผิว เครื่องสำอาง',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'อุปกรณ์กีฬา',
                'description' => 'อุปกรณ์สำหรับออกกำลังกาย กีฬากลางแจ้ง',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'หนังสือและสื่อบันเทิง',
                'description' => 'หนังสือ นิตยสาร ภาพยนตร์',
                'image' => '' // Replace with actual image path
            ],
        ];
        DB::table('shop_product_categories')->insert($productCategories);
    }
}
