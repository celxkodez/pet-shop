<?php

namespace Celestine\NotificationServices\Listeners;

use Celestine\NotificationServices\Events\OrderStatusEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

class OrderStatusListener
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
    public function handle(OrderStatusEvent $event): void
    {
        $uuid = $event->getUUID();
        $status = $event->getStatus();
        $time = $event->getTime();

        $messageBuild = "Order Status With $uuid has been updated with $status at $time";

        $message = [
            "@context" => "http://schema.org/extensions",
            "@type" => "MessageCard",
            "themeColor" => "0072C6",
            "title" => "Order Status Update",
            "text" => $messageBuild
        ];

        $headers = [
            "Content-Type" => "application/json"
        ];

        Http::withHeaders($headers)
            ->post(config('notification-services.webhook_url'), $message);
    }
}
