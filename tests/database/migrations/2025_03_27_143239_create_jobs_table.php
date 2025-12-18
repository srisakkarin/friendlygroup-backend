<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('worker_profile_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->decimal('starting_price', 10, 2);
            $table->enum('status', ['draft', 'public'])->default('draft');
            $table->timestamps();
            
            $table->foreign('category_id', 'jobs_category_id_foreign')->references('id')->on('job_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
