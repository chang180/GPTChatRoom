<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class GPTService
{
    protected const DEFAULT_SYSTEM_PROMPT = 'You are a helpful AI assistant inside a themed shared chat room. Reply naturally and use the prior conversation context when it is relevant.';

    protected const SUMMARIZE_SYSTEM_PROMPT = 'You summarize chat room conversations for use as AI context. Preserve names, decisions, action items, and topic continuity. When an existing summary is provided, merge it with the new messages into one concise updated summary without dropping important prior details. Reply with only the summary text, no preamble.';

    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('openai.api_key');
    }

    public function sendMessage($message, array $conversation = [])
    {
        if (! $this->apiKey) {
            throw new \Exception('API key missing');
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-5-nano',
                'messages' => $this->buildMessages($message, $conversation),
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('OpenAI API request failed', ['error' => $e->getMessage()]);
            throw new \Exception('API request failed: '.$e->getMessage());
        }
    }

    /**
     * 發送消息並以流式方式返回響應
     *
     * @param  string  $message  用戶消息
     * @return \Illuminate\Http\Response
     */
    public function sendMessageStream($message, array $conversation = [])
    {
        if (! $this->apiKey) {
            throw new \Exception('API key missing');
        }

        try {
            $stream = OpenAI::chat()->createStreamed([
                'model' => 'gpt-5-nano',
                'messages' => $this->buildMessages($message, $conversation),
            ]);

            return $stream;
        } catch (\Exception $e) {
            Log::error('OpenAI API stream request failed', ['error' => $e->getMessage()]);
            throw new \Exception('API stream request failed: '.$e->getMessage());
        }
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function summarizeConversation(?string $existingSummary, array $messages): string
    {
        if (! $this->apiKey) {
            throw new \Exception('API key missing');
        }

        $userContent = $this->buildSummarizeUserContent($existingSummary, $messages);

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-5-nano',
                'messages' => [
                    ['role' => 'system', 'content' => self::SUMMARIZE_SYSTEM_PROMPT],
                    ['role' => 'user', 'content' => $userContent],
                ],
            ]);

            return $response->choices[0]->message->content ?? '';
        } catch (\Exception $e) {
            Log::error('OpenAI summarize request failed', ['error' => $e->getMessage()]);
            throw new \Exception('Summarize request failed: '.$e->getMessage());
        }
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    protected function buildSummarizeUserContent(?string $existingSummary, array $messages): string
    {
        $formattedMessages = collect($messages)
            ->map(fn (array $entry): string => strtoupper($entry['role']).': '.$entry['content'])
            ->implode("\n");

        if ($existingSummary !== null && $existingSummary !== '') {
            return "Existing summary:\n{$existingSummary}\n\nNew messages to merge:\n{$formattedMessages}";
        }

        return "Messages to summarize:\n{$formattedMessages}";
    }

    protected function buildMessages(string $message, array $conversation = []): array
    {
        $messages = [
            ['role' => 'system', 'content' => self::DEFAULT_SYSTEM_PROMPT],
        ];

        foreach ($conversation as $entry) {
            if (! isset($entry['role'], $entry['content']) || $entry['content'] === '') {
                continue;
            }

            $messages[] = [
                'role' => $entry['role'],
                'content' => $entry['content'],
            ];
        }

        if (empty($conversation) || end($conversation)['content'] !== $message) {
            $messages[] = ['role' => 'user', 'content' => $message];
        }

        return $messages;
    }
}
