<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;

class GPTService
{
    protected const DEFAULT_SYSTEM_PROMPT = 'You are a helpful AI assistant inside a themed shared chat room. Reply naturally and use the prior conversation context when it is relevant.';

    protected const SUMMARIZE_SYSTEM_PROMPT = 'Summarize the following chat messages for later AI context. Use short bullet points. Keep names, decisions, and open tasks. Maximum 120 words. Output only the summary.';

    protected const COMPRESS_SUMMARY_SYSTEM_PROMPT = 'Compress the following conversation summary for AI context. Keep names, decisions, and open tasks. Maximum 80 words. Output only the compressed summary.';

    protected ?string $apiKey;

    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('openai.api_key');
        $this->model = config('openai.model');
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $conversation
     */
    public function sendMessage(string $message, array $conversation = []): CreateResponse
    {
        if (! $this->apiKey) {
            throw new \Exception('API key missing');
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
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
     * @param  array<int, array{role: string, content: string}>  $conversation
     */
    public function sendMessageStream(string $message, array $conversation = []): StreamResponse
    {
        if (! $this->apiKey) {
            throw new \Exception('API key missing');
        }

        try {
            $stream = OpenAI::chat()->createStreamed([
                'model' => $this->model,
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
    public function summarizeMessages(array $messages): string
    {
        if (! $this->apiKey) {
            throw new \Exception('API key missing');
        }

        if ($messages === []) {
            return '';
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'max_tokens' => config('conversation.summarize_max_tokens'),
                'messages' => [
                    ['role' => 'system', 'content' => self::SUMMARIZE_SYSTEM_PROMPT],
                    ['role' => 'user', 'content' => $this->formatMessagesForSummarize($messages)],
                ],
            ]);

            return trim($response->choices[0]->message->content ?? '');
        } catch (\Exception $e) {
            Log::error('OpenAI summarize request failed', ['error' => $e->getMessage()]);
            throw new \Exception('Summarize request failed: '.$e->getMessage());
        }
    }

    public function compressSummary(string $summary): string
    {
        if (! $this->apiKey) {
            throw new \Exception('API key missing');
        }

        $summary = trim($summary);

        if ($summary === '') {
            return '';
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'max_tokens' => config('conversation.summary_compress_max_tokens'),
                'messages' => [
                    ['role' => 'system', 'content' => self::COMPRESS_SUMMARY_SYSTEM_PROMPT],
                    ['role' => 'user', 'content' => $summary],
                ],
            ]);

            return trim($response->choices[0]->message->content ?? '');
        } catch (\Exception $e) {
            Log::error('OpenAI compress summary request failed', ['error' => $e->getMessage()]);
            throw new \Exception('Compress summary request failed: '.$e->getMessage());
        }
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    protected function formatMessagesForSummarize(array $messages): string
    {
        return collect($messages)
            ->map(fn (array $entry): string => strtoupper($entry['role']).': '.$entry['content'])
            ->implode("\n");
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
