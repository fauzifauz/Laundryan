<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public ?array $pipelineCounts;

    /**
     * Create a new event instance.
     */
    public function __construct(\App\Models\Order $order, ?array $pipelineCounts = null)
    {
        $this->order = $order;
        $this->pipelineCounts = $pipelineCounts;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.' . $this->order->id),
            new PrivateChannel('karyawan.orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'order_code' => $this->order->order_code,
            'counts' => $this->pipelineCounts,
        ];
    }
}
