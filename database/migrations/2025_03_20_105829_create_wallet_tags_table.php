<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag_name', 255)->unique()->comment('Name of the tag (e.g., buy_package, watch_live)');
            $table->text('description')->nullable()->comment('Description of the tag');
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
        Schema::dropIfExists('wallet_tags');
    }
}
