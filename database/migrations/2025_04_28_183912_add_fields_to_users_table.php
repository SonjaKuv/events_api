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
            $table->string('avatar')->nullable();
            $table->bigInteger('telegram_id')->nullable();
            $table->bigInteger('vk_id')->nullable();
            $table->bigInteger('instagram_id')->nullable();
            $table->json('user_events')->nullable();
            $table->json('join_events')->nullable();
            $table->json('friends')->nullable();
            $table->boolean('is_deleted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
            $table->dropColumn('telegram_id');
            $table->dropColumn('vk_id');
            $table->dropColumn('instagram_id');
            $table->dropColumn('user_events');
            $table->dropColumn('join_events');
            $table->dropColumn('friends');
            $table->dropColumn('is_deleted');
        });
    }
};
