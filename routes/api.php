<?php


use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/users/all', [UserController::class, 'getAll']);
Route::post('/users/update', [UserController::class, 'update']);
Route::post('/users/register', [UserController::class, 'create']);
Route::post('/login', [UserController::class, 'login']);

