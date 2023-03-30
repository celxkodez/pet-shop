<?php

namespace Celestine\NotificationServices;

use Celestine\NotificationServices\Events\OrderStatusEvent;
use Celestine\NotificationServices\Listeners\OrderStatusListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Event;

class NotificationServiceProvider extends EventServiceProvider
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
        $config = realpath(__DIR__ . '/../resources/config/notification-services.php');

        $this->publishes([
            $config => config_path('notification-services.php')
        ]);

        Event::listen(OrderStatusEvent::class, [
            OrderStatusListener::class, 'handle'
        ]);
    }

}
