<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\GPTService;
use App\Services\MessageCacheService;
use Illuminate\Support\Facades\Log;

class ChatRoomController extends Controller
{
    protected $gptService;
    protected $messageCacheService;

    public function __construct(GPTService $gptService, MessageCacheService $messageCacheService)
    {
        $this->gptService = $gptService;
        $this->messageCacheService = $messageCacheService;
    }

    public function index()
    {
        // 確保用戶已認證
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        // 使用快取服務載入訊息，提高效能
        $cachedData = $this->messageCacheService->getCachedMessages(1, 30);

        return Inertia::render('ChatRoom', [
            'messages' => $cachedData['messages'],
            'pagination' => $cachedData['pagination'],
            'user' => Auth::user(),
        ]);
    }

    /**
     * 載入更多歷史訊息（無限滾動）
     */
    public function loadMoreMessages(Request $request)
    {
        // 確保用戶已認證
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 30);

        // 使用快取服務載入更多訊息
        $cachedData = $this->messageCacheService->getCachedMessages($page, $perPage);

        return response()->json($cachedData);
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

            // 清除快取，因為有新訊息
            $this->messageCacheService->invalidateCacheOnNewMessage();

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

            // 清除快取，因為有新訊息（即使是錯誤訊息）
            $this->messageCacheService->invalidateCacheOnNewMessage();

            return response()->json(['error' => $errorMessage], 500);
        }
    }

    /**
     * 發送消息並以流式方式返回響應
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendMessageStream(Request $request)
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
            $stream = $this->gptService->sendMessageStream($data['message']);

            return response()->stream(function () use ($stream, $message) {
                // 初始化完整回應內容
                $fullResponse = '';

                // 發送消息 ID，以便前端識別
                echo "data: " . json_encode(['messageId' => $message->id]) . "\n\n";

                // 流式處理每個部分的響應
                foreach ($stream as $response) {
                    $content = $response->choices[0]->delta->content;
                    if ($content !== null) {
                        $fullResponse .= $content;
                        echo "data: " . json_encode(['content' => $content]) . "\n\n";
                        ob_flush();
                        flush();
                    }
                }

                // 保存完整回應到數據庫
                $gptMessage = Message::create([
                    'user_id' => Auth::id(),
                    'text' => $fullResponse,
                    'sender_type' => 'gpt',
                ]);

                // 發送完成信號
                echo "data: " . json_encode(['done' => true, 'messageId' => $gptMessage->id]) . "\n\n";
            }, 200, [
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'text/event-stream',
                'X-Accel-Buffering' => 'no',
                'Connection' => 'keep-alive',
            ]);
        } catch (\Exception $e) {
            Log::error('Stream error', ['error' => $e->getMessage()]);
            $errorMessage = $e->getMessage();

            $errorMsg = Message::create([
                'user_id' => Auth::id(),
                'text' => $errorMessage,
                'sender_type' => 'error',
            ]);

            return response()->json(['error' => $errorMessage], 500);
        }
    }
}
