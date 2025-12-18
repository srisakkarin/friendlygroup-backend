<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment("Reference to users.id");
            $table->tinyInteger('transaction_type')->comment("1 = add, 2 = deduct, 3 = withdraw");
            $table->integer('amount')->comment("Amount of coins or currency");
            $table->integer('balance_after_transaction')->comment("Remaining balance after the transaction");
            $table->unsignedBigInteger('wallet_tag_id')->comment("Reference to wallet_tag.id");
            $table->timestamps();
            
            $table->foreign('user_id', 'wallet_transactions_user_id_foreign')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('wallet_tag_id', 'wallet_transactions_wallet_tag_id_foreign')->references('id')->on('wallet_tags')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_transactions');
    }
}
