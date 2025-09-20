<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class HandleLoginErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 檢查是否是登入請求且返回了錯誤
        if ($request->is('login') && $request->isMethod('POST')) {
            // 如果 session 中有錯誤，我們已經處理了
            if (Session::has('errors')) {
                return $response;
            }

            // 如果沒有用戶認證，添加錯誤訊息
            if (!auth()->check()) {
                Session::flash('errors', [
                    'email' => ['登入失敗，請檢查您的電子郵件和密碼是否正確']
                ]);
            }
        }

        return $response;
    }
}
