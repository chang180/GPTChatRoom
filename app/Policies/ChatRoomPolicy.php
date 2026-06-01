<?php

namespace App\Policies;

use App\Models\ChatRoom;
use App\Models\User;

class ChatRoomPolicy
{
    /**
     * 成員可檢視私人房。
     */
    public function view(User $user, ChatRoom $chatRoom): bool
    {
        return $chatRoom->hasMember($user);
    }

    /**
     * 成員可發訊（含 AI）。
     */
    public function sendMessage(User $user, ChatRoom $chatRoom): bool
    {
        return $chatRoom->hasMember($user);
    }

    /**
     * 僅 owner 可清空私人房；主題房一律不可（由 controller 另行擋下）。
     */
    public function clear(User $user, ChatRoom $chatRoom): bool
    {
        return $chatRoom->isPrivateGroup() && $chatRoom->isOwnedBy($user);
    }

    /**
     * 僅 owner 可邀請。
     */
    public function invite(User $user, ChatRoom $chatRoom): bool
    {
        return $chatRoom->isOwnedBy($user);
    }

    /**
     * 僅 owner 可移除成員。
     */
    public function removeMember(User $user, ChatRoom $chatRoom): bool
    {
        return $chatRoom->isOwnedBy($user);
    }

    /**
     * 僅 owner 可刪除私人房。
     */
    public function delete(User $user, ChatRoom $chatRoom): bool
    {
        return $chatRoom->isOwnedBy($user);
    }
}
