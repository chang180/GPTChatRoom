<?php

namespace App\Actions;

use App\Models\ChatRoom;
use App\Models\ChatRoomInvitation;
use App\Models\ChatRoomMember;
use App\Models\User;
use App\Support\PendingChatRoomInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class AcceptChatRoomInvitation
{
    public function accept(User $user, string $token): RedirectResponse
    {
        PendingChatRoomInvitation::forget();

        $invitation = ChatRoomInvitation::where('token', $token)->first();

        if ($invitation === null) {
            return redirect()->route('dashboard')->with('error', '邀請連結無效。');
        }

        if ($invitation->revoked_at !== null) {
            return redirect()->route('dashboard')->with('error', '邀請連結已被撤銷。');
        }

        if (! $invitation->isValid()) {
            return redirect()->route('dashboard')->with('error', '邀請連結已過期。');
        }

        $chatRoom = $invitation->chatRoom;

        if ($chatRoom->hasMember($user)) {
            return redirect()->route('chat.private.show', $chatRoom);
        }

        if ($chatRoom->memberRecords()->count() >= ChatRoom::MAX_MEMBERS) {
            return redirect()->route('dashboard')->with('error', '此私人房成員已滿。');
        }

        $chatRoom->memberRecords()->create([
            'user_id' => $user->id,
            'role' => ChatRoomMember::ROLE_MEMBER,
            'joined_at' => now(),
        ]);

        $invitation->forceFill([
            'accepted_at' => Carbon::now(),
            'accepted_by' => $user->id,
        ])->save();

        return redirect()->route('chat.private.show', $chatRoom)
            ->with('status', '已成功加入私人聊天室。');
    }
}
