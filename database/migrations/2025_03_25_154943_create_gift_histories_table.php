<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_histories', function (Blueprint $table) {
            $table->integer('sender_id');
            $table->integer('recipient_id');
            $table->integer('gift_id');
            $table->integer('amount')->comment('Amount of coins spent on the gift');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('gift_id')->references('id')->on('gifts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gift_histories');
    }
}
