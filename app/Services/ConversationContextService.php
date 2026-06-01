<?php

namespace App\Services;

use App\Models\ChatRoom;
use App\Models\ConversationSummary;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ConversationContextService
{
    public const CONTEXT_LIMIT = 20;

    public const SUMMARY_CONTENT_PREFIX = 'Earlier conversation summary:';

    public const SUMMARIZE_LOCK_TTL_SECONDS = 120;

    public const SUMMARIZE_LOCK_WAIT_ATTEMPTS = 5;

    public const SUMMARIZE_LOCK_WAIT_MS = 100;

    public function __construct(protected GPTService $gptService) {}

    public function ensureSummaryCheckpoint(ChatRoom $chatRoom): void
    {
        if ($this->resolveUnsummarizedOverflow($chatRoom) === null) {
            return;
        }

        if (! $this->tryAcquireSummarizeLock($chatRoom)) {
            $this->waitForSummarizeLockRelease($chatRoom);

            return;
        }

        try {
            $overflow = $this->resolveUnsummarizedOverflow($chatRoom);

            if ($overflow === null) {
                return;
            }

            $summaryContent = $this->gptService->summarizeConversation(
                $overflow['existing_summary'],
                $overflow['messages'],
            );

            ConversationSummary::query()->updateOrCreate(
                ['chat_room_id' => $chatRoom->id],
                [
                    'content' => $summaryContent,
                    'summarized_up_to_message_id' => $overflow['last_message_id'],
                    'summarizing_at' => null,
                    'summarizing_until' => null,
                ],
            );
        } finally {
            $this->releaseSummarizeLock($chatRoom);
        }
    }

    public function buildConversationContext(ChatRoom $chatRoom, int $limit = self::CONTEXT_LIMIT): array
    {
        $conversation = $this->mapMessagesToConversation(
            (clone $this->contextMessagesQuery($chatRoom))
                ->latest('id')
                ->limit($limit)
                ->get()
                ->reverse()
                ->values()
        );

        $checkpoint = ConversationSummary::query()
            ->where('chat_room_id', $chatRoom->id)
            ->first();

        if ($checkpoint === null || $checkpoint->content === '') {
            return $conversation;
        }

        return array_merge([
            [
                'role' => 'user',
                'content' => self::SUMMARY_CONTENT_PREFIX."\n".$checkpoint->content,
            ],
        ], $conversation);
    }

    /**
     * @return array{existing_summary: ?string, messages: array<int, array{role: string, content: string}>, last_message_id: int}|null
     */
    protected function resolveUnsummarizedOverflow(ChatRoom $chatRoom): ?array
    {
        $contextQuery = $this->contextMessagesQuery($chatRoom);

        if ($contextQuery->count() <= self::CONTEXT_LIMIT) {
            return null;
        }

        $recentMessages = (clone $contextQuery)
            ->latest('id')
            ->limit(self::CONTEXT_LIMIT)
            ->get();

        $windowStartId = $recentMessages->min('id');

        if ($windowStartId === null) {
            return null;
        }

        $checkpoint = ConversationSummary::query()
            ->where('chat_room_id', $chatRoom->id)
            ->first();

        $lastSummarizedId = $checkpoint?->summarized_up_to_message_id ?? 0;

        $unsummarizedMessages = (clone $contextQuery)
            ->where('id', '<', $windowStartId)
            ->where('id', '>', $lastSummarizedId)
            ->orderBy('id')
            ->get();

        if ($unsummarizedMessages->isEmpty()) {
            return null;
        }

        return [
            'existing_summary' => $checkpoint?->content !== '' ? $checkpoint?->content : null,
            'messages' => $this->mapMessagesToConversation($unsummarizedMessages),
            'last_message_id' => $unsummarizedMessages->last()->id,
        ];
    }

    protected function tryAcquireSummarizeLock(ChatRoom $chatRoom): bool
    {
        return DB::transaction(function () use ($chatRoom): bool {
            $summary = ConversationSummary::query()
                ->where('chat_room_id', $chatRoom->id)
                ->lockForUpdate()
                ->first();

            $lockExpiresAt = now()->addSeconds(self::SUMMARIZE_LOCK_TTL_SECONDS);

            if ($summary === null) {
                ConversationSummary::create([
                    'chat_room_id' => $chatRoom->id,
                    'content' => '',
                    'summarized_up_to_message_id' => 0,
                    'summarizing_at' => now(),
                    'summarizing_until' => $lockExpiresAt,
                ]);

                return true;
            }

            if ($summary->isSummarizeLocked()) {
                return false;
            }

            $summary->update([
                'summarizing_at' => now(),
                'summarizing_until' => $lockExpiresAt,
            ]);

            return true;
        });
    }

    protected function waitForSummarizeLockRelease(ChatRoom $chatRoom): void
    {
        for ($attempt = 0; $attempt < self::SUMMARIZE_LOCK_WAIT_ATTEMPTS; $attempt++) {
            usleep(self::SUMMARIZE_LOCK_WAIT_MS * 1000);

            $summary = ConversationSummary::query()
                ->where('chat_room_id', $chatRoom->id)
                ->first();

            if ($summary === null || ! $summary->isSummarizeLocked()) {
                return;
            }
        }
    }

    protected function releaseSummarizeLock(ChatRoom $chatRoom): void
    {
        ConversationSummary::query()
            ->where('chat_room_id', $chatRoom->id)
            ->update([
                'summarizing_at' => null,
                'summarizing_until' => null,
            ]);
    }

    protected function contextMessagesQuery(ChatRoom $chatRoom): \Illuminate\Database\Eloquent\Builder
    {
        return Message::query()
            ->where('chat_room_id', $chatRoom->id)
            ->whereIn('sender_type', ['user', 'gpt']);
    }

    /**
     * @param  Collection<int, Message>  $messages
     * @return array<int, array{role: string, content: string}>
     */
    protected function mapMessagesToConversation(Collection $messages): array
    {
        return $messages
            ->map(function (Message $message): array {
                return [
                    'role' => $message->sender_type === 'gpt' ? 'assistant' : 'user',
                    'content' => $message->text,
                ];
            })
            ->values()
            ->all();
    }
}
