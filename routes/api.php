<?php


use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/users/all', [UserController::class, 'getAll']);
Route::post('/users', [UserController::class, 'create']);
