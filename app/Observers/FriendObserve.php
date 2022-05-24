<?php

namespace App\Observers;

use App\Models\Relation;
use App\Models\User;
use App\Notifications\NotificationFeedBackAddFriend;
use Auth;

class FriendObserve
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
