<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up():void {
        Schema::create('screenshots_history', function (Blueprint $table) {
            $table->id();
			$table->integer('timesheet_id')->nullable()->comment('ID события');
			$table->integer('from_id')->comment('Кем отправлен');
			$table->enum('user_type', ['admin','client'])->comment('Тип пользователя админ или клиент');
			
			$table->string('screenshot')->nullable()->comment('Скриншот');
			$table->text('comment')->nullable()->comment('Комментарий');
			$table->json('stat')->nullable()->comment('Типы заказов со списком заказов');
			$table->boolean('send_to_slack')->comment('Отправлено ли в слак');
			
			$table->timestamp('date_add')->comment('Дата добавления');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down():void {
        Schema::dropIfExists('screenshots_history');
    }
};
