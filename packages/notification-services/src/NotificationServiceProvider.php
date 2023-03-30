<?php

namespace Celestine\NotificationServices;

use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Event
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(EventServiceProvider::class);
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
    }

}
