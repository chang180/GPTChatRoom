<?php

namespace App\Services;

use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MessageCacheService
{
    protected int $cacheTtl = 3600; // 1 hour cache
    protected string $cachePrefix = 'messages:';

    /**
     * 獲取快取的訊息列表
     */
    public function getCachedMessages(int $page = 1, int $perPage = 30): array
    {
        $cacheKey = $this->getCacheKey($page, $perPage);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($page, $perPage) {
            return $this->getMessagesFromDatabase($page, $perPage);
        });
    }

    /**
     * 從資料庫獲取訊息
     */
    protected function getMessagesFromDatabase(int $page = 1, int $perPage = 30): array
    {
        $offset = ($page - 1) * $perPage;
        
        $messages = Message::with('user')
            ->latest()
            ->offset($offset)
            ->limit($perPage)
            ->get();

        return [
            'messages' => $messages->toArray(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => Message::count(),
                'last_page' => ceil(Message::count() / $perPage),
                'has_more_pages' => ($page * $perPage) < Message::count(),
            ]
        ];
    }

    /**
     * 清除所有訊息快取
     */
    public function clearAllCache(): void
    {
        $pattern = $this->cachePrefix . '*';
        $keys = Cache::getRedis()->keys($pattern);
        
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }

    /**
     * 清除特定頁面的快取
     */
    public function clearPageCache(int $page, int $perPage = 30): void
    {
        $cacheKey = $this->getCacheKey($page, $perPage);
        Cache::forget($cacheKey);
    }

    /**
     * 當有新訊息時清除相關快取
     */
    public function invalidateCacheOnNewMessage(): void
    {
        // 清除第一頁快取（因為新訊息會出現在第一頁）
        $this->clearPageCache(1);
        
        // 也可以選擇清除所有快取，但這會影響效能
        // $this->clearAllCache();
    }

    /**
     * 獲取快取鍵
     */
    protected function getCacheKey(int $page, int $perPage): string
    {
        return $this->cachePrefix . "page:{$page}:per_page:{$perPage}";
    }

    /**
     * 獲取訊息統計快取
     */
    public function getMessageStats(): array
    {
        $cacheKey = $this->cachePrefix . 'stats';
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return [
                'total_messages' => Message::count(),
                'user_messages' => Message::where('sender_type', 'user')->count(),
                'gpt_messages' => Message::where('sender_type', 'gpt')->count(),
                'latest_message_at' => Message::latest()->first()?->created_at,
            ];
        });
    }

    /**
     * 預載入熱門頁面的快取
     */
    public function preloadCache(int $pages = 3, int $perPage = 30): void
    {
        for ($i = 1; $i <= $pages; $i++) {
            $this->getCachedMessages($i, $perPage);
        }
        
        // 也預載入統計資料
        $this->getMessageStats();
    }
}