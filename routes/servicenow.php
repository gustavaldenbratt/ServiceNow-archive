<?php

use App\Http\Controllers\ServiceNow\AttachmentController;
use App\Http\Controllers\ServiceNow\ConnectionController;
use App\Http\Controllers\ServiceNow\RecordController;
use App\Http\Controllers\ServiceNow\SyncRunController;
use App\Http\Controllers\ServiceNow\TableConfigController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('servicenow')->name('servicenow.')->group(function () {
    Route::get('connection', [ConnectionController::class, 'edit'])->name('connection.edit');
    Route::put('connection', [ConnectionController::class, 'update'])->name('connection.update');
    Route::post('connection/test', [ConnectionController::class, 'test'])->name('connection.test');

    Route::get('tables', [TableConfigController::class, 'index'])->name('tables.index');
    Route::post('tables', [TableConfigController::class, 'store'])->name('tables.store');
    Route::patch('tables/{tableConfig}', [TableConfigController::class, 'update'])->name('tables.update');
    Route::post('tables/{tableConfig}/sync', [TableConfigController::class, 'sync'])->name('tables.sync');

    Route::get('runs', [SyncRunController::class, 'index'])->name('runs.index');

    Route::get('records', [RecordController::class, 'index'])->name('records.index');
    Route::get('records/{record}', [RecordController::class, 'show'])->name('records.show');

    Route::get('attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
});
