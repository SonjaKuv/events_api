<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventCommentController;
use App\Http\Controllers\Api\EventParticipantsController;
use App\Http\Controllers\Api\EventsController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\Api\TelegramWebhookController;
use App\Http\Controllers\Api\UserController;

Route::prefix('events-api')->group(function () {

    // Публичные маршруты
    Route::get('/events', [EventsController::class, 'index']);
    Route::get('/events/public', [EventsController::class, 'publicEvents']);
    Route::get('/events/{event}', [EventsController::class, 'show']);

    // Маршруты аутентификации
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Telegram webhook (без аутентификации)
    Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

    // Защищенные маршруты
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::post('/events', [EventsController::class, 'store']);
        Route::put('/events/{event}', [EventsController::class, 'update']);
        Route::delete('/events/{event}', [EventsController::class, 'destroy']);

        Route::get('/events/user/me', [EventsController::class, 'userEvents']);
        Route::get('/events/user/created', [EventsController::class, 'createdEvents']);
        Route::get('/events/user/participating', [EventsController::class, 'participatingEvents']);

        Route::get('/events/{event}/participants', [EventParticipantsController::class, 'index']);
        Route::post('/events/{event}/join', [EventParticipantsController::class, 'join']);
        Route::delete('/events/{event}/leave', [EventParticipantsController::class, 'leave']);
        Route::put('/events/{event}/status', [EventParticipantsController::class, 'updateStatus']);

        Route::get('/events/{event}/comments', [EventCommentController::class, 'index']);
        Route::post('/events/{event}/comments', [EventCommentController::class, 'store']);
        Route::put('/events/{event}/comments/{comment}', [EventCommentController::class, 'update']);
        Route::delete('/events/{event}/comments/{comment}', [EventCommentController::class, 'destroy']);

        Route::get('/telegram/status', [TelegramController::class, 'getStatus']);
        Route::post('/telegram/generate-code', [TelegramController::class, 'generateLinkCode']);
        Route::delete('/telegram/unlink', [TelegramController::class, 'unlinkAccount']);
        Route::post('/telegram/test', [TelegramController::class, 'sendTestMessage']);
    });

    // Маршруты пользователей
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/users', [UserController::class, 'postUser']);
    Route::put('/users/{user}', [UserController::class, 'updateUser']);
    Route::get('/users/{user}/full', [UserController::class, 'getFullUserInfo']);
    Route::get('/users/{user}', [UserController::class, 'getUserInfo']);
    Route::get('/users/{id}/friends', [UserController::class, 'getFriends']);
    Route::delete('/users/{user}', [UserController::class, 'destroyUser']);

});
