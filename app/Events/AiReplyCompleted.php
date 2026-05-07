<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AiReplyCompleted implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Message $message)
    {
        $this->message->loadMissing('user');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("chat-room.{$this->message->chat_room_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.ai-reply.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message->toArray(),
        ];
    }
}
