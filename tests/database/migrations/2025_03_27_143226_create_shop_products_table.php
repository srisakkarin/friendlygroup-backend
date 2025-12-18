<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->integer('pro_id')->primary();
            $table->integer('pro_shop_id');
            $table->string('pro_mypro_id', 40);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('pro_name');
            $table->text('pro_details');
            $table->text('pro_image');
            $table->decimal('pro_price', 10, 2);
            $table->integer('pro_min');
            $table->dateTime('pro_create');
            $table->dateTime('pro_update');
            $table->integer('pro_status');
            $table->integer('pro_delete');
            $table->foreign('category_id', 'shop_products_category_id_foreign')->references('id')->on('shop_product_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_products');
    }
}
