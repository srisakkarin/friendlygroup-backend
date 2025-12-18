<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notification', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('my_user_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('item_id')->nullable();
            $table->integer('type')->nullable()->comment("1 = Follow, 2 = Comment, 3 = Post Like, 4 Like Profile");
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            
            $table->foreign('my_user_id', 'user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_notification');
    }
}
