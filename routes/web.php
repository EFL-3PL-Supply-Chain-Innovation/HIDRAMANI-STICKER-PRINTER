<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ExcelUploadController;
use App\Http\Controllers\Controller;

Route::get('/', function () {
    return view('login');
});

Route::get('login', [UserController::class, 'index'])->name('login');
Route::post('post-login', [UserController::class, 'postLogin'])->name('login.post');

Route::get('create', [UserController::class, 'create'])->name('create');
Route::post('create-store', [UserController::class, 'store'])->name('create.store');


Route::get('/upload-excel', [ExcelUploadController::class, 'showUploadForm'])->name('operate.excel');
Route::post('/upload-excel', [ExcelUploadController::class, 'import'])->name('import.excel');

Route::get('/showdata', [ExcelUploadController::class, 'showData'])->name('operate.showdata');


Route::get('/export-excel', [ExcelUploadController::class, 'exportExcel'])->name('export.excel');



Route::get('/showprinteddata', [ExcelUploadController::class, 'showprinteddata'])->name('showprinteddata');

Route::post('/toggle-printed-status', [ExcelUploadController::class, 'togglePrintedStatus'])->name('toggle.printed.status');

Route::get('/unprinted-records', [ExcelUploadController::class, 'showUnprintedPage'])
     ->name('unprinted.records');

// Fetch records by new_item_number (AJAX)
Route::post('/fetch-unprinted-records', [ExcelUploadController::class, 'fetchUnprintedRecords'])
     ->name('fetch.unprinted.records');

// Delete selected records (AJAX)
Route::post('/delete-unprinted-records', [ExcelUploadController::class, 'deleteUnprintedRecords'])
     ->name('delete.unprinted.records');





