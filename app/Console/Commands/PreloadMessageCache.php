<?php

namespace App\Console\Commands;

use App\Services\MessageCacheService;
use Illuminate\Console\Command;

class PreloadMessageCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:preload-messages {--pages=5 : Number of pages to preload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preload message cache for better performance';

    /**
     * Execute the console command.
     */
    public function handle(MessageCacheService $cacheService): int
    {
        $pages = (int) $this->option('pages');
        
        $this->info("Starting to preload message cache for {$pages} pages...");
        
        $startTime = microtime(true);
        
        // 預載入快取
        $cacheService->preloadCache($pages, 30);
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->info("✅ Successfully preloaded cache for {$pages} pages in {$duration} seconds");
        
        // 顯示快取統計
        $stats = $cacheService->getMessageStats();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Messages', $stats['total_messages']],
                ['User Messages', $stats['user_messages']],
                ['GPT Messages', $stats['gpt_messages']],
                ['Latest Message', $stats['latest_message_at']?->format('Y-m-d H:i:s') ?? 'N/A'],
            ]
        );
        
        return self::SUCCESS;
    }
}
