<?php

namespace App\Services;

class AuthenticationResponse
{
    /**
     * Create a new authentication response instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle login response
     */
    public function toResponse($request, $user = null)
    {
        if ($user) {
            // 登入成功
            return redirect()->intended(route('chat'));
        }

        // 登入失敗 - 返回錯誤訊息
        return back()->withErrors([
            'email' => ['登入失敗，請檢查您的電子郵件和密碼是否正確'],
        ])->onlyInput('email');
    }
}
