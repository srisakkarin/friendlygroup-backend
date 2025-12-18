<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferencesToWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            //
            $table->integer('user_id')->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('wallet_tag_id')->references('id')->on('wallet_tags')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            //
        });
    }
}
