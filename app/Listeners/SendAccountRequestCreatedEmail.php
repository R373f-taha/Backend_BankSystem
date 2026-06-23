<?php

namespace App\Listeners;

use App\Events\AccountRequestCreatedEvent;
use App\Mail\AccountRequestCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendAccountRequestCreatedEmail implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(AccountRequestCreatedEvent $event): void
    {
        $accountRequest = $event->accountRequest;

        if ($accountRequest && $accountRequest->email) {
            Mail::to($accountRequest->email)->send(new AccountRequestCreatedMail($accountRequest));
        }
    }
}