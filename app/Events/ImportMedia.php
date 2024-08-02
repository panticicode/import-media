<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportMedia implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $summary;
    /**
     * Create a new event instance.
     */
    public function __construct($summary)
    {
        $this->summary = $summary;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('import-media'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'summary' => $this->summary
        ];
    }
}
