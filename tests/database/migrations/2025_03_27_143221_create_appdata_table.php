<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppdataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appdata', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('app_name', 55);
            $table->string('currency', 11)->nullable();
            $table->integer('min_threshold')->nullable();
            $table->string('coin_rate', 55)->default('0');
            $table->integer('min_user_live')->nullable();
            $table->integer('max_minute_live')->nullable();
            $table->integer('message_price')->default(3);
            $table->integer('reverse_swipe_price')->default(3);
            $table->integer('live_watching_price')->default(10);
            $table->string('admob_int_ios')->nullable();
            $table->string('admob_banner_ios')->nullable();
            $table->string('admob_int')->nullable();
            $table->string('admob_banner')->nullable();
            $table->boolean('is_dating')->default(1)->comment("1=yes 0=no");
            $table->integer('is_social_media')->comment("1=yes 0=no");
            $table->integer('post_description_limit');
            $table->integer('post_upload_image_limit');
            $table->string('apikey')->nullable();
            $table->string('secretkey')->nullable();
            $table->string('merchantID')->nullable();
            $table->text('authKey')->nullable();
            $table->string('loginPageImage')->nullable();
            $table->string('registerPageImage')->nullable();
            $table->string('welcomePageImage')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appdata');
    }
}
