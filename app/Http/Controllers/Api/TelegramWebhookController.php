<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Обработать webhook от Telegram
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $update = $request->all();
            
            // Логируем входящий запрос для отладки
            Log::info('Telegram webhook received', $update);

            // Проверяем, есть ли сообщение
            if (!isset($update['message'])) {
                return response()->json(['ok' => true]);
            }

            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $text = $message['text'] ?? '';
            $from = $message['from'];

            // Обрабатываем команды
            if (str_starts_with($text, '/')) {
                $this->handleCommand($chatId, $text, $from);
            }

            return response()->json(['ok' => true]);

        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json(['ok' => false], 500);
        }
    }

    /**
     * Обработать команду
     */
    private function handleCommand(string $chatId, string $text, array $from): void
    {
        $parts = explode(' ', $text);
        $command = $parts[0];

        switch ($command) {
            case '/start':
                $this->handleStartCommand($chatId, $from);
                break;
                
            case '/link':
                $code = $parts[1] ?? null;
                $this->handleLinkCommand($chatId, $code, $from);
                break;
                
            case '/help':
                $this->handleHelpCommand($chatId);
                break;
                
            default:
                $this->handleUnknownCommand($chatId);
                break;
        }
    }

    /**
     * Обработать команду /start
     */
    private function handleStartCommand(string $chatId, array $from): void
    {
        $firstName = $from['first_name'] ?? 'Пользователь';
        
        $message = "👋 Привет, {$firstName}!\n\n";
        $message .= "Это бот для уведомлений о событиях.\n\n";
        $message .= "Доступные команды:\n";
        $message .= "/link [код] - привязать аккаунт\n";
        $message .= "/help - показать справку\n\n";
        $message .= "Для привязки аккаунта получите код в приложении и используйте команду /link [код]";

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Обработать команду /link
     */
    private function handleLinkCommand(string $chatId, ?string $code, array $from): void
    {
        if (!$code) {
            $this->telegramService->sendMessage(
                $chatId,
                "❌ Неверный формат команды.\n\nИспользуйте: /link [код]\n\nПолучите код в приложении и повторите попытку."
            );
            return;
        }

        // Проверяем код в кэше
        $userId = cache()->get("telegram_link_{$code}");
        if (!$userId) {
            $this->telegramService->sendMessage(
                $chatId,
                "❌ Неверный или истекший код.\n\nПолучите новый код в приложении и повторите попытку."
            );
            return;
        }

        // Привязываем аккаунт
        $user = \App\Models\User::find($userId);
        if (!$user) {
            $this->telegramService->sendMessage($chatId, "❌ Ошибка: пользователь не найден.");
            return;
        }

        // Проверяем, не привязан ли уже этот Telegram аккаунт
        $existingUser = \App\Models\User::where('telegram_id', $chatId)->first();
        if ($existingUser && $existingUser->id !== $user->id) {
            $this->telegramService->sendMessage(
                $chatId,
                "❌ Этот Telegram аккаунт уже привязан к другому пользователю."
            );
            return;
        }

        // Привязываем аккаунт
        $user->update(['telegram_id' => $chatId]);
        
        // Удаляем код из кэша
        cache()->forget("telegram_link_{$code}");

        $this->telegramService->sendMessage(
            $chatId,
            "✅ <b>Аккаунт успешно привязан!</b>\n\nТеперь вы будете получать уведомления о ваших событиях."
        );
    }

    /**
     * Обработать команду /help
     */
    private function handleHelpCommand(string $chatId): void
    {
        $message = "📖 <b>Справка по командам:</b>\n\n";
        $message .= "/start - начать работу с ботом\n";
        $message .= "/link [код] - привязать аккаунт к Telegram\n";
        $message .= "/help - показать эту справку\n\n";
        $message .= "🔗 <b>Как привязать аккаунт:</b>\n";
        $message .= "1. Войдите в приложение\n";
        $message .= "2. Перейдите в настройки Telegram\n";
        $message .= "3. Нажмите 'Получить код'\n";
        $message .= "4. Отправьте команду /link [полученный код]\n\n";
        $message .= "После привязки вы будете получать уведомления о ваших событиях за час до начала.";

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Обработать неизвестную команду
     */
    private function handleUnknownCommand(string $chatId): void
    {
        $this->telegramService->sendMessage(
            $chatId,
            "❓ Неизвестная команда.\n\nИспользуйте /help для просмотра доступных команд."
        );
    }
} 
