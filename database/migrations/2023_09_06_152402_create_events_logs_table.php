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
        Schema::create('events_logs', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('from_id')->nullable();
			$table->unsignedTinyInteger('user_type')->nullable();
			$table->unsignedTinyInteger('event_type')->nullable();
			$table->unsignedTinyInteger('group')->nullable();
			$table->json('info')->nullable();
            $table->timestamp('datetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events_logs');
    }
};
