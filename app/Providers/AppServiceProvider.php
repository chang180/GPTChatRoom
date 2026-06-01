<?php

namespace App\Providers;

use App\Models\ChatRoom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('chatRoom', function (string $value): ChatRoom {
            return ChatRoom::query()
                ->whereKey($value)
                ->where('type', ChatRoom::TYPE_PRIVATE_GROUP)
                ->firstOrFail();
        });

        Inertia::share([
            'auth' => function () {
                return [
                    'user' => Auth::user(),
                ];
            },
        ]);
    }
}
