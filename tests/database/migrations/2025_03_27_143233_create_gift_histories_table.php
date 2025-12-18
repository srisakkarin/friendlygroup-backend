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
            $table->integer('amount')->comment("Amount of coins spent on the gift");
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
            
            $table->foreign('gift_id', 'gift_histories_gift_id_foreign')->references('id')->on('gifts')->onDelete('cascade');
            $table->foreign('recipient_id', 'gift_histories_recipient_id_foreign')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sender_id', 'gift_histories_sender_id_foreign')->references('id')->on('users')->onDelete('cascade');
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
