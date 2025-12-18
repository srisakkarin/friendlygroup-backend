<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdToShopProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->constrained()->onDelete('cascade')->after('pro_mypro_id')->nullable(); 
            // $table->string('description')->nullable()->after('duration_days');
            $table->foreign('category_id')->references('id')->on('shop_product_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_products', function (Blueprint $table) {
            //
        });
    }
}
