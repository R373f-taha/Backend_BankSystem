<?php

namespace App\Providers;

use App\Events\AccountRequestAcceptedEvent;
use App\Events\ArticlePublished;
use App\Events\TransferCompletedEvent;
use App\Listeners\SendAccountRequestAcceptedEmail;
use App\Listeners\SendArticlePublishedNotification;
use App\Listeners\SendTransferEmail;
use App\Listeners\SendTransferNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\WithdrawalCompletedEvent;
use App\Listeners\SendWithdrawalNotification;


class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        AccountRequestAcceptedEvent::class => [
            SendAccountRequestAcceptedEmail::class,
        ],

        TransferCompletedEvent::class => [
            SendTransferNotification::class,
            SendTransferEmail::class
        ],
        WithdrawalCompletedEvent::class => [

            SendWithdrawalNotification::class,

        ],
    ];
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
