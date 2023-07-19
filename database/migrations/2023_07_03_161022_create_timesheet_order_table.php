<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('timesheet_order', function (Blueprint $table) {
            $table->unsignedBigInteger('timesheet_id')->nullable();
			$table->foreign('timesheet_id')->references('id')->on('timesheet')->onDelete('cascade');
			
			$table->unsignedBigInteger('order_id')->nullable();
			$table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
			
			$table->unsignedInteger('doprun')->nullable();
			//$table->boolean('viewed')->default(false)->comment('Договор просмотрен');
			//$table->boolean('pinned')->default(false)->comment('Договор закреплен');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('timesheet_order');
    }
};
