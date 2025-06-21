<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Объединяем start_date и start_time в один столбец start_datetime
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Добавляем новый столбец start_datetime
            $table->datetime('start_datetime')->nullable()->after('name');
        });

        // Объединяем данные из start_date и start_time в start_datetime
        DB::statement("
            UPDATE events 
            SET start_datetime = CONCAT(start_date, ' ', start_time)
            WHERE start_date IS NOT NULL AND start_time IS NOT NULL
        ");

        Schema::table('events', function (Blueprint $table) {
            // Удаляем старые столбцы
            $table->dropColumn(['start_date', 'start_time']);
            
            // Делаем start_datetime обязательным после переноса данных
            $table->datetime('start_datetime')->nullable(false)->change();
        });
    }

    /**
     * Разделяем start_datetime обратно на start_date и start_time
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Добавляем обратно старые столбцы
            $table->date('start_date')->nullable()->after('name');
            $table->time('start_time')->nullable()->after('start_date');
        });

        // Разделяем start_datetime обратно на дату и время
        DB::statement("
            UPDATE events 
            SET start_date = DATE(start_datetime),
                start_time = TIME(start_datetime)
            WHERE start_datetime IS NOT NULL
        ");

        Schema::table('events', function (Blueprint $table) {
            // Удаляем столбец start_datetime
            $table->dropColumn('start_datetime');
            
            // Делаем старые столбцы обязательными
            $table->date('start_date')->nullable(false)->change();
            $table->time('start_time')->nullable(false)->change();
        });
    }
}; 
