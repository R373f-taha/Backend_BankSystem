<?php

namespace App\Events;

use App\Models\AccountRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountRequestCreatedEvent
{
    use Dispatchable, SerializesModels;

    public $accountRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(AccountRequest $accountRequest)
    {
        $this->accountRequest = $accountRequest;
    }
}