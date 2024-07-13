<?php

use App\Models\Message;
use App\Models\User;

test('a message can be created and associated with a user', function () {
    $user = User::factory()->create();

    $message = Message::create([
        'user_id' => $user->id,
        'text' => 'Hello, this is a test message.',
        'sender_type' => 'user', // 添加 sender_type 字段
    ]);

    expect($message)->toBeInstanceOf(Message::class);
    expect($message->user_id)->toBe($user->id);
    expect($message->text)->toBe('Hello, this is a test message.');
    expect($message->user)->toBeInstanceOf(User::class);
    expect($message->user->id)->toBe($user->id);
});
