<?php

namespace App\Listeners;

use App\Events\TransferCompletedEvent;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTransferNotification
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
    public function handle(TransferCompletedEvent $event): void
    {
        $transfer=$event->transfer;
        $senderName=User::where('id',$transfer->sender_id)->first();
        $recieverName=User::where('id',$transfer->receiver_id)->first();

        Notification::create([
         'user_id'=>$transfer->sender_id,
          'title'=>'Transfer Sent Successfully',
          'message' => "Your transfer of $" . $transfer->amount. " to {$recieverName} has been completed successfully.",
            'is_read' => false
        ]);


         Notification::create([
            'user_id' => $transfer->receiver_id,
            'title' => 'Transfer Received',
            'message' => "You have received $" . $transfer->amount. " from {$senderName}.",
            'is_read' => false
        ]);
    }
}
