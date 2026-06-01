<?php

use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrivateChatRoomController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Google OAuth（ADR-007：未啟用時 controller 內 abort(404)）
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// 邀請連結：未登入可開啟（寫入 cookie 後導向登入）；已登入則直接加入
Route::get('/chat/invitations/{token}/accept', [PrivateChatRoomController::class, 'acceptInvitation'])
    ->name('chat.invitations.accept');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/google/link', [GoogleAuthController::class, 'linkRedirect'])->name('user.google.link');
    Route::delete('/user/google/unlink', [GoogleAuthController::class, 'unlink'])->name('user.google.unlink');
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

    // Load more messages for infinite scroll
    Route::get('/chat/load-more', [ChatRoomController::class, 'loadMoreMessages'])->name('chat.load-more');

    // GPT API message route
    Route::post('/chat/send-message', [ChatRoomController::class, 'sendMessage'])->name('chat.send-message');

    // GPT API streaming message route - 修改為 POST 請求
    Route::post('/chat/send-message-stream', [ChatRoomController::class, 'sendMessageStream'])->name('chat.send-message-stream');

    // 清除聊天室記錄
    Route::delete('/chat/clear', [ChatRoomController::class, 'clearChatRoom'])->name('chat.clear');

    // 邀請制私人房（須在 /chat/{theme} 之前註冊，避免 'private' 被當主題 slug 解析）
    Route::get('/chat/private', [PrivateChatRoomController::class, 'index'])->name('chat.private.index');
    Route::post('/chat/private', [PrivateChatRoomController::class, 'store'])->name('chat.private.store');
    Route::get('/chat/private/{chatRoom}', [PrivateChatRoomController::class, 'show'])->name('chat.private.show');
    Route::delete('/chat/private/{chatRoom}', [PrivateChatRoomController::class, 'destroy'])->name('chat.private.destroy');
    Route::post('/chat/private/{chatRoom}/invitations', [PrivateChatRoomController::class, 'storeInvitation'])->name('chat.private.invitations.store');
    Route::delete('/chat/private/{chatRoom}/members/{user}', [PrivateChatRoomController::class, 'destroyMember'])->name('chat.private.members.destroy');

    Route::get('/chat/{theme}', [ChatRoomController::class, 'index'])->name('chat.theme');
});
