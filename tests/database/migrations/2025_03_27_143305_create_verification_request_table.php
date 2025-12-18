<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificationRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verification_request', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('user_id');
            $table->string('document', 555)->nullable();
            $table->string('document_type')->nullable();
            $table->string('selfie', 555)->nullable();
            $table->string('fullname')->nullable();
            $table->boolean('status')->default(0)->comment("0=pending 1=approved");
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('verification_request');
    }
}
