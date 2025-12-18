<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id')->constrained()->onDelete('cascade'); // เชื่อมโยงกับ jobs
            $table->string('image'); // ที่อยู่ของรูปภาพ
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
        Schema::dropIfExists('job_images');
    }
}
