<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCalculateWithToRevenueSharingRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('revenue_sharing_rules', function (Blueprint $table) {
            $table->enum('calculate_with',['fixed','percentage'])->nullable()->after('customer_percent')->comment('fixed or percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('revenue_sharing_rules', function (Blueprint $table) {
            //
        });
    }
}
