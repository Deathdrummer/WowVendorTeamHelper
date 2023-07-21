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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->default(null);
            $table->string('pseudoname')->nullable()->default(null);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable()->default(null);
            $table->string('temporary_password')->nullable()->default(null);
			$table->string('locale', 3)->nullable()->default(null);
			$table->json('settings')->nullable()->default(null)->comment('Настройки пользователя');
			$table->unsignedInteger('_sort')->nullable()->default(0);
            $table->rememberToken();
			$table->unsignedBigInteger('_sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
