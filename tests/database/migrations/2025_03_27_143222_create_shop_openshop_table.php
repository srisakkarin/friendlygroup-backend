<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopOpenshopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_openshop', function (Blueprint $table) {
            $table->integer('shop_id')->primary();
            $table->integer('shop_users_id');
            $table->string('shop_name');
            $table->enum('shop_business_type', ['individual', 'corporate']);
            $table->integer('shop_mcate_id');
            $table->integer('shop_status')->comment("0=disable,1=enable");
            $table->dateTime('shop_create');
            $table->dateTime('shop_update');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_openshop');
    }
}
