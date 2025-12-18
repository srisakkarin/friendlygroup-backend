<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentSettingToAppdataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appdata', function (Blueprint $table) {
            $table->string('apikey')->nullable();
            $table->string('secretkey')->nullable();
            $table->string('merchantID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appdata', function (Blueprint $table) {
            //
        });
    }
}
