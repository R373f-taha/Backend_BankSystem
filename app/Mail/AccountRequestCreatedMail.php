<?php

namespace App\Mail;

use App\Models\AccountRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountRequestCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $accountRequest;

    /**
     * Create a new message instance.
     */
    public function __construct(AccountRequest $accountRequest)
    {
        $this->accountRequest = $accountRequest;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📩 Your Account Request has been Received - Appointment Details',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-created',
        );
    }
}