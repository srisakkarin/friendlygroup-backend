<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInAppImageFieldToAppdataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appdata', function (Blueprint $table) {
            $table->after('authKey',function($table){
                $table->string('loginPageImage')->nullable();
                $table->string('registerPageImage')->nullable();
                $table->string('welcomePageImage')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_image_field_to_appdata', function (Blueprint $table) {
            //
        });
    }
}
