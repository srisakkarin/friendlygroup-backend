<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevenueSharingRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revenue_sharing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('action_key')->unique();
            $table->decimal('company_percent', 5, 2)->default(0);
            $table->decimal('customer_percent', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('revenue_sharing_rules');
    }
}
