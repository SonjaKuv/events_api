<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'user_events')) {
                $table->dropColumn('user_events');
            }
            if (Schema::hasColumn('users', 'join_events')) {
                $table->dropColumn('join_events');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_events')) {
                $table->json('user_events')->nullable();
            }
            if (!Schema::hasColumn('users', 'join_events')) {
                $table->json('join_events')->nullable();
            }
        });
    }
}; 
