<?php

use App\Models\ChatRoom;
use App\Models\Message;
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
        // 獲取主題聊天室
        $workRoom = ChatRoom::getGlobalTheme('work');
        $studyRoom = ChatRoom::getGlobalTheme('study');
        $creativeRoom = ChatRoom::getGlobalTheme('creative');
        $dailyRoom = ChatRoom::getGlobalTheme('daily');

        if (!$workRoom || !$studyRoom || !$creativeRoom || !$dailyRoom) {
            return;
        }

        // 將所有現有訊息分配到工作聊天室（作為默認）
        // 或者可以根據訊息內容智能分配到不同聊天室
        Message::whereNotNull('chat_room_id')->update([
            'chat_room_id' => $workRoom->id
        ]);

        echo "Reassigned all messages to work chat room (ID: {$workRoom->id})\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 這個 migration 沒有可逆操作，因為我們不知道原始分配
        echo "This migration cannot be reversed\n";
    }
};
