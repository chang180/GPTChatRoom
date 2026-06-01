<?php

namespace App\Http\Responses;

use App\Support\PendingChatRoomInvitation;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request)
    {
        return PendingChatRoomInvitation::completeAfterAuthentication($request);
    }
}
