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
        Schema::create('commands', function (Blueprint $table) {
            $table->id();
			$table->string('title')->nullable();
			$table->string('color', 9)->nullable();
            $table->unsignedInteger('region_id')->nullable()->comment('информация берется из простого списка "регионы"');
			$table->unsignedInteger('_sort')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('commands');
    }
};
