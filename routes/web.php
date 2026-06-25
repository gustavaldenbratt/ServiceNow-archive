<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/servicenow/records')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/servicenow/records');
});

require __DIR__.'/settings.php';
require __DIR__.'/servicenow.php';
