<?php

use App\Events\ChatMessageCreated;
use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\Message;
use App\Models\User;
use App\Services\GPTService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;

/**
 * 建立一個私人房並把指定使用者設為 owner。
 */
function privateRoomWithOwner(User $owner): ChatRoom
{
    $room = ChatRoom::factory()->create(['created_by' => $owner->id]);
    $room->memberRecords()->create([
        'user_id' => $owner->id,
        'role' => ChatRoomMember::ROLE_OWNER,
        'joined_at' => now(),
    ]);

    return $room;
}

it('forbids a non-member from viewing a private room', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $room = privateRoomWithOwner($owner);

    actingAs($outsider)
        ->get(route('chat.private.show', $room))
        ->assertForbidden();
});

it('lets a member view a private room', function () {
    $owner = User::factory()->create();
    $room = privateRoomWithOwner($owner);

    actingAs($owner)
        ->get(route('chat.private.show', $room))
        ->assertOk();
});

it('creates a private room with the creator as owner member', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('chat.private.store'), [
        'name' => '專案討論室',
    ]);

    $room = ChatRoom::where('type', ChatRoom::TYPE_PRIVATE_GROUP)->first();

    expect($room)->not->toBeNull()
        ->and($room->created_by)->toBe($user->id);

    $response->assertRedirect(route('chat.private.show', $room));

    $this->assertDatabaseHas('chat_room_members', [
        'chat_room_id' => $room->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);
});

it('lets the owner generate an invitation token', function () {
    $owner = User::factory()->create();
    $room = privateRoomWithOwner($owner);

    $response = actingAs($owner)
        ->postJson(route('chat.private.invitations.store', $room));

    $response->assertStatus(201)
        ->assertJsonStructure(['token', 'expires_at', 'accept_url']);

    $this->assertDatabaseHas('chat_room_invitations', [
        'chat_room_id' => $room->id,
        'invited_by' => $owner->id,
        'token' => $response->json('token'),
    ]);
});

it('forbids a non-owner from generating an invitation', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $room = privateRoomWithOwner($owner);
    $room->memberRecords()->create([
        'user_id' => $member->id,
        'role' => ChatRoomMember::ROLE_MEMBER,
        'joined_at' => now(),
    ]);

    actingAs($member)
        ->postJson(route('chat.private.invitations.store', $room))
        ->assertForbidden();
});

it('lets a logged-in user accept a valid invitation', function () {
    $owner = User::factory()->create();
    $invitee = User::factory()->create();
    $room = privateRoomWithOwner($owner);
    $invitation = $room->invitations()->create([
        'token' => 'valid-token',
        'invited_by' => $owner->id,
        'expires_at' => now()->addDay(),
    ]);

    actingAs($invitee)
        ->post(route('chat.invitations.accept', $invitation->token))
        ->assertRedirect(route('chat.private.show', $room));

    $this->assertDatabaseHas('chat_room_members', [
        'chat_room_id' => $room->id,
        'user_id' => $invitee->id,
        'role' => 'member',
    ]);
});

it('rejects accepting an expired invitation', function () {
    $owner = User::factory()->create();
    $invitee = User::factory()->create();
    $room = privateRoomWithOwner($owner);
    $invitation = $room->invitations()->create([
        'token' => 'expired-token',
        'invited_by' => $owner->id,
        'expires_at' => now()->subDay(),
    ]);

    actingAs($invitee)
        ->post(route('chat.invitations.accept', $invitation->token))
        ->assertStatus(422);

    $this->assertDatabaseMissing('chat_room_members', [
        'chat_room_id' => $room->id,
        'user_id' => $invitee->id,
    ]);
});

it('rejects accepting a revoked invitation', function () {
    $owner = User::factory()->create();
    $invitee = User::factory()->create();
    $room = privateRoomWithOwner($owner);
    $invitation = $room->invitations()->create([
        'token' => 'revoked-token',
        'invited_by' => $owner->id,
        'expires_at' => now()->addDay(),
        'revoked_at' => now(),
    ]);

    actingAs($invitee)
        ->post(route('chat.invitations.accept', $invitation->token))
        ->assertStatus(403);
});

