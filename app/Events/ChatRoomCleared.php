<?php

namespace App\Events;

use App\Models\ChatRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatRoomCleared implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public int $chatRoomId,
        public int $deletedCount,
    ) {}

    public function broadcastOn(): array
    {
        $room = ChatRoom::find($this->chatRoomId);

        return [
            $room?->broadcastChannel() ?? new Channel("chat-room.{$this->chatRoomId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.room.cleared';
    }

    public function broadcastWith(): array
    {
        return [
            'chat_room_id' => $this->chatRoomId,
            'deleted_count' => $this->deletedCount,
        ];
    }
}
