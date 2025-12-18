<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->integer('product_id');
            $table->integer('variant_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->timestamps();
            
            $table->foreign('order_id', 'order_details_order_id_foreign')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id', 'order_details_product_id_foreign')->references('pro_id')->on('shop_products')->onDelete('cascade');
            $table->foreign('variant_id', 'order_details_variant_id_foreign')->references('pvar_id')->on('shop_product_variants')->onDelete('set NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_details');
    }
}
