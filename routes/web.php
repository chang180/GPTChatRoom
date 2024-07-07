<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\GPTController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Chat room route
    Route::get('/chat', [ChatRoomController::class, 'index'])->name('chat.index');

    // GPT API message route
    Route::post('/chat/send-message', [ChatRoomController::class, 'sendMessage'])->name('chat.send-message');
});
