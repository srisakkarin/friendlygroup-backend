<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoyaltySystemTables extends Migration
{
    public function up()
    {
        // 1. Update Users Table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'points')) {
                $table->integer('points')->default(0)->after('invite_code');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['customer', 'staff', 'entertainer'])->default('customer')->after('points');
            }
        });

        // 2. Update Diamond Packs Table
        if (Schema::hasTable('diamond_packs')) {
            Schema::table('diamond_packs', function (Blueprint $table) {
                if (!Schema::hasColumn('diamond_packs', 'points_reward')) {
                    $table->integer('points_reward')->default(0)->after('price');
                }
            });
        }

        // 3. Create Rewards Table
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('required_points');
            $table->enum('type', ['discount', 'gift']);
            $table->enum('discount_type', ['percent', 'fixed'])->nullable();
            $table->integer('discount_value')->nullable(); // e.g., 10 for 10%, 50 for 50 Baht
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Create Redemptions Table
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->unsignedBigInteger('reward_id');
            $table->integer('points_used');
            $table->boolean('is_used')->default(false);
            $table->dateTime('used_at')->nullable();
            $table->decimal('total_price', 10, 2)->nullable(); // For discount calculation logging
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reward_id')->references('id')->on('rewards')->onDelete('cascade');
        });

        // 5. Create Point Transactions Table
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id'); 
            $table->integer('amount'); // Can be positive (earn) or negative (use/admin remove)
            $table->enum('type', ['earn', 'use', 'adjust']);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('related_id')->nullable(); // e.g., redemption_id or diamond_pack_id
            $table->string('related_type')->nullable(); // Polymorphic-like or simple string
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('redemptions');
        Schema::dropIfExists('rewards');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['points', 'role']);
        });
        
        if (Schema::hasTable('diamond_packs')) {
            Schema::table('diamond_packs', function (Blueprint $table) {
                $table->dropColumn('points_reward');
            });
        }
    }
}