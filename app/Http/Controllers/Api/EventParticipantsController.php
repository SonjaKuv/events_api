<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventParticipants;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventParticipantsController extends Controller
{
    /**
     * Получить список участников события
     */
    public function index(Event $event): JsonResponse
    {
        // Проверяем доступ к событию
        if (!$this->checkEventAccess($event)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $participants = $event->participants()
            ->withPivot('status', 'created_at')
            ->get();

        return response()->json($participants);
    }

    /**
     * Присоединиться к событию
     */
    public function join(Event $event): JsonResponse
    {
        // Проверяем доступ к событию
        if (!$this->checkEventAccess($event)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $userId = Auth::id();

        // Проверяем, не является ли пользователь создателем события
        if ($event->user_id === $userId) {
            return response()->json(['message' => 'Вы не можете подписаться на собственное событие - вы уже организатор'], 400);
        }

        // Проверяем, не является ли пользователь уже участником
        if ($event->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Вы уже участник этого события'], 400);
        }

        // Добавляем пользователя как участника
        $event->participants()->attach($userId, [
            'status' => EventParticipants::STATUS_ACCEPTED
        ]);

        return response()->json(['message' => 'Вы успешно присоединились к событию']);
    }

    /**
     * Покинуть событие
     */
    public function leave(Event $event): JsonResponse
    {
        $userId = Auth::id();

        // Проверяем, не является ли пользователь создателем события
        if ($event->user_id === $userId) {
            return response()->json(['message' => 'Вы не можете покинуть собственное событие - вы организатор'], 400);
        }

        // Проверяем, является ли пользователь участником
        if (!$event->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Вы не являетесь участником этого события'], 400);
        }

        // Удаляем пользователя из участников
        $event->participants()->detach($userId);

        return response()->json(['message' => 'Вы покинули событие']);
    }

    /**
     * Изменить статус участия
     */
    public function updateStatus(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', EventParticipants::getStatuses())
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = Auth::id();

        // Проверяем, не является ли пользователь создателем события
        if ($event->user_id === $userId) {
            return response()->json(['message' => 'Вы не можете изменить статус участия в собственном событии - вы организатор'], 400);
        }

        // Проверяем, является ли пользователь участником
        if (!$event->participants()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Вы не являетесь участником этого события'], 400);
        }

        // Обновляем статус
        $event->participants()->updateExistingPivot($userId, [
            'status' => $request->status
        ]);

        return response()->json(['message' => 'Статус участия обновлен']);
    }

    /**
     * Проверка доступа к событию (копия из EventsController)
     */
    private function checkEventAccess(Event $event): bool
    {
        if ($event->is_public) {
            return true;
        }

        if (!Auth::check()) {
            return false;
        }

        if ($event->user_id === Auth::id()) {
            return true;
        }

        return in_array(Auth::id(), $event->whitelist ?? []);
    }
} 
