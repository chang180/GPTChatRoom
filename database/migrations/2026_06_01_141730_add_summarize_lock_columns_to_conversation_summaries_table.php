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
        Schema::table('conversation_summaries', function (Blueprint $table) {
            $table->timestamp('summarizing_at')->nullable()->after('summarized_up_to_message_id');
            $table->timestamp('summarizing_until')->nullable()->after('summarizing_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_summaries', function (Blueprint $table) {
            $table->dropColumn(['summarizing_at', 'summarizing_until']);
        });
    }
};
