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
            $table->unsignedBigInteger('order_id'); // Foreign key to shop_orders
            $table->integer('product_id')->constrained('shop_products')->onDelete('cascade'); // Foreign key to shop_products
            $table->integer('variant_id')->nullable()->constrained('shop_product_variants')->onDelete('set null'); // Optional variant
            $table->integer('quantity')->default(1); // Quantity of the product
            $table->decimal('price', 10, 2); // Price at the time of checkout
            $table->timestamps();

            //ref
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('Cascade');
            $table->foreign('product_id')->references('pro_id')->on('shop_products')->onDelete('Cascade');
            $table->foreign('variant_id')->references('pvar_id')->on('shop_product_variants')->onDelete('Set Null');
            
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
