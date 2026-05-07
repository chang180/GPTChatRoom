<?php

namespace App\Http\Controllers;

use App\Events\AiReplyCompleted;
use App\Events\ChatMessageCreated;
use App\Events\ChatRoomCleared;
use App\Models\Message;
use App\Models\ChatRoom;
use App\Models\User;
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

    public function index(Request $request)
    {
        // 確保用戶已認證
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $user = Auth::user();
        $chatRoom = $this->resolveChatRoom($request, $user);

        // 獲取所有主題聊天室列表
        $themes = ChatRoom::getGlobalThemes();

        // 使用快取服務載入訊息，提高效能
        $cachedData = $this->messageCacheService->getCachedMessages(1, 30, $chatRoom->id);

        return Inertia::render('ChatRoom', [
            'messages' => $cachedData['messages'],
            'pagination' => $cachedData['pagination'],
            'user' => $user,
            'currentChatRoom' => $chatRoom,
            'themes' => $themes,
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

        $user = Auth::user();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 30);
        $chatRoom = $this->resolveChatRoom($request, $user);

        // 使用快取服務載入更多訊息
        $cachedData = $this->messageCacheService->getCachedMessages($page, $perPage, $chatRoom->id);

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
            'theme' => 'nullable|string',
            'message_type' => 'nullable|string|in:direct,ai_query',
        ]);

        $user = Auth::user();
        $chatRoom = $this->resolveChatRoom($request, $user, $data['theme'] ?? null);

        // 創建新消息，設置 sender_type 為 'user'
        $message = Message::create([
            'user_id' => Auth::id(),
            'chat_room_id' => $chatRoom->id,
            'text' => $data['message'],
            'sender_type' => 'user',
        ]);

        // 清除快取，因為有新訊息
        $this->messageCacheService->invalidateCacheOnNewMessage($chatRoom->id);

        // 如果是直接發送模式，只保存用戶訊息，不發送給 GPT
        $messageType = $data['message_type'] ?? 'ai_query';
        if ($messageType === 'direct') {
            broadcast(new ChatMessageCreated($message, 'direct'))->toOthers();

            return response()->json([
                'message' => array_merge(
                    $message->load('user')->toArray(),
                    ['message_type' => 'direct']
                ),
                'success' => true,
            ]);
        }

        // 如果是 AI 發問模式，發送給 GPT
        try {
            $conversation = $this->buildConversationContext($chatRoom);
            $gptResponse = $this->gptService->sendMessage($data['message'], $conversation);
            $gptMessageContent = $gptResponse['choices'][0]['message']['content'];
            $gptMessage = Message::create([
                'user_id' => Auth::id(),
                'chat_room_id' => $chatRoom->id,
                'text' => $gptMessageContent,
                'sender_type' => 'gpt',
            ]);

            // 再次清除快取，因為有 GPT 回應
            $this->messageCacheService->invalidateCacheOnNewMessage($chatRoom->id);

            broadcast(new ChatMessageCreated($message, 'ai_query'))->toOthers();
            broadcast(new AiReplyCompleted($gptMessage))->toOthers();

            return response()->json([
                'message' => array_merge(
                    $message->load('user')->toArray(),
                    ['message_type' => 'ai_query']
                ),
                'gptResponse' => $gptMessage->text,
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Message::create([
                'user_id' => Auth::id(),
                'chat_room_id' => $chatRoom->id,
                'text' => $errorMessage,
                'sender_type' => 'error',
            ]);

            // 清除快取，因為有新訊息（即使是錯誤訊息）
            $this->messageCacheService->invalidateCacheOnNewMessage($chatRoom->id);

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
            'theme' => 'nullable|string',
        ]);

        $user = Auth::user();
        $chatRoom = $this->resolveChatRoom($request, $user, $data['theme'] ?? null);

        // 創建新消息，設置 sender_type 為 'user'
        $message = Message::create([
            'user_id' => Auth::id(),
            'chat_room_id' => $chatRoom->id,
            'text' => $data['message'],
            'sender_type' => 'user',
        ]);

        // 先清除第一頁快取，避免串流期間重新整理看不到使用者剛送出的訊息
        $this->messageCacheService->invalidateCacheOnNewMessage($chatRoom->id);

        try {
            $conversation = $this->buildConversationContext($chatRoom);
            $stream = $this->gptService->sendMessageStream($data['message'], $conversation);

            broadcast(new ChatMessageCreated($message, 'ai_query'))->toOthers();

            return response()->stream(function () use ($stream, $message, $chatRoom) {
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
                    'chat_room_id' => $chatRoom->id,
                    'text' => $fullResponse,
                    'sender_type' => 'gpt',
                ]);

                // 清除快取，因為有新訊息
                $this->messageCacheService->invalidateCacheOnNewMessage($chatRoom->id);

                broadcast(new AiReplyCompleted($gptMessage))->toOthers();

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

            Message::create([
                'user_id' => Auth::id(),
                'chat_room_id' => $chatRoom->id,
                'text' => $errorMessage,
                'sender_type' => 'error',
            ]);

            // 清除快取，因為有新訊息（即使是錯誤訊息）
            $this->messageCacheService->invalidateCacheOnNewMessage($chatRoom->id);

            return response()->json(['error' => $errorMessage], 500);
        }
    }

    /**
     * 清除聊天室的所有訊息記錄
     */
    public function clearChatRoom(Request $request)
    {
        // 確保用戶已認證
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $user = Auth::user();
        $chatRoom = $this->resolveChatRoom($request, $user, $request->input('theme'));
        
        if (!$chatRoom) {
            return response()->json([
                'success' => false,
                'message' => '聊天室不存在',
            ], 404);
        }

        try {
            // 刪除該聊天室的所有訊息
            $deletedCount = Message::where('chat_room_id', $chatRoom->id)->delete();

            // 清除快取
            $this->messageCacheService->invalidateCacheOnNewMessage($chatRoom->id);

            broadcast(new ChatRoomCleared($chatRoom->id, $deletedCount))->toOthers();

            return response()->json([
                'success' => true,
                'message' => '聊天室記錄已清除',
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Clear chat room error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => '清除聊天室記錄時發生錯誤',
            ], 500);
        }
    }

    protected function resolveChatRoom(Request $request, User $user, ?string $theme = null): ChatRoom
    {
        $resolvedTheme = $theme ?? $request->route('theme') ?? $request->get('theme', 'work');

        return ChatRoom::getGlobalTheme($resolvedTheme)
            ?? ChatRoom::getGlobalTheme('work')
            ?? ChatRoom::getDefaultForUser($user);
    }

    protected function buildConversationContext(ChatRoom $chatRoom, int $limit = 20): array
    {
        return Message::query()
            ->where('chat_room_id', $chatRoom->id)
            ->whereIn('sender_type', ['user', 'gpt'])
            ->latest('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function (Message $message) {
                return [
                    'role' => $message->sender_type === 'gpt' ? 'assistant' : 'user',
                    'content' => $message->text,
                ];
            })
            ->values()
            ->all();
    }
}
