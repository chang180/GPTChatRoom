<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ChatRoomController extends Controller
{
    public function index()
    {
        // 確保用戶已認證
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        // 加載最近的 50 條消息記錄
        $messages = Message::with('user')->latest()->take(50)->get();

        return Inertia::render('ChatRoom', [
            'messages' => $messages,
            'user' => Auth::user(),
        ]);
    }

    public function sendMessage(Request $request)
    {
        // 確保用戶已認證
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        // 驗證請求數據
        $data = $request->validate([
            'message' => 'required|string',
        ]);

        // 創建新消息，設置 sender_type 為 'user'
        $message = Message::create([
            'user_id' => Auth::id(),
            'text' => $data['message'],
            'sender_type' => 'user',
        ]);

        // 調用 GPTController 來獲取回復
        $response = app(GPTController::class)->sendMessage($request);

        if ($response->getStatusCode() == 200 && isset($response->original['choices'])) {
            $gptMessageContent = $response->original['choices'][0]['message']['content'];
            Message::create([
                'user_id' => Auth::id(),
                'text' => $gptMessageContent,
                'sender_type' => 'gpt',
            ]);
        } else {
            $errorMessage = $response->original['error'] ?? 'API request failed';
            Message::create([
                'user_id' => Auth::id(),
                'text' => $errorMessage,
                'sender_type' => 'gpt', // 假設錯誤消息也來自 GPT
            ]);
        }

        return response()->json([
            'message' => $message,
            'gptResponse' => $gptMessageContent ?? $errorMessage,
        ]);
    }

}
