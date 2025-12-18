<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHtmlContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('html_contents', function (Blueprint $table) {
            $table->id();
            $table->morphs('contentable'); // Polymorphic relationship (เช่น product, job, etc.)
            $table->text('content'); // HTML content
            $table->string('status')->default('active'); // สถานะ
            $table->string('version')->nullable(); // เวอร์ชัน
            $table->string('meta_title')->nullable(); // meta title
            $table->string('meta_description')->nullable(); // meta description
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
        Schema::dropIfExists('html_contents');
    }
}
