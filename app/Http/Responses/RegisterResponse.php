<?php

namespace App\Http\Responses;

use App\Support\PendingChatRoomInvitation;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request)
    {
        return PendingChatRoomInvitation::completeAfterAuthentication($request);
    }
}
