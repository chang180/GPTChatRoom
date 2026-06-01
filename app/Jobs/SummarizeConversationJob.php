<?php

namespace App\Jobs;

use App\Models\ChatRoom;
use App\Services\ConversationContextService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SummarizeConversationJob
{
    use Dispatchable, SerializesModels;

    public function __construct(public int $chatRoomId) {}

    public function handle(ConversationContextService $conversationContextService): void
    {
        $chatRoom = ChatRoom::query()->find($this->chatRoomId);

        if ($chatRoom === null) {
            return;
        }

        $conversationContextService->runSummarizeCheckpoint($chatRoom);
    }
}
