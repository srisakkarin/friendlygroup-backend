<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobRequestFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_request_files', function (Blueprint $table) {
            $table->id();
            // Relationships
            $table->unsignedBigInteger('job_request_id'); // ID ของคำขอทำงาน

            // Columns
            $table->string('file_path'); // Path ของไฟล์ที่อัปโหลด

            // Timestamps
            $table->timestamps();

            // Foreign Keys
            $table->foreign('job_request_id')->references('id')->on('job_requests')->onDelete('cascade');

            // Indexes
            $table->index('job_request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_request_files');
    }
}
