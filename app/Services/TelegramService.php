<?php

namespace App\Services;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private BotApi $bot;

    public function __construct()
    {
        $token = config('services.telegram.bot_token');
        if (!$token) {
            throw new \Exception('Telegram bot token не настроен');
        }
        
        $this->bot = new BotApi($token);
    }

    /**
     * Отправить сообщение пользователю
     */
    public function sendMessage(string $chatId, string $message): bool
    {
        try {
            $this->bot->sendMessage($chatId, $message, 'HTML');
            return true;
        } catch (Exception $e) {
            Log::error('Ошибка отправки Telegram сообщения', [
                'chat_id' => $chatId,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Отправить уведомление о событии
     */
    public function sendEventNotification(string $chatId, array $eventData): bool
    {
        $message = $this->formatEventMessage($eventData);
        return $this->sendMessage($chatId, $message);
    }

    /**
     * Форматировать сообщение о событии
     */
    private function formatEventMessage(array $eventData): string
    {
        $message = "🎉 <b>Напоминание о событии!</b>\n\n";
        $message .= "📅 <b>Событие:</b> {$eventData['name']}\n";
        $message .= "📍 <b>Место:</b> {$eventData['location_name']}\n";
        $message .= "🕐 <b>Время:</b> {$eventData['start_date']} в {$eventData['start_time']}\n";
        
        if (!empty($eventData['description'])) {
            $message .= "📝 <b>Описание:</b> {$eventData['description']}\n";
        }
        
        $message .= "\n⏰ Событие начнется через час!";
        
        return $message;
    }

    /**
     * Получить информацию о боте
     */
    public function getBotInfo(): array
    {
        try {
            $me = $this->bot->getMe();
            return [
                'id' => $me->getId(),
                'username' => $me->getUsername(),
                'first_name' => $me->getFirstName(),
            ];
        } catch (Exception $e) {
            Log::error('Ошибка получения информации о боте', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Проверить валидность chat_id
     */
    public function validateChatId(string $chatId): bool
    {
        try {
            $this->bot->getChat($chatId);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
} 
