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
        Schema::create('timesheet', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('command_id')->nullable()->comment('информация берется из табл. "команды"');
            $table->unsignedInteger('event_type_id')->nullable()->comment('информация берется из табл. "типы событий". одной строкой "название-сложность"');
            $table->unsignedInteger('timesheet_period_id')->nullable()->comment('принадлежность к париоду');
			$table->timestamp('datetime')->nullable();
			$table->unsignedInteger('_sort')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('timesheet');
    }
};
