<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/users', [UserController::class, 'getUsers']);
Route::post('/users', [UserController::class, 'postUser']);
Route::put('/users/{user}', [UserController::class, 'updateUser']);
Route::get('/users/{user}/full', [UserController::class, 'getFullUserInfo']);
Route::get('/users/{user}', [UserController::class, 'getUserInfo']);
Route::get('/users/{id}/friends', [UserController::class, 'getFriends']);
Route::delete('/users/{user}', [UserController::class, 'destroyUser']);


