<?php

namespace App\Events;

use App\AuditResult;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AuditResultCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auditresults;
    public $isSendOwner;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(AuditResult $auditresults, $isSendOwner)
    {
        $this->auditresults = $auditresults;
        $this->isSendOwner = $isSendOwner;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
