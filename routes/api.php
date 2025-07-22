<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExcelUploadController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('auth/login', [UserController::class, 'login'])->name('apilogin');

Route::get('/pallet/{number}', [ExcelUploadController::class, 'show']);

// Route::middleware('auth:sanctum')->post('/pallet/print', [ExcelUploadController::class, 'printLabel']);
// Route::middleware('auth:sanctum')->post('/locations-by-item', [ExcelUploadController::class, 'getByItem']);

// Route::middleware('auth:sanctum')->get('/items', [ExcelUploadController::class, 'getAllItemNumbers']);

// routes/api.php
Route::get('/items', [ExcelUploadController::class, 'getAllItemNumbers']);
Route::post('/locations-by-item', [ExcelUploadController::class, 'getLocationsByItem']);
Route::post('/hu-ids', [ExcelUploadController::class, 'getHuIds']);
Route::post('/mark-printed', [ExcelUploadController::class, 'markPrinted']);






