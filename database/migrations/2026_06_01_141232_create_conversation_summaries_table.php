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
        Schema::create('conversation_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->unsignedBigInteger('summarized_up_to_message_id');
            $table->timestamps();

            $table->index('summarized_up_to_message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_summaries');
    }
};
