<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopProductStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_product_stock', function (Blueprint $table) {
            $table->integer('tock_id')->primary();
            $table->integer('tock_shop_id');
            $table->integer('tock_pro_id');
            $table->integer('tock_pvar_id');
            $table->integer('tcok_instock');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_product_stock');
    }
}
