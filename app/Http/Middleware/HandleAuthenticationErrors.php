<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class HandleAuthenticationErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 檢查是否有認證錯誤
        if ($request->session()->has('errors')) {
            $errors = $request->session()->get('errors');
            
            // 檢查是否有認證相關錯誤
            if ($errors->has('email') || $errors->has('password')) {
                // 添加友好的錯誤訊息
                $request->session()->flash('error', '登入失敗，請檢查您的電子郵件和密碼是否正確。');
            }
        }

        // 檢查是否有認證失敗的錯誤
        if ($request->session()->has('login.error')) {
            $request->session()->flash('error', '登入失敗，請檢查您的電子郵件和密碼是否正確。');
        }

        return $response;
    }
}
