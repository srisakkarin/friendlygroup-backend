<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->boolean('is_block')->default(0)->comment("0=no 1=blocked");
            $table->integer('gender')->nullable()->comment("1=male 2=female 3=other");
            $table->mediumText('savedprofile')->nullable();
            $table->string('interests')->nullable();
            $table->integer('age')->nullable();
            $table->string('identity')->nullable();
            $table->string('username')->nullable();
            $table->string('fullname')->nullable();
            $table->text('instagram')->nullable();
            $table->text('youtube')->nullable();
            $table->text('facebook')->nullable();
            $table->string('live')->nullable();
            $table->text('bio')->nullable();
            $table->string('about', 999)->nullable();
            $table->string('lattitude')->nullable();
            $table->string('longitude')->nullable();
            $table->integer('login_type')->nullable();
            $table->text('device_token')->nullable();
            $table->text('blocked_users')->nullable();
            $table->integer('wallet')->default(0);
            $table->integer('total_gifts_sent')->default(0);
            $table->integer('total_gifts_received')->default(0);
            $table->integer('total_collected')->default(0);
            $table->integer('total_streams')->default(0);
            $table->integer('device_type')->nullable();
            $table->integer('is_notification')->default(1)->comment("1=yes 0=no");
            $table->boolean('is_verified')->default(0)->comment("0=no 1=pending 2=verified");
            $table->integer('show_on_map')->default(1);
            $table->integer('anonymous')->default(0);
            $table->integer('is_video_call')->default(1);
            $table->integer('can_go_live')->default(0)->comment("0=no 1=pending 2=yes");
            $table->boolean('is_live_now')->default(0)->comment("0=no 1=yes");
            $table->boolean('is_fake')->default(0)->comment("0=no 1=yes");
            $table->string('password')->nullable()->comment("only for fake users");
            $table->integer('following')->default(0);
            $table->integer('followers')->default(0);
            $table->integer('gender_preferred')->default(1)->comment("1 = Male / 2 = Female / 3 = Both	");
            $table->integer('age_preferred_min')->default(0);
            $table->integer('age_preferred_max')->default(100);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('update_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
