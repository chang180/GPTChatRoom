<?php

use App\Models\ChatRoom;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 跳過測試環境
        if (app()->environment('testing')) {
            return;
        }

        // 創建4個全局主題聊天室
        $themes = [
            'work' => [
                'name' => '工作',
                'description' => '工作相關的討論和任務',
                'user_id' => null, // 全局聊天室不需要特定用戶
            ],
            'study' => [
                'name' => '學習',
                'description' => '學習和知識分享',
                'user_id' => null,
            ],
            'creative' => [
                'name' => '創意',
                'description' => '創意發想和靈感交流',
                'user_id' => null,
            ],
            'daily' => [
                'name' => '日常',
                'description' => '日常對話和閒聊',
                'user_id' => null,
            ]
        ];
        
        foreach ($themes as $slug => $theme) {
            ChatRoom::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $theme['name'],
                    'description' => $theme['description'],
                    'user_id' => $theme['user_id'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 刪除全局主題聊天室
        ChatRoom::whereIn('slug', ['work', 'study', 'creative', 'daily'])->delete();
    }
};
