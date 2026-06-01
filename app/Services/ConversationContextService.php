<?php

namespace App\Services;

use App\Jobs\SummarizeConversationJob;
use App\Models\ChatRoom;
use App\Models\ConversationSummary;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConversationContextService
{
    public const CONTEXT_LIMIT = 20;

    public const SUMMARY_CONTENT_PREFIX = 'Earlier conversation summary:';

    public const SUMMARIZE_LOCK_TTL_SECONDS = 120;

    public function __construct(protected GPTService $gptService) {}

    public function ensureSummaryCheckpoint(ChatRoom $chatRoom): void
    {
        if ($this->resolveUnsummarizedOverflow($chatRoom) === null) {
            return;
        }

        $summary = ConversationSummary::query()
            ->where('chat_room_id', $chatRoom->id)
            ->first();

        if ($summary?->isSummarizeLocked()) {
            return;
        }

        SummarizeConversationJob::dispatch($chatRoom->id)->afterResponse();
    }

    public function runSummarizeCheckpoint(ChatRoom $chatRoom): void
    {
        if ($this->resolveUnsummarizedOverflow($chatRoom) === null) {
            return;
        }

        if (! $this->tryAcquireSummarizeLock($chatRoom)) {
            return;
        }

        try {
            $overflow = $this->resolveUnsummarizedOverflow($chatRoom);

            if ($overflow === null) {
                return;
            }

            $batchSummary = $this->gptService->summarizeMessages($overflow['messages']);
            $summaryContent = $this->mergeSummaryContent($overflow['existing_summary'], $batchSummary);

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

    public function buildConversationContext(ChatRoom $chatRoom, ?int $limit = null): array
    {
        $limit ??= $this->contextLimit();

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
                'content' => self::SUMMARY_CONTENT_PREFIX."\n".$this->trimSummaryForContext($checkpoint->content),
            ],
        ], $conversation);
    }

    /**
     * @return array{existing_summary: ?string, messages: array<int, array{role: string, content: string}>, last_message_id: int}|null
     */
    protected function resolveUnsummarizedOverflow(ChatRoom $chatRoom): ?array
    {
        $contextQuery = $this->contextMessagesQuery($chatRoom);
        $contextLimit = $this->contextLimit();

        if ($contextQuery->count() <= $contextLimit) {
            return null;
        }

        $recentMessages = (clone $contextQuery)
            ->latest('id')
            ->limit($contextLimit)
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

        if ($unsummarizedMessages->count() < $this->summarizeMinOverflow()) {
            return null;
        }

        return [
            'existing_summary' => $checkpoint?->content !== '' ? $checkpoint?->content : null,
            'messages' => $this->mapMessagesToConversation($unsummarizedMessages),
            'last_message_id' => $unsummarizedMessages->last()->id,
        ];
    }

    protected function mergeSummaryContent(?string $existingSummary, string $batchSummary): string
    {
        $batchSummary = trim($batchSummary);

        if ($batchSummary === '') {
            return trim($existingSummary ?? '');
        }

        $merged = $existingSummary !== null && $existingSummary !== ''
            ? trim($existingSummary)."\n".$batchSummary
            : $batchSummary;

        if (mb_strlen($merged) <= config('conversation.summary_max_chars')) {
            return $merged;
        }

        return $this->gptService->compressSummary($merged);
    }

    protected function trimSummaryForContext(string $summary): string
    {
        return Str::limit(
            $summary,
            config('conversation.summary_context_max_chars'),
            '…',
        );
    }

    protected function contextLimit(): int
    {
        return config('conversation.context_limit', self::CONTEXT_LIMIT);
    }

    protected function summarizeMinOverflow(): int
    {
        return config('conversation.summarize_min_overflow', 5);
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
