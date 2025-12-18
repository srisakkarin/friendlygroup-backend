<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateShopProductsTableWithStatusAndVisibility extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_products', function (Blueprint $table) {
            // Add 'status' column for admin control
            // pending: Product is awaiting review or approval
            // active: Product is available and visible (if published)
            // inactive: Product is not available, typically temporarily
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending')->after('pro_delete');

            // Add 'visibility' column for customer view control
            // published: Product is visible to customers
            // unpublished: Product is hidden from customers
            $table->enum('visibility', ['published', 'unpublished'])->default('unpublished')->after('status');
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
