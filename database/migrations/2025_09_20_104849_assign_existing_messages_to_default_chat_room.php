<?php

use App\Models\ChatRoom;
use App\Models\User;
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
        // 為每個用戶創建默認聊天室並將現有訊息分配給它
        $users = User::all();
        
        foreach ($users as $user) {
            // 創建默認聊天室
            $defaultChatRoom = ChatRoom::getDefaultForUser($user);
            
            // 將該用戶的所有現有訊息分配給默認聊天室
            $user->messages()->whereNull('chat_room_id')->update([
                'chat_room_id' => $defaultChatRoom->id
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 將訊息從聊天室移除
        \DB::table('messages')->update(['chat_room_id' => null]);
        
        // 刪除默認聊天室
        ChatRoom::where('slug', 'default')->delete();
    }
};
