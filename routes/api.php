<?php

use App\Http\Controllers\ComputerController;
use App\Http\Controllers\ComputerInfoController;
use App\Http\Controllers\ComputerMetadataController;
use App\Http\Controllers\ComputerRentalController;
use App\Http\Controllers\PaymentHistoryController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\TokenIsValid;
use Illuminate\Support\Facades\Route;

Route::post('/authorization', [UserController::class, 'authorization']);

Route::prefix('user')->middleware([TokenIsValid::class])->group(function () {
    Route::post('/getInfo', [UserController::class, 'getInfo']);
    Route::post('/getRentalsActive', [UserController::class, 'getRentalsActive']);
    Route::post('/replenishment', [PaymentHistoryController::class, 'replenishment']);
    Route::post('/replenishment/history', [PaymentHistoryController::class, 'history']);
});



Route::post('/computer-addInfo', [ComputerInfoController::class, 'store']);
Route::post('/computer-addMetadata', [ComputerMetadataController::class, 'store']);
Route::post('/computer-add', [ComputerController::class, 'store']);
Route::post('/computer-rentall', [ComputerRentalController::class, 'store']);

Route::post('/computer-all', [ComputerController::class, 'getAll']);
