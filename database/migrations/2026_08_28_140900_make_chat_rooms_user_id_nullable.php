<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Production's `chat_rooms` table was created before the base migration
     * was updated to mark `user_id` nullable (2025-10-24), so environments
     * that already ran the original migration are stuck with a NOT NULL
     * column. This brings them in line with the current schema so global
     * theme rooms (ChatRoom::ensureGlobalThemes()) can be owned by nobody.
     */
    public function up(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
