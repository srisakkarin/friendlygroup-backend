<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopOrdersdetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_ordersdetails', function (Blueprint $table) {
            $table->integer('odtal_id')->primary();
            $table->integer('odtal_shop_id');
            $table->integer('odtal_order_id');
            $table->integer('odtal_pro_id');
            $table->integer('odtal_option_id');
            $table->decimal('odtal_price', 10, 2);
            $table->integer('odtal_qty');
            $table->integer('odtal_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_ordersdetails');
    }
}
