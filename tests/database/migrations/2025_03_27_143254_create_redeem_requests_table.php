<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedeemRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redeem_requests', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('user_id')->nullable();
            $table->string('request_id', 55)->nullable();
            $table->integer('coin_amount')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->text('account_details')->nullable();
            $table->string('amount_paid')->default('0');
            $table->integer('status')->default(0)->comment("0 = pending 1=completed");
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            
            $table->foreign('user_id', 'user_id_redeem_request')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redeem_requests');
    }
}
