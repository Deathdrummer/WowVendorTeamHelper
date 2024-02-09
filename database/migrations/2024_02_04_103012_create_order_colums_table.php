<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up():void {
        Schema::create('order_colums', function (Blueprint $table) {
            $table->id();
			$table->integer('user_id')->comment('ID пользователя ЛК');
			$table->json('visible')->nullable()->comment('Массив столбцов, которые нужно отобразить и их сортировка. По-умолчанию все');
			$table->json('width')->nullable()->comment('Объект столбцов со значениями ширины');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down():void {
        Schema::dropIfExists('order_colums');
    }
};
