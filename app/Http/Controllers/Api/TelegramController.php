<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Получить информацию о привязке Telegram
     */
    public function getStatus(): JsonResponse
    {
        $user = Auth::user();
        
        return response()->json([
            'is_connected' => !empty($user->telegram_id),
            'telegram_id' => $user->telegram_id,
            'bot_info' => $this->telegramService->getBotInfo(),
        ]);
    }

    /**
     * Сгенерировать код для привязки аккаунта
     */
    public function generateLinkCode(): JsonResponse
    {
        $user = Auth::user();
        
        // Генерируем уникальный код
        $linkCode = Str::random(8);
        
        // Сохраняем код в кэше на 10 минут
        cache()->put("telegram_link_{$linkCode}", $user->id, 600);
        
        return response()->json([
            'link_code' => $linkCode,
            'expires_in' => 600, // 10 минут
            'instructions' => 'Отправьте команду /link ' . $linkCode . ' боту в Telegram'
        ]);
    }

    /**
     * Привязать аккаунт по коду (вызывается из webhook)
     */
    public function linkAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:8',
            'telegram_id' => 'required|string',
            'username' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Проверяем код
        $userId = cache()->get("telegram_link_{$request->code}");
        if (!$userId) {
            return response()->json(['message' => 'Неверный или истекший код'], 400);
        }

        // Находим пользователя
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        // Проверяем, не привязан ли уже этот Telegram аккаунт к другому пользователю
        $existingUser = \App\Models\User::where('telegram_id', $request->telegram_id)->first();
        if ($existingUser && $existingUser->id !== $user->id) {
            return response()->json(['message' => 'Этот Telegram аккаунт уже привязан к другому пользователю'], 400);
        }

        // Привязываем аккаунт
        $user->update([
            'telegram_id' => $request->telegram_id,
        ]);

        // Удаляем код из кэша
        cache()->forget("telegram_link_{$request->code}");

        // Отправляем подтверждение в Telegram
        $this->telegramService->sendMessage(
            $request->telegram_id,
            "✅ <b>Аккаунт успешно привязан!</b>\n\nТеперь вы будете получать уведомления о ваших событиях."
        );

        return response()->json(['message' => 'Аккаунт успешно привязан']);
    }

    /**
     * Отвязать Telegram аккаунт
     */
    public function unlinkAccount(): JsonResponse
    {
        $user = Auth::user();
        
        if (empty($user->telegram_id)) {
            return response()->json(['message' => 'Telegram аккаунт не привязан'], 400);
        }

        // Отправляем уведомление об отвязке
        $this->telegramService->sendMessage(
            $user->telegram_id,
            "❌ <b>Аккаунт отвязан</b>\n\nВы больше не будете получать уведомления о событиях."
        );

        // Отвязываем аккаунт
        $user->update(['telegram_id' => null]);

        return response()->json(['message' => 'Telegram аккаунт отвязан']);
    }

    /**
     * Отправить тестовое сообщение
     */
    public function sendTestMessage(): JsonResponse
    {
        $user = Auth::user();
        
        if (empty($user->telegram_id)) {
            return response()->json(['message' => 'Telegram аккаунт не привязан'], 400);
        }

        $success = $this->telegramService->sendMessage(
            $user->telegram_id,
            "🧪 <b>Тестовое сообщение</b>\n\nЕсли вы видите это сообщение, значит уведомления работают корректно!"
        );

        if ($success) {
            return response()->json(['message' => 'Тестовое сообщение отправлено']);
        } else {
            return response()->json(['message' => 'Ошибка отправки сообщения'], 500);
        }
    }
} 
