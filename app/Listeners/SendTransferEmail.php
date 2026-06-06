<?php

namespace App\Listeners;

use App\Events\TransferCompletedEvent;
use App\Mail\TransferForRecievedMail;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendTransferEmail implements ShouldQueue
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
       $receiver = Customer::findOrFail($transfer->receiver_id);

         $sender = Customer::findOrFail($transfer->sender_id);

        Mail::to($receiver->email)->send(
            new TransferForRecievedMail($sender, $receiver,$transfer->amount)
        );
    }
}
