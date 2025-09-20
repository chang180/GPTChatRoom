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
    Route::get('/chat/{theme}', [ChatRoomController::class, 'index'])->name('chat.theme');
    
    // Load more messages for infinite scroll
    Route::get('/chat/load-more', [ChatRoomController::class, 'loadMoreMessages'])->name('chat.load-more');

    // GPT API message route
    Route::post('/chat/send-message', [ChatRoomController::class, 'sendMessage'])->name('chat.send-message');

    // GPT API streaming message route - 修改為 POST 請求
    Route::post('/chat/send-message-stream', [ChatRoomController::class, 'sendMessageStream'])->name('chat.send-message-stream');

    // 清除聊天室記錄
    Route::delete('/chat/clear', [ChatRoomController::class, 'clearChatRoom'])->name('chat.clear');
});
