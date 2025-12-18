<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_requests', function (Blueprint $table) {
            $table->id();
            // Relationships
            $table->integer('user_id'); // ID ของลูกค้า
            $table->unsignedBigInteger('worker_profile_id'); // ID ของ worker_profile
            $table->unsignedBigInteger('job_id'); // ID ของงานที่ลูกค้าสนใจ

            // Columns
            $table->text('description')->nullable(); // รายละเอียดเพิ่มเติมที่ลูกค้ากรอก
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending'); // สถานะคำขอ

            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('worker_profile_id')->references('id')->on('worker_profiles')->onDelete('cascade');
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('worker_profile_id');
            $table->index('job_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_requests');
    }
}
