<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $user_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $type
 * @property int|null $created_by
 * @property-read \App\Models\ConversationSummary|null $conversationSummary
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatRoomInvitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatRoomMember> $memberRecords
 * @property-read int|null $member_records_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\ChatRoomFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperChatRoom {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $chat_room_id
 * @property string $token
 * @property int $invited_by
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $accepted_at
 * @property int|null $accepted_by
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property int|null $max_uses
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ChatRoom $chatRoom
 * @property-read \App\Models\User $inviter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation whereAcceptedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation whereChatRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation whereInvitedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation whereMaxUses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation whereRevokedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomInvitation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperChatRoomInvitation {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $chat_room_id
 * @property int $user_id
 * @property string $role
 * @property \Illuminate\Support\Carbon|null $joined_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ChatRoom $chatRoom
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomMember whereChatRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomMember whereJoinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomMember whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoomMember whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperChatRoomMember {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $chat_room_id
 * @property string $content
 * @property int $summarized_up_to_message_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $summarizing_at
 * @property \Illuminate\Support\Carbon|null $summarizing_until
 * @property-read \App\Models\ChatRoom $chatRoom
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationSummary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationSummary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationSummary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationSummary whereChatRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationSummary whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationSummary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationSummary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationSummary whereSummarizedUpToMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationSummary whereSummarizingAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationSummary whereSummarizingUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationSummary whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperConversationSummary {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $text
 * @property string $sender_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $chat_room_id
 * @property-read \App\Models\ChatRoom|null $chatRoom
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereChatRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereSenderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMessage {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $google_id
 * @property string|null $google_token
 * @property string|null $google_refresh_token
 * @property bool $is_admin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatRoom> $chatRooms
 * @property-read int|null $chat_rooms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read string $profile_photo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrentTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGoogleRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGoogleToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

