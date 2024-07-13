<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\GPTService;

class ChatRoomController extends Controller
{
    protected $gptService;

    public function __construct(GPTService $gptService)
    {
        $this->gptService = $gptService;
    }

    public function index()
    {
        // 確保用戶已認證
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('ChatRoom');
    }

    public function client()
    {
        // 確保用戶已認證
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        // 加載最近的 50 條消息記錄
        $messages = Message::with('user')->latest()->take(50)->get();

        return Inertia::render('ChatRoomClient', [
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

        try {
            $gptResponse = $this->gptService->sendMessage($data['message']);
            $gptMessageContent = $gptResponse['choices'][0]['message']['content'];
            $gptMessage = Message::create([
                'user_id' => Auth::id(),
                'text' => $gptMessageContent,
                'sender_type' => 'gpt',
            ]);

            return response()->json([
                'message' => $message,
                'gptResponse' => $gptMessage->text,
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Message::create([
                'user_id' => Auth::id(),
                'text' => $errorMessage,
                'sender_type' => 'gpt', // 假設錯誤消息也來自 GPT
            ]);

            return response()->json(['error' => $errorMessage], 500);
        }
    }
}
