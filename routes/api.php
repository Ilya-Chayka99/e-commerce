<?php


use App\Http\Controllers\Computer_metadataController;
use Illuminate\Support\Facades\Route;

Route::get('computer-metadata/metadata',[Computer_metadataController::class,'frontend']);
