<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_history', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('user_id')->nullable();
            $table->string('started_at')->nullable();
            $table->string('streamed_for')->nullable();
            $table->integer('amount_collected')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            
            $table->foreign('user_id', 'user_id_live_history_const')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('live_history');
    }
}
