<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GPTController extends Controller
{
    public function sendMessage(Request $request)
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            return response()->json(['error' => 'API key missing'], 500);
        }

        $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => $request->input('message')]
            ],
        ]);

        if ($response->failed()) {
            return response()->json(['error' => $response->json()['error']], $response->status());
        }

        return response()->json($response->json());
    }
}
