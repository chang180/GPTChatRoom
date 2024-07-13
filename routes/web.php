<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChatRoomController;

Route::get('/', [HomeController::class, 'index'])->name('home');

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

    // Chat room client route
    Route::get('/chat-client', [ChatRoomController::class, 'client'])->name('chat.client');

    // GPT API message route
    Route::post('/chat/send-message', [ChatRoomController::class, 'sendMessage'])->name('chat.send-message');
});
