<?php

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
        Schema::table('messages', function (Blueprint $table) {
            // 為 user_id 添加索引（用於查詢特定用戶的訊息）
            $table->index('user_id');
            
            // 為 created_at 添加索引（用於按時間排序）
            $table->index('created_at');
            
            // 為 sender_type 添加索引（用於篩選 GPT 或用戶訊息）
            $table->index('sender_type');
            
            // 複合索引：user_id + created_at（用於查詢特定用戶的訊息並按時間排序）
            $table->index(['user_id', 'created_at']);
            
            // 複合索引：sender_type + created_at（用於按類型篩選並按時間排序）
            $table->index(['sender_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['sender_type']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['sender_type', 'created_at']);
        });
    }
};
