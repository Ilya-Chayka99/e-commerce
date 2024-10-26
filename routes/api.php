<?php


use App\Http\Controllers\PaymentHistoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/authorization', [UserController::class, 'authorization']);
Route::post('/user/getInfo', [UserController::class, 'getInfo']);
Route::post('/user/replenishment', [PaymentHistoryController::class, 'replenishment']);

