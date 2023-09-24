<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up():void {
        Schema::create('order_raw_data_history', function (Blueprint $table) {
            $table->id();
			
			$table->unsignedBigInteger('order_id')->nullable();
			$table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
			
			$table->unsignedBigInteger('from_id')->nullable();
			$table->unsignedTinyInteger('user_type')->nullable();
			$table->text('data')->nullable();
			
			$table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down():void {
        Schema::dropIfExists('order_raw_data_history');
    }
};
