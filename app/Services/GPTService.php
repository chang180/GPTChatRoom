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
}
