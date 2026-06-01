<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Dark Mode Script - 在頁面載入時立即設定正確的主題 -->
        <script>
            (function() {
                try {
                    const savedTheme = localStorage.getItem('darkMode');
                    let isDark = false;
                    
                    if (savedTheme !== null) {
                        isDark = savedTheme === 'true';
                    } else {
                        // 檢查系統偏好
                        isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    }
                    
                    // 立即應用主題
                    if (isDark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (e) {
                    // 如果出現錯誤，預設為淺色模式
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        {{-- Echo 僅在後端 ably + ABLY_KEY 就緒時啟用（與 config/broadcasting.php client_enabled 一致） --}}
        <script>
            window.__broadcasting = @json(['enabled' => (bool) config('broadcasting.client_enabled')]);
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
