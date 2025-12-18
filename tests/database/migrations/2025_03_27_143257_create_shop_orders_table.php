<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->integer('order_id')->primary();
            $table->integer('order_shop_id');
            $table->integer('order_mem_id');
            $table->integer('order_address');
            $table->text('order_infos');
            $table->integer('order_status');
            $table->dateTime('order_datetime');
            $table->dateTime('order_dateupdate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_orders');
    }
}
