<?php

namespace App\Support;

use App\Actions\AcceptChatRoomInvitation;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;

class PendingChatRoomInvitation
{
    public const COOKIE_NAME = 'pending_private_room_invitation';

    /** Cookie 保留 7 天，與邀請連結有效期限一致。 */
    private const COOKIE_MINUTES = 60 * 24 * 7;

    public static function remember(string $token): void
    {
        Cookie::queue(cookie(
            name: self::COOKIE_NAME,
            value: Crypt::encryptString($token),
            minutes: self::COOKIE_MINUTES,
            path: '/',
            secure: config('session.secure'),
            httpOnly: true,
            sameSite: 'lax',
        ));
    }

    public static function forget(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    public static function pull(Request $request): ?string
    {
        $encrypted = $request->cookie(self::COOKIE_NAME);

        self::forget();

        if ($encrypted === null) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return null;
        }
    }

    public static function completeAfterAuthentication(Request $request): RedirectResponse
    {
        $token = self::pull($request);

        if ($token !== null && $request->user() !== null) {
            return app(AcceptChatRoomInvitation::class)->accept($request->user(), $token);
        }

        return redirect()->intended(route('dashboard'));
    }
}
