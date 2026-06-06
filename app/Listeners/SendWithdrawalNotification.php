<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\WithdrawalCompletedEvent;
use App\Models\Notification;
class SendWithdrawalNotification
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
    public function handle(
        WithdrawalCompletedEvent $event
    ): void {

        Notification::create([
            'user_id' => $event->customer->user_id,

            'title' => 'Withdrawal Completed',

            'message' =>
                "Your withdrawal of $" .
                $event->transaction->amount .
                " has been completed successfully.",

            'is_read' => false
        ]);
    }
}
