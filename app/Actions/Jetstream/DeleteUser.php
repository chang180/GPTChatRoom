<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use App\Services\AdminGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        $adminGuard = app(AdminGuard::class);

        if (! $adminGuard->canDelete($user)) {
            throw ValidationException::withMessages([
                'password' => ['無法刪除帳號：系統必須保留至少一位管理員。'],
            ]);
        }

        $user->deleteProfilePhoto();
        $user->tokens()->delete();
        User::destroy($user->getKey());
    }
}
