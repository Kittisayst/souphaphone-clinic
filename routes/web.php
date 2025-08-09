<?php

use App\Http\Controllers\QueueDisplayController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('admin');
});

Route::get('/queue-display', [QueueDisplayController::class, 'index'])->name('queue.display');