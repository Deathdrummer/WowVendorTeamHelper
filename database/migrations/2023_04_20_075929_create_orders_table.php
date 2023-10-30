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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
			$table->longText('raw_data');
            $table->string('order')->nullable();
            $table->unsignedInteger('order_type')->nullable();
            $table->boolean('ot_changed')->default(0)->comment('Изменен ли вручную тип заказа');
            $table->boolean('manually')->default(0)->comment('Создан ли заказ вручную');
			$table->decimal('price', 10, 2)->nullable()->default(0);
            $table->string('server_name')->nullable();
            $table->string('link')->nullable();
			$table->timestamp('date')->nullable();
			$table->unsignedInteger('timezone_id')->nullable();
			$table->integer('status')->default(0)->comment('new: 0, wait: -1, cancel: -2, ready: 1, doprun: 2');
			$table->timestamp('date_add')->nullable();
		    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('orders');
    }
};
