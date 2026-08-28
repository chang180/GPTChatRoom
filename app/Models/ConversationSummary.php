<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperConversationSummary
 */
class ConversationSummary extends Model
{
    protected $fillable = [
        'chat_room_id',
        'content',
        'summarized_up_to_message_id',
        'summarizing_at',
        'summarizing_until',
    ];

    protected function casts(): array
    {
        return [
            'summarizing_at' => 'datetime',
            'summarizing_until' => 'datetime',
        ];
    }

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function isSummarizeLocked(): bool
    {
        if ($this->summarizing_at === null) {
            return false;
        }

        if ($this->summarizing_until === null) {
            return true;
        }

        return $this->summarizing_until->isFuture();
    }
}
