<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnlinkGoogleAccountRequest;
use App\Models\User;
use App\Support\PendingChatRoomInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * 導向 Google OAuth 同意畫面（未登入：註冊／登入）。
     */
    public function redirect(): RedirectResponse
    {
        abort_unless(config('services.google.enabled'), 404);

        return Socialite::driver('google')->redirect();
    }

    /**
     * 由已登入使用者發起綁定 Google 帳號。
     */
    public function linkRedirect(): RedirectResponse
    {
        abort_unless(config('services.google.enabled'), 404);

        return Socialite::driver('google')->redirect();
    }

    /**
     * 處理 Google 回呼：依登入狀態決定登入／註冊（ADR-004）或綁定。
     */
    public function callback(): RedirectResponse
    {
        abort_unless(config('services.google.enabled'), 404);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()->route('login')->with('error', 'Google 登入失敗，請再試一次。');
        }

        if (Auth::check()) {
            return $this->linkToCurrentUser($googleUser);
        }

        // 1. 已綁定 google_id → 直接登入。
        $user = User::where('google_id', $googleUser->getId())->first();
        if ($user) {
            Auth::login($user, true);

            return PendingChatRoomInvitation::completeAfterAuthentication(request());
        }

        // 2. email 已存在但未綁定 → 不建立新帳號（ADR-004）。
        if (User::where('email', $googleUser->getEmail())->exists()) {
            return redirect()->route('login')
                ->with('error', '此電子郵件已註冊，請先以電子郵件與密碼登入，再至個人設定綁定 Google 帳號。');
        }

        // 3. 建立新帳號（無密碼）並登入。
        $user = User::create([
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: $googleUser->getEmail(),
            'email' => $googleUser->getEmail(),
            'password' => null,
            'google_id' => $googleUser->getId(),
            'google_token' => $googleUser->token,
            'google_refresh_token' => $googleUser->refreshToken,
        ]);

        Auth::login($user, true);

        return PendingChatRoomInvitation::completeAfterAuthentication(request());
    }

    /**
     * 解除目前帳號的 Google 綁定（須保留其他登入方式，見 Form Request）。
     */
    public function unlink(UnlinkGoogleAccountRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null,
        ])->save();

        return redirect()->route('profile.show')->with('success', '已解除 Google 帳號綁定。');
    }

    /**
     * 將 Google 帳號綁定至目前登入使用者。
     */
    protected function linkToCurrentUser(SocialiteUser $googleUser): RedirectResponse
    {
        $owner = User::where('google_id', $googleUser->getId())->first();
        if ($owner && $owner->isNot(Auth::user())) {
            return redirect()->route('profile.show')->with('error', '此 Google 帳號已綁定其他使用者。');
        }

        Auth::user()->forceFill([
            'google_id' => $googleUser->getId(),
            'google_token' => $googleUser->token,
            'google_refresh_token' => $googleUser->refreshToken,
        ])->save();

        return redirect()->route('profile.show')->with('success', '已成功綁定 Google 帳號。');
    }
}
