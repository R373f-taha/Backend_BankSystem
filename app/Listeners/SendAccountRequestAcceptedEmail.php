<?php

namespace App\Listeners;


use App\Events\AccountRequestAcceptedEvent;
use App\Mail\AccountRequestAcceptedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendAccountRequestAcceptedEmail implements ShouldQueue
{


    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AccountRequestAcceptedEvent $event): void
    {
        $accountRequest=$event->accountRequest;

        if($accountRequest->email){

            Mail::to($accountRequest->email)->send(new AccountRequestAcceptedMail($accountRequest));
        }
    }
}
