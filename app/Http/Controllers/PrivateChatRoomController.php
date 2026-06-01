<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrivateChatRoomRequest;
use App\Models\ChatRoom;
use App\Models\ChatRoomInvitation;
use App\Models\ChatRoomMember;
use App\Models\User;
use App\Services\MessageCacheService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PrivateChatRoomController extends Controller
{
    public function __construct(
        protected MessageCacheService $messageCacheService,
    ) {}

    /**
     * 列出目前使用者可進入的私人房。
     */
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('ChatRoom', [
            'roomMode' => 'private',
            'privateRooms' => $this->privateRoomsFor($user),
            'themes' => ChatRoom::getGlobalThemes(),
            'currentChatRoom' => null,
            'messages' => [],
            'user' => $user,
        ]);
    }

    /**
     * 顯示單一私人房（僅成員）。
     */
    public function show(ChatRoom $chatRoom)
    {
        $this->authorize('view', $chatRoom);

        $user = Auth::user();
        $cachedData = $this->messageCacheService->getCachedMessages(1, 30, $chatRoom->id);

        return Inertia::render('ChatRoom', [
            'roomMode' => 'private',
            'currentChatRoom' => $chatRoom,
            'messages' => $cachedData['messages'],
            'pagination' => $cachedData['pagination'],
            'themes' => ChatRoom::getGlobalThemes(),
            'privateRooms' => $this->privateRoomsFor($user),
            'members' => $this->membersFor($chatRoom),
            'canClear' => $user->can('clear', $chatRoom),
            'user' => $user,
        ]);
    }

    /**
     * 建立私人房，建立者即為 owner。
     */
    public function store(StorePrivateChatRoomRequest $request)
    {
        $user = Auth::user();

        $chatRoom = ChatRoom::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'slug' => 'private-'.Str::uuid(),
            'type' => ChatRoom::TYPE_PRIVATE_GROUP,
            'created_by' => $user->id,
            'is_active' => true,
        ]);

        $chatRoom->memberRecords()->create([
            'user_id' => $user->id,
            'role' => ChatRoomMember::ROLE_OWNER,
            'joined_at' => now(),
        ]);

        return redirect()->route('chat.private.show', $chatRoom);
    }

    /**
     * 產生邀請連結 token（僅 owner，ADR-005）。
     */
    public function storeInvitation(ChatRoom $chatRoom)
    {
        $this->authorize('invite', $chatRoom);

        $invitation = $chatRoom->invitations()->create([
            'token' => Str::random(40),
            'invited_by' => Auth::id(),
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'token' => $invitation->token,
            'expires_at' => $invitation->expires_at,
            'accept_url' => route('chat.invitations.accept', $invitation->token),
        ], 201);
    }

    /**
     * 接受邀請（僅已登入；token 須有效且房未滿，ADR-005）。
     */
    public function acceptInvitation(string $token)
    {
        $invitation = ChatRoomInvitation::where('token', $token)->first();

        abort_if($invitation === null, 404);
        abort_if($invitation->revoked_at !== null, 403, '邀請連結已被撤銷。');
        abort_if(! $invitation->isValid(), 422, '邀請連結已過期。');

        $chatRoom = $invitation->chatRoom;
        $user = Auth::user();

        if ($chatRoom->hasMember($user)) {
            return redirect()->route('chat.private.show', $chatRoom);
        }

        abort_if(
            $chatRoom->memberRecords()->count() >= ChatRoom::MAX_MEMBERS,
            422,
            '此私人房成員已滿。'
        );

        $chatRoom->memberRecords()->create([
            'user_id' => $user->id,
            'role' => ChatRoomMember::ROLE_MEMBER,
            'joined_at' => now(),
        ]);

        $invitation->forceFill([
            'accepted_at' => Carbon::now(),
            'accepted_by' => $user->id,
        ])->save();

        return redirect()->route('chat.private.show', $chatRoom);
    }

    /**
     * 移除成員（僅 owner；不可移除唯一 owner）。
     */
    public function destroyMember(ChatRoom $chatRoom, User $user)
    {
        $this->authorize('removeMember', $chatRoom);

        $isOnlyOwner = $chatRoom->isOwnedBy($user)
            && $chatRoom->memberRecords()->where('role', ChatRoomMember::ROLE_OWNER)->count() === 1;

        abort_if($isOnlyOwner, 422, '無法移除唯一的房主。');

        $chatRoom->memberRecords()->where('user_id', $user->id)->delete();

        return back();
    }

    /**
     * 使用者可進入的私人房列表（含成員數）。
     *
     * @return array<int, array<string, mixed>>
     */
    protected function privateRoomsFor(User $user): array
    {
        return ChatRoom::query()
            ->where('type', ChatRoom::TYPE_PRIVATE_GROUP)
            ->whereHas('memberRecords', fn ($query) => $query->where('user_id', $user->id))
            ->withCount('memberRecords')
            ->get()
            ->map(fn (ChatRoom $room) => [
                'id' => $room->id,
                'name' => $room->name,
                'slug' => $room->slug,
                'member_count' => $room->member_records_count,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function membersFor(ChatRoom $chatRoom): array
    {
        return $chatRoom->members()
            ->get()
            ->map(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $member->pivot->role,
            ])
            ->all();
    }
}
