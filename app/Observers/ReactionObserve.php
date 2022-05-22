<?php

namespace App\Observers;

use App\Models\Reaction;
use App\Models\User;
use Auth;

class ReactionObserve
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
    /**
     * Handle the Reaction "updated" event.
     *
     * @param  \App\Models\Reaction  $reaction
     * @return void
     */
    public function updated(Reaction $reaction)
    {
        //
    }

    /**
     * Handle the Reaction "deleted" event.
     *
     * @param  \App\Models\Reaction  $reaction
     * @return void
     */
    public function deleted(Reaction $reaction)
    {
        //
    }

    /**
     * Handle the Reaction "restored" event.
     *
     * @param  \App\Models\Reaction  $reaction
     * @return void
     */
    public function restored(Reaction $reaction)
    {
        //
    }

    /**
     * Handle the Reaction "force deleted" event.
     *
     * @param  \App\Models\Reaction  $reaction
     * @return void
     */
    public function forceDeleted(Reaction $reaction)
    {
        //
    }
}
