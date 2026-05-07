<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Message $message,
        public ?string $messageType = null,
    ) {
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
        return 'chat.message.created';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => array_merge(
                $this->message->toArray(),
                ['message_type' => $this->messageType]
            ),
        ];
    }
}
