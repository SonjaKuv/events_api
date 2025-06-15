<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Возвращает всех пользователей
     * @return JsonResponse
     */
    public function getUsers()
    {
        return response()->json(User::all());
    }

    /**
     * Добавляет нового пользователя в бд
     * @param Request $request
     * @return JsonResponse
     */
    public function postUser(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'avatar' => 'mimes:jpeg,jpg,png|max:2048',
            'telegram_id' => 'nullable|numeric',
            'vk_id' => 'nullable|numeric',
            'instagram_id' => 'nullable|numeric',
            'user_events' => 'nullable|array',
            'join_events' => 'nullable|array',
            'friends' => 'nullable|array',
            'is_deleted' => 'nullable|boolean',
        ]);

        // Загрузка аватара (если он был загружен)
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        } else {
            $avatarPath = null;
        }

        $user = User::create([
            'login' => $validated['login'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar' => $avatarPath,
            'telegram_id' => $validated['telegram_id'] ?? null,
            'vk_id' => $validated['vk_id'] ?? null,
            'instagram_id' => $validated['instagram_id'] ?? null,
            'user_events' => $validated['user_events'] ?? [],
            'join_events' => $validated['join_events'] ?? [],
            'friends' => $validated['friends'] ?? [],
            'is_deleted' => $validated['is_deleted'] ?? false,
        ]);

        return response()->json($user, 201);
    }

    /**
     * Обновляет инфо о пользователе
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'login' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8',
            'avatar' => 'sometimes|file|mimes:jpeg,jpg,png|max:2048',
            'telegram_id' => 'nullable|numeric',
            'vk_id' => 'nullable|numeric',
            'instagram_id' => 'nullable|numeric',
            'user_events' => 'nullable|array',
            'join_events' => 'nullable|array',
            'friends' => 'nullable|array',
            'is_deleted' => 'nullable|boolean',
        ]);

        // Обновление аватара, если новый загружен
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        // Хешируем пароль, если он есть в запросе
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }

    /**
     * Возвращает полную информацию о пользователе по id
     * @param $id
     * @return JsonResponse
     */
    public function getFullUserInfo($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        return response()->json($user);
    }

    /**
     * Возвращает частичную информацию о пользователе по id
     * @param $id
     * @return JsonResponse
     */
    public function getUserInfo($id)
    {
        $user = User::find($id, ['id', 'login', 'avatar']);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        return response()->json($user);
    }

    /**
     * Возвращает друзей пользователя по его id
     * @param $id
     * @return JsonResponse
     */
    public function getFriends($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $friendIds = $user->friends ?? [];

        if (is_string($friendIds)) {
            $friendIds = json_decode($friendIds, true);
        }

        $friends = User::whereIn('id', $friendIds)->get(['id', 'login', 'avatar']);

        return response()->json($friends);
    }


    /**
     * Мягкое удаление пользователя (установка is_deleted = true)
     * @param $id
     * @return JsonResponse
     */
    public function destroyUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $user->is_deleted = true;
        $user->save();

        return response()->json(['message' => 'Пользователь успешно удалён']);
    }
}
