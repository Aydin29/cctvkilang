<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CctvStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $statistics;
    public $streamingStats;
    public $systemStatus;

    /**
     * Create a new event instance.
     */
    public function __construct($statistics, $streamingStats, $systemStatus)
    {
        $this->statistics = $statistics;
        $this->streamingStats = $streamingStats;
        $this->systemStatus = $systemStatus;
    }

    /**
     * Channel untuk broadcast.
     */
    public function broadcastOn()
    {
        return new Channel('dashboard');
    }

    /**
     * Custom nama event di frontend.
     */
    public function broadcastAs()
    {
        return 'cctv.status.updated';
    }

    /**
     * Data yang akan dibroadcast.
     */
    public function broadcastWith()
    {
        return [
            'statistics'     => $this->statistics,
            'streamingStats' => $this->streamingStats,
            'systemStatus'   => $this->systemStatus,
        ];
    }
}
