<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Автор события

            $table->string('name');
            $table->date('start_date');
            $table->time('start_time');
            $table->boolean('is_long')->default(false);
            $table->date('end_date')->nullable();

            $table->string('location_name')->nullable();
            $table->json('location_coords')->nullable();
            $table->json('weather')->nullable();

            $table->string('image')->nullable();

            $table->text('description')->nullable();

            $table->json('participants')->nullable(); // EventParticipants
            $table->string('link')->nullable();

            $table->boolean('is_public')->default(true);
            $table->json('whitelist')->nullable();
            $table->json('tags')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
