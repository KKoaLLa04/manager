<?php

namespace App\Listeners;

use App\Events\CreateNotification;
use App\Models\UserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateNotificationListener
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
    public function handle(CreateNotification $event): void
    {
        $data = $event->notifications;
        UserNotification::query()->insert($data);
    }
}
