<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransferForRecievedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sender, $receiver,$amount;
    /**
     * Create a new message instance.
     */
    public function __construct(Customer $sender,Customer $receiver,$amount)
    {
         $this->sender=$sender;
         $this->receiver=$receiver;
         $this->amount=$amount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
             subject: "You've Received a Transfer! - ". config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.transfer-received',
            with:[
                'senderName'=>$this->sender->user->name,
                'receiverName'=>$this->receiver->user->name,
                'amount'=>$this->amount,
                'date'=>now()->format('F j, Y H:i'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
