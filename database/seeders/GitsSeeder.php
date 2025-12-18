<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $gifts = [
            [
                'name' => 'ดอกกุหลาบแดง',
                'coin_price' => 10,
                'image' => 'https://example.com/images/red_rose.png'
            ],
            [
                'name' => 'ช่อดอกไม้',
                'coin_price' => 50,
                'image' => 'https://example.com/images/bouquet.png'
            ],
            [
                'name' => 'ตุ๊กตาหมี',
                'coin_price' => 75,
                'image' => 'https://example.com/images/teddy_bear.png'
            ],
            [
                'name' => 'กล่องช็อกโกแลต',
                'coin_price' => 60,
                'image' => 'https://example.com/images/chocolate_box.png'
            ],
            [
                'name' => 'หัวใจสีแดง',
                'coin_price' => 20,
                'image' => 'https://example.com/images/red_heart.png'
            ],
            [
                'name' => 'จูบ',
                'coin_price' => 30,
                'image' => 'https://example.com/images/kiss.png'
            ],
            [
                'name' => 'แหวนเพชร',
                'coin_price' => 500,
                'image' => 'https://example.com/images/diamond_ring.png'
            ],
            [
                'name' => 'รถสปอร์ต',
                'coin_price' => 1000,
                'image' => 'https://example.com/images/sports_car.png'
            ],
            [
                'name' => 'บ้านหรู',
                'coin_price' => 2000,
                'image' => 'https://example.com/images/luxury_house.png'
            ],
            [
                'name' => 'เครื่องบินส่วนตัว',
                'coin_price' => 5000,
                'image' => 'https://example.com/images/private_jet.png'
            ],
            [
                'name' => 'มงกุฎ',
                'coin_price' => 300,
                'image' => 'https://example.com/images/crown.png'
            ],
            [
                'name' => 'สร้อยคอทองคำ',
                'coin_price' => 250,
                'image' => 'https://example.com/images/gold_necklace.png'
            ],
            [
                'name' => 'นาฬิกาหรู',
                'coin_price' => 400,
                'image' => 'https://example.com/images/luxury_watch.png'
            ],
            [
                'name' => 'กระเป๋าแบรนด์เนม',
                'coin_price' => 350,
                'image' => 'https://example.com/images/designer_bag.png'
            ],
            [
                'name' => 'น้ำหอม',
                'coin_price' => 100,
                'image' => 'https://example.com/images/perfume.png'
            ],
            [
                'name' => 'เทียนหอม',
                'coin_price' => 40,
                'image' => 'https://example.com/images/scented_candle.png'
            ],
            [
                'name' => 'ลูกโป่ง',
                'coin_price' => 15,
                'image' => 'https://example.com/images/balloon.png'
            ],
            [
                'name' => 'เค้ก',
                'coin_price' => 80,
                'image' => 'https://example.com/images/cake.png'
            ],
            [
                'name' => 'แก้วกาแฟ',
                'coin_price' => 25,
                'image' => 'https://example.com/images/coffee_cup.png'
            ],
            [
                'name' => 'หูฟัง',
                'coin_price' => 120,
                'image' => 'https://example.com/images/headphones.png'
            ],
            [
                'name' => 'โทรศัพท์มือถือ',
                'coin_price' => 600,
                'image' => 'https://example.com/images/smartphone.png'
            ],
            [
                'name' => 'แล็ปท็อป',
                'coin_price' => 800,
                'image' => 'https://example.com/images/laptop.png'
            ],
            [
                'name' => 'กล้องถ่ายรูป',
                'coin_price' => 700,
                'image' => 'https://example.com/images/camera.png'
            ],
            [
                'name' => 'เกมคอนโซล',
                'coin_price' => 450,
                'image' => 'https://example.com/images/game_console.png'
            ],
            [
                'name' => 'หนังสือ',
                'coin_price' => 30,
                'image' => 'https://example.com/images/book.png'
            ],
            [
                'name' => 'บัตรของขวัญ',
                'coin_price' => 150,
                'image' => 'https://example.com/images/gift_card.png'
            ],
            [
                'name' => 'ดาว',
                'coin_price' => 5,
                'image' => 'https://example.com/images/star.png'
            ],
            [
                'name' => 'สายรุ้ง',
                'coin_price' => 45,
                'image' => 'https://example.com/images/rainbow.png'
            ],
            [
                'name' => 'สายฟ้า',
                'coin_price' => 35,
                'image' => 'https://example.com/images/lightning.png'
            ],
            [
                'name' => 'พลุ',
                'coin_price' => 70,
                'image' => 'https://example.com/images/fireworks.png'
            ],
            [
                'name' => 'หัวใจสีชมพู',
                'coin_price' => 25,
                'image' => 'https://example.com/images/pink_heart.png'
            ],
            [
                'name' => 'หัวใจสีม่วง',
                'coin_price' => 25,
                'image' => 'https://example.com/images/purple_heart.png'
            ],
            [
                'name' => 'หัวใจสีฟ้า',
                'coin_price' => 25,
                'image' => 'https://example.com/images/blue_heart.png'
            ],
            [
                'name' => 'หัวใจสีเขียว',
                'coin_price' => 25,
                'image' => 'https://example.com/images/green_heart.png'
            ],
            [
                'name' => 'หัวใจสีเหลือง',
                'coin_price' => 25,
                'image' => 'https://example.com/images/yellow_heart.png'
            ],
            [
                'name' => 'รถหรู',
                'coin_price' => 800,
                'image' => 'https://example.com/images/luxury_car.png'
            ],
            [
                'name' => 'เรือยอร์ช',
                'coin_price' => 1500,
                'image' => 'https://example.com/images/yacht.png'
            ],
            [
                'name' => 'เงินสด',
                'coin_price' => 100,
                'image' => 'https://example.com/images/cash.png'
            ],
            [
                'name' => 'ทองคำแท่ง',
                'coin_price' => 750,
                'image' => 'https://example.com/images/gold_bar.png'
            ],
            [
                'name' => 'เพชร',
                'coin_price' => 600,
                'image' => 'https://example.com/images/diamond.png'
            ],
            [
                'name' => 'ดาวตก',
                'coin_price' => 150,
                'image' => 'https://example.com/images/shooting_star.png'
            ],
            [
                'name' => 'ดวงจันทร์',
                'coin_price' => 200,
                'image' => 'https://example.com/images/moon.png'
            ],
            [
                'name' => 'ดวงอาทิตย์',
                'coin_price' => 300,
                'image' => 'https://example.com/images/sun.png'
            ],
            [
                'name' => 'จักรวาล',
                'coin_price' => 10000,
                'image' => 'https://example.com/images/universe.png'
            ],
            [
                'name' => 'ดาวเคราะห์',
                'coin_price' => 5000,
                'image' => 'https://example.com/images/planet.png'
            ],
            [
                'name' => 'หุ่นยนต์',
                'coin_price' => 300,
                'image' => 'https://example.com/images/robot.png'
            ],
            [
                'name' => 'มังกร',
                'coin_price' => 400,
                'image' => 'https://example.com/images/dragon.png'
            ],
            [
                'name' => 'ยูนิคอร์น',
                'coin_price' => 350,
                'image' => 'https://example.com/images/unicorn.png'
            ],
            [
                'name' => 'ผีเสื้อ',
                'coin_price' => 50,
                'image' => 'https://example.com/images/butterfly.png'
            ],
            [
                'name' => 'นก',
                'coin_price' => 40,
                'image' => 'https://example.com/images/bird.png'
            ],
            [
                'name' => 'แมว',
                'coin_price' => 60,
                'image' => 'https://example.com/images/cat.png'
            ],
        ];

        // Insert the data into the gifts table
        DB::table('gifts')->insert($gifts);
    }
}
