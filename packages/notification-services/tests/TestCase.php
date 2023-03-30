<?php

namespace Celestine\NotificationServices\Tests;

use Celestine\NotificationServices\NotificationServiceProvider;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Test;
class TestCase extends Test
{
    public function setUp(): void
    {
        parent::setUp();

        File::deleteDirectory(base_path('app'));
    }

    protected function getPackageProviders($app)
    {
        return [NotificationServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app) : void
    {
        $app['config']->set('notification-services.webhook_url', 'https://webhook.site/8b148898-b27e-49a9-ad64-21e592ec77b3');
    }


}
