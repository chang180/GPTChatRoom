<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserAdminRequest;
use App\Models\User;
use App\Services\AdminGuard;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'users' => User::query()
                ->orderBy('id')
                ->paginate(20)
                ->through(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->isAdmin(),
                    'created_at' => $user->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function update(UpdateUserAdminRequest $request, User $user, AdminGuard $adminGuard): RedirectResponse
    {
        $isAdmin = $request->boolean('is_admin');

        if (! $isAdmin && ! $adminGuard->canDemote($user)) {
            return back()->with('error', '系統必須保留至少一位管理員。');
        }

        $user->forceFill(['is_admin' => $isAdmin])->save();

        return back()->with('success', '使用者權限已更新。');
    }
}
