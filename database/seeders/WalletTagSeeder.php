<?php

namespace Database\Seeders;

use App\Models\WalletTags;
use Illuminate\Database\Seeder;

class WalletTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WalletTags::create(['tag_name' => 'top_up', 'description' => 'User topped up their wallet']);
        WalletTags::create(['tag_name' => 'watch_live', 'description' => 'Coins deducted for watching live']);
        WalletTags::create(['tag_name' => 'buy_user_package', 'description' => 'Purchase of a user package']);
        WalletTags::create(['tag_name' => 'buy_promotion_package', 'description' => 'Purchase of a promotion package']);
    }
}
