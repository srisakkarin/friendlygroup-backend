<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkerProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->constrained()->onDelete('cascade'); // เชื่อมโยงกับ users
            $table->text('bio')->nullable(); // ประวัติส่วนตัว
            $table->string('profile_picture')->nullable(); // รูปโปรไฟล์
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
        Schema::dropIfExists('worker_profiles');
    }
}
