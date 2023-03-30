<?php

namespace Celestine\NotificationServices\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected string $order_uuid;
    protected string $status;
    protected $time;

    /**
     * Create a new event instance.
     */
    public function __construct(string $order_uuid, string $status, $time = null)
    {
        $this->order_uuid = $order_uuid;
        $this->status = $status;
        $this->time = $time ?? now()->toString();
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function getUUID(): string
    {
        return $this->order_uuid;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
