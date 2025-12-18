<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->integer('product_id');
            $table->integer('variant_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->timestamps();
            
            $table->foreign('cart_id', 'cart_items_cart_id_foreign')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('product_id', 'cart_items_product_id_foreign')->references('pro_id')->on('shop_products')->onDelete('cascade');
            $table->foreign('variant_id', 'cart_items_variant_id_foreign')->references('pvar_id')->on('shop_product_variants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
}
