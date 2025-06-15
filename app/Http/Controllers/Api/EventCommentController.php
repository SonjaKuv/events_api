<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventComment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventCommentController extends Controller
{
    /**
     * Получить список комментариев события
     */
    public function index(Event $event): JsonResponse
    {
        // Проверяем доступ к событию
        if (!$this->checkEventAccess($event)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $comments = $event->comments()
            ->with('user:id,login,avatar')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    /**
     * Создать новый комментарий
     */
    public function store(Request $request, Event $event): JsonResponse
    {
        // Проверяем доступ к событию
        if (!$this->checkEventAccess($event)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:1|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $comment = EventComment::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        // Загружаем связанные данные для ответа
        $comment->load('user:id,login,avatar');

        return response()->json($comment, 201);
    }

    /**
     * Обновить комментарий
     */
    public function update(Request $request, Event $event, EventComment $comment): JsonResponse
    {
        // Проверяем доступ к событию
        if (!$this->checkEventAccess($event)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        // Проверяем, что комментарий принадлежит этому событию
        if ($comment->event_id !== $event->id) {
            return response()->json(['message' => 'Комментарий не найден'], 404);
        }

        // Проверяем, что пользователь является автором комментария
        if (!$comment->isAuthor(Auth::id())) {
            return response()->json(['message' => 'Вы можете редактировать только свои комментарии'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:1|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $comment->update([
            'content' => $request->content,
        ]);

        $comment->load('user:id,login,avatar');

        return response()->json($comment);
    }

    /**
     * Удалить комментарий
     */
    public function destroy(Event $event, EventComment $comment): JsonResponse
    {
        // Проверяем доступ к событию
        if (!$this->checkEventAccess($event)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        // Проверяем, что комментарий принадлежит этому событию
        if ($comment->event_id !== $event->id) {
            return response()->json(['message' => 'Комментарий не найден'], 404);
        }

        // Проверяем права на удаление (автор комментария или создатель события)
        if (!$comment->isAuthor(Auth::id()) && $event->user_id !== Auth::id()) {
            return response()->json(['message' => 'Недостаточно прав для удаления комментария'], 403);
        }

        $comment->delete();

        return response()->json(null, 204);
    }

    /**
     * Проверка доступа к событию
     */
    private function checkEventAccess(Event $event): bool
    {
        // Если событие публичное - доступ разрешен
        if ($event->is_public) {
            return true;
        }

        // Если пользователь не авторизован - доступ запрещен
        if (!Auth::check()) {
            return false;
        }

        // Если пользователь создатель события - доступ разрешен
        if ($event->user_id === Auth::id()) {
            return true;
        }

        // Проверяем whitelist
        return in_array(Auth::id(), $event->whitelist ?? []);
    }
}
