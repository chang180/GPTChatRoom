<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatRoomController extends Controller
{
    public function index()
    {
        // 确保用户已认证
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        // 加载最近的 50 条消息记录
        $messages = Message::with('user')->latest()->take(50)->get();

        return Inertia::render('ChatRoom', [
            'messages' => $messages,
            'user' => Auth::user(),
        ]);
    }

    public function sendMessage(Request $request)
    {
        // 确保用户已认证
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        // 验证请求数据
        $data = $request->validate([
            'message' => 'required|string',
        ]);

        // 创建新消息
        $message = Message::create([
            'user_id' => Auth::id(),
            'text' => $data['message'],
        ]);

        // 调用 GPTController 来获取回复
        $response = app(GPTController::class)->sendMessage($request);

        return response()->json([
            'message' => $message,
            'gptResponse' => $response->original['choices'][0]['message']['content'],
        ]);
    }
}
