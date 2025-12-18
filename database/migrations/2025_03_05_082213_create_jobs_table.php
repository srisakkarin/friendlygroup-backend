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
            $table->unsignedBigInteger('user_id')->constrained()->onDelete('cascade'); // เชื่อมโยงกับ users
            $table->string('title'); // ชื่องาน
            $table->text('description'); // รายละเอียดงาน
            $table->decimal('starting_price', 10, 2); // ราคาเริ่มต้น
            $table->enum('status', ['draft', 'public'])->default('draft'); // สถานะงาน
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
        Schema::dropIfExists('jobs');
    }
}
