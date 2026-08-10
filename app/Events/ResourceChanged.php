<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ResourceChanged implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public string $resource;
    public string $action;
    public array $data;
    public string $timestamp;

    public function __construct(string $resource, string $action, array $data)
    {
        $this->resource = $resource;
        $this->action = $action;
        $this->data = $data;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn(): Channel
    {
        return new Channel('mi-stock');
    }

    public function broadcastAs(): string
    {
        return 'resource.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'resource' => $this->resource,
            'action' => $this->action,
            'data' => $this->data,
            'timestamp' => $this->timestamp,
        ];
    }
}
