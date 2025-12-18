<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->integer('user_id'); // Foreign key to users
            $table->integer('shop_id'); // Foreign key to shops
            $table->string('order_number')->unique(); // Unique order number
            $table->text('shipping_address');
            $table->decimal('total_amount', 10, 2); // Total amount of the order
            $table->string('payment_method'); // e.g., Credit Card, PayPal
            $table->string('payment_status')->default('pending'); // pending, completed, failed
            $table->string('order_status')->default('pending'); // pending, shipped, delivered
            $table->timestamps(); // Adds created_at and updated_at columns
            //ref
            $table->foreign('user_id')->references('id')->on('users')->onDelete('Cascade');
            $table->foreign('shop_id')->references('shop_id')->on('shop_openshop')->onDelete('Cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
