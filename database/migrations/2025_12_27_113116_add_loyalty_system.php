<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoyaltySystem extends Migration
{
    public function up()
    {
        // 1. Modify Users Table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'points')) {
                $table->integer('points')->default(0)->after('invite_code');
                $table->enum('role', ['customer', 'staff', 'entertainer'])->default('customer')->after('points');
            }
        });

        // 2. Modify Diamond Packs
        if (Schema::hasTable('diamond_packs')) {
            Schema::table('diamond_packs', function (Blueprint $table) {
                if (!Schema::hasColumn('diamond_packs', 'points_reward')) {
                    $table->integer('points_reward')->default(0)->comment('Points given when purchased');
                }
            });
        }

        // 3. Rewards Table (ตารางใหม่ ใช้ id() ปกติ เป็น UnsignedBigInt)
        Schema::create('rewards', function (Blueprint $table) {
            $table->id(); 
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('required_points');
            $table->enum('type', ['discount', 'gift']);
            $table->enum('discount_type', ['percent', 'fixed'])->nullable();
            $table->integer('discount_value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Redemptions Table
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            
            // --- จุดสำคัญ: ต้อง Type ให้ตรงแม่ ---
            $table->integer('user_id'); // ใช้ integer ตาม users เดิมของพี่
            $table->unsignedBigInteger('reward_id'); // ใช้ unsignedBigInteger ตาม rewards ที่เพิ่งสร้าง
            // --------------------------------

            $table->string('code')->unique()->nullable();
            $table->integer('points_used');
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reward_id')->references('id')->on('rewards')->onDelete('cascade');

            // Indexes (ตามสไตล์โปรเจคพี่)
            $table->index('user_id');
            $table->index('reward_id');
        });

        // 5. Point Transactions
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            
            // --- จุดสำคัญ: ใช้ integer ตาม users เดิม ---
            $table->integer('user_id'); 
            // ---------------------------------------

            $table->integer('amount');
            $table->enum('type', ['earn', 'redeem', 'adjust']);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('related_id')->nullable(); // Polymorphic มักจะเป็น BigInt หรือ String ก็ได้แล้วแต่ design แต่ BigInt ปลอดภัยสุดสำหรับ id
            $table->string('related_type')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index(['related_id', 'related_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('redemptions');
        Schema::dropIfExists('rewards');

        if (Schema::hasTable('diamond_packs')) {
            Schema::table('diamond_packs', function (Blueprint $table) {
                $table->dropColumn('points_reward');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['points', 'role']);
        });
    }
}