<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('shop_id');
            $table->timestamps();
            
            $table->foreign('shop_id', 'carts_shop_id_foreign')->references('shop_id')->on('shop_openshop')->onDelete('cascade');
            $table->foreign('user_id', 'carts_user_id_foreign')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carts');
    }
}
