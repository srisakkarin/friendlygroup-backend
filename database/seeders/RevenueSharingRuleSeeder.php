<?php

namespace Database\Seeders;

use App\Models\RevenueSharingRule;
use Illuminate\Database\Seeder;

class RevenueSharingRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rules = [
            [
                'title' => 'การดูคลิปวิดีโอ และ การชมไลฟ์สด',
                'action_key' => 'watch_video',
                'company_percent' => 20,
                'customer_percent' => 80,
            ],
            [
                'title' => 'การสมัครเป็นสมาชิกเพื่อดูเนื้อหาเฉพาะกลุ่ม Subscribe',
                'action_key' => 'subscribe',
                'company_percent' => 20,
                'customer_percent' => 80,
            ],
            [
                'title' => 'การขายสินค้าในแอพของสมาชิกที่เปิดร้าน',
                'action_key' => 'sell_item',
                'company_percent' => 13,
                'customer_percent' => 87,
            ],
            [
                'title' => 'การเพิ่มการมองเห็น โปรไฟล์ของตัวเอง',
                'action_key' => 'profile_boost',
                'company_percent' => 0,
                'customer_percent' => 100,
            ],
            [
                'title' => 'การไลฟ์สด',
                'action_key' => 'live_stream',
                'company_percent' => 0,
                'customer_percent' => 100,
            ],
            [
                'title' => 'ซื้อของขวัญ',
                'action_key' => 'send_gift',
                'company_percent' => 20,
                'customer_percent' => 80,
            ],
            [
                'title' => 'การซื้อเครื่องหมายสมาชิกพรีเมียม เพื่อเข้าสู่กลุ่มสมาชิกระดับพรีเมียม',
                'action_key' => 'buy_premium_badge',
                'company_percent' => 0,
                'customer_percent' => 100,
            ],
            [
                'title' => 'การรับงานผ่านแอพ รับค่าจ้างเป็น Coin',
                'action_key' => 'job_income',
                'company_percent' => 20,
                'customer_percent' => 80,
            ],
        ];

        foreach ($rules as $rule) {
            RevenueSharingRule::updateOrCreate(
                ['action_key' => $rule['action_key']],
                $rule
            );
        }
    }
}
