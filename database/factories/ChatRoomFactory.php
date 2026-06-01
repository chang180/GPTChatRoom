<?php

namespace Database\Factories;

use App\Models\ChatRoom;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChatRoom>
 */
class ChatRoomFactory extends Factory
{
    protected $model = ChatRoom::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => 'private-'.Str::uuid(),
            'description' => fake()->sentence(),
            'type' => ChatRoom::TYPE_PRIVATE_GROUP,
            'is_active' => true,
        ];
    }
}
