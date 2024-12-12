<?php

use App\Http\Controllers\TableController;
use App\Http\Controllers\TableInfoController;
use App\Http\Controllers\TableMetadataController;
use App\Http\Controllers\TableRentalController;
use App\Http\Controllers\PaymentHistoryController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\PermCheck;
use App\Http\Middleware\TokenIsValid;
use Illuminate\Support\Facades\Route;

Route::post('/authorization', [UserController::class, 'authorization']);

Route::prefix('user')->middleware([TokenIsValid::class])->group(function () {
    Route::post('/getInfo', [UserController::class, 'getInfo']);
    Route::post('/getRentalsActive', [UserController::class, 'getRentalsActive']);
    Route::post('/replenishment', [PaymentHistoryController::class, 'replenishment']);
    Route::post('/getLink', [PaymentHistoryController::class, 'getLink']);
    Route::post('/replenishment/history', [PaymentHistoryController::class, 'history']);
});

Route::prefix('table')->middleware([TokenIsValid::class])->group(function () {
    Route::post('/rental', [TableRentalController::class, 'store']);
    Route::post('/rentalOff', [TableRentalController::class, 'cancelRental']);
});
Route::post('/table/rentalCheck', [TableRentalController::class, 'check']);
Route::post('/table-all', [TableController::class, 'getAll']);

////Route::prefix('computer')->middleware([TokenIsValid::class])->group(function () {
//    Route::post('/table/addInfo', [TableInfoController::class, 'store']);//->middleware([PermCheck::class]);
//    Route::post('/table/addMetadata', [TableMetadataController::class, 'store']);//->middleware([PermCheck::class]);
//    Route::post('/table/add', [TableController::class, 'store']);//->middleware([PermCheck::class]);
////});




