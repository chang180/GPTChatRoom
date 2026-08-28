<?php

use App\Models\ChatRoom;
use Illuminate\Database\Migrations\Migration;

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
        //
    }
};
