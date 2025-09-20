<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatRoom extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the default chat room for a user
     */
    public static function getDefaultForUser(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'default-' . $user->id],
            [
                'name' => '預設聊天室',
                'description' => '預設聊天室',
                'is_active' => true,
            ]
        );
    }
}
