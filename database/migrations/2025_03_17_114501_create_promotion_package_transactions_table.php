<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionPackageTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotion_package_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key to users table
            $table->unsignedBigInteger('promotion_package_id'); // Foreign key to packages table
            $table->enum('action', ['assign', 'remove']); // Action: assign or remove
            $table->dateTime('start_date')->nullable(); // Start date of the package
            $table->dateTime('end_date')->nullable(); // End date of the package
            $table->enum('status', ['active', 'inactive']); // Status: active or inactive
            $table->unsignedBigInteger('created_by_user_id')->nullable(); // ID of the user who performed the action (if applicable)
            $table->unsignedBigInteger('created_by_admin_id')->nullable(); // ID of the admin who performed the action (if applicable)
            $table->timestamps(); // created_at and updated_at columns 
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotion_package_transactions');
    }
}
