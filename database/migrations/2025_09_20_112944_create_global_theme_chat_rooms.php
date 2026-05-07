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
        ChatRoom::ensureGlobalThemes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 刪除全局主題聊天室
        ChatRoom::whereIn('slug', array_keys(ChatRoom::GLOBAL_THEME_DEFINITIONS))->delete();
    }
};
