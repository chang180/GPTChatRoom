<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class GPTController extends Controller
{
    public function sendMessage(Request $request)
    {
        $apiKey = config('openai.api_key');

        if (!$apiKey) {
            return response()->json(['error' => 'API key missing'], 500);
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'user', 'content' => $request->input('message')]
                ],
            ]);

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('OpenAI API request failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'API request failed: ' . $e->getMessage()], 500);
        }
    }
}
