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
        Schema::table('users', function (Blueprint $table) {
            // Приводим нужные столбцы к JSON
            $table->json('user_events')->nullable()->change();
            $table->json('join_events')->nullable()->change();
            $table->json('friends')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Возвращаем поля к текстовому типу
            $table->text('user_events')->nullable()->change();
            $table->text('join_events')->nullable()->change();
            $table->text('friends')->nullable()->change();
        });
    }
};
