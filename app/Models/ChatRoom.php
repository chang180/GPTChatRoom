<?php

namespace App\Models;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatRoom extends Model
{
    use HasFactory;

    public const TYPE_GLOBAL_THEME = 'global_theme';

    public const TYPE_PRIVATE_GROUP = 'private_group';

    /** 私人房成員上限（含 owner，ADR-005） */
    public const MAX_MEMBERS = 20;

    public const GLOBAL_THEME_DEFINITIONS = [
        'work' => [
            'name' => '工作',
            'description' => '工作相關的討論和任務',
        ],
        'study' => [
            'name' => '學習',
            'description' => '學習和知識分享',
        ],
        'creative' => [
            'name' => '創意',
            'description' => '創意發想和靈感交流',
        ],
        'daily' => [
            'name' => '日常',
            'description' => '日常對話和閒聊',
        ],
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'user_id',
        'created_by',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function memberRecords(): HasMany
    {
        return $this->hasMany(ChatRoomMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_room_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ChatRoomInvitation::class);
    }

    public function conversationSummary(): HasOne
    {
        return $this->hasOne(ConversationSummary::class);
    }

    /**
     * Get the default chat room for a user
     */
    public static function getDefaultForUser(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'default-'.$user->id],
            [
                'name' => '預設聊天室',
                'description' => '預設聊天室',
                'is_active' => true,
            ]
        );
    }

    /**
     * Get all global theme chat rooms
     */
    public static function getGlobalThemes(): array
    {
        return self::query()
            ->whereIn('slug', array_keys(self::GLOBAL_THEME_DEFINITIONS))
            ->orderByRaw("CASE slug WHEN 'work' THEN 1 WHEN 'study' THEN 2 WHEN 'creative' THEN 3 WHEN 'daily' THEN 4 END")
            ->get()
            ->toArray();
    }

    /**
     * Get a specific global theme chat room
     */
    public static function getGlobalTheme(string $slug): ?self
    {
        return self::query()
            ->where('slug', $slug)
            ->first();
    }

    public static function ensureGlobalThemes(): void
    {
        foreach (self::GLOBAL_THEME_DEFINITIONS as $slug => $theme) {
            static::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $theme['name'],
                    'description' => $theme['description'],
                    'user_id' => null,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Check if a chat room is a global theme
     */
    public function isGlobalTheme(): bool
    {
        return array_key_exists($this->slug, self::GLOBAL_THEME_DEFINITIONS);
    }

    /**
     * Check if a chat room is an invite-only private group (ADR-002).
     */
    public function isPrivateGroup(): bool
    {
        return $this->type === self::TYPE_PRIVATE_GROUP;
    }

    /**
     * Whether the given user is a member of this room.
     */
    public function hasMember(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->memberRecords()->where('user_id', $user->id)->exists();
    }

    /**
     * Whether the given user is an owner of this room.
     */
    public function isOwnedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->memberRecords()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }

    /**
     * Resolve the broadcast channel for this room (ADR-003):
     * global themes stay public; private groups use a PrivateChannel.
     */
    public function broadcastChannel(): Channel
    {
        $name = "chat-room.{$this->id}";

        return $this->isPrivateGroup()
            ? new PrivateChannel($name)
            : new Channel($name);
    }
}
