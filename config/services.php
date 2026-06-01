<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),

        // 本機（local）一律關閉；非 local 才看 GOOGLE_OAUTH_ENABLED 與憑證是否齊全（ADR-007）。
        // 註：此處用 env('APP_ENV') 而非 app()->environment()，因 config 於 LoadConfiguration
        // 階段載入時 $app['env'] 尚未綁定，呼叫會丟出 "Target class [env]"。
        'enabled' => env('APP_ENV', 'production') !== 'local'
            && filter_var(env('GOOGLE_OAUTH_ENABLED', false), FILTER_VALIDATE_BOOL)
            && filled(env('GOOGLE_CLIENT_ID'))
            && filled(env('GOOGLE_CLIENT_SECRET')),

        'disabled_reason' => 'Google 登入／註冊僅在已佈署環境提供。本機開發請使用電子郵件與密碼；若需測試 Google 流程，請於 staging／production 環境操作。',
    ],

];
