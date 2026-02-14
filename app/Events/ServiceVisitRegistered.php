<?php
// UTF-8 sem BOM
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceVisitRegistered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $serviceType;
    public $serviceId;
    public $count;

    public function __construct($serviceType, $serviceId, $count)
    {
        $this->serviceType = $serviceType;
        $this->serviceId = $serviceId;
        $this->count = $count;
    }

    public function broadcastOn()
    {
        return new Channel('service-visits');
    }

    public function broadcastWith()
    {
        return [
            'serviceType' => $this->serviceType,
            'serviceId' => $this->serviceId,
            'count' => $this->count,
        ];
    }
}