it('rejects accepting when the room is already full', function () {
    $owner = User::factory()->create();
    $room = privateRoomWithOwner($owner);

    // 補滿到 20 人（含 owner）
    User::factory()->count(ChatRoom::MAX_MEMBERS - 1)->create()->each(function (User $member) use ($room) {
        $room->memberRecords()->create([
            'user_id' => $member->id,
            'role' => ChatRoomMember::ROLE_MEMBER,
            'joined_at' => now(),
        ]);
    });

    $invitee = User::factory()->create();
    $invitation = $room->invitations()->create([
        'token' => 'full-room-token',
        'invited_by' => $owner->id,
        'expires_at' => now()->addDay(),
    ]);

    actingAs($invitee)
        ->post(route('chat.invitations.accept', $invitation->token))
        ->assertStatus(422);

    expect($room->memberRecords()->count())->toBe(ChatRoom::MAX_MEMBERS);
});

it('forbids a non-member from posting a message to a private room', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $room = privateRoomWithOwner($owner);

    actingAs($outsider)
        ->postJson(route('chat.send-message'), [
            'message' => 'hi',
            'room' => $room->id,
            'message_type' => 'direct',
        ])
        ->assertForbidden();
});

it('lets a member post a direct message to a private room', function () {
    $owner = User::factory()->create();
    $room = privateRoomWithOwner($owner);

    actingAs($owner)
        ->postJson(route('chat.send-message'), [
            'message' => 'hello team',
            'room' => $room->id,
            'message_type' => 'direct',
        ])
        ->assertOk();

    $this->assertDatabaseHas('messages', [
        'chat_room_id' => $room->id,
        'text' => 'hello team',
        'sender_type' => 'user',
    ]);
});

it('forbids a member who is not the owner from clearing a private room', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $room = privateRoomWithOwner($owner);
    $room->memberRecords()->create([
        'user_id' => $member->id,
        'role' => ChatRoomMember::ROLE_MEMBER,
        'joined_at' => now(),
    ]);

    actingAs($member)
        ->delete(route('chat.clear'), ['room' => $room->id])
        ->assertForbidden();
});

it('broadcasts private room messages on a PrivateChannel', function () {
    $owner = User::factory()->create();
    $room = privateRoomWithOwner($owner);
    $message = Message::create([
        'user_id' => $owner->id,
        'chat_room_id' => $room->id,
        'text' => 'private hi',
        'sender_type' => 'user',
    ]);

    $event = new ChatMessageCreated($message);

    expect($event->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class);
});

it('broadcasts global theme messages on a public Channel', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $work = ChatRoom::getGlobalTheme('work');
    $message = Message::create([
        'user_id' => $user->id,
        'chat_room_id' => $work->id,
        'text' => 'theme hi',
        'sender_type' => 'user',
    ]);

    $channel = (new ChatMessageCreated($message))->broadcastOn()[0];

    expect($channel::class)->toBe(Channel::class);
});

it('keeps the global theme send-message flow working', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();

    $mock = Mockery::mock(GPTService::class);
    $mock->shouldReceive('sendMessage')->once()->andReturn([
        'choices' => [['message' => ['content' => 'theme reply']]],
    ]);
    $this->app->instance(GPTService::class, $mock);

    actingAs($user)
        ->postJson(route('chat.send-message'), [
            'message' => 'hello theme',
            'theme' => 'work',
        ])
        ->assertOk()
        ->assertJsonPath('gptResponse', 'theme reply');
});

it('shares private room mode props on the index page (front-end contract)', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('chat.private.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('ChatRoom')
            ->where('roomMode', 'private')
            ->has('privateRooms')
        );
});

it('shares private room props including members on the show page', function () {
    $owner = User::factory()->create();
    $room = privateRoomWithOwner($owner);

    actingAs($owner)
        ->get(route('chat.private.show', $room))
        ->assertInertia(fn (Assert $page) => $page
            ->component('ChatRoom')
            ->where('roomMode', 'private')
            ->where('canClear', true)
            ->has('members', 1)
        );
});
