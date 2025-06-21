<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EventsController extends Controller
{
    /**
     * Конструктор контроллера
     */
    public function __construct()
    {
        // Увеличиваем лимит времени выполнения до 1 минуты для операций с событиями
        set_time_limit(120);
        
        // Увеличиваем лимит памяти если нужно
        ini_set('memory_limit', '256M');
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

    /**
     * Получить список всех событий с пагинацией
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        
        // Простой запрос без сложных связей для начала
        $query = Event::query();

        // Если пользователь авторизован, показываем ему все доступные события
        if (Auth::check()) {
            $query->where(function ($q) {
                $q->where('is_public', true)
                    ->orWhere('user_id', Auth::id())
                    ->orWhereJsonContains('whitelist', Auth::id());
            });
        } else {
            // Если не авторизован, показываем только публичные
            $query->where('is_public', true);
        }

        // Поиск по названию и описанию
        if ($request->get('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($events);
    }

    /**
     * Создать новое событие
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_datetime' => 'required|date',
            'is_long' => 'boolean',
            'end_date' => 'required_if:is_long,true|date|after_or_equal:start_datetime',
            'location_name' => 'required|string|max:255',
            'location_coords' => 'required|array',
            'description' => 'required|string',
            'is_public' => 'boolean',
            'whitelist' => 'array',
            'tags' => 'array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = Event::create([
            'user_id' => Auth::id(),
            ...$request->all()
        ]);

        return response()->json($event, 201);
    }

    /**
     * Получить информацию о конкретном событии
     */
    public function show(Event $event): JsonResponse
    {
        if (!$this->checkEventAccess($event)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $event->load(['user', 'comments', 'participants']);
        return response()->json($event);
    }

    /**
     * Обновить информацию о событии
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        // Проверяем права доступа
        if ($event->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'start_datetime' => 'date',
            'is_long' => 'boolean',
            'end_date' => 'date|after_or_equal:start_datetime',
            'location_name' => 'string|max:255',
            'location_coords' => 'array',
            'description' => 'string',
            'is_public' => 'boolean',
            'whitelist' => 'array',
            'tags' => 'array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event->update($request->all());
        return response()->json($event);
    }

    /**
     * Удалить событие
     */
    public function destroy(Event $event): JsonResponse
    {
        if ($event->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->delete();
        return response()->json(null, 204);
    }

    /**
     * Получить события пользователя
     */
    public function userEvents(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $user = Auth::user();
        
        $events = $user->getAllEvents()
            ->with(['comments', 'participants'])
            ->orderBy('start_datetime', 'desc')
            ->paginate($perPage);

        return response()->json($events);
    }

    /**
     * Получить публичные события
     */
    public function publicEvents(): JsonResponse
    {
        $events = Event::where('is_public', true)
            ->with(['user', 'comments'])
            ->orderBy('start_datetime', 'desc')
            ->get();

        return response()->json($events);
    }

    /**
     * Получить события, созданные пользователем
     */
    public function createdEvents(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $user = Auth::user();
        
        $events = $user->createdEvents()
            ->with(['comments', 'participants'])
            ->orderBy('start_datetime', 'desc')
            ->paginate($perPage);

        return response()->json($events);
    }

    /**
     * Получить события, в которых участвует пользователь
     */
    public function participatingEvents(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $user = Auth::user();
        
        $events = $user->participatingEvents()
            ->with(['user', 'comments'])
            ->orderBy('start_datetime', 'desc')
            ->paginate($perPage);

        return response()->json($events);
    }
}
