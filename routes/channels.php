<?php

use App\Models\ChatRoom;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat-room.{chatRoomId}', function ($user, $chatRoomId) {
    $room = ChatRoom::find($chatRoomId);

    if (! $room) {
        return false;
    }

    // 主題房（public）：任何登入者皆可訂閱；私人房：僅成員（ADR-003）。
    if ($room->isGlobalTheme()) {
        return $user !== null;
    }

    return $room->hasMember($user);
});
