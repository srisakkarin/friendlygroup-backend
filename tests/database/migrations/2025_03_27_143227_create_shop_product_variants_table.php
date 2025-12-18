<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_product_variants', function (Blueprint $table) {
            $table->integer('pvar_id')->primary();
            $table->integer('pvar_shop_id');
            $table->integer('pvar_pro_id');
            $table->integer('pvar_n1');
            $table->string('pvar_name1', 20);
            $table->integer('pvar_n2');
            $table->string('pvar_name2', 20);
            $table->decimal('pvar_price', 10, 2);
            $table->string('pvar_sku', 20);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_product_variants');
    }
}
