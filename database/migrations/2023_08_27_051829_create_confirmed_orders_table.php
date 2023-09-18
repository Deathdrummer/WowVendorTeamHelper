<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('confirmed_orders', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('order_id')->nullable();
			$table->unsignedBigInteger('timesheet_id')->nullable();
			$table->unsignedBigInteger('from_id')->nullable();
			$table->unsignedBigInteger('confirmed_from_id')->nullable();
			$table->boolean('confirm')->default(0);
			$table->timestamp('date_add')->nullable();
			$table->timestamp('date_confirm')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('confirmed_orders');
    }
};
