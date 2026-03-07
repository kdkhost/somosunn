<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceVisitRegistered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $serviceType;
    public ?int $serviceId;
    public int $count;

    public function __construct(string $serviceType, ?int $serviceId, int $count)
    {
        $this->serviceType = $serviceType;
        $this->serviceId = $serviceId;
        $this->count = $count;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('service-visits');
    }

    public function broadcastAs(): string
    {
        return 'service.visit.registered';
    }

    public function broadcastWith(): array
    {
        return [
            'serviceType' => $this->serviceType,
            'serviceId' => $this->serviceId,
            'count' => $this->count,
        ];
    }
}
