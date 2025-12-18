<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPromotionPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_promotion_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('promotion_package_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_promotion_packages');
    }
}
