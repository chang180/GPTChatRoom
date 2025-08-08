<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class GPTService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('openai.api_key');
    }

    public function sendMessage($message)
    {
        if (!$this->apiKey) {
            throw new \Exception('API key missing');
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $message]
                ],
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('OpenAI API request failed', ['error' => $e->getMessage()]);
            throw new \Exception('API request failed: ' . $e->getMessage());
        }
    }

    /**
     * 發送消息並以流式方式返回響應
     *
     * @param string $message 用戶消息
     * @return \Illuminate\Http\Response
     */
    public function sendMessageStream($message)
    {
        if (!$this->apiKey) {
            throw new \Exception('API key missing');
        }

        try {
            $stream = OpenAI::chat()->createStreamed([
                'model' => 'gpt-5-nano',
                'messages' => [
                    ['role' => 'user', 'content' => $message]
                ],
            ]);

            return $stream;
        } catch (\Exception $e) {
            Log::error('OpenAI API stream request failed', ['error' => $e->getMessage()]);
            throw new \Exception('API stream request failed: ' . $e->getMessage());
        }
    }
}
