<?php

namespace Celestine\NotificationServices;

use Celestine\NotificationServices\Events\OrderStatusEvent;
use Celestine\NotificationServices\Listeners\OrderStatusListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Event
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        Event::listen(OrderStatusEvent::class, [
            OrderStatusListener::class, 'handle'
        ]);
    }

}
