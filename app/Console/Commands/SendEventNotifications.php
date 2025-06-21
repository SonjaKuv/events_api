<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendEventNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'events:send-notifications {--hours=1 : Hours before event to send notification}';

    /**
     * The console command description.
     */
    protected $description = 'Send Telegram notifications for upcoming events';

    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $this->info("Поиск событий, которые начнутся через {$hours} час(ов)...");

        // Вычисляем временной диапазон
        $now = Carbon::now();
        $targetTime = $now->copy()->addHours($hours);
        
        // Диапазон ±5 минут для учета времени выполнения cron
        $startTime = $targetTime->copy()->subMinutes(5);
        $endTime = $targetTime->copy()->addMinutes(5);

        // Находим события, которые начинаются в указанное время
        $events = Event::whereBetween('start_datetime', [
                $startTime->format('Y-m-d H:i:s'),
                $endTime->format('Y-m-d H:i:s')
            ])
            ->with(['user', 'participants.user'])
            ->get();

        if ($events->isEmpty()) {
            $this->info('Событий для уведомления не найдено.');
            return self::SUCCESS;
        }

        $sentCount = 0;
        $errorCount = 0;

        foreach ($events as $event) {
            $this->info("Обработка события: {$event->name}");

            // Получаем всех пользователей, которым нужно отправить уведомление
            $usersToNotify = collect();

            // Добавляем создателя события
            if ($event->user && $event->user->telegram_id) {
                $usersToNotify->push($event->user);
            }

            // Добавляем участников с подтвержденным статусом
            foreach ($event->participants as $participant) {
                if ($participant->pivot->status === 'accepted' && $participant->telegram_id) {
                    $usersToNotify->push($participant);
                }
            }

            // Убираем дубликаты
            $usersToNotify = $usersToNotify->unique('id');

            if ($usersToNotify->isEmpty()) {
                $this->warn("Для события '{$event->name}' нет пользователей с привязанным Telegram");
                continue;
            }

            // Отправляем уведомления
            foreach ($usersToNotify as $user) {
                $success = $this->sendNotification($user->telegram_id, $event);
                
                if ($success) {
                    $sentCount++;
                    $this->info("✓ Уведомление отправлено пользователю {$user->login}");
                } else {
                    $errorCount++;
                    $this->error("✗ Ошибка отправки уведомления пользователю {$user->login}");
                }
            }
        }

        $this->info("Обработка завершена. Отправлено: {$sentCount}, ошибок: {$errorCount}");
        
        return self::SUCCESS;
    }

    /**
     * Отправить уведомление пользователю
     */
    private function sendNotification(string $telegramId, Event $event): bool
    {
        try {
            $eventData = [
                'name' => $event->name,
                'location_name' => $event->location_name,
                'start_date' => $event->start_datetime->format('d.m.Y'),
                'start_time' => $event->start_datetime->format('H:i'),
                'description' => $event->description,
            ];

            return $this->telegramService->sendEventNotification($telegramId, $eventData);

        } catch (\Exception $e) {
            Log::error('Ошибка отправки уведомления о событии', [
                'telegram_id' => $telegramId,
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}
