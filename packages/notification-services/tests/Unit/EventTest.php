<?php

namespace Celestine\NotificationServices\Tests;

use Celestine\NotificationServices\Events\OrderStatusEvent;
use Celestine\NotificationServices\Listeners\OrderStatusListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

class EventTest extends TestCase
{
    /** @test */
    public function test_notification_can_be_sent()
    {
        Event::fake();

        OrderStatusEvent::dispatch("f2a564fb-3949-4ce4-a9c8-61883eada8dc", 'cancelled', now()->toString());

        Event::assertDispatched(OrderStatusEvent::class, function ($event) {
            return $event->getStatus() === 'cancelled';
        });
    }
}
