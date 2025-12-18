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
            $table->unsignedBigInteger('user_id')->constrained()->onDelete('cascade')->onUpdate('cascade')->comment('Reference to users.id');
            $table->tinyInteger('transaction_type')->comment('1=add, 2=deduct');
            $table->integer('amount')->comment('Amount of coins or currency');
            $table->integer('balance_after_transaction')->comment('Remaining balance after the transaction');
            $table->unsignedBigInteger('wallet_tag_id')->constrained()->onDelete('cascade')->onUpdate('cascade')->comment('Reference to wallet_tag.id');
            $table->timestamps();

            //
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
