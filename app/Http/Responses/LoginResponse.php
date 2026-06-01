<?php

namespace App\Http\Responses;

use App\Support\PendingChatRoomInvitation;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request)
    {
        return PendingChatRoomInvitation::completeAfterAuthentication($request);
    }
}
