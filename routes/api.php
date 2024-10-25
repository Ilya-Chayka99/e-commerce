<?php


use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

//Route::post('/users/update', [UserController::class, 'update']);
Route::post('/authorization', [UserController::class, 'authorization']);

